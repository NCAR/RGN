<?php

/**
 * @brief ganglia host name fixer
 * @param $host hostname in raw form
 * @return ganglia version of hostname
 */
function to_ganglia_host($SETUP, $host)
{
    if($SETUP['cluster']['config']["ganglia"]["hostname-fix"])
	return preg_replace("/-ib$/", "", $host);

    return $host;
}

/**
 * @brief ganglia host name fixer (reverser)
 * @param $host hostname in raw form
 * @return ganglia version of hostname
 */
function from_ganglia_host($SETUP, $host)
{
    if($SETUP['cluster']['config']["ganglia"]["hostname-fix"])
	return preg_replace("/$/", "-ib", $host);

    return $host;
}

/**
 * @brief create rrdsockets for ganglia data
 *  creates all sockets at once for paralel
 */ 
function ganglia_create_rrdsockets($SETUP, &$sockets)
{
    $sockets = array();

    foreach($SETUP['cluster']['config']['ganglia']['rrdtool']['daemon'] as $remote)
    {
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if($socket === false)
	    die("socket_create() failed: reason: " . socket_strerror(socket_last_error()));

	$r = explode(":", $remote); 

	#echo "connect $remote\n<br>";
	$result = socket_connect($socket, $r[0], $r[1]);
	if($result === false) 
	    die("socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)));

	#make sure everything is timely
	socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>15, "usec"=>0));

	$sockets[$remote] = $socket;
    }

    return $sockets;
}

/**
 * @brief send cmd to rrdsocket 
 * @param socket php socket
 * @param cmd text command to send to rrd
 */ 
function ganglia_sendcmd_rrdsocket($SETUP, &$socket, $cmd)
{
    if(!is_resource($socket)) die("invalid socket $socket");
    socket_write($socket, $cmd . "\r\n");
    #syslog(LOG_INFO, "send socket:". $cmd);
}    

/**
 * @brief send cmd to all rrdsockets 
 * @param sockets array of the php sockets
 * @param cmd text command to send to rrds
 */ 
function ganglia_sendcmd_rrdsockets($SETUP, &$sockets, $cmd)
{
    foreach($sockets as $remote => &$socket)
        ganglia_sendcmd_rrdsocket($SETUP, $socket, $cmd);
} 

/**
 * @brief close all rrdsockets 
 * @param sockets array of the php sockets
 */ 
function ganglia_close_rrdsockets($SETUP, &$sockets)
{
    foreach($sockets as $remote => &$socket)
	if(is_resource($socket))
	{
	    #make sure the socket is closed with out question
	    socket_shutdown($socket, 2);
	    socket_set_block($socket);
	    socket_set_option($socket, SOL_SOCKET, SO_LINGER, array(
		'l_onoff' => 1, 
		'l_linger' => 1
	    ));
	    socket_close($socket);
	}

    #clear the array since everything in it is now dead
    $sockets = array();
} 

/**
 * @brief read cmd results from 1 rrdsocket
 * @param socket socket to read from
 * @param result string returned by rrd
 *  rrd has a response format for all commands
 */ 
function ganglia_readcmd_rrdsocket($SETUP, &$socket, &$result)
{
    if(!is_resource($socket)) die("invalid socket $socket");

    $matches = array();
    while($out = socket_read($socket, 2048, PHP_NORMAL_READ))
    {
	#syslog(LOG_INFO, "read from socket:". $out);

	if(preg_match("/^OK u:(\d+.\d+) s:(\d+.\d+)( r:\d+.\d+|)/", $out))
	    return true;

	$result[] = $out;

	if(preg_match("/^ERROR: /", $out))
	{
	    syslog(LOG_ERR, "read error from socket:". $out);
	    return false;
	}
    } 
    return false;
}  

/**
 * @brief read cmd results from rrdsockets
 * @param sockets sockets to read from
 *  verify OK recieved on all sockets
 */ 
function ganglia_verifycmds_rrdsocket($SETUP, &$sockets)
{
    foreach($sockets as $r => $socket)
	if(!ganglia_readcmd_rrdsocket($SETUP, $socket, $result))
	    return false;

    return true;
}  

/**
 * @brief Get list of Ganglia Hosts known to a each rrd server
 * @warn assumes that host names are unique in request
 * @param $SETUP
 * @param $regex regex to test hosts against
 * @return array of [$remote][] = host names
 */
function get_ganglia_rrd_hosts(&$SETUP, $regex)
{
    $names = array();

    /**
     * SGI's Setup of Ganglia is a cluster per rack
     * so allow the config to list out each ganglia cluster
     * and scan them all instead of assuming there is a single
     * cluster
     */
    if(is_array($SETUP['cluster']['config']['ganglia']['name']))
	$names = $SETUP['cluster']['config']['ganglia']['name'];
    else #simple setup with just one cluster
	$names[] = $SETUP['cluster']['config']['ganglia']['name'];

    if(!isset($SETUP['cluster']['config']['ganglia']['queryhosts']))
    { #only query ganglia once for all hosts
	$allhosts = array(); #allhosts[rrdsrv host][cluster name] = array(host1, ...)
	$allrhosts = array(); #reverse lookup for rrdsrv  allrhosts=array(host1 => rrdsrv, ) 

	ganglia_create_rrdsockets($SETUP, $sockets);

	foreach($names as $name)
	{
	    $wsockets = array(); #sockets where cd works
	    ganglia_sendcmd_rrdsockets($SETUP, $sockets, "cd \"".$name."\"");

	    #filter out list of sockets that have the directory
	    $result = array();
	    foreach($sockets as $r => $socket)
		if(ganglia_readcmd_rrdsocket($SETUP, $socket, $result))
		    $wsockets[$r] = &$socket;

	    #Don't assume each Ganglia has every cluster
	    if(!empty($wsockets))
	    {
		ganglia_sendcmd_rrdsockets($SETUP, $wsockets, "ls");

		foreach($wsockets as $r => &$socket)
		{
		    $result = array();
		    if(!ganglia_readcmd_rrdsocket($SETUP, $socket, $result))
			die("unable to read ls response from rrd");

		    foreach($result as $out)
		    {
			if(preg_match("/^d (.+)$/", $out, $matches))
			{
			    $hname = $matches[1];
			    
			    if($hname != "__SummaryInfo__")
			    {
				$allhosts[$r][$name][] = $hname;
				$allrhosts[$hname] = array(
				    'srv'	=> $r,
				    'cluster'	=> $name
				);
			    }
			} 
		    }
		}

		ganglia_sendcmd_rrdsockets($SETUP, $wsockets, "cd ..");
		if(!ganglia_verifycmds_rrdsocket($SETUP,$wsockets))
		    die("unable to chdir on rrd");
	    }
	}

	ganglia_close_rrdsockets($SETUP, $sockets);

	$SETUP['cluster']['config']['ganglia']['queryhosts'] = &$allhosts;
	$SETUP['cluster']['config']['ganglia']['queryrhosts'] = &$allrhosts;
    }

    $hosts = array();
    foreach($SETUP['cluster']['config']['ganglia']['queryhosts'] as $r => &$rinst)
    {
	#syslog(LOG_INFO, "found rrdsrv: $r hosts: ".join(',', $hl));
	foreach($rinst as $host => $hinst)
	    foreach($hinst as $path)
		if(empty($regex) || preg_match('/'.$regex.'/', $path))
		    $hosts[] = $path;
    }

    return array_unique($hosts, SORT_STRING);
}        
                                     
/**
 * @brief Get list of Ganglia Hosts
 * @param $SETUP
 * @param $regex regex to test hosts against
 * @return array of host names
 */
function get_ganglia_hosts(&$SETUP, $regex)
{
    $uhosts = array();

    if(isset($SETUP['cluster']['config']['ganglia']['rrdtool']['daemon']) 
	&& $SETUP['cluster']['config']['ganglia']['rrdtool']['daemon'] != ""
    )
    { #query rrd sockets
	$uhosts = get_ganglia_rrd_hosts($SETUP, $regex);
    }
    else #use local fs
    {
	$hosts = array();
	$d = dir($SETUP['cluster']['config']['ganglia']['rrds'].'/'.$SETUP['cluster']['config']["ganglia"]["name"]);

	while (false !== ($entry = $d->read())) 
	{
	    if($entry != '.' && $entry != '..')
	    {
		$host = from_ganglia_host($SETUP, $entry);

		if(!$regex || preg_match('/'.$regex.'/', $host))
		    $hosts[] = $host;
	    }
	}
	$d->close();

	$uhosts = array_unique($hosts, SORT_STRING);
	syslog(LOG_INFO, "found locahost rrd hosts: ".join(',', $uhosts));
    }

    #syslog(LOG_INFO, "ganglia hosts: ". json_encode($uhosts));

    return $uhosts;
}

/**
 * @brief Load Ganglia RRD data
 * @param $what array of nodes => sensors to load
 * @param $data data to fill out with given host/sensor names
 */
function gangliarrd($SETUP, $what, &$data, &$timestamps)
{
    $rsrv = false;
    if(isset($SETUP['cluster']['config']['ganglia']['rrdtool']['daemon']) && $SETUP['cluster']['config']['ganglia']['rrdtool']['daemon'] != "")
	$rsrv = true;

    $rrddata = array();

    #get the raw data
    if($rsrv)
	$data = rrdraw_socket($SETUP, $timestamps, $what, $xml);
    else #use local bin
    {
	$rrds = array();
        $vnames = array();
	$vid = 0;

	#make list of all rrds to load and mangle the names
	foreach($what as $rawhost => $sensors)
	{
	    $host = to_ganglia_host($SETUP, $rawhost);
     
	    foreach($sensors as $sensor)
	    {
		$vname = "vname".++$vid;
		if($rsrv)
		    $rrds[$vname] = $SETUP['cluster']['config']["ganglia"]["name"].'/'.$host.'/'.$sensor.'.rrd';
		else
		    $rrds[$vname] = $SETUP['cluster']['config']['ganglia']['rrds'].'/'.$SETUP['cluster']['config']["ganglia"]["name"].'/'.$host.'/'.$sensor.'.rrd';
		$vnames[$rawhost][$sensor] = $vname;
	    }
	}

	$rrddata = rrdraw_local($SETUP, $rrds, $xml);

	#renormalize the data
	rrddata($SETUP, $xml, $rrddata, $timestamps);

	#unmangle the names and load the data array
	foreach($what as $host => $sensors)
	{
	    $data[$host] = array();

	    foreach($sensors as $sensor)
		$data[$host][$sensor] = $rrddata[$vnames[$host][$sensor]];
	} 
    }
}

/**
 * @brief rrd raw data loader via local rrd bin
 * Loads the given list of rrds into xml
 * @param $what array with sensor name => rrd file
 * @param $xml rrd xport output
 */
function rrdraw_local($SETUP, $what, &$xml)
{
    $cmd = array(
	"xport",
	"-s", $SETUP['start_time'],
	"-e", $SETUP['end_time'],
	"--step", max(1, round($SETUP['chart']['tslices'] / 2))
    );
    
    foreach($what as $key => $file)
    {
        $cmd[] = "DEF:".$key."=".$file.":sum:AVERAGE";
	$cmd[] = "XPORT:".$key.":".$key;
    }

    $out = "";
    $err = "";

    $cmdtxt = $SETUP['cluster']['config']['ganglia']['rrdtool']['path'];
    foreach($cmd as $op)
	$cmdtxt .= ' '.escapeshellarg((string) $op);

    #var_dump($cmdtxt);die;
    $proc = proc_open($cmdtxt, array(
	    0 => array("pipe", "0"),
	    1 => array("pipe", "w"),
	    2 => array("pipe", "w")
	), $pipes, $SETUP['cluster']['config']['ganglia']['rrdtool']['cwd'], NULL
    );

    if(is_resource($proc)) 
    {
	fclose($pipes[0]);
        $out = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
	proc_close($proc);
    }
    else
	die("unable to exec rrdtool correctly");

    if($out === FALSE || $out == "")
    {
	debug_print_backtrace();
	var_dump(array(
		'what' => $what,
		'cmdtxt' => $cmdtxt,
		'error' => $err
	));
	die("rrdtool did not return anything.");
    }

    $xml = simplexml_load_string($out);
}

/**
 * @brief rrd raw data loader via sockets
 * Loads the given list of rrds into xml
 * @param $what array with sensor name => rrd file
 * @param $xml rrd xport output
 */
function rrdraw_socket(&$SETUP, &$timestamps, $what, &$xml)
{
    $sockets = array();
    ganglia_create_rrdsockets($SETUP, $sockets);

    $cmd = array(
	"xport",
	"-s", $SETUP['start_time'],
	"-e", $SETUP['end_time'],
	"--step", max(1, round($SETUP['chart']['tslices'])),
	"--maxrows", $SETUP['chart']['slices']
    );

    #figure out which cmds go to which rrd server in batches
    $cmds = array();
    $cmdscount = array();
    $maxcmds = 200;

    #make list of all rrds to load and mangle the names
    $vid = 0;
    $vnames = array();
    foreach($what as $rawhost => &$sensors)
    {
	$host = to_ganglia_host($SETUP, $rawhost);
	$srv = NULL;  #Ganglia RRD Server
	$rcname = NULL; #Ganglia Cluster (top dir containing rrds)

	#find host in rrd servers list
	foreach($SETUP['cluster']['config']['ganglia']['queryrhosts'] as $rhost => $rdata)
	    if($rhost == $host)
	    {
		$srv	= $rdata['srv'];
		$rcname = $rdata['cluster'];
	    }

	if($srv == NULL)
	    die("rrd server not found for $host");
 	if(empty($host))
	    die("unexpected empty host for rrd");
 
	syslog(LOG_INFO, "rrd $srv -> $host sensors: ".join(' ', $sensors));
	foreach($sensors as &$sensor)
	{
	    $vname = "vname".++$vid;

	    #split everything into a run
	    if(!isset($cmdscount[$srv]['count']))
	    {
		$cmdscount[$srv]['count'] = 0;
		$cmdscount[$srv]['run'] = 0;
		$cmdscount[$srv]['sent'] = false;
		$cmdscount[$srv]['wait'] = false;
	    }
	    if(++$cmdscount[$srv]['count'] > $maxcmds)
	    {
		++$cmdscount[$srv]['run'];
		$cmdscount[$srv]['count'] = 0;
	    }

	    #add default start of each command
	    if(!isset($cmds[$srv][$cmdscount[$srv]['run']]))
		$cmds[$srv][$cmdscount[$srv]['run']] = $cmd;

	    #dump the cmd for the sensor
	    $file = '"'.$rcname.'/'.$host.'/'.$sensor.'.rrd"';
	    $cmds[$srv][$cmdscount[$srv]['run']][] = "DEF:".$vname."=".$file.":sum:AVERAGE";
	    $cmds[$srv][$cmdscount[$srv]['run']][] = "XPORT:".$vname.":".$vname;            
	    $vnames[$vname]['host'] = $rawhost;
	    $vnames[$vname]['sensor'] = $sensor;
	}
    }

    #var_dump($cmds);die;
    #var_dump($cmdscount);die;
    foreach($cmds as $srv => &$cmdsa)
	foreach($cmdsa as $id => &$cmd)
	    syslog(LOG_INFO, "rrd $srv cmd $id:".join(' ', $cmd));

    #send the cmds to rrd servers in order until all have returned
    $data = array();
    $toparse = array();
    $done = false;
    do
    {
	$done = true;
	$lastsrv = false;

	#send to any idle socket
	foreach($cmdscount as $srv => &$stat)
	{
	    if($stat['sent'] !== true)
	    {
		$done = false;
		if($stat['wait'] === false) 
		{
		    if($stat['sent'] === false)
			$stat['sent'] = 0;

		    if($stat['sent'] > $stat['run'])
		    {
			#all cmds send and recieved from this srv
			$stat['sent'] = true;
                        continue;
		    }

		    $cmdtxt = "";
		    foreach($cmds[$srv][$stat['sent']] as $op)
			$cmdtxt .= ' '.((string) $op);
			#$cmdtxt .= ' '.escapeshellarg((string) $op);

		    if(strlen($cmdtxt) == 0)
			die("cowardly refusing to send empty rrd command to $srv");

		    syslog(LOG_INFO, "send cmd to socket ".strlen($cmdtxt)." chars to $srv: \"$cmdtxt\"");
		    ganglia_sendcmd_rrdsocket($SETUP, $sockets[$srv], $cmdtxt);
		    #var_dump($cmdtxt);

		    $stat['wait'] = true;
		}
	    }
	}

	#parse returned data from last round while rrd is doing work on remote boxes
	#if only php had thread!
 	foreach($toparse as &$adata)
	{
	    syslog(LOG_INFO, "start process rrd from socket '".$adata['srv']."' length:".strlen($adata['data']));
	    if(strlen($adata['data']) < 200) #debug it if its too short
		syslog(LOG_ERR, "error: short response from socket '".$adata['srv']."' ( ".strlen($adata['data'])."characters): ". $adata['data']);

	    $rrddata = array();
	    $xml = simplexml_load_string(trim($adata['data']));

	    #var_dump($xml);

	    #renormalize the data
	    rrddata($SETUP, $xml, $rrddata, $timestamps); 

	    #unmangle the names and load the data array
	    foreach($rrddata as $vname => &$rdata)
		$data[$vnames[$vname]['host']][$vnames[$vname]['sensor']] = $rdata;

	    syslog(LOG_INFO, "finish process rrd from socket '".$adata['srv']."'.");
	}
	$toparse = array();

	#read responses
 	foreach($cmdscount as $srv => &$stat)
	{
	    if($stat['sent'] !== true && $stat['wait'] === true)
	    {
		$done = false;

                $result = array();
		syslog(LOG_INFO, "read from socket $srv");

		if(!ganglia_readcmd_rrdsocket($SETUP, $sockets[$srv], $result))
		{
		    syslog(LOG_ERR, "error: unable to read xport response from rrd '".$srv.".");
		    die("unable to read xport response from rrd");
		}
		$adata = array(
		    'data' => implode('', $result),
		    'srv' => $srv
		);
	        $toparse[] = $adata;

		$stat['wait'] = false;
		++$stat['sent'];
	    }
	} 
    }
    while(!$done);

    ganglia_close_rrdsockets($SETUP, $sockets);

    return $data;
}


?>
