<?php

global $__DEFAULT_RESPONSE;
$__DEFAULT_RESPONSE = Array(

	'status' => false,
	'status_message' => false,
	'status_type' => false,
	'data' => Array(),

	//error
	'error' => false,
	'error_no' => -1,
	'error_msg' => '',
	'error_data' => Array(),
	
	'redirect' => false,
);

global $__DEFAULT_ERROR_RESPONSE;
$__DEFAULT_ERROR_RESPONSE = array_merge($__DEFAULT_RESPONSE,Array(
	'error' => true,
	'error_no' => 0,
	'error_msg' => 'error.undefined',
));

function default_response($response=Array())
{
	global $__DEFAULT_RESPONSE;
	return array_merge($__DEFAULT_RESPONSE,$response,Array(
		"user_jwt" => Config::get("current_user_jwt"),
		"user_activity_duration" => (float)Config::get("user_activity_duration",-1),
		"user_activity_start" => Config::get("user_activity_start",-1),
	));
}

function default_error_response($response=Array())
{
	global $__DEFAULT_ERROR_RESPONSE;
	return array_merge($__DEFAULT_ERROR_RESPONSE,$response);
}

?>