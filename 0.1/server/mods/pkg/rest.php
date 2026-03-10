<?php

require_once(dirname(__FILE__) . '/rest/get.php');
require_once(dirname(__FILE__) . '/rest/post.php');

function fn_pkg_handle_rest($method,$config,$args)
{
	$args = func_get_args();
	$method = strtolower(array_shift($args));
	$funcName = __FUNCTION__ . "_{$method}";
	
	if(function_exists($funcName))
		return call_user_func_array($funcName,$args);
	else return false;
}

?>