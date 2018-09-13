<?php
require_once("include/common.php");
error_reporting(E_ALL);
ini_set("memory_limit",-1);

set_time_limit(4048);
date_default_timezone_set('America/Denver');
define("SECONDS_PER_DAY", (60*60*24), false);

$CLUSTERS = array(
); 

$CHART_THEMES = array( 
    'simple'
);

$TIME_RANGES = array(
#   'hour'=>'-1 hour',
#   '2hr'=>'-2 hours',
#   '4hr'=>'-4 hours',
#   'day'=>'-1 day',
   '2 days'=>'-2 days',
   'week'=>'-1 week',
   '2 weeks'=>'-2 weeks',
   'month'=>'-1 month',
   'year'=>'-1 year'
);

//break down of time frequences for time histograms
$TIME_FREQS = array(
    array(
	'start' => -1,   ///>
	'stop' => 120,  ///<=
	'label' => '<2m'
    ),
    array(
	'start' => 120,
	'stop' => 300,
	'label' => '<5m'
    ),
    array(
	'start' => 301,
	'stop' => 600,
	'label' => '<10m'
    ),
    array(
	'start' => 600,
	'stop' => 1200,
	'label' => '<20m'
    ),
    array(
	'start' => 1200,
	'stop' => 2400,
	'label' => '<40m'
    ),
    array(
	'start' => 2400,
	'stop' => 3600,
	'label' => '<1hr'
    ),
    array(
	'start' => 3600,
	'stop' => 7200,
	'label' => '<2hr'
    ),
    array(
	'start' => 7200,
	'stop' => 14400,
	'label' => '<4hr'
    ),
    array(
	'start' => 14400,
	'stop' => 28800,
	'label' => '<8hr'
    ),
    array(
	'start' => 28800,
	'stop' => 86400,
	'label' => '<1d'
    ),
    array(
	'start' => 86400,
	'stop' => 172800,
	'label' => '<2d'
    ),
    array(
	'start' => 172800,
	'stop' => 43545600,
	'label' => '>2d'
    )
);

//break down of pe frequences for util histograms
$PE_FREQS = array(
    array(
        'start' => -1,   ///>
        'stop' => 1,  ///<=
        'label' => '1 pe'
    ),
    array(
        'start' => 1,
        'stop' => 3,
        'label' => '2-3 pe'
    ),
    array(
        'start' => 3,
        'stop' => 4,
        'label' => '4 pe'
    ),
    array(
        'start' => 4,
        'stop' => 7,
        'label' => '5-7 pe'
    ),
    array(
        'start' => 7,
        'stop' => 8,
        'label' => '8 pe'
    ),
    array(
        'start' => 8,
        'stop' => 15,
        'label' => '9-15 pe'
    ),
    array(
        'start' => 15,
        'stop' => 16,
        'label' => '16 pe'
    ),
    array(
        'start' => 16,
        'stop' => 23,
        'label' => '17-23 pe'
    ),
    array(
        'start' => 23,
        'stop' => 24,
        'label' => '24 pe'
    ),
    array(
        'start' => 24,
        'stop' => 31,
        'label' => '25-31 pe'
    ),
    array(
        'start' => 31,
        'stop' => 32,
        'label' => '32 pe'
    ),
    array(
        'start' => 32,
        'stop' => 63,
        'label' => '33-63 pe'
    ),
    array(
        'start' => 63,
        'stop' => 64,
        'label' => '64 pe'
    ),
    array(
        'start' => 64,
        'stop' => 127,
        'label' => '65-127 pe'
    ),
    array(
        'start' => 127,
        'stop' => 128,
        'label' => '128 pe'
    ),
    array(
        'start' => 128,
        'stop' => 255,
        'label' => '129-255 pe'
    ),
    array(
        'start' => 255,
        'stop' => 256,
        'label' => '256 pe'
    ),
    array(
        'start' => 256,
        'stop' => 511,
        'label' => '257-511 pe'
    ),
    array(
        'start' => 511,
        'stop' => 512,
        'label' => '512 pe'
    ),
    array(
        'start' => 512,
        'stop' => 1023,
        'label' => '513-1023 pe'
    ),
    array(
        'start' => 1023,
        'stop' => 1024,
        'label' => '1024 pe'
    ),
    array(
        'start' => 1024,
        'stop' => 2047,
        'label' => '1025-2047 pe'
    ),
    array(
        'start' => 2047,
        'stop' => 2048,
        'label' => '2048 pe'
    ),
    array(
        'start' => 2048,
        'stop' => 4095,
        'label' => '2049-4095 pe'
    ),
    array(
        'start' => 4095,
        'stop' => 4096,
        'label' => '4096 pe'
    ),
    array(
        'start' => 4096,
        'stop' => 8191,
        'label' => '4097-8191 pe'
    ),
    array(
        'start' => 8191,
        'stop' => 8192,
        'label' => '8192 pe'
    ),
    array(
        'start' => 8192,
        'stop' => 16383,
        'label' => '8193-16383 pe'
    ),
    array(
        'start' => 16383,
        'stop' => 16384,
        'label' => '16384 pe'
    ),
    array(
        'start' => 16384,
        'stop' => 32767,
        'label' => '16385-32767 pe'
    ),
    array(
        'start' => 32767,
        'stop' => 32768,
        'label' => '32768 pe'
    ),
    array(
        'start' => 32768,
        'stop' => 65535,
        'label' => '32769-65535 pe'
    ),
    array(
        'start' => 65535,
        'stop' => 65536,
        'label' => '65536 pe'
    ),
    array(
        'start' => 65536,
        'stop' => 43545600,
        'label' => '>65536 pe'
    )
);

#parse the setup from the user
$SETUP = parseGet();
$SETUP['window_time'] = ($SETUP['end_time'] - $SETUP['start_time']);
$SETUP['date_format'] = "H:i:s m.d.y";
$SETUP['date_day_format'] = "m.d.y";
$SETUP['window_txt'] = date("H:i:s m.d.y", $SETUP['start_time']) ." to ". date("H:i:s m.d.y",$SETUP['end_time']);
$SETUP['chart']['width'] = 1024;
$SETUP['chart']['height'] = 768;
$SETUP['chart']['slices'] = 500;
#$SETUP['chart']['slices'] = 50;
$SETUP['chart']['tslices'] = ($SETUP['window_time']  / ($SETUP['chart']['slices'] - 1)); #dont modify
$SETUP['chart']['label']['slices'] = 50;

$SETUP['chart']['label']['format'] = "H:i:s\nm-d-y";
if($SETUP['window_time'] > 86400*3) #dont show hours if more than 3 days
    $SETUP['chart']['label']['format'] = "m-d-y";

$SETUP['chart']['rrd']['fudge_factor'] = 0.5; #factor to include values from RRD data, aka the fudge factor 

define("RT_DB", "rt3", false);

define("PCHART_DIR", "/var/www/pchart/", false);
putenv('GDFONTPATH=' .realpath(PCHART_DIR.'/fonts/'));

$REPORTS = array(
#     'incident' => array(
#	'visible' => false,
#	'incident_props' => array(
#	    'type' => 'table',	
#            'src' => 'query_incident_props',
#        ),
# 	'incident_conv' => array(
#	    'type' => 'table',	
#            'src' => 'query_incident_conv',
#        )
#    ),
    'Job' => array(
	'visible' => false,
	'gauss_flop' => array(
	    'type' => 'graph',	
            'src' => 'query_job_gauss',
            'tpl' => 'gauss_flop'
        ), 
 	'node_flop' => array( #need to setup a csv viewer
	    'type' => 'graph',	#its a lie!
            'src' => 'query_job_gauss',
            'tpl' => 'gauss_flop'
        ), 
	'gauss_enet' => array(
	    'type' => 'graph',	
            'src' => 'query_job_gauss',
            'tpl' => 'gauss_net'
        ),
 	'gauss_cpu' => array(
	    'type' => 'graph',	
            'src' => 'query_job_gauss',
            'tpl' => 'gauss_cpu'
        ),
 	'gauss_mem' => array(
	    'type' => 'graph',	
            'src' => 'query_job_gauss',
            'tpl' => 'gauss_mem'
        ),
 	'detail' => array(
	    'type' => 'job',
            'src' => 'query_job_detail',
 	    'fields' => array(
		#source to query columns
		'report' => 'Jobs', 
		'sub' => 'Job Fields'
	    )
	) 
    ),
    'Utilization' => array(
	'Reservation Utilization' => array(
	    'type' => 'graph',	
            'src' => 'query_rsv_util_timeline',
            'tpl' => 'util_rsv_hist'
        ), 
        'Batch Usage Memory Stack (master)' => array(
	    'type' => 'graph',	
            'src' => 'query_mem_util_timeline_master',
            'tpl' => 'mem_util_stack'
        ), 
        'Batch Usage Memory (backup)' => array(
	    'type' => 'graph',	
            'src' => 'query_mem_util_timeline_backup',
            'tpl' => 'mem_util_stack'
        ),  
        'Batch Usage CPU Stack (master)' => array(
	    'type' => 'graph',	
            'src' => 'query_util_timeline_master',
            'tpl' => 'util_stack'
        ), 
        'Batch Usage CPU Stack (backup)' => array(
	    'type' => 'graph',	
            'src' => 'query_util_timeline_backup',
            'tpl' => 'util_stack'
        ), 
        'Batch Usage CPU Percent Stack (master)' => array(
	    'type' => 'graph',	
            'src' => 'query_util_percent_timeline_master',
            'tpl' => 'util_percent_stack'
        ),  
        'Batch Usage CPU Percent Stack (backup)' => array(
	    'type' => 'graph',	
            'src' => 'query_util_percent_timeline_backup',
            'tpl' => 'util_percent_stack'
	),
        'Batch Usage CPU Scheduler Stack (master)' => array(
	    'type' => 'graph',	
            'src' => 'query_util_sch_timeline_master',
            'tpl' => 'util_stack'
        ),
        'Batch Usage CPU Scheduler Stack (backup)' => array(
	    'type' => 'graph',	
            'src' => 'query_util_sch_timeline_backup',
            'tpl' => 'util_stack'
        ),               
        'Batch Scheduler Node Stack (master)' => array(
	    'type' => 'graph',	
            'src' => 'query_sch_node_timeline_master',
            'tpl' => 'sch_node_stack'
        ),                   
        'Batch Scheduler Node Percent Stack (master)' => array(
	    'type' => 'graph',	
            'src' => 'query_sch_node_percent_timeline_master',
            'tpl' => 'sch_node_percent_stack'
        ),                   
        'Batch Scheduler Agg Stats (master)' => array(
	    'type' => 'table',	
            'src' => 'query_sch_agg_stats_master',
	    'tables' => array(
		 array(
		    'title' => 'Batch Scheduler Agg Stats',
		    'columns' => array(
			array(
			    'series' => 'stats',
			    'name' => 'Statistic'
			),
 			array(
			    'series' => 'values',
			    'name' => 'values'
			)
		    )
		)
	    )            
        ),   
        'Batch Scheduler Agg Stats (backup)' => array(
	    'type' => 'table',	
            'src' => 'query_sch_agg_stats_backup',
	    'tables' => array(
		 array(
		    'title' => 'Batch Scheduler Agg Stats',
		    'columns' => array(
			array(
			    'series' => 'stats',
			    'name' => 'Statistic'
			),
 			array(
			    'series' => 'values',
			    'name' => 'values'
			)
		    )
		)
	    )            
        ),    
        'batch scheduler node stack (backup)' => array(
	    'type' => 'graph',	
            'src' => 'query_sch_node_timeline_backup',
            'tpl' => 'sch_node_stack'
        ),                   
        'Batch Scheduler Node Percent Stack (backup)' => array(
	    'type' => 'graph',	
            'src' => 'query_sch_node_percent_timeline_backup',
            'tpl' => 'sch_node_percent_stack'
        ),            
	'Batch Avail vs Util Stack (master)' => array(
	    'type' => 'graph',	
            'src' => 'query_util_sch_node_timeline_master',
            'tpl' => 'util_sch_node_stack'
        ),    
	'Batch Avail vs Util Stack (backup)' => array(
	    'type' => 'graph',	
            'src' => 'query_util_sch_node_timeline_backup',
            'tpl' => 'util_sch_node_stack'
        ),       
	'Batch Avail vs Util Percent Stack (master)' => array(
	    'type' => 'graph',	
            'src' => 'query_util_sch_node_percent_timeline_master',
            'tpl' => 'util_sch_node_percent_stack'
        ),    
	'Batch Avail vs Util Percent Stack (backup)' => array(
	    'type' => 'graph',	
            'src' => 'query_util_sch_node_percent_timeline_backup',
            'tpl' => 'util_sch_node_percent_stack'
        )
    )
);

?>
