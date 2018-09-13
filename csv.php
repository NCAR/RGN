<?php
require_once("config.php");
require_once("include/sql.php");
require_once("include/chart.php");
require_once("include/pchart.php");
require_once("include/query.php");
require_once("include/ganglia.php");  

$db = null;

$report = $SETUP['report']['name'];
$sub = $SETUP['report']['sub'];
$raw = array();

if($SETUP['json']['query'] != '')
{
    #json direct query
    switch($SETUP['json']['query'])
    {
	case 'general':
	    $raw = array(
		'CLUSTERS' => $CLUSTERS, 
		'REPORTS' => $REPORTS,
		'TIME_RANGES' => $TIME_RANGES,
		'CHART_THEMES' => $CHART_THEMES
	    );
	    break;
    }
} 
else if(isset($REPORTS[$report][$sub]))
{
    $src = $REPORTS[$report][$sub]['src'];
    $raw = call_user_func_array($src, array(&$db, &$SETUP));
}
else #non requesting a chart, just a list of reports
{
    die('unknown query type');
}

query_set_common_params($raw, $db, $SETUP);

#http://stackoverflow.com/questions/13108157/php-array-to-csv
$output = fopen("php://output",'w') or die("Can't open php://output");
header("Content-Type:application/csv");
header("Content-Disposition:attachment;filename=rgn_output.csv"); 

if(isset($raw['series']))
    foreach($raw['series'] as $name => $data )
	#dont send newlines or VOID 
	fputcsv($output, array_merge(array($name),str_replace(VOID, "", str_replace("\n", "", $data))));

fclose($output) or die("Can't close php://output");

?>
