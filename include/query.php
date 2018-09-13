<?php

/** 
 * @brief Set common values in query return array
 */ 
function query_set_common_params(&$raw, &$db, $SETUP)
{
    $raw['text']['batch_nodes'] = $SETUP['cluster']['config']['batch']['maxhosts'] ." Batch Nodes";
    $raw['text']['batch_cores'] = $SETUP['cluster']['config']['batch']['maxcpus'] ." Batch Threads";
}

function read_pbs_avail_log($SETUP)
{
    $keys = array(
	'time' => 0,
	'name' => 1,
	'nodes_up' => 2,
	'nodes_down' => 3,
	'nodes_busy' => 4,
	'cores_busy' => 5,
	'threads_busy' => 6,
	'rsv_sys' => 7,
	'rsv_user' => 8
    );


    $d = array();

    #total values
    init_timeline($d['nodes_up'], $SETUP, VOID);
    init_timeline($d['nodes_down'], $SETUP, VOID);
    init_timeline($d['nodes_busy'], $SETUP, VOID);
    init_timeline($d['cores_busy'], $SETUP, VOID);
    init_timeline($d['threads_busy'], $SETUP, VOID);
    init_timeline($d['rsv_sys'], $SETUP, VOID);
    init_timeline($d['rsv_user'], $SETUP, VOID);

    #total count of values
    init_timeline($dc['nodes_up'], $SETUP, VOID);
    init_timeline($dc['nodes_down'], $SETUP, VOID);
    init_timeline($dc['nodes_busy'], $SETUP, VOID);
    init_timeline($dc['cores_busy'], $SETUP, VOID);
    init_timeline($dc['threads_busy'], $SETUP, VOID);
    init_timeline($dc['rsv_sys'], $SETUP, VOID);
    init_timeline($dc['rsv_user'], $SETUP, VOID);

    $fh = fopen($SETUP['cluster']['config']['pbspro']['avail_log'], "r");
    while (!feof($fh)) {
	$line = fgets($fh);

	#ignore header line
	if(strncmp($line, "time", 4) == 0)
	    continue;

	#time name nodes_up nodes_down nodes_busy cores_busy threads_busy rsv_sys rsv_user
	$v = explode(" ", $line);

	$ts = round($v[$keys['time']]);
	if($ts < $SETUP['start_time'] || $ts > $SETUP['end_time'])
	    continue; #ignore data outside of time window

	#find nearest entry and average them

	#get the correct timestamp offset
	if($ts == $SETUP['start_time'])
	    $tso = 0;
	elseif($ts == $SETUP['end_time'])
	    $tso = $SETUP['chart']['slices'] - 1;
	else #not at ends, so find the correct slice and round down
	    $tso = round(($ts - $SETUP['start_time']) / $SETUP['chart']['tslices']);

	foreach(array_keys($d) as $key)
	{
	    $vd = $v[$keys[$key]];
	    $dts = &$d[$key][$tso];
	    $dtsc = &$dc[$key][$tso];

	    #var_dump(array($key, $tso, $vd, $dts, $dtsc));

	    if($dts == VOID)
	    {
		$dts = $vd;
		$dtsc = 1;
	    }
	    else
	    {
		$dts += $vd;
		$dtsc += 1;
	    }
	}

    }
    fclose($fh);

    #average out the data
    foreach(array_keys($d) as $key)
	div_timelines($d[$key], $dc[$key], $SETUP);

    return $d;
}

function query_util_sch_timeline($host, &$db, $SETUP)
{
    init_timeline_stamps($timestamps, $stamplabels, $SETUP);
    init_timeline($maxcpus, $SETUP, $SETUP['cluster']['config']['batch']['maxcpus']);

    $cpus = VOID;

    if(isset($SETUP['cluster']['config']['pbspro']))
    {
	$data = read_pbs_avail_log($SETUP);
	$cpus = $data['threads_busy'];
    }
    elseif(isset($SETUP['cluster']['config']['slurm'])) #slurm_cluster_stat.caldera_cpu_allocated
    {
	#get the actual host stats
	$ehosts = get_ganglia_hosts($SETUP, $host);
	if(count($ehosts) == 0) die("invalid host $host");

	$p = "slurm_cluster_stat.". $SETUP['cluster']['config']['slurm']['batch']['prefix'].'_cpu_allocated';

	#query all the rrd data at once
	$gquery = array();
	foreach($ehosts as $ehost)
	   $gquery[$ehost] = array(
	       $p
	   );

	gangliarrd($SETUP, $gquery, $rdata, $timestamps);
	unset($gquery);

	$cpus = $rdata[$host][$p];
    }

    max_limit_timeline($cpus, $SETUP['cluster']['config']['batch']['maxcpus'], $SETUP);

    $r = array(
	'series' => array(
	    'cpus' => $cpus,
	    'maxcpus' => $maxcpus,
	    'timestamps' => $timestamps,
	    'Labels' => $stamplabels,
 	    'YMax' => array($SETUP['cluster']['config']['batch']['maxcpus']),
	    'YMin' => array(0) 
	),
	'desc' => array(),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." Batch Utilization (".$SETUP['window_txt'].")"
	)
    ); 

    #var_dump($r);die;

    return $r;
}

function query_util_sch_timeline_master(&$db, $SETUP)
{
    if(isset($SETUP['cluster']['config']['pbspro']))
	return query_util_sch_timeline($SETUP['cluster']['config']['pbspro']['master'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['lsf']))
	return query_util_sch_timeline($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['slurm']))
	return query_util_sch_timeline($SETUP['cluster']['config']['slurm']['master'], $db, $SETUP);
} 

function query_util_sch_timeline_backup(&$db, $SETUP)
{
    if(isset($SETUP['cluster']['config']['pbspro']))
	return query_util_sch_timeline($SETUP['cluster']['config']['pbspro']['backup'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['lsf']))
	return query_util_sch_timeline($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['slurm']))
	return query_util_sch_timeline($SETUP['cluster']['config']['slurm']['backup'], $db, $SETUP);
}

function query_rsv_util_timeline(&$db, $SETUP)
{
    init_timeline_stamps($timestamps, $stampLabels, $SETUP);

    $host = NULL;
    $rsv_sys_name = NULL;
    $rsv_usr_name = NULL;

    if(isset($SETUP['cluster']['config']['pbspro']))
    {
	$host = $SETUP['cluster']['config']['pbspro']['master'];
	$rsv_sys_name = 'pbs_status.'.$SETUP['cluster']['config']['pbspro']['batch']['prefix'].'.rsv_sys';
	$rsv_usr_name = 'pbs_status.'.$SETUP['cluster']['config']['pbspro']['batch']['prefix'].'.rsv_user';
    }
    elseif(isset($SETUP['cluster']['config']['slurm']))
    {# slurm_cluster_stat.caldera_resv_sys
	$host = $SETUP['cluster']['config']['slurm']['master'];
	$rsv_sys_name = 'slurm_cluster_stat.'.$SETUP['cluster']['config']['slurm']['batch']['prefix'].'_resv_sys';
	$rsv_usr_name = 'slurm_cluster_stat.'.$SETUP['cluster']['config']['slurm']['batch']['prefix'].'_resv_user';
    }
    elseif(isset($SETUP['cluster']['config']['lsf']))
    {
	$host = $SETUP['cluster']['config']['lsf']['master'];
	$rsv_sys_name = $SETUP['cluster']['config']['lsf']['reservation']['system'];
	$rsv_usr_name = $SETUP['cluster']['config']['lsf']['reservation']['user'];
    }
 
    #get the actual host stats
    $ehosts = get_ganglia_hosts($SETUP, $host);

    #query all the rrd data at once
    $gquery = array();
    foreach($ehosts as $ehost)
	$gquery[$ehost] = array(
	    $rsv_sys_name,
	    $rsv_usr_name
	);

    #$var_dump($gquery);die;
    gangliarrd($SETUP, $gquery, $rdata, $timestamps);
    unset($gquery); 

    $sysrsv = $rdata[$host][$rsv_sys_name];
    $usrrsv = $rdata[$host][$rsv_usr_name];

    unset($rdata);

    div_timeline($sysrsv, $SETUP['cluster']['config']['batch']['maxhosts'], $SETUP);
    mul_timeline($sysrsv, 100, $SETUP);
    max_limit_timeline($sysrsv, 100, $SETUP);
    min_limit_timeline($sysrsv, 0, $SETUP);

    div_timeline($usrrsv, $SETUP['cluster']['config']['batch']['maxhosts'], $SETUP);
    mul_timeline($usrrsv, 100, $SETUP);
    max_limit_timeline($usrrsv, 100, $SETUP);
    min_limit_timeline($usrrsv, 0, $SETUP);

    $r = array(
	'series' => array(
	    'max_hosts' => $SETUP['cluster']['config']['batch']['maxhosts'],
	    'usr_rsv' => $usrrsv,
	    'sys_rsv' => $sysrsv,
	    'timestamps' => $timestamps,
	    'Labels' => $stampLabels,
 	    'YMax' => array(100),
	    'YMin' => array(0)  
	),
	'desc' => array(),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." Reservation Utilization (".$SETUP['window_txt'].")"
	)
    ); 

    return $r;
}
 

function query_util_stats_timeline(&$db, $SETUP)
{
    init_timeline_stamps($timestamps, $stampLabels, $SETUP);
    init_timeline($actcpus, $SETUP); ///actual cpus
    init_timeline($user, $SETUP); ///user cpu usage
    init_timeline($nice, $SETUP); ///nice cpu usage
    init_timeline($wio, $SETUP); ///wio cpu usage
    init_timeline($system, $SETUP); ///system cpu usage
    init_timeline($maxcpus, $SETUP, $SETUP['cluster']['config']['batch']['maxcpus']); 
    $scale = 100; 
 
    #get the actual host stats
    $ehosts = get_ganglia_hosts($SETUP, $SETUP['cluster']['config']['batch']['hosts']);

    #query all the rrd data at once
    $gquery = array();
    foreach($ehosts as $ehost)
	$gquery[$ehost] = array(
	    "cpu_num",
	    "cpu_user",
	    "cpu_nice",
	    "cpu_wio",
	    "cpu_system"
	);

    #echo json_encode(array('rrd hosts' => sizeof($ehosts), 'query count' => sizeof($gquery), 'query' => $gquery));die;
    gangliarrd($SETUP, $gquery, $rdata, $timestamps);
    unset($gquery); 

    $hosts_count = sizeof($rdata);

    #var_dump($rdata);die;
    foreach($rdata as $host => $dhost)
    {
	add_timelines($actcpus, $dhost["cpu_num"], $SETUP);

	min_limit_void_timeline($dhost["cpu_user"], 0, $SETUP);
	min_limit_void_timeline($dhost["cpu_nice"], 0, $SETUP);
	min_limit_void_timeline($dhost["cpu_wio"], 0, $SETUP);
	min_limit_void_timeline($dhost["cpu_system"], 0, $SETUP);

 	max_limit_void_timeline($dhost["cpu_user"], 100, $SETUP);
	max_limit_void_timeline($dhost["cpu_nice"], 100, $SETUP);
	max_limit_void_timeline($dhost["cpu_wio"], 100, $SETUP);
	max_limit_void_timeline($dhost["cpu_system"], 100, $SETUP);

	#ganglia stores as percent, convert to float
	div_timeline($dhost["cpu_user"], $scale, $SETUP);
 	div_timeline($dhost["cpu_nice"], $scale, $SETUP);
 	div_timeline($dhost["cpu_wio"], $scale, $SETUP);
 	div_timeline($dhost["cpu_system"], $scale, $SETUP); 

        #multipy against available cpus on node
        mul_timelines($dhost["cpu_user"], $dhost["cpu_num"], $SETUP);
        mul_timelines($dhost["cpu_nice"], $dhost["cpu_num"], $SETUP);
        mul_timelines($dhost["cpu_wio"], $dhost["cpu_num"], $SETUP);
        mul_timelines($dhost["cpu_system"], $dhost["cpu_num"], $SETUP);

	#add up aggregates
	add_timelines($user, $dhost["cpu_user"], $SETUP);
	add_timelines($nice, $dhost["cpu_nice"], $SETUP);
	add_timelines($wio, $dhost["cpu_wio"], $SETUP);
	add_timelines($system, $dhost["cpu_system"], $SETUP);
    }

#   echo json_encode(array(
#          'hosts_count' => $hosts_count,
#          'maxcpus' => $maxcpus,
#          'actcpus' => $actcpus,
#          'usercpus' => $user,
#          'nicecpus' => $nice,
#          'wiocpus' => $wio,
#          'systemcpus' => $system,
#	  'rdata' => $rdata
#	  ) );die;
    $r = array(
	'series' => array(
	    'maxcpus' => $maxcpus,
	    'actcpus' => $actcpus,
	    'usercpus' => $user,
	    'nicecpus' => $nice,
	    'wiocpus' => $wio,
	    'systemcpus' => $system,
	    'timestamps' => $timestamps,
	    'Labels' => $stampLabels,
 	    'YMax' => array($SETUP['cluster']['config']['batch']['maxcpus']),
	    'YMin' => array(0)  
	),
	'desc' => array(),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." Batch Utilization (".$SETUP['window_txt'].")"
	)
    ); 

    return $r;
}

function query_mem_util_stats_timeline(&$db, $SETUP)
{
    init_timeline_stamps($timestamps, $stampLabels, $SETUP);
    init_timeline($memtotal, $SETUP); ///total possible memory
    init_timeline($memcached, $SETUP); ///memory dedicated to cache
    init_timeline($memused, $SETUP); ///memory being used by processes
    init_timeline($memfree, $SETUP); ///memory not being used
    init_timeline($togb, $SETUP, 1 / (1000*1000) ); ///value scaling (1kB to 1GB) 
 
    #get the actual host stats
    $ehosts = get_ganglia_hosts($SETUP, $SETUP['cluster']['config']['batch']['hosts']);

    #query all the rrd data at once
    $gquery = array();
    foreach($ehosts as $ehost)
	$gquery[$ehost] = array(
	    "mem_total", 
	    "mem_free", 
	    "mem_cached"
	);

    #$var_dump($gquery);die;
    gangliarrd($SETUP, $gquery, $rdata, $timestamps);
    unset($gquery); 

    #var_dump($rdata);die;
    foreach($rdata as $host => $dhost)
    {
	add_timelines($memtotal, $dhost["mem_total"], $SETUP);
	add_timelines($memcached, $dhost["mem_cached"], $SETUP);
	add_timelines($memfree, $dhost["mem_free"], $SETUP);
    }
    unset($rdata);

    add_timelines($memused, $memtotal, $SETUP);
    sub_timelines($memused, $memcached, $SETUP);
    sub_timelines($memused, $memfree, $SETUP);
    min_limit_timeline($memused, 0, $SETUP); #dont allow negative memory
    
    #scale from 1kb to 1gb
    mul_timelines($memused, $togb, $SETUP);
    mul_timelines($memtotal, $togb, $SETUP);
    mul_timelines($memfree, $togb, $SETUP);
    mul_timelines($memcached, $togb, $SETUP);

    #make sure 0 is the min of any resultant value
    min_limit_timeline($memused, 0, $SETUP);
    min_limit_timeline($memtotal, 0, $SETUP);
    min_limit_timeline($memfree, 0, $SETUP);
    min_limit_timeline($memcached, 0, $SETUP);

    $r = array(
	'series' => array(
	    'memused'	    => $memused,
	    'memtotal'	    => $memtotal,
	    'memfree'	    => $memfree,
	    'memcached'	    => $memcached,
	    'timestamps'    => $timestamps,
	    'Labels'	    => $stampLabels,
 	    'YMax'	    => array(max($memtotal)),
	    'YMin'	    => array(0)  
	),
	'desc' => array(),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." Batch Memory Utilization (".$SETUP['window_txt'].")"
	)
    ); 

    return $r;
}         

function query_mem_util_timeline($host, &$db, $SETUP)
{
    $SETUP['cluster']['hosts']['regex'] = $SETUP['cluster']['config']['batch']['hosts'];
    return query_mem_util_stats_timeline($db, $SETUP);
} 

function query_mem_util_timeline_master(&$db, $SETUP)
{
    if(isset($SETUP['cluster']['config']['pbspro']))
	return query_mem_util_timeline($SETUP['cluster']['config']['pbspro']['master'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['lsf']))
	return query_mem_util_timeline($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['slurm']))
	return query_mem_util_timeline($SETUP['cluster']['config']['slurm']['master'], $db, $SETUP);
} 

function query_mem_util_timeline_backup(&$db, $SETUP)
{
    if(isset($SETUP['cluster']['config']['pbspro']))
	return query_mem_util_timeline($SETUP['cluster']['config']['pbspro']['backup'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['lsf']))
	return query_mem_util_timeline($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['slurm']))
	return query_mem_util_timeline($SETUP['cluster']['config']['slurm']['backup'], $db, $SETUP);
} 

function query_util_timeline($host, &$db, $SETUP)
{
    $SETUP['cluster']['hosts']['regex'] = $SETUP['cluster']['config']['batch']['hosts'];
    $r1 = query_util_sch_timeline($host, $db, $SETUP);
    $r2 = query_util_stats_timeline($db, $SETUP);

    return array(
	'series' => array(
	    'cpus' => $r1['series']['cpus'],
	    'maxcpus' => $r1['series']['maxcpus'],

	    'maxthreads' => $r2['series']['maxcpus'],
	    'actcpus' => $r2['series']['actcpus'],
	    'usercpus' => $r2['series']['usercpus'],
	    'nicecpus' => $r2['series']['nicecpus'],
	    'wiocpus' => $r2['series']['wiocpus'],
	    'systemcpus' => $r2['series']['systemcpus'],
	    'timestamps' => $r2['series']['timestamps'],
	    'Labels' => $r2['series']['Labels'],

 	    'YMax' => array($SETUP['cluster']['config']['batch']['maxcpus']),
	    'YMin' => array(0) 
	),
	'desc' => array(),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." Batch Utilization (".$SETUP['window_txt'].")"
	)
    ); 
} 

function query_util_timeline_master(&$db, $SETUP)
{
    if(isset($SETUP['cluster']['config']['pbspro']))
	return query_util_timeline($SETUP['cluster']['config']['pbspro']['master'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['lsf']))
	return query_util_timeline($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['slurm']))
	return query_util_timeline($SETUP['cluster']['config']['slurm']['master'], $db, $SETUP);
} 

function query_util_timeline_backup(&$db, $SETUP)
{
    if(isset($SETUP['cluster']['config']['pbspro']))
	return query_util_timeline($SETUP['cluster']['config']['pbspro']['backup'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['lsf']))
	return query_util_timeline($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['slurm']))
	return query_util_timeline($SETUP['cluster']['config']['slurm']['backup'], $db, $SETUP);
}

function query_util_percent_timeline($host, &$db, $SETUP)
{
    $r = query_util_timeline($host, $db, $SETUP);

    $maxthreads = $SETUP['cluster']['config']['batch']['maxcpus'];

    div_timeline($r['series']['actcpus'], $maxthreads, $SETUP); 
    div_timeline($r['series']['usercpus'], $maxthreads, $SETUP); 
    div_timeline($r['series']['nicecpus'], $maxthreads, $SETUP); 
    div_timeline($r['series']['wiocpus'], $maxthreads, $SETUP); 
    div_timeline($r['series']['systemcpus'], $maxthreads, $SETUP); 
    div_timeline($r['series']['maxthreads'], $maxthreads, $SETUP); 
    div_timeline($r['series']['cpus'], $maxthreads, $SETUP); 

    $thread_scale = 100;
    $core_scale = 50;

    init_timeline($r['series']['maxcpus'], $SETUP, $core_scale);
    init_timeline($r['series']['maxthreads'], $SETUP, $thread_scale);
  
    mul_timeline($r['series']['actcpus'], $thread_scale, $SETUP);
    mul_timeline($r['series']['usercpus'], $thread_scale, $SETUP);
    mul_timeline($r['series']['nicecpus'], $thread_scale, $SETUP);
    mul_timeline($r['series']['wiocpus'], $thread_scale, $SETUP);
    mul_timeline($r['series']['systemcpus'], $thread_scale, $SETUP);
    mul_timeline($r['series']['cpus'], $thread_scale, $SETUP);

    $r['series']['YMax'] = array($thread_scale);
    $r['series']['YMin'] = array(0);
    return $r;
} 

function query_util_percent_timeline_master(&$db, $SETUP)
{
    if(isset($SETUP['cluster']['config']['pbspro']))
	return query_util_percent_timeline($SETUP['cluster']['config']['pbspro']['master'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['lsf']))
	return query_util_percent_timeline($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['slurm']))
	return query_util_percent_timeline($SETUP['cluster']['config']['slurm']['master'], $db, $SETUP);
} 

function query_util_percent_timeline_backup(&$db, $SETUP)
{
    if(isset($SETUP['cluster']['config']['pbspro']))
	return query_util_percent_timeline($SETUP['cluster']['config']['pbspro']['backup'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['lsf']))
	return query_util_percent_timeline($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['slurm']))
	return query_util_percent_timeline($SETUP['cluster']['config']['slurm']['backup'], $db, $SETUP);
}

function query_sch_node_timeline($host, &$db, $SETUP)
{
    init_timeline_stamps($timestamps, $stamplabels, $SETUP);

    #get the actual host stats
    $ehosts = get_ganglia_hosts($SETUP, $host);
    $series = NULL;

    if(isset($SETUP['cluster']['config']['pbspro']))
    {
	$data = read_pbs_avail_log($SETUP);

	#max hosts isnt recorded, use configured max instead
	init_timeline($maxhosts, $SETUP, $SETUP['cluster']['config']['batch']['maxhosts']); 

	#PBS uses different states, so just try to match is as closely as possible
	$series = array(
           'bnodecount'	    => $maxhosts,
           'bnodeok'	    => $data['nodes_up'],
           'bnodeunavail'   => $data['nodes_down']
	);
    }
    elseif(isset($SETUP['cluster']['config']['lsf']))
    {
	$p = "lsf_stats.lsf.". $SETUP['cluster']['config']['lsf']['batch stats prefix'];

	#query all the rrd data at once
	$gquery = array();
	foreach($ehosts as $ehost)
	   $gquery[$ehost] = array(
	       $p."nodes",
	       $p."nodes_ok",
	       $p."nodes_admindown",
	       $p."nodes_unreach",
	       $p."nodes_unavail",
	       $p."nodes_limunavail",
	       $p."nodes_limclosed",
	       $p."nodes_limlocked"
	   );

	gangliarrd($SETUP, $gquery, $rdata, $timestamps);
	unset($gquery);

	$series = array(
           'bnodecount' => $rdata[$host][$p."nodes"],
           'bnodeok' => $rdata[$host][$p."nodes_ok"],
           'bnodeadmindown' => $rdata[$host][$p."nodes_admindown"],
           'bnodeunreach' => $rdata[$host][$p."nodes_unreach"],
           'bnodeunavail' => $rdata[$host][$p."nodes_unavail"],
           'bnodelimunavail' => $rdata[$host][$p."nodes_limunavail"],
           'bnodelimclosed' => $rdata[$host][$p."nodes_limclosed"],
	   'bnodelimlocked' => $rdata[$host][$p."nodes_limlocked"]
	);
    }
    elseif(isset($SETUP['cluster']['config']['slurm']))
    {
	#slurm_cluster_stat.caldera_state_avail
	$p = 'slurm_cluster_stat.'.$SETUP['cluster']['config']['slurm']['batch']['prefix'].'_';

	#query all the rrd data at once
	$gquery = array();
	foreach($ehosts as $ehost)
	   $gquery[$ehost] = array(
	       $p."state_avail"
	   );

	gangliarrd($SETUP, $gquery, $rdata, $timestamps);
	unset($gquery);

	#max hosts isnt recorded, use configured max instead
	init_timeline($maxhosts, $SETUP, $SETUP['cluster']['config']['batch']['maxhosts']); 
	init_timeline($downhosts, $SETUP, $SETUP['cluster']['config']['batch']['maxhosts']); 
	sub_timelines($downhosts, $rdata[$host][$p."state_avail"], $SETUP);

	#slurm uses different states, so just try to match is as closely as possible
	$series = array(
           'bnodecount'	    => $maxhosts,
           'bnodeok'	    => $rdata[$host][$p."state_avail"],
           'bnodeunavail'   => $downhosts,
	);

    }

    $r = array(
	'series' => array_merge(
	   $series,
	   array(
	       'timestamps' => $timestamps,
	       'labels' => $stamplabels,
	   )
       ),
       'desc' => array(),
       'text' => array(
           'title' => $SETUP['cluster']['config']["name"]." Batch Nodes LSF (".$SETUP['window_txt'].")",
       )
    );

    return $r;
}

function query_sch_node_percent_timeline($host, &$db, $SETUP)
{
    $r = query_sch_node_timeline($host, $db, $SETUP);

    div_timeline($r['series']['bnodecount'], $SETUP['cluster']['config']['batch']['maxhosts'], $SETUP);
    div_timeline($r['series']['bnodeok'], $SETUP['cluster']['config']['batch']['maxhosts'], $SETUP);
    div_timeline($r['series']['bnodeadmindown'], $SETUP['cluster']['config']['batch']['maxhosts'], $SETUP);
    div_timeline($r['series']['bnodeunreach'], $SETUP['cluster']['config']['batch']['maxhosts'], $SETUP);
    div_timeline($r['series']['bnodeunavail'], $SETUP['cluster']['config']['batch']['maxhosts'], $SETUP);
    div_timeline($r['series']['bnodelimunavail'], $SETUP['cluster']['config']['batch']['maxhosts'], $SETUP);
    div_timeline($r['series']['bnodelimclosed'], $SETUP['cluster']['config']['batch']['maxhosts'], $SETUP);
    div_timeline($r['series']['bnodelimlocked'], $SETUP['cluster']['config']['batch']['maxhosts'], $SETUP);
    mul_timeline($r['series']['bnodecount'], 100, $SETUP);
    mul_timeline($r['series']['bnodeok'], 100, $SETUP);
    mul_timeline($r['series']['bnodeadmindown'], 100, $SETUP);
    mul_timeline($r['series']['bnodeunreach'], 100, $SETUP);
    mul_timeline($r['series']['bnodeunavail'], 100, $SETUP);
    mul_timeline($r['series']['bnodelimunavail'], 100, $SETUP);
    mul_timeline($r['series']['bnodelimclosed'], 100, $SETUP);
    mul_timeline($r['series']['bnodelimlocked'], 100, $SETUP);
    $r['series']['YMax'] = array(100);
    $r['series']['YMin'] = array(0); 

    return $r;
}

function query_sch_avail_pie($host, &$db, $SETUP)
{
    $SETUP['cluster']['hosts']['regex'] = $SETUP['cluster']['config']['batch']['hosts'];
    $r = query_sch_node_timeline($host, $db, $SETUP);
    $r2 = query_util_sch_timeline($host, $db, $SETUP);
    $rdata = &$r['series'];
    $r2data = &$r2['series'];

    #calc the node availablity
    #Availability = nodes were available to run jobs during ATP. If a node goes down for 2 hours then 2 hours is subtracted from 4218 x24 - 2 =
    $maxtime = $SETUP['window_time']*$SETUP['cluster']['config']['batch']['maxhosts'];

    #Per Tom, ignore hot spares
    max_limit_timeline($rdata['bnodeok'], $SETUP['cluster']['config']['batch']['maxhosts'], $SETUP);
 
    $nok = sum_timeline($rdata['bnodeok'], $SETUP['chart']['tslices'], $SETUP);
    $nadmindown = sum_timeline($rdata['bnodeadmindown'], $SETUP['chart']['tslices'], $SETUP);
    $nlimunavail = sum_timeline($rdata['bnodelimunavail'], $SETUP['chart']['tslices'], $SETUP);
    $nunavail = sum_timeline($rdata['bnodeunavail'], $SETUP['chart']['tslices'], $SETUP);
    $nunreach = sum_timeline($rdata['bnodeunreach'], $SETUP['chart']['tslices'], $SETUP);
    $nlimclosed = sum_timeline($rdata['bnodelimclosed'], $SETUP['chart']['tslices'], $SETUP);
    #calc utilization
    $maxcputime = $SETUP['window_time']*$SETUP['cluster']['config']['batch']['maxcpus'];
    $schcpus = sum_timeline($r2data['cpus'], $SETUP['chart']['tslices'], $SETUP);

    #get time that was VOID
    $nunknown = 0;
    for($i = 0; $i < $SETUP['chart']['slices']; ++$i)
	if($rdata['bnodeok'][$i] == VOID)
	    $nunknown += $SETUP['chart']['tslices'] * $SETUP['cluster']['config']['batch']['maxhosts'];

    $r = array(
       'series' => array(
	   'nodestates' => array('ok', 'Admin Down', 'Unreachable', 'Unavailable', 'Lim Closed', 'Lim Locked', 'Lim unavail'),
	   'nodestatetimes' => array($nok, $nadmindown, $nunreach, $nunavail, $nlimclosed, $nlimlocked, $nlimunavail),
	   'nodeupdown' => array('up', 'down', 'unknown'),
	   'nodeupdowntimes' => array($nok, $maxtime - $nok - $nunknown, $nunknown),
	   'schcpu' => array('Running', 'Empty'),
	   'schcputimes' => array($schcpus, $maxcputime - $schcpus)
       ),
       'desc' => array(),
       'text' => array(
           'title' => $SETUP['cluster']['config']["name"]." Batch Nodes Availability (".$SETUP['window_txt'].")",
	   'maxuptime' => "Max Hours: ".round($maxtime/(60*60),2),
	   'maxcputime' => "Max CPU Hours: ".round($maxcputime/(60*60),2),
	   'utiltime' => "CPU Hours Assigned: ".round($schcpus/(60*60),2)." (".round(($schcpus/$maxcputime)*100,2)."%)",
	   'uptime' => "Up Time: ".round($nok/(60*60),2)." (".round(($nok/$maxtime)*100,2)."%)",
	   'unknowntime' => $nunknown > 0 ? "Unknown Time: ".round($nunknown/(60*60),2)." (".round(($nunknown/$maxtime)*100,2)."%)" : ""
       )
    );

    return $r;
}

function query_sch_job_node_timeline($host, &$db, $SETUP)
{
    $SETUP['cluster']['hosts']['regex'] = $SETUP['cluster']['config']['batch']['hosts'];
    $r = query_sch_node_timeline($host, $db, $SETUP);
    $r2 = query_util_sch_timeline($host, $db, $SETUP);
    div_timeline($r2['series']['cpus'], $SETUP['cluster']['config']['batch']['maxcpus'], $SETUP);
    mul_timeline($r2['series']['cpus'], $SETUP['cluster']['config']['batch']['maxhosts'], $SETUP);
    $r['series']['bnoderun'] = $r2['series']['cpus'];

    $r['text'] = array(
           'title' => $SETUP['cluster']['config']["name"]." LSF Batch Nodes Available vs Running (".$SETUP['window_txt'].")"
    );

    return $r;
}

function query_sch_job_node_percent_timeline($host, &$db, $SETUP)
{
    $r = query_sch_job_node_timeline($host, $db, $SETUP);

    div_timeline($r['series']['bnodecount'], $SETUP['cluster']['config']['batch']['maxhosts'], $SETUP);
    div_timeline($r['series']['bnodeok'], $SETUP['cluster']['config']['batch']['maxhosts'], $SETUP);
    div_timeline($r['series']['bnoderun'], $SETUP['cluster']['config']['batch']['maxhosts'], $SETUP);
    mul_timeline($r['series']['bnodecount'], 100, $SETUP);
    mul_timeline($r['series']['bnodeok'], 100, $SETUP);
    mul_timeline($r['series']['bnoderun'], 100, $SETUP);
    $r['series']['YMax'] = array(100);
    $r['series']['YMin'] = array(0); 

    return $r;
} 

function query_util_sch_node_timeline_master(&$db, $SETUP)
{         
    if(isset($setup['cluster']['config']['pbspro']))
	return query_sch_job_node_timeline($setup['cluster']['config']['pbspro']['master'], $db, $setup);
    elseif(isset($setup['cluster']['config']['lsf']))
	return query_sch_job_node_timeline($setup['cluster']['config']['lsf']['master'], $db, $setup);
    elseif(isset($setup['cluster']['config']['slurm']))
	return query_sch_job_node_timeline($setup['cluster']['config']['slurm']['master'], $db, $setup);
}

function query_util_sch_node_timeline_backup(&$db, $SETUP)
{         
    if(isset($setup['cluster']['config']['pbspro']))
	return query_sch_job_node_timeline($setup['cluster']['config']['pbspro']['backup'], $db, $setup);
    elseif(isset($setup['cluster']['config']['lsf']))
	return query_sch_job_node_timeline($setup['cluster']['config']['lsf']['backup'], $db, $setup);
    elseif(isset($setup['cluster']['config']['slurm']))
	return query_sch_job_node_timeline($setup['cluster']['config']['slurm']['backup'], $db, $setup);
} 

function query_util_sch_node_percent_timeline_master(&$db, $SETUP)
{         
    if(isset($setup['cluster']['config']['pbspro']))
	return query_sch_job_node_percent_timeline($SETUP['cluster']['config']['pbspro']['master'], $db, $SETUP);
    elseif(isset($setup['cluster']['config']['lsf']))
	return query_sch_job_node_percent_timeline($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
    elseif(isset($setup['cluster']['config']['slurm']))
	return query_sch_job_node_percent_timeline($SETUP['cluster']['config']['slurm']['master'], $db, $SETUP);
}

function query_util_sch_node_percent_timeline_backup(&$db, $SETUP)
{         
    if(isset($setup['cluster']['config']['pbspro']))
	return query_sch_job_node_percent_timeline($SETUP['cluster']['config']['pbspro']['backup'], $db, $SETUP);
    elseif(isset($setup['cluster']['config']['lsf']))
	return query_sch_job_node_percent_timeline($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
    elseif(isset($setup['cluster']['config']['slurm']))
	return query_sch_job_node_percent_timeline($SETUP['cluster']['config']['slurm']['backup'], $db, $SETUP);
} 

function query_sch_node_timeline_master(&$db, $SETUP)
{         
    if(isset($setup['cluster']['config']['pbspro']))
	return query_sch_node_timeline($SETUP['cluster']['config']['pbspro']['master'], $db, $SETUP);
    elseif(isset($setup['cluster']['config']['lsf']))
	return query_sch_node_timeline($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
    elseif(isset($setup['cluster']['config']['slurm']))
	return query_sch_node_timeline($SETUP['cluster']['config']['slurm']['master'], $db, $SETUP);
}

function query_sch_node_timeline_backup(&$db, $SETUP)
{         
    if(isset($setup['cluster']['config']['pbspro']))
	return query_sch_node_timeline($SETUP['cluster']['config']['pbspro']['backup'], $db, $SETUP);
    elseif(isset($setup['cluster']['config']['lsf']))
	return query_sch_node_timeline($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
    elseif(isset($setup['cluster']['config']['slurm']))
	return query_sch_node_timeline($SETUP['cluster']['config']['slurm']['backup'], $db, $SETUP);
}

function query_sch_node_percent_timeline_master(&$db, $SETUP)
{    
    if(isset($setup['cluster']['config']['pbspro']))
	return query_sch_node_percent_timeline($SETUP['cluster']['config']['pbspro']['master'], $db, $SETUP);
    elseif(isset($setup['cluster']['config']['lsf']))
	return query_sch_node_percent_timeline($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);    
    elseif(isset($setup['cluster']['config']['slurm']))
	return query_sch_node_percent_timeline($SETUP['cluster']['config']['slurm']['backup'], $db, $SETUP);    
}

function query_sch_node_percent_timeline_backup(&$db, $SETUP)
{
    if(isset($setup['cluster']['config']['pbspro']))
	return query_sch_node_percent_timeline($SETUP['cluster']['config']['pbspro']['backup'], $db, $SETUP);
    elseif(isset($setup['cluster']['config']['lsf']))
	return query_sch_node_percent_timeline($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
    elseif(isset($setup['cluster']['config']['slurm']))
	return query_sch_node_percent_timeline($SETUP['cluster']['config']['slurm']['backup'], $db, $SETUP);
}

function query_sch_avail_pie_master(&$db, $SETUP)
{
    if(isset($setup['cluster']['config']['pbspro']))
	return query_sch_avail_pie($SETUP['cluster']['config']['pbspro']['master'], $db, $SETUP);
    elseif(isset($setup['cluster']['config']['lsf']))
	return query_sch_avail_pie($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
    elseif(isset($setup['cluster']['config']['slurm']))
	return query_sch_avail_pie($SETUP['cluster']['config']['slurm']['master'], $db, $SETUP);
}

function query_sch_avail_pie_backup(&$db, $SETUP)
{
    if(isset($setup['cluster']['config']['pbspro']))
	return query_sch_avail_pie($SETUP['cluster']['config']['pbspro']['backup'], $db, $SETUP);
    elseif(isset($setup['cluster']['config']['lsf']))
	return query_sch_avail_pie($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
    elseif(isset($setup['cluster']['config']['slurm']))
	return query_sch_avail_pie($SETUP['cluster']['config']['slurm']['backup'], $db, $SETUP);
}

function query_sch_agg_stats($host, &$db, $SETUP)
{
    #max time over time period for cpus
    $wtime = $SETUP['window_time'];
    $maxtime = $wtime * $SETUP['cluster']['config']['batch']['maxhosts'];
    $maxcputime = $wtime * $SETUP['cluster']['config']['batch']['maxcpus'];

    $SETUP['cluster']['hosts']['regex'] = $SETUP['cluster']['config']['batch']['hosts'];
    $r = query_sch_node_timeline($host, $db, $SETUP);
    $r2 = query_util_sch_timeline($host, $db, $SETUP);
    #$r3 = query_flop_timeline($db, $SETUP);
    $r4 = query_util_stats_timeline($db, $SETUP);
    $r5 = query_rsv_util_timeline($db, $SETUP);

    $rdata = &$r['series'];
    $r2data = &$r2['series'];
    #$r3data = &$r3['series'];
    $r4data = &$r4['series'];
    $r5data = &$r5['series'];
    $r6data = VOID;

    #calc user stats
    $uuser = sum_timeline($r4data['usercpus'], $SETUP['chart']['tslices'], $SETUP) + sum_timeline($r4data['nicecpus'], $SETUP['chart']['tslices'], $SETUP);
    $uwuio = sum_timeline($r4data['wiocpus'], $SETUP['chart']['tslices'], $SETUP);
    $usys = sum_timeline($r4data['systemcpus'], $SETUP['chart']['tslices'], $SETUP);
    $uidle = $maxcputime - ($uuser + $uwuio + $usys);

    #calc the floppery

    #calc the node availablity
    max_limit_timeline($rdata['bnodeok'], $SETUP['cluster']['config']['batch']['maxhosts'], $SETUP);
    $nok = sum_timeline($rdata['bnodeok'], $SETUP['chart']['tslices'], $SETUP);
    #Only the contract number of nodes count towards util at each timestamp
    max_limit_timeline($r2data['cpus'], $SETUP['cluster']['config']['batch']['maxcpus'], $SETUP);
    $schcpus = sum_timeline($r2data['cpus'], $SETUP['chart']['tslices'], $SETUP);

    $usr_rsv = sum_timeline($r5data['usr_rsv'], $SETUP['chart']['tslices'], $SETUP);
    $sys_rsv = sum_timeline($r5data['sys_rsv'], $SETUP['chart']['tslices'], $SETUP);

    $flopavg = VOID;
    $jobwait = VOID;

    $r = array(
       'series' => array(
 	   'hosts' => array(
		'wtime'		=> $wtime,
		'maxtime'	=> $maxtime,
		'maxcputime'	=> $maxcputime,
		'cpus'		=> $r2data['cpus'],
		'sumcpus'       => $schcpus
	   ), 
	   'stats' => array(
		'Average TFlops', 
		'Utilization', 
		'User CPU', 
		'Idle CPU', 
		'System CPU', 
		'Wait IO CPU', 
		'Availability',
		'System Reservation', 
		'User Reservation', 
		'Average Queue Wait (m)', 
	   ),
	   'values' => array(
		$flopavg == VOID ? '-' : round(max(0, $flopavg),4), 
		$schcpus == VOID ? '-' : round(bound(($schcpus/$maxcputime)*100,0,100),2).'%', 
		$uuser	 == VOID ? '-' : round(bound(($uuser/$maxcputime)*100,0,100),2).'%', 
		$uidle	 == VOID ? '-' : round(bound(($uidle/$maxcputime)*100,0,100),2).'%', 
		$usys	 == VOID ? '-' : round(bound(($usys/$maxcputime)*100,0,100),2).'%', 
		$uwuio	 == VOID ? '-' : round(bound(($uwuio/$maxcputime)*100,0,100),2).'%', 
		$nok	 == VOID ? '-' : round(bound(($nok/$maxtime)*100,0,100),2).'%', 
		$sys_rsv == VOID ? '-' : round(bound(($sys_rsv/$wtime),0,100),2).'%',
		$usr_rsv == VOID ? '-' : round(bound(($usr_rsv/$wtime),0,100),2).'%',
		$jobwait == VOID ? '-' : round($jobwait, 2)
	    ),
	    'waits' => $r6data 
       ),
       'desc' => array(),
       'text' => array(
           'title' => $SETUP['cluster']['config']["name"]." Nodes Statistics (".$SETUP['window_txt'].")",
       )
    );

    return $r;
} 

function query_sch_agg_stats_master(&$db, $SETUP)
{
    if(isset($SETUP['cluster']['config']['pbspro']))
	return query_sch_agg_stats($SETUP['cluster']['config']['pbspro']['master'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['lsf']))
	return query_sch_agg_stats($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['slurm']))
	return query_sch_agg_stats($SETUP['cluster']['config']['slurm']['master'], $db, $SETUP);
} 

function query_sch_agg_stats_backup(&$db, $SETUP)
{
    if(isset($SETUP['cluster']['config']['pbspro']))
	return query_sch_agg_stats($SETUP['cluster']['config']['pbspro']['backup'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['lsf']))
	return query_sch_agg_stats($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
    elseif(isset($SETUP['cluster']['config']['slurm']))
	return query_sch_agg_stats($SETUP['cluster']['config']['slurm']['backup'], $db, $SETUP);
}

?>
