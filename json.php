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
#    switch($report)
#    {
#	case 'general':
#	    $raw = array(
#		'CLUSTERS' => $CLUSTERS, 
#		'REPORTS' => $REPORTS,
#		'TIME_RANGES' => $TIME_RANGES,
#		'CHART_THEMES' => $CHART_THEMES
#	    );
#	    break;
#    }
}

query_set_common_params($raw, $db, $SETUP);

cleanSQLsetup($SETUP); ///safety, dont export SQL settings

echo json_encode(array(
    "SETUP" => $SETUP, 
    "DATA" => $raw
));

?>
