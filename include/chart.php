<?php

/**
 * @brief Add 1 to histogram bin per bin config
 * @param $array array with values to round
 * @param $precision number of digits after the decimal point
 */
function array_round(&$array, $precision)
{
    for($i = 0; $i < count($array); ++$i)
	$array[$i] = round($array[$i], $precision);
}  

/**
 * @brief Divide each element of array by Value
 * @param $array array with values
 * @param $value value to divide with
 */
function array_divide(&$array, $value)
{
    for($i = 0; $i < count($array); ++$i)
	$array[$i] /= $value;
}   

/**
 * @brief Multiple each element of array by Value
 * @param $array array with values
 * @param $value value to multiply with
 */
function array_multiply(&$array, $value)
{
    for($i = 0; $i < count($array); ++$i)
	$array[$i] *= $value;
}    

/**
 * @brief Initialize Histogram
 * @param $data makes array of zeros
 * @param $binc bin configuration array
 */
function init_histogram_bins(&$bin, $binc, $SETUP)
{
    $bin = array();
    for($i = 0; $i < count($binc); ++$i)
	$bin[$i] = 0; 
}

/**
 * @brief Add 1 to histogram bin per bin config
 * @param $bins array of bins
 * @param $binc bin config
 * @param $where value to check per bin config
 */
function add_histogram(&$bins, $binc, $where, $SETUP)
{
    for($i = 0; $i < count($binc); ++$i)
	if($where > $binc[$i]['start'] && $where <= $binc[$i]['stop'])
	    $bins[$i] += 1;
} 

/**
 * @brief Add value to histogram bin per bin config
 * @param $bins array of bins
 * @param $binc bin config
 * @param $where value to check per bin config
 * @param $value value to add to bin
 */
function add_histogram_value(&$bins, $binc, $where, $value, $SETUP)
{
    for($i = 0; $i < count($binc); ++$i)
	if($where > $binc[$i]['start'] && $where <= $binc[$i]['stop'])
	    $bins[$i] += $value; 
}  
	   
/**
 * @brief Get histogram labels from bin config
 * @param $binc bin config
 */
function get_histogram_labels($binc, $SETUP)
{
    $bin = array();
    for($i = 0; $i < count($binc); ++$i)
	$bin[$i] = $binc[$i]['label'];
    return $bin;
}
 
/**
 * @brief Initialize Timeline
 * @param $data makes array of zeros
 */
function init_timeline(&$data, $SETUP, $default = 0)
{
    $data = array();
    for($i = 0; $i < $SETUP['chart']['slices']; ++$i)
	$data[$i] = $default; 
}

/**
 * @brief Initialize set of Times lines
 * @param $data array of array to fill with zeros
 * @param $keys array of keys for data to fill
 */
function init_timelines(&$data, &$keys, $SETUP)
{
    $data = array();
    foreach($keys as $key)
	init_timeline($data[$key], $SETUP);
}

/**
 * @brief Initialize Timestamps for Timeline
 * @param $timestamps array of timestamps to fill
 * @param $stampLabels array of timestamp labels to fill out
 */
function init_timeline_stamps(&$timestamps, &$stampLabels, $SETUP)
{
    $stampLabels = array();
    $timestamps = array();
    for($i = 0; $i < $SETUP['chart']['slices']; ++$i)
    {
	$timestamps[$i] = round($SETUP['start_time'] + $SETUP['chart']['tslices'] * $i);

	if($i % $SETUP['chart']['label']['slices'] == 0 || $i == $SETUP['chart']['slices'] - 1)
	    $stampLabels[$i] = date($SETUP['chart']['label']['format'], $timestamps[$i]);
	else
	    $stampLabels[$i] = VOID;
    } 
}

/**
 * @brief Add scaled accumlated time of a given time span to Timeline for given time window
 * @param $timeline array containing timeline
 * @param $timestamps array of timestamps for timeline
 * @param $start time to start adding values
 * @param $stop time to stop adding values
 * @param $scale scale accumulated value
 */
function add_accumulated_time_timeline(&$timeline, &$timestamps, $start, $stop, $scale, $SETUP)
{
    for($i = 0; $i < $SETUP['chart']['slices']; ++$i)
	if($timestamps[$i] >= $start && $timestamps[$i] <= $stop)
	    $timeline[$i] += $scale * ($timestamps[$i] - $start);
}
 

/**
 * @brief Add value to Timeline for given time window
 * @param $timeline array containing timeline
 * @param $timestamps array of timestamps for timeline
 * @param $start time to start adding values
 * @param $stop time to stop adding values
 * @param $value value to add during time window
 */
function add_timeline(&$timeline, &$timestamps, $start, $stop, $value, $SETUP)
{
    for($i = 0; $i < $SETUP['chart']['slices']; ++$i)
	if($timestamps[$i] >= $start && $timestamps[$i] <= $stop)
	    $timeline[$i] += $value;
}

/**
 * @brief for min limit for each value of timeline
 * @param $timeline array containing timeline
 * @param $min min possible value for each value
 */
function min_limit_timeline(&$timeline, $min, $SETUP)
{
    for($i = 0; $i < $SETUP['chart']['slices']; ++$i)
	if($timeline[$i] < $min)
	    $timeline[$i] = $min;
} 

/**
 * @brief for min limit for each value of timeline but set at VOID instead of setting min
 * @param $timeline array containing timeline
 * @param $min min possible value for each value
 */
function min_limit_void_timeline(&$timeline, $min, $SETUP)
{
    for($i = 0; $i < $SETUP['chart']['slices']; ++$i)
	if($timeline[$i] < $min)
	    $timeline[$i] = VOID;
}    

/**
 * @brief for max limit for each value of timeline
 * @param $timeline array containing timeline
 * @param $max max possible value for each value
 */
function max_limit_timeline(&$timeline, $max, $SETUP)
{
    for($i = 0; $i < $SETUP['chart']['slices']; ++$i)
	if($timeline[$i] > $max)
	    $timeline[$i] = $max;
} 

/**
 * @brief for max limit for each value of timeline but set at VOID instead of setting min
 * @param $timeline array containing timeline
 * @param $max max possible value for each value
 */
function max_limit_void_timeline(&$timeline, $max, $SETUP)
{
    for($i = 0; $i < $SETUP['chart']['slices']; ++$i)
	if($timeline[$i] > $max)
	    $timeline[$i] = VOID;
}    

/**
 * @brief Divide every value in Timeline by another timeline for given time window
 * @param $timeline array containing timeline
 * @param $value time line to divide
 */
function div_timelines(&$timeline, $value, $SETUP)
{
    for($i = 0; $i < $SETUP['chart']['slices']; ++$i)
	if($timeline[$i] != VOID && $value[$i] != VOID)
	{
	    if($timeline[$i] == 0 || $value[$i] == 0)
		$timeline[$i] = VOID;
	    else
		$timeline[$i] /= $value[$i];
	}
}

/**
 * @brief Divide every value in Timeline by value
 * @param $timeline array containing timeline
 * @param $value value to multiple
 */
function div_timeline(&$timeline, $value, $SETUP)
{
    for($i = 0; $i < $SETUP['chart']['slices']; ++$i)
	if($timeline[$i] != VOID)
	    $timeline[$i] /= $value;
}                     

/**
 * @brief Multiply every value in Timeline by value
 * @param $timeline array containing timeline
 * @param $value value to multiple
 */
function mul_timeline(&$timeline, $value, $SETUP)
{
    for($i = 0; $i < $SETUP['chart']['slices']; ++$i)
	if($timeline[$i] != VOID)
	    $timeline[$i] *= $value;
}            

/**
 * @brief Multiply every value in Timeline by another timeline for given time window
 * @param $timeline array containing timeline
 * @param $value time line to divide
 */
function mul_timelines(&$timeline, $value, $SETUP)
{
    for($i = 0; $i < $SETUP['chart']['slices']; ++$i)
	if($timeline[$i] != VOID && $value[$i] != VOID)
	    $timeline[$i] *= $value[$i];
}
 

/**
 * @brief Add every value in Timeline by another timeline for given time window
 * @param $timeline array containing timeline
 * @param $value time line to add
 */
function add_timelines(&$timeline, $value, $SETUP)
{
    for($i = 0; $i < $SETUP['chart']['slices']; ++$i)
    {
        if($timeline[$i] == VOID && $value[$i] != VOID)
	    $timeline[$i] = $value[$i]; 
	if($timeline[$i] != VOID && $value[$i] != VOID)
	    $timeline[$i] += $value[$i];
    }
} 

/**
 * @brief Subtract every value in Timeline by another timeline for given time window
 * @param $timeline array containing timeline
 * @param $value time line to subtract
 */
function sub_timelines(&$timeline, $value, $SETUP)
{
    for($i = 0; $i < $SETUP['chart']['slices']; ++$i)
    {
        if($timeline[$i] == VOID && $value[$i] != VOID)
	    $timeline[$i] = -$value[$i]; 
	if($timeline[$i] != VOID && $value[$i] != VOID)
	    $timeline[$i] -= $value[$i];
    }
} 

/**
 * @brief Sum every value * multiplier in Timeline
 * @param $timeline array containing timeline
 * @param $multiplier multiplier to apply to each value before addition
 */
function sum_timeline($timeline, $multiplier, $SETUP)
{
    $total = 0;

    for($i = 0; $i < $SETUP['chart']['slices']; ++$i)
	if($timeline[$i] != VOID)
	    $total += $multiplier * $timeline[$i];

    return $total;
} 

/**
 * @brief Average every value * multiplier in Timeline
 * @param $timeline array containing timeline
 * @param $multiplier multiplier to apply to each value before addition
 */
function avg_timeline($timeline, $multiplier, $SETUP)
{
    $total = 0;
    $c = 0;

    for($i = 0; $i < $SETUP['chart']['slices']; ++$i)
	if($timeline[$i] != VOID)
	{
	    $total += $multiplier * $timeline[$i];
	    ++$c;
	}

    if($c == 0) return 0; #no non-VOID data
    return ($total / $c);
} 

/**
 * @brief rrd data loader and normalizer
 * Loads the given list of rrds and then averages the data to fit into the
 * given time window with the number of slices. this is done 
 * to make sure other data can be graphed at same time.
 * @param $xml xml object of parsed output of rrd
 * @param $data array to be filled with sensor name => data
 * @param $timestamps array of timestamps (init_timeline_stamps)
 */
function rrddata($SETUP, &$xml, &$data, &$timestamps)
{
    $rrdstep = (int) $xml->meta->step; #rrd's time step
    if(!is_array($data)) $data = array();
    $rawts = array(); #raw timestamps

    #extract timestamps
    if(isset($xml->data->row[0]->t))
    {
	foreach($xml->data->row as $row)
	    $rawts[] = (int) $row->t;
    } else { #newer rrdtool doesnt give the t value
	$rowcnt = (int) $xml->meta->rows;
	$ts = (int) $xml->meta->start;
	$te = (int) $xml->meta->end;
	$tstep = (int) $xml->meta->step;

	if($te == 0 || $ts == 0)
	    die('rrd xml response has invalid times');
	if($ts == $te)
	    die('rrd xml response has same start and end time');
	if($ts > $te)
	    die('rrd xml response has start time after end time');
	$tt = $te - $ts; #total time 
	if($tt < $rrdstep)
	    die('rrd xml response has start time to end time less than a single step');
	if($tt % $rrdstep != 0)
	    die('rrd xml response does not fit into a given step count: '.($tt / $tstep)." steps");

	#rrdtool gives end time of last row bucket
	#place each data point in the center of each bucket
#	$rawts = range($ts, $ts + (($rowcnt - 1) * $tstep), $tstep);
	$rawts = range($ts + ($tstep/2), $ts + ($tstep/2) + (($rowcnt - 1) * $tstep), $tstep);
	#echo json_encode(array($ts,$te, sizeof($rawts), $rawts,$rowcnt,$tstep));die;
    }

    #echo json_encode(array(sizeof($xml->data->row)));die;

    #calc the output timestamps
    #for($i = 0; $i < GRAPH_SLICES; ++$i)
    #    $timestamps[] = round(STARTTIME + ($i * GRAPH_TSLICE));

    for($ei = 0; $ei < count($xml->meta->legend->entry); ++$ei)
    {
	$name = (string) $xml->meta->legend->entry[$ei];
	$data[$name] = array();

	#extract out the data first
	$vd = array();
	foreach($xml->data->row as $row)
	{
	    $fv = VOID;

	    $ts = $row->v[$ei];
	    if(((string) $ts) != "NaN")
	    {
		$fv = floatval($row->v[$ei]);

		#insane values are to be ignored
		if(is_nan($fv) || !is_finite($fv))
		    $fv = VOID;
	    }

	    $vd[] = $fv;
	}

	#echo json_encode(array(sizeof($rawts), $rawts, sizeof($vd), $vd));die;

	#try to fit data into time slices
	#this will smooth the data since it is average based
	$vdi = 0; #start with first data point
	for($i = 0; $i < $SETUP['chart']['slices']; ++$i)
	{
	    $pool = array();
	    $poolv = FALSE; #void data in pool
	    {#find pool of data in slice window
		$tss = round($timestamps[$i] - ($SETUP['chart']['rrd']['fudge_factor']*$SETUP['chart']['tslices']));
		$tse = round($timestamps[$i] + ($SETUP['chart']['rrd']['fudge_factor']*$SETUP['chart']['tslices']));

		foreach($rawts as $vdi => $t)
		{
		    #echo $t." >= ".$tss." && ".$t." <= ".$tse." = ".($t >= $tss && $t <= $tse)."\n";
		    #in the slice window?
		    if($t >= $tss && $t <= $tse)
		    {
			if($vd[$vdi] === VOID)
			    $poolv = TRUE;
			else
			    $pool[] = $vd[$vdi];
		    }
		}
		#echo "--\n";
	    }

	    $d = VOID;
	    if($poolv === TRUE || count($pool) > 0)
	    {
		#average values ignoring VOIDs if possible
		if(count($pool) > 0)
		    $d = array_sum($pool) / count($pool);
	    }
	    else #nothing found, try to find closet value witin rrdstep. if nothing found: VOID
	    {
        	$tss = round($timestamps[$i] - ($rrdstep * 0.5));
		$tse = round($timestamps[$i] + ($rrdstep * 0.5));
		$mind = $rrdstep; #min distance

                foreach($rawts as $vdi => $t)
		{
		    #in the rrd step window?
		    if($t >= $tss && $t <= $tse)
		    {
			$distance = ($t > $timestamps[$i]) ? $t - $timestamps[$i] : $timestamps[$i] - $t;
			if($distance < $mind)
			    $d = $vd[$vdi];
		    }
		}
	    }
	    
	    $data[$name][] = $d;
	}

    }

    #echo json_encode(array($xml, $data)); die;
}

/**
 * @brief Gaussian Calculation
 * takes array of arrays with timestamps and then 
 * calcs the gaussian values from it at each timestep
 * @param $data array of arrays
 * @param $timestamps array of timestamps for data arrays
 * @return array with arrays of each gaussian value
 */
function gauss_calc(&$data, &$timestamps)
{
    $r = array(
	"avg" => array(),
	"min" => array(),
	"max" => array(),
	"stdp1" => array(),
	"stdm1" => array()
	);

    foreach($timestamps as $key => $timestamp)
    {
	$values = array();

	#extract non void values
	foreach($data as $dval)
	    if($dval[$key] != VOID)
		$values[] = $dval[$key];

	if(count($values) < 1)
	{
	    $r['avg'][$key] = VOID;
	    $r['min'][$key] = VOID;
	    $r['max'][$key] = VOID;
	    $r['stdp1'][$key] = VOID;
	    $r['stdm1'][$key] = VOID; 	    
	}
	else
	{
	    $d = sd($values);
	    $r['avg'][$key] = array_sum($values) / count($values);
	    $r['min'][$key] = min($values);
	    $r['max'][$key] = max($values);
	    #bound std dev by max/min, otherwise graph could look odd
	    $r['stdp1'][$key] = min($r['avg'][$key] + $d, $r['max'][$key]);
	    $r['stdm1'][$key] = max($r['avg'][$key] - $d, $r['min'][$key]);
	}
    } 

    return $r;
} 

?>
