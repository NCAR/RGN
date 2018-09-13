<?php

if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
    die("Please disable magic quotes");

##Volunteer to die first
#$oomadj = fopen('/proc/self/oom_score_adj', 'w');
#if(!$oomadj) die('unable to adjust oom score');
#fwrite($oomadj, '1000');
#fclose($oomadj);
#unset($oomadj);

/**
 * @brief determines if str is an integer and extracts integer
 * @return true if is a integer
 */
function isInt($str, &$int)
{
    if($str == "" || !is_numeric($str))
	return false;

    $ints = array();
    $ret = preg_match('/^\s*([0-9]+)\s*$/', $str, $ints);

    if($ret)
	$int = (int) $ints[1];

    return $ret;
}

/**
 * @brief get time from string
 * takes a string which can take time in several formats
 * and attempts to convert it to a timestamp
 * @param $str string to convert
 * @return time in seconds or FALSE
 */
function getTime($str)
{
    $time = FALSE;

    if(!isInt($str, $time))
	$time = strtotime($str);

    return $time;
}

/**
 * @brief get seconds from a ##:##:## string
 * converts a time string to seconds for searches 
 * does not do relative values to current time()
 * @param $str string to convert
 * @return time in seconds or FALSE
 */
function getSeconds($str)
{
    if(is_null($str) || $str === FALSE) return FALSE;

    $time = 0;
    $i = 0;

    foreach(array_reverse(explode(":", preg_replace('/\s+/','', $str))) as $u)
    {
	$v = FALSE;
	if(!isInt($u, $v)) return FALSE; 
	if($i == 0) #minutes
	    $m = 60;
 	if($i == 1) #hours
	    $m = 3600;
 	if($i == 2) #days
	    $m = 86400;
 	if($i > 3) return FALSE;
 
	$time += $v * $m;
	++$i;
    }

    return $time;
}          

/**
 * @brief scale an array
 * @param $array array of values to scale
 * @param $scalar scale value
 */
function scale_array(&$array, $scalar)
{
    foreach($array as &$value)
    {
	if($value != VOID)
	    $value *= $scalar;
    }
}

/**
 * @brief Parse the GET array
 * reads and parses each variable
 * sets all the constants
 */
function parseGet()
{
    $starttime = time() - 60*60*7; #default to 1 week back
    $starttxt = "-1 week";
    $endtime = time();
    $endtxt = "now";
    $defr = "week";

    if(isset($_GET["r"]))
    {       
	if($_GET["r"] == "custom") 
	{
	    if(isset($_GET["cs"]) && isset($_GET["ce"]) && $_GET["cs"] != "" && $_GET["ce"] != "")
	    {
		$starttime = getTime($_GET["cs"]);
		$endtime = getTime($_GET["ce"]);
		$starttxt = $_GET["cs"];
		$endtxt = $_GET["ce"];

		if($starttime === FALSE)
		    die("unable to parse start time.");

		if($endtime === FALSE)
		    die("unable to parse start time."); 
	    }
	    else
		die("custom time window given but cs and ce not given");
	}
	else #easy time window 
	{
	    if(!isset($GLOBALS["TIME_RANGES"][$_GET["r"]]))
		die("Invalid Preset Time Window");

	    $starttime = strtotime($GLOBALS["TIME_RANGES"][$_GET["r"]]);
	    if($starttime === FALSE)
		die("Invalid Preset Time Window");
	    $starttxt = $GLOBALS["TIME_RANGES"][$_GET["r"]];
	}
    }

    if(!($starttime > 0 || $endtime > 0))
	die("invalid time window");

    if($starttime > $endtime)
    { #user swapped times, fix it silently
	$buffer = $endtime;
	$endtime = $starttime;
	$starttime = $buffer;
    }
   
    if(isset($_GET["c"]) && !isset($GLOBALS["CLUSTERS"][$_GET["c"]]))
	die("Unkown cluster");

    $c = isset($_GET["c"]) ? $_GET["c"] : "cheyenne";
    return array(
	"filters"	=> $_GET,
	"debug"		=> isset($_GET["debug"]) ? $_GET["debug"] : "",
	"start_time"	=> $starttime,
	"start_txt"	=> $starttxt,
	"end_time"	=> $endtime,
	"end_txt"	=> $endtxt,
	"time_range"    => isset($_GET["r"]) ? $_GET["r"] : $defr,
	"cluster"	=> array(
	    "name"	=> $c,
	    "config"	=> $GLOBALS['CLUSTERS'][$c],
	    "queues"	=> array(
		"regex" => isset($_GET["queue"]) ? $_GET["queue"] : NULL
	    ),
 	    "hosts"	=> array(
		"default" => !(isset($_GET["host"]) && $_GET["host"] != ""),
		"regex" => (
		    (isset($_GET["host"]) && $_GET["host"] != "")
		    ? 
			$_GET["host"]
		    : 
		    (
			isset($GLOBALS['CLUSTERS'][$c]["default hosts to batch"]) &&
			$GLOBALS['CLUSTERS'][$c]["default hosts to batch"] == "yes"
			?
			    $GLOBALS['CLUSTERS'][$c]['batch']['hosts']
			:
			    NULL
		    )
		)
	    ),
	    "user"	=> array(
		"regex" => isset($_GET["user"]) ? $_GET["user"] : NULL
	    ),
 	    "job"		=> array(
		"id"		=> isset($_GET["jobId"]) ? $_GET["jobId"] : NULL,
		"idx"		=> isset($_GET["idx"]) ? $_GET["idx"] : NULL,
		"submitTime"	=> isset($_GET["submitTime"]) ? $_GET["submitTime"] : NULL,
		"runtime"	=> array(
		    "min"	=> isset($_GET["rt_min"]) && getSeconds($_GET["rt_min"]) !== FALSE ? $_GET["rt_min"] : NULL, 
		    "max" 	=> isset($_GET["rt_max"]) && getSeconds($_GET["rt_max"]) !== FALSE  ? $_GET["rt_max"] : NULL
		),
		"slots"		=> array(
		    "min"	=> isset($_GET["slot_min"]) ? $_GET["slot_min"] : NULL, 
		    "max"	=> isset($_GET["slot_max"]) ? $_GET["slot_max"] : NULL
		)
	    )
	),
	"chart"		=> array(
	    "theme"		=> array(
		"name"		=> isset($_GET["theme"]) ? $_GET["theme"] : "simple",
		"background"	=> "img/cisl.png",
	    )
	),
	"report" => array(
	    "name"	    => isset($_GET["report"]) ? $_GET["report"] : "general",
	    "sub"	    => isset($_GET["report_sub"]) ? $_GET["report_sub"] : "" 
	),
	"json" => array(
	    "query"	    => isset($_GET["jquery"]) ? $_GET["jquery"] : "",
	)
    );
}

// Function to calculate square of value - mean
/// @from http://php.net/manual/en/function.stats-standard-deviation.php
function sd_square($x, $mean) 
{ 
    return pow($x - $mean, 2); 
}

// Function to calculate standard deviation (uses sd_square)    
/// @from http://php.net/manual/en/function.stats-standard-deviation.php
function sd($raw_array) 
{
	#Filter out VOIDs
	$array = array();
	foreach($raw_array as $value)
	    if($value != VOID)
		$array[] = $value;
    
	if(count($array) < 2) return 0;
	// square root of sum of squares devided by N-1
	return sqrt(
	    array_sum(
		array_map("sd_square", $array, array_fill(
		    0,	count($array), (array_sum($array) / count($array)) 
		    ) 
		) 
	    ) / (count($array)-1) 
	);
}

/**
 * @brief create histogram buckets
 * 1 bucket per day
 * @return histogram frequency array
 */
function create_histogram_buckets_every_day($SETUP)
{
    #create histogram for days
    $DAY_FREQS = array();
    $ts = floor($SETUP['start_time'] / SECONDS_PER_DAY) * SECONDS_PER_DAY; #start time forced to start of beginning of day
    $te = (floor($SETUP['end_time'] / SECONDS_PER_DAY) + 1) * SECONDS_PER_DAY; #end time forced to start of end of day 
    for($t = $ts; $t < $te; $t += SECONDS_PER_DAY)
	$DAY_FREQS[] = array(
	    'start'	=> $t,
	    'stop'	=> $t + SECONDS_PER_DAY,
	    'label'	=> date($SETUP['date_day_format'], $t)
	); 

    return $DAY_FREQS;
}

/**
 * @brief bound value by min to max
 */
function bound($value, $min, $max)
{
    if($value == VOID)
	return $min;

    if($value >= $min)
    {
	if($value <= $max)
	    return $value;
	else
	    return $max;
    }
    else
	return $min;
}

?>
