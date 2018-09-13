<?php
require_once("config.php");
require_once("include/sql.php");
require_once("include/query.php");
require_once("include/chart.php");
require_once("include/pchart.php");
require_once("graph_tpl.php");
require_once("include/ganglia.php");

$db = null;

$chart = $SETUP['report']['name'];
$sub = $SETUP['report']['sub'];

if($REPORTS[$chart][$sub]['type'] != 'graph')
    die("$chart/$sub is not a graph report"); 

$src = $REPORTS[$chart][$sub]['src'];
$tpl = $REPORTS[$chart][$sub]['tpl'];
$raw = call_user_func_array($src, array(&$db, &$SETUP));
if($raw == FALSE)
    die("src $src ftor failed");

query_set_common_params($raw, $db, $SETUP);

cleanSQLsetup($SETUP); ///safety, dont export SQL settings

if($SETUP["debug"] == "raw")
{
    var_dump($raw);
    die;
}

$data = new pData();
foreach($raw['series'] as $name => $rd)
    $data->AddPoints($rd, $name); 
if(isset($raw['desc']))
    foreach($raw['desc'] as $name => $desc)
	$data->setSerieDescription($name, $desc );

unset($raw['series']); 

if($SETUP["debug"] == "data")
{
    var_dump($data->getData());
    die;
}

pchart_draw($data, $raw['text'], $TEMPLATES[$tpl], $GLOBALS['SETUP']); 

?>
