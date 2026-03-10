<?php

//if(!constant('REST_API')) die('-no_rest_api');

require_once(dirname(__FILE__) . '/../../../core/API.php');
M3::reqCore('Config');
M3::reqCore('Response');
M3::reqCore('Mods');
M3::reqCore('Auth');

$baseParamKeys = Array(
	'client',
	'lang',
	'authorization',
	
	'controller',
	'action',
);
$baseParams = array_fill_keys($baseParamKeys,false);

$args = $_GET;

foreach($baseParamKeys as $key)
{
	if(isset($args[$key]))
	{
		$baseParams[$key] = $args[$key];
		unset($args[$key]);
	}
}

if($baseParams['lang'] !== false) if(trim($baseParams['lang']) !== '') Config::set('lang',$baseParams['lang']);
$baseParams['client_shortname'] = str_replace('melon3.','',$baseParams['client']);

$_REQUEST_HEADERS = apache_request_headers();

if(isset($_REQUEST_HEADERS['Authorization']))
	$baseParams['authorization'] = $_REQUEST_HEADERS['Authorization'];
else if(isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))
	$baseParams['authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];

if($baseParams['client'] !== false)
{
}
else die("no seteado client version, error sin catchear");

/*if($baseParams['controller'] === false)
	$baseParams['controller'] = 'sys';*/

//$mods = constant("mods_aliases");
//$controllerName = isset($mods[$baseParams['controller']]) ? $mods[$baseParams['controller']] : $baseParams['controller'];

$controllerName = $baseParams['controller'];


Config::set("rest_settings",Array(
	'baseParams' => $baseParams,
	'controllerName' => $controllerName,
	'protocol' => $_SERVER['REQUEST_METHOD'],
	'args' => $args,
));

fn_rest_set_client_config($baseParams['client']);

//require_once(dirname(__FILE__) . "/client/{$baseParams['client']}/config.php");

?>