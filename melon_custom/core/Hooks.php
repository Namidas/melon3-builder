<?php

global $_HOOKS;
$_HOOKS = Array();
function fn_hooks_set($hookName,$handler)
{
	global $_HOOKS;
	if(!isset($_HOOKS[$hookName])) $_HOOKS[$hookName] = Array();
	if(!in_array($handler,$_HOOKS[$hookName]))
		$_HOOKS[$hookName][] = $handler;
	else
	{
		__vdump("--- intentando setear un mismo hook más de una vez",$hookName,$handler);
		die();
	}
}

function fn_hooks_get($hookName)
{
	global $_HOOKS;
	if(!isset($_HOOKS[$hookName])) return false;
	$handlers = &$_HOOKS[$hookName];
	return $handlers;
}

function fn_hooks_call($hookName,&$args=Array())
{
	$handlers = fn_hooks_get($hookName);
	if(empty($handlers)) return false;
	
	$returns = Array();
	if(!empty($handlers)) foreach($handlers as $handler)
		$returns[] = call_user_func_array($handler,$args);
	return $returns;
}



?>