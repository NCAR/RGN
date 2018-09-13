<?php

/** 
 * @brief load sql
 * quick function to activate sql connection
 * @param db database reference
 * @param SQLSETUP database setup
 */
function sql(&$db, &$SQLSETUP)
{                           
    if(!is_resource($db))
       $db = mysql_connect(
	    $SQLSETUP["host"], 
	    $SQLSETUP["user"], 
	    $SQLSETUP["password"]
       ) or die ('Could not connect: ' . mysql_error());

    mysql_select_db($SQLSETUP["database"], $db) or die('Unable to select DB: ' . mysql_error()); 
}

/**
 * @brief SQL Escape
 * @return string escape fully ready for direct sql insert
 */
function sqle($str)
{
    return sprintf("'%s'", mysql_real_escape_string($str));
}

/**
 * @brief removes the SQL sections from $SETUP
 */
function cleanSQLsetup(&$SETUP)
{
    foreach($SETUP as $key => &$value)
    {
	if($key == "sql")
	    $value = array();

	if(is_array($value))
	    cleanSQLsetup($value);
    }
}

?>
