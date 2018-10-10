<?php

/** 
 * @brief Set common values in query return array
 */ 
function query_set_common_params(&$raw, &$db, $SETUP)
{
    $raw['text']['batch_nodes'] = $SETUP['cluster']['config']['batch']['maxhosts'] ." Batch Nodes";
    $raw['text']['batch_cores'] = $SETUP['cluster']['config']['batch']['maxcpus'] ." Batch Threads";
}

/** 
 * @brief get Query Filter for Jobs Query
 */
function getQueryFilter_jobs($SETUP, &$join, &$where, &$group)
{
    $cs = $SETUP['cluster'];
    $h = $cs['hosts']['regex'];
    $u = $cs['user']['regex'];
    $nu = isset($cs['user']['not_regex']) ? $cs['user']['not_regex'] : FALSE;
    $j = $cs['job']['id'];
    $q = $cs['queues']['regex'];
    $ts =  $SETUP['start_time'];
    $te =  $SETUP['end_time'];
    $jst_min = $cs['job']['slots']['min'];
    $jst_max = $cs['job']['slots']['max'];
    $jrt_min = getSeconds($cs['job']['runtime']['min']);
    $jrt_max = getSeconds($cs['job']['runtime']['max']);

    $prefix = $SETUP['cluster']['config']['lsf']['sql']['prefix'];

    $where .= sprintf("
	    ((startTime >= %s && endTime <= %s) || 
	    (startTime <= %s && endTime >= %s) || 
	    (startTime <= %s && endTime >= %s) || 
	    (startTime <= %s && endTime >= %s))
	",
	sqle($ts), sqle($te),
	sqle($ts), sqle($te),
	sqle($ts), sqle($ts),
	sqle($te), sqle($te)
    ); 

    if($jrt_min != FALSE)
	$where .= sprintf("
		&& %s_jobFinishLog.endTime - %s_jobFinishLog.startTime >= %s 
	    ",
	    $prefix,
	    $prefix,
	    sqle($jrt_min)
	); 
    if($jrt_max != FALSE)
	$where .= sprintf("
		&& %s_jobFinishLog.endTime - %s_jobFinishLog.startTime <= %s 
	    ",
	    $prefix,
	    $prefix,
	    sqle($jrt_max)
	);               
    if($jst_min != FALSE)
	$where .= sprintf("
		&& %s_jobFinishLog.numProcessors >= %s 
	    ",
	    $prefix,
	    sqle($jst_min)
	);               
    if($jst_max != FALSE)
	$where .= sprintf("
		&& %s_jobFinishLog.numProcessors <= %s 
	    ",
	    $prefix,
	    sqle($jst_max)
	);               
      
    if($h !== FALSE || $u !== FALSE)
    {
	$join .= sprintf("
	    INNER JOIN %s_jobFinishLog_execHosts ON %s_jobFinishLog_execHosts.jobId = %s_jobFinishLog.jobId &&
	    %s_jobFinishLog_execHosts.submitTime = %s_jobFinishLog.submitTime &&
	    %s_jobFinishLog_execHosts.idx = %s_jobFinishLog.idx
	    ",
	    $prefix,
	    $prefix,
	    $prefix,
	    $prefix,
	    $prefix,
	    $prefix,
	    $prefix,
	    $prefix
	);

	if($h != FALSE)
	    $where .= sprintf("
		    && %s_jobFinishLog_execHosts.name REGEXP %s 
		",
		$prefix,
		sqle($h)
	    );
        if($u != FALSE)
	    $where .= sprintf("
		    && %s_jobFinishLog.userName REGEXP %s 
		",
		$prefix,
		sqle($u)
	    );
        if($nu != FALSE)
	    $where .= sprintf("
		    && %s_jobFinishLog.userName NOT_REGEXP %s 
		",
		$prefix,
		sqle($nu)
	    );              
    }

    if($j != FALSE)
	    $where .= sprintf("
		    && %s_jobFinishLog.jobId = %s 
		",
		$prefix,
		sqle($j)
	    ); 

    if($q != FALSE)
	    $where .= sprintf("
		    && %s_jobFinishLog.queue REGEXP %s 
		",
		$prefix,
		sqle($q)
	    ); 
    
    $groups = array();
    if(is_array($group) || !empty($group))
	$groups = $group;

    $groups[] = sprintf("%s_jobFinishLog.jobId", $prefix);
    $groups[] = sprintf("%s_jobFinishLog.submitTime", $prefix);
    $groups[] = sprintf("%s_jobFinishLog.idx ", $prefix);

    $group = sprintf(" GROUP BY %s ", join(',', $groups));
}

function query_queue_wait(&$db, $SETUP)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $join = '';
    $where = '';
    $group = array();
    
    getQueryFilter_jobs($SETUP, $join, $where, $group);

    $request = sprintf("
	    SELECT 
		queue,
		%s_jobFinishLog.userName as user,
		((startTime - %s_jobFinishLog.submitTime) / 60) as wait,
		%s_jobFinishLog.jobId as jobid,
		%s_jobFinishLog.submitTime as submitTime,
		%s_jobFinishLog.idx as idx
	    FROM `%s_jobFinishLog`
	    %s
	    WHERE %s
	    %s
	    ;
	",
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$join,
	$where,
	$group
    );

    #var_dump($request); die;

    $bqueue = array(); #by queue
    $buser = array();  #by user
    $jobs = array();   #sanity check of jobids
    $jobcnt = 0; #job count
    $jobwait = 0; #total job wait time
    $qrank = array();
    $urank = array();

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
	$user = $row["user"];
	$queue = $row["queue"];
	$wait = $row["wait"];
	$jobcnt += 1;
	$jobwait += $wait;

	$jobid = $row["jobid"];
	$sub = $row["submitTime"];
	$idx = $row["idx"];

	#sanity check for dup jobs
	if(isset($jobs[$jobid][$sub][$idx]))
	    die('dup job! ' .  $jobid);
	else 
	{
	    if(!isset($jobs[$jobid])) $jobs[$jobid] = array();
	    if(!isset($jobs[$jobid][$sub])) $jobs[$jobid][$sub] = array();
	    $jobs[$jobid][$sub][$idx] = 1;
	}

	if(!isset($bqueue[$queue])) $bqueue[$queue] = array('jobs' => 0, 'wait' => 0);
	$bqueue[$queue]['jobs'] += 1;
	$bqueue[$queue]['wait'] += $wait;
	$qrank[$queue] = $bqueue[$queue]['wait'] / $bqueue[$queue]['jobs'];

 	if(!isset($buser[$user])) $buser[$user] = array('jobs' => 0, 'wait' => 0);
	$buser[$user]['jobs'] += 1;
	$buser[$user]['wait'] += $wait;
	$urank[$user] = $buser[$user]['wait'] / $buser[$user]['jobs'];
    } 
    mysql_free_result($result);  

    array_multisort($urank, SORT_DESC,  $buser);
    array_multisort($qrank, SORT_DESC,  $bqueue);

    $qwavg = array();
    $qwsum = array();
    $qwqueue = array();
    $qwjobs = array();
    $uwavg = array();
    $uwsum = array();
    $uwqueue = array();
    $uwjobs = array();

    foreach($bqueue as $queue => $val)
    {
	$qwavg[]    = round($val['wait'] / $val['jobs'],2);
	$qwsum[]    = round($val['wait'],2);
	$qwjobs[]   = $val['jobs'];
	$qwqueue[]  = $queue;
    }                  

    foreach($buser as $user => $val)
    {
	$uwavg[]    = round($val['wait'] / $val['jobs'],2);
	$uwsum[]    = round($val['wait'],2);
	$uwjobs[]   = $val['jobs'];
	$uwuser[]   = $user;
    }                   

    $urank = array_keys($uwavg);
    #start at 1, not zero
    array_walk($urank, function(&$val,$key) use(&$array){ 
	++$val;
    });        

    $qrank = array_keys($qwavg);
    #start at 1, not zero
    array_walk($qrank, function(&$val,$key) use(&$array){ 
	++$val;
    });
 
    return array(
	'series' => array(
	    'values' => array( #dont change array order
		round($jobwait / $jobcnt,2),
		round(array_sum($uwavg) / count($uwavg),2),
		round(array_sum($qwavg) / count($qwavg),2),
		$jobcnt
	    ),
	    'stats' => array(
 		'Average Wait Time by Job',
		'Average Wait Time by User',
		'Average Wait Time by Queue',
 		'Total Number of Jobs'
	    ),
	    'qrank'	    => $qrank,
	    'qwait_avg'	    => $qwavg,
	    'qwait_sum'	    => $qwsum,
	    'qqueue'	    => $qwqueue,
	    'qjobs'	    => $qwjobs,
 	    'urank'	    => $urank,
	    'uwait_avg'	    => $uwavg,
	    'uwait_sum'	    => $uwsum,
	    'uuser'	    => $uwuser,
	    'ujobs'	    => $uwjobs,
	),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." Queue Wait Times (".$SETUP['window_txt'].")"
	)
    ); 
}

function query_queue_wait_freq(&$db, $SETUP)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $totals = array();    

    $join = '';
    $where = '';
    $group = array();
    getQueryFilter_jobs($SETUP, $join, $where, $group);
 
    $request = sprintf("
	    SELECT 
		queue,
		(startTime - %s_jobFinishLog.submitTime) as wait
	    FROM `%s_jobFinishLog`
	    %s
	    WHERE %s
	    %s
	",
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$join,
	$where,
	$group
    );
    #var_dump($request);die;

    $qb = array(); #queue bins

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
	$q = $row["queue"];

	if(!isset($qb[$q]))
	    init_histogram_bins($qb[$q], $GLOBALS['TIME_FREQS'], $SETUP);

	add_histogram($qb[$q], $GLOBALS['TIME_FREQS'], $row["wait"], $SETUP);
    } 
    mysql_free_result($result);
    #var_dump($qb);die;

    $r = array(
	'series' => array(),
	'desc' => array(),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." Jobs Average Wait Time Frequency per Queue (".$SETUP['window_txt'].")"
	) 
    ); 
    foreach($qb as $queue => $data)
    {
 	$k = 'tfreq.'.$queue;

	$r['series'][$k] = $qb[$queue];
	$r['desc'][$k] = $queue;
    }
    $r['series']['Labels'] = get_histogram_labels($GLOBALS['TIME_FREQS'], $SETUP);
    return $r; 
}

function query_queue_wait_avg_timeline(&$db, $SETUP)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $join = '';
    $where = '';
    $group = array();
    
    getQueryFilter_jobs($SETUP, $join, $where, $group);

    $request = sprintf("
	    SELECT 
		queue,
		%s_jobFinishLog.submitTime,
		startTime
	    FROM `%s_jobFinishLog`
	    %s
	    WHERE %s && endTime != 0 && cpuTime != 0 && endTime >= startTime
	    %s
	",
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$join,
	$where,
	$group
    );
    #var_dump($request);die;

    init_timeline_stamps($timestamps, $stampLabels, $SETUP);
    $wait = array(); #total wait time
    $qc = array(); #queue count

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
	if($row["submitTime"] > 0 && $row["startTime"] > 0 && $row["submitTime"] < $row["startTime"])
	{ 
	    $q = $row["queue"];

	    if(!isset($qc[$q]))
	    {
		init_timeline($wait[$q], $SETUP);
		init_timeline($qc[$q], $SETUP);
	    }
	    #var_dump($row);die;

	    add_timeline($qc[$q], $timestamps, $row["submitTime"], $row["startTime"], 1, $SETUP);
	    add_accumulated_time_timeline($wait[$q], $timestamps, $row["submitTime"], $row["startTime"], (1/60), $SETUP);
	} 
    mysql_free_result($result);
    #var_dump(array("wait"=>$wait, "count"=>$qc));die;

    $r = array(
	'series' => array(),
	'desc' => array(),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." Jobs Average Accumulated Wait Time per Queue (".$SETUP['window_txt'].")"
	) 
    ); 
    foreach($qc as $queue => $data)
    {
 	$k = 'queue.'.$queue;

	$r['series']['qwait.'.$queue] = $wait[$queue];
	$r['series']['qcount.'.$queue] = $qc[$queue];
	div_timelines($wait[$queue], $qc[$queue], $SETUP);
	$r['series'][$k] = $wait[$queue];
	$r['desc'][$k] = $queue;
    }
    $r['series']['Labels'] = $stampLabels;
    $r['series']['timestamps'] = $timestamps;
    return $r; 
}

function query_queue_wait_timeline(&$db, $SETUP)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $join = '';
    $where = '';
    $group = array();
    
    getQueryFilter_jobs($SETUP, $join, $where, $group);

    $request = sprintf("
	    SELECT 
		queue,
		%s_jobFinishLog.submitTime,
		startTime
	    FROM `%s_jobFinishLog`
	    %s
	    WHERE %s
	    %s
	",
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$join,
	$where,
	$group
    );

    init_timeline_stamps($timestamps, $stampLabels, $SETUP);
    $timelines = array();

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
	if(!isset($timelines[$row["queue"]]))
	    init_timeline($timelines[$row["queue"]], $SETUP);

	add_timeline($timelines[$row["queue"]], $timestamps, $row["submitTime"], $row["startTime"], 1, $SETUP);
    } 
    mysql_free_result($result);
    #var_dump($timelines);die;

    $r = array(
	'series' => array(),
	'desc' => array(),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." Jobs Waiting per Queue (".$SETUP['window_txt'].")"
	) 
    ); 
    foreach($timelines as $queue => $data)
    {
 	$k = 'queue.'.$queue;
	$r['series'][$k] = $data;
	$r['desc'][$k] = $queue;
    }
    $r['series']['Labels'] = $stampLabels;
    $r['series']['timestamps'] = $timestamps;
    return $r; 
}

function query_util_mem_sch_timeline_sql(&$db, $SETUP)
{
    $SETUP['cluster']['hosts']['regex'] = $SETUP['cluster']['config']['batch']['hosts'];
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $join = '';
    $where = '';
    $group = array();
    
    getQueryFilter_jobs($SETUP, $join, $where, $group);

    $request = sprintf("
	    SELECT 
		startTime,
		endTime,
		((numExHosts*(SUB_EXCLUSIVE IS FALSE))+(%s * numExPhysicalHosts * (SUB_EXCLUSIVE IS TRUE))) as cpus,
		maxRmem AS maxrss
	    FROM `%s_jobFinishLog`
	    %s 
	    WHERE %s
	    %s
	",
	$SETUP['cluster']['config']["ncpus"],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$join,
	$where,
	$group
    );                  

    init_timeline($maxrss, $SETUP);
    init_timeline_stamps($timestamps, $stampLabels, $SETUP);
    init_timeline($maxmemory, $SETUP, 100);
    #add_timeline($maxmemory, $timestamps, $SETUP['start_time'], $SETUP['end_time'], $SETUP['cluster']['config']['batch']['maxmemory'], $SETUP);

    #var_dump($request);die;

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
	add_timeline($maxrss, $timestamps, $row["startTime"], $row["endTime"], $row["maxrss"], $SETUP);
    } 
    mysql_free_result($result);
    #var_dump($timelines);die;

    div_timeline($maxrss, $SETUP['cluster']['config']['batch']['maxmemory'], $SETUP); 
    mul_timeline($maxrss, 100, $SETUP); 
    min_limit_timeline($maxrss, 0, $SETUP); 

    $r = array(
	'series' => array(
	    'maxrss' => $maxrss,
	    'maxmemory' => $maxmemory,
	    'timestamps' => $timestamps,
	    'Labels' => $stampLabels,
 	    'YMax' => array(100),
	    'YMin' => array(0) 
	),
	'desc' => array(),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." Batch Memory Utilization (".$SETUP['window_txt'].")"
	)
    ); 

    return $r; 
} 

function query_util_sch_timeline_sql(&$db, $SETUP)
{
    $SETUP['cluster']['hosts']['regex'] = $SETUP['cluster']['config']['batch']['hosts'];
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $join = '';
    $where = '';
    $group = array();
    
    getQueryFilter_jobs($SETUP, $join, $where, $group);

    $request = sprintf("
	    SELECT 
		startTime,
		endTime,
		((numExHosts*(SUB_EXCLUSIVE IS FALSE))+(%s * numExPhysicalHosts * (SUB_EXCLUSIVE IS TRUE))) as cpus,
		numExHosts
	    FROM `%s_jobFinishLog`
	    %s 
	    WHERE %s
	    %s
	",
	$SETUP['cluster']['config']["ncores"],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$join,
	$where,
	$group
    );                  

    init_timeline($cpus, $SETUP);
    init_timeline_stamps($timestamps, $stampLabels, $SETUP);
    init_timeline($maxcpus, $SETUP);
    add_timeline($maxcpus, $timestamps, $SETUP['start_time'], $SETUP['end_time'], $SETUP['cluster']['config']['batch']['maxcpus'], $SETUP);

    #var_dump($request);die;

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
	$jmaxcores = $row["numExHosts"] * $SETUP['cluster']['config']["ncores"];

	#LSF provides threads, so we force a limit to fake cores
	if($row["cpus"] > $jmaxcores)
	    $row["cpus"] = $jmaxcores;

	add_timeline($cpus, $timestamps, $row["startTime"], $row["endTime"], $row["cpus"], $SETUP);
    } 
    mysql_free_result($result);
    #var_dump($timelines);die;

    $r = array(
	'series' => array(
	    'cpus' => $cpus,
	    'maxcpus' => $maxcpus,
	    'timestamps' => $timestamps,
	    'Labels' => $stampLabels,
 	    'YMax' => array($maxcores),
	    'YMin' => array(0) 
	),
	'desc' => array(),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." Batch Utilization (".$SETUP['window_txt'].")"
	)
    ); 

    return $r; 
}

function query_util_sch_interactive_jobs_sql(&$db, $SETUP)
{

#day
#total job count
#total interactive count
#total batch count
#total interactive cputime  count
#total batch cputime  count



    $SETUP['cluster']['hosts']['regex'] = $SETUP['cluster']['config']['batch']['hosts'];
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $join = '';
    $where = '';
    $group = array();
    
    getQueryFilter_jobs($SETUP, $join, $where, $group);

    $request = sprintf("
	    SELECT 
		startTime,
		endTime,
		@cpus:=((numExHosts*(SUB_EXCLUSIVE IS FALSE))+(%s * numExPhysicalHosts * (SUB_EXCLUSIVE IS TRUE))) as cpus,
		SUB_INTERACTIVE as interactive,
		(((endTime - startTime) / (60*60)) * @cpus ) as CPUhr
	    FROM `%s_jobFinishLog`
	    %s 
	    WHERE %s
	    %s
	",
	$SETUP['cluster']['config']["ncpus"],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$join,
	$where,
	$group
    );                  

    init_timeline_stamps($timestamps, $stampLabels, $SETUP);

    #create histogram for days
    $DAY_FREQS = create_histogram_buckets_every_day($SETUP);
    init_histogram_bins($hist_jobcount, $DAY_FREQS, $SETUP);
    init_histogram_bins($hist_job_intr_count, $DAY_FREQS, $SETUP);
    init_histogram_bins($hist_job_bash_count, $DAY_FREQS, $SETUP);
    init_histogram_bins($hist_job_intr_agg_cputime, $DAY_FREQS, $SETUP);
    init_histogram_bins($hist_job_bash_agg_cputime, $DAY_FREQS, $SETUP);

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
	add_histogram($hist_jobcount, $DAY_FREQS, $row["endTime"], $SETUP);
	if($row["interactive"])
	{
	    add_histogram($hist_job_intr_count, $DAY_FREQS, $row["endTime"], $SETUP);
	    add_histogram_value($hist_job_intr_agg_cputime, $DAY_FREQS, $row["endTime"], $row["CPUhr"], $SETUP);
	}
	else
	{
 	    add_histogram($hist_job_batch_count, $DAY_FREQS, $row["endTime"], $SETUP);
	    add_histogram_value($hist_job_batch_agg_cputime, $DAY_FREQS, $row["endTime"], $row["CPUhr"], $SETUP); 
	}
    } 
    mysql_free_result($result);
    #var_dump(array($jexitcodes,$jexitcodescount,$jexitcodesfailcount));die;

    array_round($hist_job_intr_agg_cputime, 2);
    array_round($hist_job_batch_agg_cputime, 2);

    $r = array(
	'series' => array(
	    'timestamps'       	=> $timestamps,
	    'Labels'	       	=> $stampLabels,
            'hist_jobcount'	=> $hist_jobcount,
            'hist_job_intr_count'	=> $hist_job_intr_count,
            'hist_job_batch_count'	=> $hist_job_batch_count,
            'hist_job_intr_agg_cputime'	=> $hist_job_intr_agg_cputime,
            'hist_job_batch_agg_cputime' => $hist_job_batch_agg_cputime,
	    'hist_labels'	=> get_histogram_labels($DAY_FREQS, $SETUP),
	),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." Batch vs Interactive Job Utilization (".$SETUP['window_txt'].")",
	)
    ); 

    return $r; 
}
 

function query_util_sch_timeline_jobs_sql(&$db, $SETUP)
{
    $SETUP['cluster']['hosts']['regex'] = $SETUP['cluster']['config']['batch']['hosts'];
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $join = '';
    $where = '';
    $group = array();
    
    getQueryFilter_jobs($SETUP, $join, $where, $group);

    $request = sprintf("
	    SELECT 
		startTime,
		endTime,
		((numExHosts*(SUB_EXCLUSIVE IS FALSE))+(%s * numExPhysicalHosts * (SUB_EXCLUSIVE IS TRUE))) as cpus,
		(exitStatus != '0') as exitNonZero,
		exitStatus,
		(exitInfo != 'TERM_UNKNOWN') as failed,
		exitInfo
	    FROM `%s_jobFinishLog`
	    %s 
	    WHERE %s
	    %s
	",
	$SETUP['cluster']['config']["ncpus"],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$join,
	$where,
	$group
    );                  

    init_timeline($cpus, $SETUP);
    init_timeline_stamps($timestamps, $stampLabels, $SETUP);
    init_timeline($maxcpus, $SETUP);
    init_timeline($exitNonZerocpus, $SETUP);
    init_timeline($failedcpus, $SETUP);
    add_timeline($maxcpus, $timestamps, $SETUP['start_time'], $SETUP['end_time'], $SETUP['cluster']['config']['batch']['maxcpus'], $SETUP);

    #create histogram for days
    $DAY_FREQS = create_histogram_buckets_every_day($SETUP);
    init_histogram_bins($hist_jobcount, $DAY_FREQS, $SETUP);
    init_histogram_bins($hist_jobfailcount, $DAY_FREQS, $SETUP);
    init_histogram_bins($hist_jobnonzerocount, $DAY_FREQS, $SETUP);

    #var_dump($DAY_FREQS);die;
    $totaljobs = 0;
    $totalexitnonzero = 0;
    $totalfailed = 0;
    $exitcodescol = array(); #exit code => column id
    $exitcodes = array(); #exit code per column
    $exitcodescount = array(); #exit code job count
    $exitcodesnonzerocount = array(); #exit code nonzero exit job count
    $exitcodes_known = array(); #exit code per column of known exit codes
    $exitcodesnonzerocount_known = array(); #exit code nonzero known exit job count
    $jexitcodescol = array(); #job exit code => column id
    $jexitcodes = array(); #job exit code per column
    $jexitcodescount = array(); #job exit code job count
    $jexitcodesfailcount = array(); #failed job count
    $jexitcodes_culled = array(); #job exit code per column
    $jexitcodescount_culled = array(); #job exit code job count
    $jexitcodesfailcount_culled = array(); #failed job count

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
	++$totaljobs;
	add_timeline($cpus, $timestamps, $row["startTime"], $row["endTime"], $row["cpus"], $SETUP);
	add_histogram($hist_jobcount, $DAY_FREQS, $row["endTime"], $SETUP);
    
	$exitInfo = $row["exitInfo"];
	if(!isset($exitcodescol[$exitInfo])) 
	{
	    $fcol = $exitcodescol[$exitInfo] = sizeof($exitcodes);
	    $exitcodescount[$fcol] = 0;
	    $exitcodesnonzerocount[$fcol] = 0;
	}
	$fcol = $exitcodescol[$exitInfo];
	$exitcodes[$fcol] = $exitInfo;
	$exitcodescount[$fcol]++;
	if($row["exitNonZero"] == 1) 
	    $exitcodesnonzerocount[$fcol]++;

 	$exitStatus = $row["exitStatus"];
	if(!isset($jexitcodescol[$exitStatus])) 
	{
	    $fcol = $jexitcodescol[$exitStatus] = sizeof($jexitcodes);
	    $jexitcodescount[$fcol] = 0;
	    $jexitcodesfailcount[$fcol] = 0;
	}
	$fcol = $jexitcodescol[$exitStatus];
	$jexitcodes[$fcol] = $exitStatus;
	$jexitcodescount[$fcol]++;
	if($row["failed"] == 1) 
	    $jexitcodesfailcount[$fcol]++; 

	if($row["exitNonZero"] == 1) {
	    add_timeline($exitNonZerocpus, $timestamps, $row["startTime"], $row["endTime"], $row["cpus"], $SETUP);
	    add_histogram($hist_jobfailcount, $DAY_FREQS, $row["endTime"], $SETUP);
	    ++$totalexitnonzero;
	}
	if($row["failed"] == 1) {
	    add_timeline($failedcpus, $timestamps, $row["startTime"], $row["endTime"], $row["cpus"], $SETUP);
	    add_histogram($hist_jobnonzerocount, $DAY_FREQS, $row["endTime"], $SETUP);
	    ++$totalfailed;
	}
    } 
    mysql_free_result($result);
    #var_dump(array($jexitcodes,$jexitcodescount,$jexitcodesfailcount));die;

    $jexitcodes_culled_other = 0;
    foreach($jexitcodes as $fcol => $code)
    {
	if($code != 0)
	{
	    if($jexitcodescount[$fcol] / $totalexitnonzero > 0.039)
	    {
		$jexitcodes_culled[$fcol] = $code;
		$jexitcodesfailcount_culled[$fcol] = $jexitcodescount[$fcol];
	    }
	    else
		$jexitcodes_culled_other += $jexitcodescount[$fcol];
	}
    }
    $fcol = sizeof($jexitcodes_culled);
    $jexitcodes_culled[$fcol] = 'Other';
    $jexitcodesfailcount_culled[$fcol] = $jexitcodes_culled_other; 

    foreach($exitcodes as $fcol => $code)
    {
	if($code != 'TERM_UNKNOWN')
	{
	    $exitcodes_known[$fcol] = $code;
	    $exitcodesnonzerocount_known[$fcol] = $exitcodesnonzerocount[$fcol];
	}
    }        

    $jobfailpielabels = array(
	"User Zero Exit Code Jobs = ".($totaljobs - $totalexitnonzero)." (".round((($totaljobs - $totalexitnonzero)/$totaljobs)*100,2)."%)", 
	"LSF Failed/Killed Jobs = ".($totalfailed)." (".round(($totalfailed/$totaljobs)*100,2)."%)",
	"Nonzero User Exit Code Jobs = ".($totalexitnonzero - $totalfailed)." (".round((($totalexitnonzero - $totalfailed)/$totaljobs)*100,2)."%)"
    );    

    $r = array(
	'series' => array(
	    'cpus'	       	=> $cpus,
	    'maxcpus'	       	=> $maxcpus,
	    'exitnonzero'      	=> $exitNonZerocpus,
	    'failed'	       	=> $failedcpus,
	    'timestamps'       	=> $timestamps,
	    'Labels'	       	=> $stampLabels,
 	    'YMax'	       	=> array($SETUP['cluster']['config']['batch']['maxcpus']),
	    'YMin'	       	=> array(0),
	    'jobfailpie'       	=> $jobfailpielabels,
	    'jobfailpievalues' 	=> array($totaljobs - $totalfailed, $totalfailed, $totalexitnonzero - $totalfailed ),
            'hist_jobcount'	=> $hist_jobcount,
            'hist_jobfailcount'	=> $hist_jobfailcount,
            'hist_jobnonzerocount' => $hist_jobnonzerocount,
	    'hist_labels'	=> get_histogram_labels($DAY_FREQS, $SETUP),
	    'exitcodes'		    => $exitcodes,
	    'exitcodescount'	    => $exitcodescount,
	    'exitcodesnonzerocount' => $exitcodesnonzerocount,
	    'exitcodes_known'		  => $exitcodes_known,
	    'exitcodesnonzerocount_known' => $exitcodesnonzerocount_known,
	    'jexitcodes'	    => $jexitcodes,
	    'jexitcodes_culled'	    => $jexitcodes_culled,
	    'jexitcodescount'	    => $jexitcodescount,
	    'jexitcodesfailcount'   => $jexitcodesfailcount,
	    'jexitcodesfailcount_culled' => $jexitcodesfailcount_culled
	),
	'desc' => array(
	    'cpus'	    => "Total Jobs = $totaljobs",
	    'failed'	    => "LSF Jobs Killed/Failed = $totalfailed",
	    'exitnonzero'   => "Nonzero User Generated Exit Code Jobs  = $totalexitnonzero",
	),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." Batch Job Utilization (".$SETUP['window_txt'].")",
	    'jobfaillabel_nzuser' => "LSF Failed/Killed Jobs = ".($totalfailed)." (".round(($totalfailed/$totaljobs)*100,2)."%) and Nonzero User Exit Code Jobs = ".($totalexitnonzero - $totalfailed)." (".round((($totalexitnonzero - $totalfailed)/$totaljobs)*100,2)."%)",
	    'jobfaillabel_nzlsf' => $jobfailpielabels[1], 
	)
    ); 

    return $r; 
}
 
function query_util_sch_jobs_fail_sql(&$db, $SETUP)
{
    $r = query_util_sch_timeline_jobs_sql($db, $SETUP);
    $r['text']['title'] = $SETUP['cluster']['config']["name"]." Job Exit Status (".$SETUP['window_txt'].")";
    return $r;
}

function query_util_sch_timeline($host, &$db, $SETUP)
{
    #use SQL if end time is more than a day from today
    #LSF is slow to dumping jobs, so we can use SQL for last 24hrs 
    if($SETUP['end_time'] < (time() - 86400))
	return query_util_sch_timeline_sql($db, $SETUP); #until sensor gets a full day of data

    $maxcores = $SETUP['cluster']['config']['batch']['maxhosts'] * $SETUP['cluster']['config']['ncores'];
    
    init_timeline_stamps($timestamps, $stamplabels, $SETUP);
    init_timeline($maxcpus, $SETUP, $maxcores);

    #get the actual host stats
    $ehosts = get_ganglia_hosts($SETUP, $host);
    if(count($ehosts) == 0) die("invalid host");

    $p = "lsf_stats.lsf.". $SETUP['cluster']['config']['lsf']['batch stats prefix'];

    #query all the rrd data at once
    $gquery = array();
    foreach($ehosts as $ehost)
       $gquery[$ehost] = array(
           $p."running_jobs"
       );

    gangliarrd($SETUP, $gquery, $rdata, $timestamps);
    unset($gquery);

    max_limit_timeline($rdata[$host][$p."running_jobs"], $maxcores, $SETUP);

    $r = array(
	'series' => array(
	    'cpus' => $rdata[$host][$p."running_jobs"],
	    'maxcpus' => $maxcpus,
	    'timestamps' => $timestamps,
	    'Labels' => $stamplabels,
 	    'YMax' => array($maxcores),
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
    return query_util_sch_timeline($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
} 

function query_util_sch_timeline_backup(&$db, $SETUP)
{
    return query_util_sch_timeline($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
}

function query_rsv_util_timeline(&$db, $SETUP)
{
    init_timeline_stamps($timestamps, $stampLabels, $SETUP);
    
    $host = $SETUP['cluster']['config']['lsf']['master'];
 
    #get the actual host stats
    $ehosts = get_ganglia_hosts($SETUP, $host);

    #query all the rrd data at once
    $gquery = array();
    foreach($ehosts as $ehost)
	$gquery[$ehost] = array(
	    $SETUP['cluster']['config']['lsf']['reservation']['system'],
	    $SETUP['cluster']['config']['lsf']['reservation']['user']
	);

    #$var_dump($gquery);die;
    gangliarrd($SETUP, $gquery, $rdata, $timestamps);
    unset($gquery); 

    $sysrsv = $rdata[$host][$SETUP['cluster']['config']['lsf']['reservation']['system']];
    $usrrsv = $rdata[$host][$SETUP['cluster']['config']['lsf']['reservation']['user']];

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
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
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
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $SETUP['cluster']['hosts']['regex'] = $SETUP['cluster']['config']['batch']['hosts'];
    return query_mem_util_stats_timeline($db, $SETUP);
} 

function query_mem_util_timeline_master(&$db, $SETUP)
{
    return query_mem_util_timeline($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
} 

function query_mem_util_timeline_backup(&$db, $SETUP)
{
    return query_mem_util_timeline($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
} 

/**
 * @brief query memory utilization statistics of high memory use jobs
 */
function query_mem_util_stats_high_use_jobs(&$db, $SETUP)
{
    if($SETUP['end_time'] - $SETUP['start_time'] >= 1209600)
	die("Please limit searches to a window less than 2 weeks to avoid missing jobs.");

    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    init_timeline_stamps($timestamps, $stampLabels, $SETUP);

    $HIGH_MEMORY_THRESHOLD = 0.70;

    #get the actual host stats
    $ehosts = get_ganglia_hosts($SETUP, $SETUP['cluster']['config']['batch']['hosts']);

    ##var_dump($ehosts);die;

    #query all the rrd data at once
    $gquery = array();
    foreach($ehosts as $ehost)
        $gquery[$ehost] = array(
            "mem_total", 
            "mem_free", 
            "mem_cached"
        );

    #var_dump($gquery);die;
    gangliarrd($SETUP, $gquery, $rdata, $timestamps);
    unset($gquery); 

    #var_dump($rdata);die;

    #hits above memory threshold host => [ timestamp => usage ]
    $hits = array();
 
    foreach($rdata as $host => $dhost)
    {
        #Calculate user used memory and then find the average
        $used = $dhost["mem_total"];
        sub_timelines($used, $dhost["mem_cached"], $SETUP);
        sub_timelines($used, $dhost["mem_free"], $SETUP);
        min_limit_void_timeline($used, 1, $SETUP); #dont allow negative memory
        min_limit_void_timeline($dhost["mem_total"], $SETUP['cluster']['config']['maxmemory'] * 0.90, $SETUP);
        div_timelines($used, $dhost["mem_total"], $SETUP);

        #find each time instance where above memory threshold
        foreach($used as $key => $pused)
            if($pused >= $HIGH_MEMORY_THRESHOLD && $pused != VOID)
        	$hits[$host][$timestamps[$key]] = $pused;
    }

    #release the ganglia data
    unset($rdata, $used);

    $jobs = array();
    $searched_timestamps = array();

    #search for job at each hit
    #and then merge all hits that apply to job
    #to avoid more sql queries since they are slow!
    while(count($hits) > 0)
    {
	$found = false;
	#find next instance to search for jobs
	foreach($hits as $host => &$stamps)
	    foreach($stamps as $timestamp => $usage)
	    {
		#var_dump(array('found', $host, $timestamp, $usage));
		$found = array($host, $timestamp, $usage);
		break 2;
	    }
	unset($stamps);

	if($found === false) break;
	    
	/** before this used a subquery to search
	  * against the given host but mysql was 
	  * really slow. this method gets all
	  * jobs at the given time and uses that
	  * to cull any other hits
	  * not exactly efficient but hopefully not horrible
	  * @HACK remove exclusion of matthews and ssgadmin and setup using user supplied options
	  */
	$request = sprintf("
		SELECT 
		    jl.jobId,
		    jl.submitTime,
		    jl.idx, 
		    jl.userName, 
		    jl.queue,
		    je.name as host,
		    jl.startTime,
		    jl.endTime
		FROM `%s_jobFinishLog` jl
		INNER JOIN %s_jobFinishLog_execHosts as je ON je.jobId = jl.jobId &&
		je.submitTime = jl.submitTime && je.idx = jl.idx &&
		je.name REGEXP %s  
		WHERE 
		(startTime <= %s && endTime >= %s) &&
		jl.userName != 'matthews' && jl.userName != 'ssgadmin'
	    ",
	    $SETUP['cluster']['config']['lsf']['sql']['prefix'],
	    $SETUP['cluster']['config']['lsf']['sql']['prefix'],
	    sqle($SETUP['cluster']['hosts']['regex']),
	    sqle($found[1]), sqle($found[1])
	);       

	$sqlres = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());   
	/** In theory, there should always be atleast 1 job returned
	  * and there should always be 1 purged hit
	  */
	while (count($hits) > 0 && $row = mysql_fetch_assoc($sqlres))  
	{
	    #convert lsf hostname to ganglia
	    $host = to_ganglia_host($SETUP, $row['host']);
	    if($host == "") continue;

	    /** Find and purge all hits that land in job 
	     * record the job that have hits
	     */
	    if(isset($hits[$host]) && !empty($hits[$host]))
		foreach($hits[$host] as $timestamp => $usage)
		{
		    if($timestamp >= $row['startTime'] && $timestamp <= $row['endTime'])
		    {
			unset($hits[$host][$timestamp]);
			if(empty($hits[$host]))  #cull empty hosts 
			    unset($hits[$host]);

			#make composite job key
			$jobkey = implode(':', array($row['jobId'], $row['submitTime'], $row['idx']));

			if(!isset($jobs[$jobkey]))
			{
			    $jobs[$jobkey]['jobId']		= $row['jobId'];
			    $jobs[$jobkey]['submitTime']	= $row['submitTime'];
			    $jobs[$jobkey]['idx']		= $row['idx'];
			    $jobs[$jobkey]['userName']		= $row['userName'];
			    $jobs[$jobkey]['queue']		= $row['queue']; 
			    $jobs[$jobkey]['usage']		= 0; #will be caught later
			}

			#find highest usage host
			if($usage > $jobs[$jobkey]['usage'])
			{
			    $jobs[$jobkey]['host']		= $host;
			    $jobs[$jobkey]['hosts'][]		= $host . "[". round($usage * 100, 4) ."]";
			    $jobs[$jobkey]['timestamp']		= $timestamp;
			    $jobs[$jobkey]['usage']		= $usage;
			}
		    }
		}
	}

	/** 
	  * cull all instances of this timestamp
	  * since we are getting all jobs at this 
	  * timestamp, its not needed to sql query
	  * again, shouldnt happen unless there
	  * is a hit when there are no jobs running
	  * TODO: figure out there are hits when no jobs running
	  */
        foreach($hits as $host => &$stamps)
	{ 
	    foreach($stamps as $timestamp => $usage)
		if($timestamp == $found[1])
		    unset($hits[$host][$timestamp]);

	    #cull empty hosts
	    if(empty($hits[$host]))  
		unset($hits[$host]); 
	}
	unset($stamps); 
    }

    unset($hits);
     
    #dump jobs in format for table rendering (col major)
    $NEXT = 0;
    $output['Time'] = array();
    $output['Usage'] = array();
    $output['Host'] = array();
    $output['jobId'] = array();
    $output['submitTime'] = array();
    $output['idx'] = array();
    $output['userName'] = array();
    $output['Queue'] = array(); 

    foreach($jobs as $job)
    {
	$output['Time'][$NEXT]		= date($SETUP['date_format'], $job['timestamp']);
	$output['Usage'][$NEXT]		= round($job['usage'] * 100, 4);
	$output['Host'][$NEXT]		= join(', ', $job['hosts']);
	#$output['Host'][$NEXT]		= $job['host'];
	$output['jobId'][$NEXT]		= $job['jobId'];
	$output['submitTime'][$NEXT]	= date($SETUP['date_format'], $job['submitTime']);
	$output['idx'][$NEXT]		= $job['idx'];
	$output['userName'][$NEXT]	= $job['userName'];
	$output['Queue'][$NEXT]		= $job['queue'];

	++$NEXT;
    }
    unset($jobs);
    
    $r = array(
	'series' => array(
	    'hu_job_time'	=> $output['Time'],
	    'hu_job_usage'	=> $output['Usage'],
	    'hu_job_host'	=> $output['Host'],
	    'hu_job_jobid'	=> $output['jobId'],
	    'hu_job_submittime'	=> $output['submitTime'],
	    'hu_job_idx'	=> $output['idx'],
	    'hu_job_username'	=> $output['userName'],
	    'hu_job_queue'	=> $output['Queue']
	),
	'desc' => array(),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." Batch Node Memory Utilization Histogram (".$SETUP['window_txt'].")"
	)
    ); 

    return $r;
}

/**
 * @brief query memory utilization statistics histogram and timeline
 */
function query_mem_util_stats_histogram_timeline(&$db, $SETUP)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    init_timeline_stamps($timestamps, $stampLabels, $SETUP);

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

    #var_dump($gquery);die;
    gangliarrd($SETUP, $gquery, $rdata, $timestamps);
    unset($gquery); 

    $USED_FREQS = array();
    $PINC = 5; #5% at a time
    for($t = 0; $t <= 100; $t += $PINC)
	$USED_FREQS[] = array(
	    'start'	=> $t - ($PINC / 2),
	    'stop'	=> $t + ($PINC / 2),
	    'label'	=> $t . "%"
	);

    init_histogram_bins($hist_memusage, $USED_FREQS, $SETUP);

    #var_dump($rdata);die;
    $phosts = array();
 
    foreach($rdata as $host => $dhost)
    {
	#Calculate user used memory and then find the average
	$used = $dhost["mem_total"];
	sub_timelines($used, $dhost["mem_cached"], $SETUP);
	sub_timelines($used, $dhost["mem_free"], $SETUP);
	min_limit_void_timeline($used, 0, $SETUP); #dont allow negative memory
	div_timelines($used, $dhost["mem_total"], $SETUP);
	$pused = avg_timeline($used, 100, $SETUP);

	add_histogram($hist_memusage, $USED_FREQS, $pused, $SETUP);

	$phosts[$host] = round($pused, 2);
    }

    $r = array(
	'series' => array(
	    'hist_labels'   => get_histogram_labels($USED_FREQS, $SETUP),
	    'hist_memusage' => $hist_memusage,
	    'host_pusage_hosts'   => array_keys($phosts),
	    'host_pusage_usage'   => array_values($phosts)
	),
	'desc' => array(),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." Batch Node Memory Utilization Histogram (".$SETUP['window_txt'].")"
	)
    ); 

    return $r;
}

function query_mem_util_histogram_timeline($host, &$db, $SETUP)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $SETUP['cluster']['hosts']['regex'] = $SETUP['cluster']['config']['batch']['hosts'];
    return query_mem_util_stats_histogram_timeline($db, $SETUP);
} 

function query_mem_util_histogram_timeline_master(&$db, $SETUP)
{
    return query_mem_util_histogram_timeline($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
} 

function query_mem_util_histogram_timeline_backup(&$db, $SETUP)
{
    return query_mem_util_histogram_timeline($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
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
    return query_util_timeline($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
} 

function query_util_timeline_backup(&$db, $SETUP)
{
    return query_util_timeline($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
}
 

function query_util_pe_freq(&$db, $SETUP)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $totals = array();    

    $join = '';
    $where = '';
    $group = array();
    getQueryFilter_jobs($SETUP, $join, $where, $group);
 
    $request = sprintf("
	    SELECT 
		@cpus:=((numExHosts*(SUB_EXCLUSIVE IS FALSE))+(%s * numExPhysicalHosts * (SUB_EXCLUSIVE IS TRUE))) as cpus,
		(((endTime - startTime) / (60*60)) * @cpus ) as CPUhr,
                ((ru_utime + ru_stime) / 3600) as time
	    FROM `%s_jobFinishLog`
	    %s
	    WHERE %s
	    %s
	",
	$SETUP['cluster']['config']["ncpus"],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$join,
	$where,
	$group
    );
    #var_dump($request);die;

    init_histogram_bins($jobs, $GLOBALS['PE_FREQS'], $SETUP);
    init_histogram_bins($cpus, $GLOBALS['PE_FREQS'], $SETUP);
    init_histogram_bins($cpuhrs, $GLOBALS['PE_FREQS'], $SETUP);
    init_histogram_bins($cputime, $GLOBALS['PE_FREQS'], $SETUP);

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
	add_histogram($jobs, $GLOBALS['PE_FREQS'], $row["cpus"], $SETUP);
	add_histogram_value($cpus, $GLOBALS['PE_FREQS'], $row["cpus"], $row["cpus"], $SETUP);
	add_histogram_value($cputime, $GLOBALS['PE_FREQS'], $row["cpus"], $row["time"], $SETUP);
	add_histogram_value($cpuhrs, $GLOBALS['PE_FREQS'], $row["cpus"], $row["CPUhr"], $SETUP);
    } 
    mysql_free_result($result);
    #var_dump($cputime);die;

    $r = array(
	'series' => array(
	    'jobs' => $jobs,
	    'cpus' => $cpus,
	    'cputime' => $cputime,
	    'cpuhrs' => $cpuhrs,
	    #'cpuhrsp' => $cpuhrsp,
	    'Labels' => get_histogram_labels($GLOBALS['PE_FREQS'], $SETUP)
	),
	'desc' => array(),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." PE Histogram (".$SETUP['window_txt'].")"
	) 
    ); 
    return $r; 
}

function query_util_job_node_freq(&$db, $SETUP)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $totals = array();    

    $join = '';
    $where = '';
    $group = array();
    getQueryFilter_jobs($SETUP, $join, $where, $group);
 
    $request = sprintf("
	    SELECT
		nodes,
		cpus,
		rcpus,
		SUM(CPUhr) as CPUhrs,
		COUNT(jobId) as jobs
	    FROM
	    (
		SELECT 
		    yellowstone_jobFinishLog.jobId as jobId,
		    numExPhysicalHosts as nodes,
		    numExHosts as rcpus,
		    @cpus:=((numExHosts*(SUB_EXCLUSIVE IS FALSE))+(%s * numExPhysicalHosts * (SUB_EXCLUSIVE IS TRUE))) as cpus,
		    (((endTime - startTime) / (60*60)) * @cpus ) as CPUhr 
		FROM `%s_jobFinishLog`
		%s
		WHERE %s
		%s
	    ) jobs
	    GROUP BY nodes,rcpus
	",
	$SETUP['cluster']['config']["ncpus"],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$join,
	$where,
	$group
    );
    #var_dump($request);die;
    
    #empty bins
    $nodes = array();
    $cpus = array();
    $rcpus = array();
    $CPUhrs = array();
    $jobs = array();

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
	$nodes[] = $row["nodes"];
	$cpus[] = $row["cpus"];
	$rcpus[] = $row["rcpus"];
	$CPUhrs[] = round($row["CPUhrs"],2);
	$jobs[] = $row["jobs"];
    } 
    mysql_free_result($result);
    #var_dump($cputime);die;

    $r = array(
	'series' => array(
	    'nodes' => $nodes,
	    'cpus' => $cpus,
	    'rcpus' => $rcpus,
	    'CPUhrs' => $CPUhrs,
	    'jobs' => $jobs
	),
	'desc' => array(),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." PE Histogram (".$SETUP['window_txt'].")"
	) 
    ); 
    return $r; 
}

function query_util_pe_percent_freq(&$db, $SETUP)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $r = query_util_pe_freq($db, $SETUP);

    $cpuhrtotal = array_sum($r['series']['cpuhrs']);
    $cpuhrsp = $r['series']['cpuhrs'];
    array_divide($cpuhrsp, $cpuhrtotal);
    array_divide($r['series']['cputime'], array_sum($r['series']['cputime']));
    array_divide($r['series']['jobs'], array_sum($r['series']['jobs']));
    array_multiply($cpuhrsp, 100);
    array_multiply($r['series']['cputime'], 100);
    array_multiply($r['series']['jobs'], 100);

    $r['series']['YMax'] = array(100);
    $r['series']['YMin'] = array(0); 

    #array_round($cputime, 2);
    #array_round($cpuhrs, 2);
    #array_round($cpuhrsp, 4);

    $r['series']['cpuhrs'] = $cpuhrsp;

    return $r;
}
 
function query_util_percent_timeline($host, &$db, $SETUP)
{
    $r = query_util_timeline($host, $db, $SETUP);

    $maxcores = $SETUP['cluster']['config']['batch']['maxhosts'] * $SETUP['cluster']['config']['ncores'];
    $maxthreads = $SETUP['cluster']['config']['batch']['maxcpus'];

    div_timeline($r['series']['actcpus'], $maxthreads, $SETUP); 
    div_timeline($r['series']['usercpus'], $maxthreads, $SETUP); 
    div_timeline($r['series']['nicecpus'], $maxthreads, $SETUP); 
    div_timeline($r['series']['wiocpus'], $maxthreads, $SETUP); 
    div_timeline($r['series']['systemcpus'], $maxthreads, $SETUP); 
    div_timeline($r['series']['maxthreads'], $maxthreads, $SETUP); 

    div_timeline($r['series']['cpus'], $maxcores, $SETUP); 

    $thread_scale = 200;
    $core_scale = 100;

    init_timeline($r['series']['maxcpus'], $SETUP, $core_scale);
    init_timeline($r['series']['maxthreads'], $SETUP, $thread_scale);
  
    mul_timeline($r['series']['actcpus'], $thread_scale, $SETUP);
    mul_timeline($r['series']['usercpus'], $thread_scale, $SETUP);
    mul_timeline($r['series']['nicecpus'], $thread_scale, $SETUP);
    mul_timeline($r['series']['wiocpus'], $thread_scale, $SETUP);
    mul_timeline($r['series']['systemcpus'], $thread_scale, $SETUP);

    mul_timeline($r['series']['cpus'], $core_scale, $SETUP);

    $r['series']['YMax'] = array($thread_scale);
    $r['series']['YMin'] = array(0);
    return $r;
} 

function query_util_percent_timeline_master(&$db, $SETUP)
{
    return query_util_percent_timeline($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
} 

function query_util_percent_timeline_backup(&$db, $SETUP)
{
    return query_util_percent_timeline($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
}

/**
 * @brief Query Top User Timeline
 * @param $db database to query
 * @param $timelines array to fill out with timelines for the top users
 * @param $others timeline for all other users
 * @param $timestamps array of timestamps for timeline
 * @param $stampLabels array of timestamp labels
 * @param $userList ordered array of top users
 */
function query_topuser_timeline(&$db, $SETUP)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $tlist = query_topuser_list($db, $SETUP);

    init_timelines($timelines, $tlist['series']['UserName'], $SETUP);
    init_timeline($others, $SETUP);
    init_timeline_stamps($timestamps, $stampLabels, $SETUP);

    $join = '';
    $where = '';
    $group = array();
    
    getQueryFilter_jobs($SETUP, $join, $where, $group);

    $request = sprintf("
	    SELECT 
		userName, 
		startTime,
		endTime,
		((numExHosts*(SUB_EXCLUSIVE IS FALSE))+(%s * numExPhysicalHosts * (SUB_EXCLUSIVE IS TRUE))) as cpus 
	    FROM `%s_jobFinishLog`
	    %s 
	    WHERE %s
	    %s
	",
	$SETUP['cluster']['config']["ncpus"],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$join,
	$where,
	$group
    );                  

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
	if(isset($timelines[$row["userName"]]))
	    add_timeline($timelines[$row["userName"]], $timestamps, $row["startTime"], $row["endTime"], $row["cpus"], $SETUP);
	else
	    add_timeline($others, $timestamps, $row["startTime"], $row["endTime"], $row["cpus"], $SETUP);
    } 
    mysql_free_result($result);
    #var_dump($timelines);die;

    $r = array(
	'series' => array(),
	'desc' => array(),
	'text' => $tlist['text']
    ); 
    foreach($timelines as $user => $data)
    {
	$k = 'CPUs.'.$user;
	$r['series'][$k] = $data;
	$r['desc'][$k] = $user;
    }
    $r['series']['Labels'] = $stampLabels;
    $r['series']['timestamps'] = $timestamps;
    $r['series']['[others]'] = $others;
    return $r;
}

function query_topuser_list(&$db, $SETUP)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $join = '';
    $where = '';
    $group = array();
    
    getQueryFilter_jobs($SETUP, $join, $where, $group);

    $request = sprintf("
	    SELECT 
		userName, 
		COUNT(%s_jobFinishLog.jobid) as JobCount, 
		sum(((endTime - startTime) / (60*60)) * ((numExHosts*(SUB_EXCLUSIVE IS FALSE))+(%s * numExPhysicalHosts * (SUB_EXCLUSIVE IS TRUE))) ) as CPUhr 
	    FROM `%s_jobFinishLog`
	    %s 
	    WHERE %s
	    GROUP BY userName 
	    ORDER BY CPUhr DESC 
	    LIMIT 0,25; 
	",
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$SETUP['cluster']['config']["ncpus"],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$join,
	$where
    );

    $userList = array();
    $jobCountList = array();
    $cpuHrList = array();

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
	$userList[] = $row["userName"];
	$jobCountList[] = $row["JobCount"];
	$cpuHrList[] = $row["CPUhr"];
    } 
    mysql_free_result($result);  

    $rank = array_keys($userList);

    #start at 1, not zero
    array_walk($rank, function(&$val,$key) use(&$array){ 
	++$val;
    });

    return array(
	'series' => array(
	    'UserRank' => $rank,
	    'UserName' => $userList,
	    'jobCount' => $jobCountList,
	    'CPUhr' => $cpuHrList
	),
	'text' => array(
		'title' => $SETUP['cluster']['config']["name"]." Top 25 Users by CPUhrs (".$SETUP['window_txt'].")"
	    )
    ); 
}

function query_fairshare_timeline(&$db, $SETUP)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $join = '';
    $where = '';
    $group = array();
    
    getQueryFilter_jobs($SETUP, $join, $where, $group);

    $request = sprintf("
	    SELECT 
		startTime,
		endTime,
		(
		    ((numExHosts*(SUB_EXCLUSIVE IS FALSE))
		    +(%s * numExPhysicalHosts * (SUB_EXCLUSIVE IS TRUE))) 
		) as CPUs,
		@s1:=LOCATE(\"/\", chargedSAAP, 2),
		@s2:=LOCATE(\"/\", chargedSAAP, @s1 + 1),
 		SUBSTRING(chargedSAAP, 2, @s2 - @s1 + 1) as facility,
		SUBSTRING(chargedSAAP, @s1 + 1, @s2 - @s1 - 1) proposalGroup
	    FROM `%s_jobFinishLog`
	    %s 
	    WHERE %s AND chargedSAAP != \"\"
	    %s
	",
	$SETUP['cluster']['config']["ncpus"],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$join,
	$where,
	$group
    );

    #var_dump($request);die;

    # this is a hack: only works on bf/ff
    # (CSL: 1-CCSM, 2-OtherCSL)
    # (COM: 3-NCAR, 4-UNIV)
    # (ASD: 5-ASD)
    $pgroups = array("1" => "CCSM", "2" => "OtherCSL", "3" => "NCAR", "4" => "UNIV", "5" => "ASD");

    init_timeline_stamps($timestamps, $stampLabels, $SETUP);
    $pgroup = array();

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
	$p = $row["proposalGroup"];
	if(isset($pgroups[$row["proposalGroup"]]))
	    $p = $pgroups[$row["proposalGroup"]];

	if(!isset($pgroup[$p]))
	    init_timeline($pgroup[$p], $SETUP);

	add_timeline($pgroup[$p], $timestamps, $row["startTime"], $row["endTime"], $row["CPUs"], $SETUP);
    } 
    mysql_free_result($result);  

    $r = array(
	'series' => array(
	    "timestamps" => $timestamps,
	    "Labels" => $stampLabels
	),
	'desc' => array(),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." Fair Share Proposal Groups by CPUhrs (".$SETUP['window_txt'].")",
            'cluster' => $SETUP['cluster']['name']
	)
    );  

    foreach($pgroup as $p => $pd)
    {
 	$k = 'pgroup.'.$p;

	$r['series'][$k] = $pd;
	$r['desc'][$k] = $p;
    }                                        

    return $r; 
}
 
function query_fairshare(&$db, $SETUP)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $join = '';
    $where = '';
    $group = array();
    
    getQueryFilter_jobs($SETUP, $join, $where, $group);

    $request = sprintf("
	    SELECT 
		sum(
		    ((endTime - startTime) / (60*60)) * ((numExHosts*(SUB_EXCLUSIVE IS FALSE))
		    +(%s * numExPhysicalHosts * (SUB_EXCLUSIVE IS TRUE))) 
		) as CPUhr,
		count(%s_jobFinishLog.jobId) as jobCount,
		@s1:=LOCATE(\"/\", chargedSAAP, 2),
		@s2:=LOCATE(\"/\", chargedSAAP, @s1 + 1),
		SUBSTRING(chargedSAAP, 2, @s2 - @s1 + 1) as facility,
		SUBSTRING(chargedSAAP, @s1 + 1, @s2 - @s1 - 1) proposalGroup,
		projectName 
	    FROM `%s_jobFinishLog`
	    %s 
	    WHERE %s AND chargedSAAP != \"\"
	    GROUP BY facility,proposalGroup
	    ORDER BY CPUhr DESC
	",
	$SETUP['cluster']['config']["ncpus"],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$join,
	$where
    );

    #var_dump($request);die;

    # this is a hack: only works on bf/ff
    # (CSL: 1-CCSM, 2-OtherCSL)
    # (COM: 3-NCAR, 4-UNIV)
    # (ASD: 5-ASD)
    $pgroups = array("1" => "CCSM", "2" => "OtherCSL", "3" => "NCAR", "4" => "UNIV", "5" => "ASD");

    $jobCountList = array();
    $facilityList = array();
    $proposalGroupList = array();
    $cpuHrList = array();

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
	$jobCountList [] = $row["jobCount"];
	$facilityList[] = $row["facility"];
	#translate magic numbers of proposal groups if possible
	$proposalGroupList[] = isset($pgroups[$row["proposalGroup"]]) ? $pgroups[$row["proposalGroup"]] : $row["proposalGroup"];
	$cpuHrList[] = $row["CPUhr"];
    } 
    mysql_free_result($result);  

    #extract list of the facilities (top lvl)
    $toplvlhrs = array();
    $toplvljobs = array();
    $totalhrs = 0;
    foreach($proposalGroupList as $key => $group)
    {
	$totalhrs += $cpuHrList[$key];

	if(!isset($toplvlhrs[$facilityList[$key]]))
	{
	    $toplvlhrs[$facilityList[$key]] = $cpuHrList[$key];
	    $toplvljobs[$facilityList[$key]] = $jobCountList[$key];
	}
	else
	{
	    $toplvlhrs[$facilityList[$key]] += $cpuHrList[$key];
	    $toplvljobs[$facilityList[$key]] += $jobCountList[$key];
	}
    }

    $r = array(
	'series' => array(
	    "cluster.cpuHr" => array_values($toplvlhrs),
	    "cluster.facility" => array_keys($toplvlhrs),
	    "pgroup.group" => $proposalGroupList,
	    "pgroup.facility" => $facilityList,
	    "pgroup.cpuHr" => $cpuHrList,
	    "pgroup.jobCount" => $jobCountList
	),
	'desc' => array(),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." Fair Share Tree by CPUhrs (".$SETUP['window_txt'].")",
            'cluster' => $SETUP['cluster']['name']
	)
    );  

    {#extract proposal groups data per facility
        $f = array(); #facilities

        #extract the hrs for each facility
        foreach($toplvlhrs as $facility => $hrs)
        {
            foreach($proposalGroupList as $key => $group)
            {
                if($facilityList[$key] == $facility)
                    #var_dump(array($facility,$group,$cpuHrList[$key]));
                    $f[$facility][$group] = $cpuHrList[$key];
            }
        }

        foreach($f as $facility => $pgroups)
        {
	    $r['series'][$facility.".cpuHr"] = array_values($pgroups);
	    $r['series'][$facility.".pgroup"] = array_keys($pgroups);
        }
    }
    return $r; 
}

function query_job(&$db, $SETUP, $jobId, $submitTime, $idx, &$job)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $request = sprintf("
	    SELECT 
		*,
		(((endTime - startTime) / (60*60)) * ((numExHosts*(SUB_EXCLUSIVE IS FALSE))+(%s * numExPhysicalHosts * (SUB_EXCLUSIVE IS TRUE))) ) as CPUhr
	    FROM `%s_jobFinishLog`  
	    WHERE jobId = %s AND submitTime = %s AND idx = %s
	    LIMIT 0,1
	",
	$SETUP['cluster']['config']["ncpus"],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
        sqle($jobId),
        sqle($submitTime),
        sqle($idx)
    );  

    $job = array();

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
	#should only get one (or 0) result
	$job = $row;
    } 
    mysql_free_result($result);  
} 

function query_job_askedHosts(&$db, $SETUP, $jobId, $submitTime, $idx, &$ahosts)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $request = sprintf("
	    SELECT 
		name
	    FROM `%s_jobFinishLog_askedHosts`  
	    WHERE jobId = %s AND submitTime = %s AND idx = %s
	",
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
        sqle($jobId),
        sqle($submitTime),
        sqle($idx)
    ); 
    $ahosts = array();

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
	$ahosts[] = array("name" => $row["name"]);
    } 
    mysql_free_result($result);  
}        

function query_job_exechosts(&$db, $SETUP, $jobId, $submitTime, $idx, &$ehosts)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $request = sprintf("
	    SELECT 
		name,
		count 
	    FROM `%s_jobFinishLog_execHosts`  
	    WHERE jobId = %s AND submitTime = %s AND idx = %s
	",
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
        sqle($jobId),
        sqle($submitTime),
        sqle($idx)
    ); 

    $ehosts = array();

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
	$ehosts[] = array("name" => $row["name"], "count" => $row["count"]);
    } 
    mysql_free_result($result);  
}

function query_jobs_fields_desc(&$db, $SETUP)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $request = sprintf("SELECT 
	COLUMN_NAME,COLUMN_COMMENT,COLUMN_TYPE
        FROM information_schema.COLUMNS
	WHERE table_name=%s",
	sqle($SETUP['cluster']['config']['lsf']['sql']['prefix']."_jobFinishLog")
    ); 

    $name = array();
    $desc = array();
    $type = array();

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
 	$rtype = $row["COLUMN_TYPE"];

	#fix timestamps
	switch($row["COLUMN_NAME"])
	{
	    case 'submitTime':
	    case 'beginTime':
	    case 'termTime':
	    case 'startTime':
	    case 'endTime':
	    case 'lastResizeTime':
		$rtype = 'timestamp';
		break;
	}

	#fix bool
	if($rtype == 'tinyint(1)')
	    $rtype = 'bool';

	$name[] = $row["COLUMN_NAME"];
	$desc[] = $row["COLUMN_COMMENT"];
 	$type[] = $rtype;
    } 
    mysql_free_result($result);   

    #add magical CPUhrs
    $name[] = 'CPUhrs';
    $desc[] = 'Total CPU Hours';
    $type[] = 'double';

    return array(
	'series' => array(
	    'field' => $name,
	    'desc' => $desc,
	    'type' => $type
	)
    );  
}

function query_jobs_list(&$db, $SETUP)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $join = '';
    $where = '';
    $group = array();
    
    getQueryFilter_jobs($SETUP, $join, $where, $group);

    $request = sprintf("
	    SELECT 
		startTime,
		endTime,
		userName,
		numExPhysicalHosts,
		(((endTime - startTime) / (60*60)) * ((numExHosts*(SUB_EXCLUSIVE IS FALSE))+(%s * numExPhysicalHosts * (SUB_EXCLUSIVE IS TRUE))) ) as CPUhrs,
		%s_jobFinishLog.jobId,
		%s_jobFinishLog.submitTime,
		%s_jobFinishLog.idx, 
		jobName
	    FROM `%s_jobFinishLog`
	    %s 
	    WHERE %s
	    %s
	    ORDER BY endTime DESC
	    LIMIT 0,1000
	",
	$SETUP['cluster']['config']["ncpus"],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$join,
	$where,
	$group
    );

    $jobs = array();

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    $jcount = mysql_num_rows($result);
    $skipped = (mysql_num_rows($result) >= 1000) ? 1000 : FALSE;
    while ($row = mysql_fetch_assoc($result))  
	$jobs[] = $row;
    mysql_free_result($result);  

    return array(
	'jobs' => $jobs,
	'skipped' => $skipped,
	'count' => $jcount
    );   
} 

function query_job_detail(&$db, $SETUP)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $jobId = (int) $_GET["jobId"];
    $submitTime = (int) $_GET["submitTime"];
    $idx = (int) $_GET["idx"];
     
    query_job($db, $SETUP, $jobId, $submitTime, $idx, $job);
    if(empty($job)) die("Error: Invalid job.");
    query_job_execHosts($db, $SETUP, $jobId, $submitTime, $idx, $ehosts);
    query_job_askedHosts($db, $SETUP, $jobId, $submitTime, $idx, $ahosts);

    $ah = array();
    $eh = array();
    $ehc = array();

    foreach($ahosts as $ahost)
	$ah[] = $ahost['name'];

    foreach($ehosts as $ehost)
    {
	$eh[] = $ehost['name'];
	$ehc[] = $ehost['count'];
    }
 
    return array(
	'series' => array(
	    'props' => $job,
	    'ahosts_name' => $ah,
 	    'ehosts_name' => $eh,
	    'ehosts_count' => $ehc
	),
	'desc' => array(),
    );  
}

function query_job_gauss(&$db, $SETUP)
{
    $ehosts = get_ganglia_hosts($SETUP, $SETUP['cluster']['config']['batch']['hosts']);

    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $r = array(
	'series' => array(),
	'desc' => array(),
	'text' => array()
    );  

    $jobId = (int) $_GET["jobId"];
    $submitTime = (int) $_GET["submitTime"];
    $idx = (int) $_GET["idx"];
     
    query_job($db, $SETUP, $jobId, $submitTime, $idx, $job);
    if(empty($job)) die("Error: Invalid job.");
    query_job_execHosts($db, $SETUP, $jobId, $submitTime, $idx, $ehosts);
    $title = $SETUP['cluster']['config']["name"]." Job (Per Node): ".$jobId.(($idx != 0) ? "[".$idx."]" : ''). " (".date($SETUP['date_format'],$submitTime).")";

    init_timeline_stamps($timestamps, $stampLabels, $SETUP);

    $rddlist = FALSE;
    switch($SETUP['report']['sub'])
    {
	case "gauss_cpu":
	    $rrdlist = array("load_five");
	    break;
 	case "gauss_mem":
	    $rrdlist = array("mem_total", "mem_free", "mem_cached", "swap_total", "swap_free");
	    break;
 	case "gauss_enet":
	    $rrdlist = array("bytes_in", "bytes_out");
	    break;
  	case "gauss_flop":
  	case "node_flop":
	    $rrdlist = array("hpm_dbl_rate_wallclock", "hpm_snl_rate_wallclock");
	    break;  
 	default:
	    die("invalid graph type");
    }

    #query all the rrd data at once
    $gquery = array();
    foreach($ehosts as $ehost)
	$gquery[$ehost["name"]] = $rrdlist;

    gangliarrd($SETUP, $gquery, $rdata, $timestamps);
    unset($gquery);

    $r['series']['Labels'] = $stampLabels;
    $r['series']['timestamps'] = $timestamps;
    $r['text']['title'] = $title;

    switch($SETUP['report']['sub'])
    {
	case "gauss_cpu":
	    $sdata = array();
	    foreach($rdata as $host => $dhost)
		$sdata[] = $dhost["load_five"];

	    $std = gauss_calc($sdata, $timestamps);

	    $r['series']['stdp1'] = $std['stdp1'];
	    $r['series']['stdm1'] = $std['stdm1'];
	    $r['series']['avg'] = $std['avg'];
	    $r['series']['minv'] = $std['min'];
	    $r['series']['maxv'] = $std['max'];
	    break;
   	case "node_flop": #per node flops
	    $sdata = array();
	    foreach($rdata as $host => $dhost)
	    {
		$r['series'][$host.'_dbl_flop'] = $dhost["hpm_dbl_rate_wallclock"];
		$r['series'][$host.'_snl_flop'] = $dhost["hpm_snl_rate_wallclock"];
	    }

	    $r['series']['max'] = $r['series'][$host.'_dbl_flop'];
	    add_timelines($r['series']['max'], $r['series'][$host.'_snl_flop'], $SETUP);
	    break;                             
  	case "gauss_flop":
	    $sdata = array();
	    foreach($rdata as $host => $dhost)
	    {
		$flops = $dhost["hpm_dbl_rate_wallclock"];
	        add_timelines($flops, $dhost["hpm_snl_rate_wallclock"], $SETUP);
		$sdata[] = $flops;
	    }

	    $std = gauss_calc($sdata, $timestamps);
             
	    $r['series']['stdp1'] = $std['stdp1'];
	    $r['series']['stdm1'] = $std['stdm1'];
	    $r['series']['avg'] = $std['avg'];
	    $r['series']['minv'] = $std['min'];
	    $r['series']['maxv'] = $std['max'];
	    break; 
 	case "gauss_mem":
	    $sdata = array();
	    $scale = 1 / (1000*1000); #value scaling (1kB to 1GB)
	    foreach($rdata as $host => $dhost)
	    {
		$values = array();

		foreach($timestamps as $key => $timestamp)
		{
		    if($dhost["mem_total"][$key] == VOID || $dhost["mem_free"][$key] == VOID || $dhost["mem_cached"][$key] == VOID)
			$values[$key] = VOID;
		    else #values provided
			$values[$key] = ($dhost["mem_total"][$key] - $dhost["mem_free"][$key] - $dhost["mem_cached"][$key]) * $scale;
		}

		$sdata[] = $values;
	    }

	    $std = gauss_calc($sdata, $timestamps);

	    $r['series']['stdp1'] = $std['stdp1'];
	    $r['series']['stdm1'] = $std['stdm1'];
	    $r['series']['avg'] = $std['avg'];
	    $r['series']['minv'] = $std['min'];
	    $r['series']['maxv'] = $std['max']; 
	    
	    $sdata = array();
	    foreach($rdata as $host => $dhost)
	    {
		$values = array();

		foreach($timestamps as $key => $timestamp)
		{
		    if($dhost["mem_total"][$key] == VOID)
			$values[$key] = VOID;
		    else 
			$values[$key] = $dhost["mem_total"][$key] * $scale;
		}

		$sdata[] = $values; 
	    }
	    $std = gauss_calc($sdata, $timestamps);

 	    $r['series']['max'] = $std['max'];
	    break;
 	case "gauss_enet":
	    #$rrdlist = array("bytes_in", "bytes_out");
	    $scale = 1/(1048576);
            $sdata = array();
	    foreach($rdata as $host => $dhost)
	    {
		scale_array($dhost["bytes_in"], $scale);
		$sdata[] = $dhost["bytes_in"];
	    }

	    $std = gauss_calc($sdata, $timestamps);

 	    $r['series']['istdp1'] = $std['stdp1'];
	    $r['series']['istdm1'] = $std['stdm1'];
	    $r['series']['iavg'] = $std['avg'];
	    $r['series']['iminv'] = $std['min'];
	    $r['series']['imaxv'] = $std['max']; 
            
            $sdata = array();
	    foreach($rdata as $host => $dhost)
	    {
		scale_array($dhost["bytes_out"], $scale);
		$sdata[] = $dhost["bytes_out"];
	    }

	    $std = gauss_calc($sdata, $timestamps);

 	    $r['series']['ostdp1'] = $std['stdp1'];
	    $r['series']['ostdm1'] = $std['stdm1'];
	    $r['series']['oavg'] = $std['avg'];
	    $r['series']['ominv'] = $std['min'];
	    $r['series']['omaxv'] = $std['max']; 
	    break;
 	default:
	    die("invalid graph type");
    }

    return $r;
}

function query_incidents(&$db, $SETUP)
{
#    sql($db, RT_DB);
#
#    $userFilter = "";
#    if(isset($SETUP['filters']['user']) && $SETUP['filters']['user'] != '')
#	$userFilter = sprintf(
#	    "INNER JOIN Users ON Users.id = Tickets.Owner && Users.Name REGEXP %s",
#	    sqle($SETUP['filters']['user'])
#	);
#
##@warning: by the nature of the data, these category filters will allow 
##incidents to pass when thay have multiple categorys
#    $request = sprintf("
#	    SELECT 
#		Tickets.id,
#		UNIX_TIMESTAMP(Tickets.Created) as Created,
#		UNIX_TIMESTAMP(Tickets.Resolved) as Resolved,
#		Status
#	    FROM `Tickets`
#	    %s
#	    INNER JOIN ObjectCustomFieldValues ON ObjectCustomFieldValues.ObjectId = Tickets.id
#	    INNER JOIN CustomFields ON 
#		ObjectCustomFieldValues.CustomField = CustomFields.id &&
#		CustomFields.Name = 'category' && 
#		ObjectCustomFieldValues.Content != 'FalsePositive' &&
#		ObjectCustomFieldValues.Content != 'Null'
#	    WHERE 
#		Tickets.Created >= FROM_UNIXTIME(%s) && 
#		Tickets.Resolved <= FROM_UNIXTIME(%s) &&
#		Tickets.id = Tickets.EffectiveId
#	    GROUP BY Tickets.id
#	    ORDER BY Tickets.id DESC
#	",
#	$userFilter,
#        sqle($SETUP['start_time']),
#        sqle($SETUP['end_time'])
#    ); 
#    #var_dump($request);die;
#
#    $inc = array();
#
#    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
#    while ($row = mysql_fetch_assoc($result))  
#    { 
#	$inc[] = $row;
#    } 
#    mysql_free_result($result);  
#    #var_dump($inc);die;
#
#    return array('incidents' => $inc);
}    

function query_incident_props(&$db, $SETUP)
{
#    sql($db, RT_DB);
#    $request = sprintf("
#	SELECT 
#	    CustomFields.Name,
#	    ObjectCustomFieldValues.Content,
#	    ObjectCustomFieldValues.LargeContent
#	FROM `ObjectCustomFieldValues` 
#	INNER JOIN CustomFields ON ObjectCustomFieldValues.CustomField = CustomFields.id
#	WHERE 
#	    objectid = %s && 
#	    ObjectCustomFieldValues.Disabled = 0 
#	",
#	$SETUP['filters']['ticket']
#    ); 
#
#    $props = array();
#
#    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
#    while ($row = mysql_fetch_assoc($result))  
#    { 
#	if(isset($row['LargeContent']))
#	{
#	    if($row['LargeContent'] != '')
#		$row['Content'] = $row['LargeContent'];
#
#	    unset($row['LargeContent']);
#	}
#
#	$props[] = array(
#	    "name" => $row['Name'],
#	    "content" => (isset($row['LargeContent']) && $row['LargeContent'] != '') ? $row['LargeContent'] : $row['Content']
#	);
#    } 
#    mysql_free_result($result);  
#
#
#    return array(
#	"props" => $props
#    );
}    

function query_incident_conv(&$db, $SETUP)
{
#    sql($db, RT_DB);
#    $request = sprintf("
#	    SELECT 
#		Transactions.Type,
#		UNIX_TIMESTAMP(Attachments.Created) as Created,
#		Attachments.Content
#	    FROM Tickets
#	    INNER JOIN Transactions ON 
#		Transactions.ObjectId = Tickets.id && 
#		Transactions.Type = 'Comment'
#	    INNER JOIN Attachments ON Attachments.TransactionId = Transactions.id 
#	    WHERE Tickets.id = %s
#	",
#	$SETUP['filters']['ticket']
#    ); 
#
#    $conv = array();
#
#    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
#    while ($row = mysql_fetch_assoc($result))  
#    { 
#	$conv[] = $row;
#    } 
#    mysql_free_result($result);  
#
#
#    return array(
#	"conv" => $conv
#    );
}

function query_gauss_explain(&$db, $SETUP)
{
    $y = array();
    $x = array();
    $d1 = array();
    $b = array();
    $z = array();
    $labels = array();

    for($i = -5; $i <= 5; $i += 0.25)
    {
	$v = 0.3989422806 * pow(2.7182818284, (-0.5 * pow($i, 2)));
	$l = #hard code in the std devis
	    (
		$i == 1 || $i == 2 || $i == 3 || $i == 4 || $i == 5 ||
		$i == -1 || $i == -2 || $i == -3 || $i == -4 || $i == -5
	    ) 
	    ? $i : VOID;  
	if($i == 0)
	    $l = "     0\nMean";
	$labels[] = $l;
	$z[] = 0;
	$x[] = $i;
	$y[] = ($i >= -2.25 && $i <= 2.25) ? $v : VOID;
	$ny[] = !($i > -2.25 && $i < 2.25) ? $v : VOID;
	$d1[] = ($i >= -1 && $i <= 1) ? $v : VOID;
	$b[] = ($i >= -2.25 && $i <= 2.25) ? $v : VOID;
    }

    return array(
	'series' => array(
	    'x' => $x,
	    'y' => $y,
	    'ny' => $ny,
	    'd1' => $d1,
	    'b' => $b,
	    'z' => $z,
	    'Labels' => $labels
	),
 	'text' => array(
	    'title' => 'Gaussian Graph Legend'
	) 
    );      
}

function query_bw(&$db, $SETUP)
{
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $where = '';

    $ts =  $SETUP['start_time'];
    $te =  $SETUP['end_time'];
    $host = $SETUP['filters']['host'];

    $request = sprintf("
	    SELECT 
		dst,
		timestamp,
		speed
	    FROM `bw`
	    WHERE timestamp >= %s AND timestamp  <= %s && src = %s
	",
	sqle($ts),
	sqle($te),
	sqle($host)
    );

    #var_dump($request);die;
    $timestamps = array();
    $dst = array();

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
	$timestamps[$row["timestamp"]] = date("m.d\nH",$row['timestamp']);
	$dst[$row["dst"]][$row["timestamp"]] = $row['speed'];
    } 
    mysql_free_result($result);  

    #var_dump($dst);die;

    $ret = array();
    foreach($timestamps as $timestamp => $time)
    {
	foreach(array_keys($dst) as $node)
	{
	    if(isset($dst[$node][$timestamp]) && $dst[$node][$timestamp] != -1)
		$ret['series']['node_'.$node][] = $dst[$node][$timestamp];
	    else
		$ret['series']['node_'.$node][] = VOID;
	}
    }

    foreach(array_keys($dst) as $node)
    {
	$ret['desc']['node_'.$node] = $node; 
    }

    $ret['series']['timestamps'] = array_values($timestamps);
    $ret['series']['times'] = array_keys($timestamps);
    $ret['text']['title'] = "Bandwidth from ".$host." (".$SETUP['window_txt'].")";
    #$ret['text']['query'] = $request;
    return $ret;
} 

function query_gpu_usage_timeline(&$db, $SETUP)
{
    if(!isset($SETUP['cluster']['config']['batch']['maxgpus']) ||  $SETUP['cluster']['config']['batch']['maxgpus'] == 0)
	die('cluster does not have gpus');

    #sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    init_timeline_stamps($timestamps, $stamplabels, $SETUP);
    $timelines = array();
    #init_timeline($sflops, $SETUP); ///

    #get the actual host stats
    $ehosts = get_ganglia_hosts($SETUP, $SETUP['cluster']['config']['batch']['hosts']);

    #gen list of gpus to query since clusters have different counts
    $gpuquery = array();
    for($i = 1; $i <= $SETUP['cluster']['config']['batch']['maxgpus']; ++$i)
       $gpuquery[] = "gpu".$i.".utilizationgpu";

    #query all the rrd data at once
    $gquery = array();
    foreach($ehosts as $ehost)
       $gquery[$ehost] = $gpuquery;

    #$var_dump($gquery);die;
    gangliarrd($SETUP, $gquery, $rdata, $timestamps);
    unset($gquery);

    #var_dump($rdata);die;
    $desc = array();
    init_timeline($gpu_max, $SETUP, count($ehosts) * $SETUP['cluster']['config']['batch']['maxgpus']);
    foreach($rdata as $host => $dhost)
    {
	$n = 'gpu_util_'.$host;
	$desc[$n] = $host;
	if(!isset($timelines[$n]))
	    init_timeline($timelines[$n], $SETUP);

	foreach($gpuquery as &$rrdfield)
	    add_timelines($timelines[$n], $dhost[$rrdfield], $SETUP);
    }

    foreach($timelines as &$tl)
    {
	div_timeline($tl, $SETUP['cluster']['config']['batch']['maxgpus'], $SETUP);
	div_timeline($tl, count($ehosts), $SETUP);
    }

    $r = array(
       'series' => $timelines,
       'desc' => $desc,
       'text' => array(
           'title' => $SETUP['cluster']['config']["name"]." Batch GPU Utilization (".$SETUP['window_txt'].")"
       )
    );
    $r['series']['timestamps'] = $timestamps;
    $r['series']['labels'] = $stamplabels;
    #$r['series']['max_gpus'] = $gpu_max;

    return $r;
}

function query_gpu_mem_usage_timeline(&$db, $SETUP)
{
    if(!isset($SETUP['cluster']['config']['batch']['maxgpus']) ||  $SETUP['cluster']['config']['batch']['maxgpus'] == 0)
	die('cluster does not have gpus');

    #sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    init_timeline_stamps($timestamps, $stamplabels, $SETUP);
    $timelines = array();
    #init_timeline($sflops, $SETUP); ///

    #get the actual host stats
    $ehosts = get_ganglia_hosts($SETUP, $SETUP['cluster']['config']['batch']['hosts']);

    #gen list of gpus to query since clusters have different counts
    $gpuquery = array();
    for($i = 1; $i <= $SETUP['cluster']['config']['batch']['maxgpus']; ++$i)
       $gpuquery[] = "gpu".$i.".utilizationmemory";

    #query all the rrd data at once
    $gquery = array();
    foreach($ehosts as $ehost)
       $gquery[$ehost] = $gpuquery;

    #$var_dump($gquery);die;
    gangliarrd($SETUP, $gquery, $rdata, $timestamps);
    unset($gquery);

    #var_dump($rdata);die;
    $desc = array();
    init_timeline($gpu_max, $SETUP, count($ehosts) * $SETUP['cluster']['config']['batch']['maxgpus']);
    foreach($rdata as $host => $dhost)
    {
	$n = 'gpu_util_'.$host;
	$desc[$n] = $host;
	if(!isset($timelines[$n]))
	    init_timeline($timelines[$n], $SETUP);

	foreach($gpuquery as &$rrdfield)
	    add_timelines($timelines[$n], $dhost[$rrdfield], $SETUP);
    }

    foreach($timelines as &$tl)
    {
	div_timeline($tl, $SETUP['cluster']['config']['batch']['maxgpus'], $SETUP);
	div_timeline($tl, count($ehosts), $SETUP);
    }

    $r = array(
       'series' => $timelines,
       'desc' => $desc,
       'text' => array(
           'title' => $SETUP['cluster']['config']["name"]." Batch GPU Memory Utilization (".$SETUP['window_txt'].")"
       )
    );
    $r['series']['timestamps'] = $timestamps;
    $r['series']['labels'] = $stamplabels;
    #$r['series']['max_gpus'] = $gpu_max;

    return $r;
}
 
function query_flop_timeline(&$db, $SETUP)
{
    #sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    init_timeline_stamps($timestamps, $stamplabels, $SETUP);
    init_timeline($dflops, $SETUP); ///
    init_timeline($sflops, $SETUP); ///
    init_timeline($mflops, $SETUP, 1000000); ///MFLOPS
    init_timeline($tflops, $SETUP, 1000000000000); ///TFLOPS

    #get the actual host stats
    $ehosts = get_ganglia_hosts($SETUP, $SETUP['cluster']['config']['batch']['hosts']);

    #query all the rrd data at once
    $gquery = array();
    foreach($ehosts as $ehost)
       $gquery[$ehost] = array(
           "hpm_dbl_rate_wallclock",
           "hpm_snl_rate_wallclock"
       );

    #$var_dump($gquery);die;
    gangliarrd($SETUP, $gquery, $rdata, $timestamps);
    unset($gquery);

    #var_dump($rdata);die;
    foreach($rdata as $host => $dhost)
    {
       add_timelines($sflops, $dhost["hpm_snl_rate_wallclock"], $SETUP);
       add_timelines($dflops, $dhost["hpm_dbl_rate_wallclock"], $SETUP);
    }

    mul_timelines($sflops, $mflops, $SETUP);
    div_timelines($sflops, $tflops, $SETUP);
    mul_timelines($dflops, $mflops, $SETUP);
    div_timelines($dflops, $tflops, $SETUP);

    $r = array(
       'series' => array(
           'sflops' => $sflops,
           'dflops' => $dflops,
           'timestamps' => $timestamps,
           'labels' => $stamplabels,
       ),
       'desc' => array(),
       'text' => array(
           'title' => $SETUP['cluster']['config']["name"]." Batch Average FLOPS (".$SETUP['window_txt'].")"
       )
    );

    return $r;
}

function query_sch_node_timeline($host, &$db, $SETUP)
{
    init_timeline_stamps($timestamps, $stamplabels, $SETUP);

    #get the actual host stats
    $ehosts = get_ganglia_hosts($SETUP, $host);

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

    $r = array(
       'series' => array(
           'bnodecount' => $rdata[$host][$p."nodes"],
           'bnodeok' => $rdata[$host][$p."nodes_ok"],
           'bnodeadmindown' => $rdata[$host][$p."nodes_admindown"],
           'bnodeunreach' => $rdata[$host][$p."nodes_unreach"],
           'bnodeunavail' => $rdata[$host][$p."nodes_unavail"],
           'bnodelimunavail' => $rdata[$host][$p."nodes_limunavail"],
           'bnodelimclosed' => $rdata[$host][$p."nodes_limclosed"],
           'bnodelimlocked' => $rdata[$host][$p."nodes_limlocked"],
           'timestamps' => $timestamps,
           'labels' => $stamplabels,
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

function query_util_sch_node_timeline(&$db, $SETUP)
{ #warning this function does not give acturate results with share jobs
    sql($db, $SETUP['cluster']['config']['lsf']['sql']);
    $join = '';
    $where = '';
    $group = array();
    
    getQueryFilter_jobs($SETUP, $join, $where, $group);

    $request = sprintf("
	    SELECT 
		startTime,
		endTime,
		numExPhysicalHosts as nodes
	    FROM `%s_jobFinishLog`
	    %s 
	    WHERE %s
	    %s
	",
	$SETUP['cluster']['config']['lsf']['sql']['prefix'],
	$join,
	$where,
	$group
    );                  

    init_timeline($nodes, $SETUP);
    init_timeline_stamps($timestamps, $stampLabels, $SETUP);

    #var_dump($request);die;

    $result = mysql_query($request, $db) or die('Unable to query DB: ' . mysql_error());  
    while ($row = mysql_fetch_assoc($result))  
    { 
	add_timeline($nodes, $timestamps, $row["startTime"], $row["endTime"], $row["nodes"], $SETUP);
    } 
    mysql_free_result($result);
    #var_dump($timelines);die;

    $r = array(
	'series' => array(
	    'nodes' => $nodes,
	    'timestamps' => $timestamps,
	    'Labels' => $stampLabels,
	),
	'desc' => array(),
	'text' => array(
	    'title' => $SETUP['cluster']['config']["name"]." Batch Node Utilization (".$SETUP['window_txt'].")"
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
    return query_sch_job_node_timeline($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
}

function query_util_sch_node_timeline_backup(&$db, $SETUP)
{         
    return query_sch_job_node_timeline($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
} 

function query_util_sch_node_percent_timeline_master(&$db, $SETUP)
{         
    return query_sch_job_node_percent_timeline($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
}

function query_util_sch_node_percent_timeline_backup(&$db, $SETUP)
{         
    return query_sch_job_node_percent_timeline($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
} 

function query_sch_node_timeline_master(&$db, $SETUP)
{         
    return query_sch_node_timeline($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
}

function query_sch_node_percent_timeline_master(&$db, $SETUP)
{
    return query_sch_node_percent_timeline($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
}

function query_sch_avail_pie_master(&$db, $SETUP)
{
    return query_sch_avail_pie($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
}

function query_sch_node_timeline_backup(&$db, $SETUP)
{         
    return query_sch_node_timeline($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
}

function query_sch_node_percent_timeline_backup(&$db, $SETUP)
{
    return query_sch_node_percent_timeline($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
}

function query_sch_avail_pie_backup(&$db, $SETUP)
{
    return query_sch_avail_pie($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
}

function query_sch_agg_stats($host, &$db, $SETUP)
{
    #max time over time period for cpus
    $wtime = $SETUP['window_time'];
    $maxtime = $wtime * $SETUP['cluster']['config']['batch']['maxhosts'];
    $maxcputime = $wtime * $SETUP['cluster']['config']['batch']['maxcpus'];
    $maxcoretime = $wtime * $SETUP['cluster']['config']['batch']['maxhosts'] * $SETUP['cluster']['config']['ncores'];

    if(isset($SETUP['cluster']['config']['pbspro']))
    {
	$flopavg = VOID;
	$schcpus = VOID;
	$uuser	 = VOID;
	$uidle	 = VOID;
	$usys	 = VOID;
	$uwuio	 = VOID;
	$nok	 = VOID;
	$sys_rsv = VOID;
	$usr_rsv = VOID;
	$jobwait = VOID;

		#round(max(0, $flopavg),4), 
		#round(bound(($schcpus/$maxcoretime)*100,0,100),2).'%', 
		#round(bound(($uuser/$maxcputime)*100,0,100),2).'%', 
		#round(bound(($uidle/$maxcputime)*100,0,100),2).'%', 
		#round(bound(($usys/$maxcputime)*100,0,100),2).'%', 
		#round(bound(($uwuio/$maxcputime)*100,0,100),2).'%', 
		#round(bound(($nok/$maxtime)*100,0,100),2).'%', 
		#$sys_rsv == VOID ? '-' : round(bound(($sys_rsv/$wtime),0,100),2).'%',
		#$usr_rsv == VOID ? '-' : round(bound(($usr_rsv/$wtime),0,100),2).'%',
		#round($jobwait, 2)
    }
    elseif(isset($SETUP['cluster']['config']['lsf']))
    {
	$SETUP['cluster']['hosts']['regex'] = $SETUP['cluster']['config']['batch']['hosts'];
	$r = query_sch_node_timeline($host, $db, $SETUP);
	$r2 = query_util_sch_timeline($host, $db, $SETUP);
	$r3 = query_flop_timeline($db, $SETUP);
	$r4 = query_util_stats_timeline($db, $SETUP);
	$r5 = query_rsv_util_timeline($db, $SETUP);
	$r6 = query_queue_wait($db, $SETUP);

	$rdata = &$r['series'];
	$r2data = &$r2['series'];
	$r3data = &$r3['series'];
	$r4data = &$r4['series'];
	$r5data = &$r5['series'];
	$r6data = &$r6['series'];

	#calc the floppery
	$ftl = $r3data['sflops'];
	add_timelines($ftl, $r3data['dflops'], $SETUP);
	$flopavg = avg_timeline($ftl, 1, $SETUP); 

	#calc user stats
	$uuser = sum_timeline($r4data['usercpus'], $SETUP['chart']['tslices'], $SETUP) + sum_timeline($r4data['nicecpus'], $SETUP['chart']['tslices'], $SETUP);
	$uwuio = sum_timeline($r4data['wiocpus'], $SETUP['chart']['tslices'], $SETUP);
	$usys = sum_timeline($r4data['systemcpus'], $SETUP['chart']['tslices'], $SETUP);
	$uidle = $maxcputime - ($uuser + $uwuio + $usys);

	#calc the node availablity
	max_limit_timeline($rdata['bnodeok'], $SETUP['cluster']['config']['batch']['maxhosts'], $SETUP);
	$nok = sum_timeline($rdata['bnodeok'], $SETUP['chart']['tslices'], $SETUP);
	#Only the contract number of nodes count towards util at each timestamp
	max_limit_timeline($r2data['cpus'], $SETUP['cluster']['config']['batch']['maxcpus'], $SETUP);
	$schcpus = sum_timeline($r2data['cpus'], $SETUP['chart']['tslices'], $SETUP);

	$usr_rsv = sum_timeline($r5data['usr_rsv'], $SETUP['chart']['tslices'], $SETUP);
	$sys_rsv = sum_timeline($r5data['sys_rsv'], $SETUP['chart']['tslices'], $SETUP);

	$jobwait = $r6data['values'][0];
    }

    $r = array(
       'series' => array(
 	   'hosts' => array(
		'wtime'		=> $wtime,
		'maxtime'	=> $maxtime,
		'maxcputime'	=> $maxcputime,
		'maxcoretime'	=> $maxcoretime
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
		$schcpus == VOID ? '-' : round(bound(($schcpus/$maxcoretime)*100,0,100),2).'%', 
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
    return query_sch_agg_stats($SETUP['cluster']['config']['lsf']['master'], $db, $SETUP);
} 

function query_sch_agg_stats_backup(&$db, $SETUP)
{
    return query_sch_agg_stats($SETUP['cluster']['config']['lsf']['backup'], $db, $SETUP);
}
                                           

?>
