<?php

M3::reqCore('Time');

$GLOBALS['LOG_FILE_LOADED'] = false;

function fn_log_write()
{
	$args = func_get_args();
	if($GLOBALS['LOG_FILE_LOADED']) fn_log_load();
	
	$logFile = Config::get('base_path') . 'log.txt';
	$fh = fopen($logFile, 'a') or die('can\'t open log file');
	
	if(!empty($args))
		foreach($args as $arg)
			fwrite($fh,(is_string($arg) ? $arg : print_r($arg,true)) . "\n");
			
	fclose($fh);
}

function fn_log_load($restart = null)
{
	if($GLOBALS['LOG_FILE_LOADED']) return;
	$GLOBALS['LOG_FILE_LOADED'] = true;
	if($restart === null) $restart = (bool)Config::get("log_always_clean",1);
	$logFile = Config::get("base_path") . "log.txt";
	$fh = fopen($logFile, $restart ? 'w' : 'r+') or die("can't open log file");
	fwrite($fh, "\n\n\n--------------------------------------------------------------\n - " . time() . " - " . fn_time_parse_date_template() . "- log start\n--------------------------------------------------------------\n");
	fclose($fh);
}

?>
