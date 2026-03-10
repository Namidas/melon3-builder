<?php

M3::reqCore('Hooks');

$GLOBALS['REST_CURRENT_UID'] = null;
$GLOBALS['REST_CLIENT_CONFIG'] = Array();

function fn_rest($method,$controller,$action,$data=Array(),$headers=Array(),$url=false,$auth=false)
{
	$restURL = $url !== false ? $url : Config::get('rest_url');
	if($restURL === Config::get('rest_url'))
	{
		//it's the same base domain
		M3::reqCore('Array');
		$baseSettings = Config::get('rest_settings');
		fn_array_set('baseParams.controller',$controller,$baseSettings);
		fn_array_set('baseParams.action',$action,$baseSettings);
		$baseSettings['controllerName'] = $controller;
		$baseSettings['args'] = $data;
		return fn_rest_init_exec($baseSettings);
	}
	
	if($auth && !$GLOBALS['CURRENT_USER']) die("--- FileSystem::rest con auth pero sin usuario actual");
	$restSettings = Config::getD('rest_settings.baseParams');
	if($auth) $headers[] = 'Authorization: ' . Config::getD('rest_settings.baseParams.authorization');
	$restURL = $url !== false ? $url : Config::get('rest_url');
	$url = $restURL . '?client=' . $restSettings['client'] .'&lang=' . $restSettings['lang'] . "&controller={$controller}&action={$action}";
	//__vdump($url);
	$ch = curl_init($url);
	curl_setopt($ch,CURLOPT_CUSTOMREQUEST,$method);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
	
	if(!empty($data))
	{
		$payload = json_encode($data);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload );
		$headers[] = 'Content-Type:application/json';
	}
	
	switch($method)
	{
		case 'POST':
			break;
	}
	
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
	$rawData=curl_exec($ch);
	curl_close ($ch);
	//__vdump($rawData);
	try {
		$response = json_decode($rawData,true);
	}
	catch(Exception $e)
	{
		//die("---- tuve una excepcion en FileSystem::rest");
	}
	return Array($response,$url,$rawData);
}

function fn_rest_set_client_config($client,$reload=false)
{
	//__vdump("fn_rest_set_client_config",$client);
	
	if(isset($GLOBALS['REST_CLIENT_CONFIG'][$client]))
		return $GLOBALS['REST_CLIENT_CONFIG'][$client];
	
	//__vdump("continuo");
	M3::reqCore('Client');
	fn_client_set($client);
	
	$_APP_CONFIG = Array(
		//'routes' => Array(),
		//'menu' => Array(),
		//'components' => Array(),
		//'styles' => Array(),
		'config' => Array(),
		'lang' => Array(),
		//'options' => Array(),
	);
	
	//$confPath = Config::get("base_path") . "client/{$client}/config.php";
	//require_once($confPath);
	
	$_HOOK_ARGS = Array(
		'_APP_CONFIG' => &$_APP_CONFIG,
	);
	fn_hooks_call('client_app:get_client_config',$_HOOK_ARGS);
	//__vdump("PRE MERGE",$_APP_CONFIG['config'],Config::get());
	//__vdump("PRE MERGE",$_APP_CONFIG);
	foreach($_APP_CONFIG['config'] as $key => $val)
		Config::set($key,$val);
	Config::set('client_config',array_merge(Config::get('client_config',Array()),array_keys($_APP_CONFIG['config'])));
	//__vdump("AL SETEAR",$_APP_CONFIG);
	$GLOBALS['REST_CLIENT_CONFIG'][$client] = $_APP_CONFIG;
	
	return $_APP_CONFIG;
}

$_REST_INIT = false;
function fn_rest_init($_REST_SETTINGS=Array())
{
	try
	{
		//DB::addHook('run_failed','fn_rest_response_exception_db_handler');
		$config = fn_rest_get_config();
		
		global $_REST_INIT;
		if($_REST_INIT) return true;
		
		try {
		
			$method = $_SERVER['REQUEST_METHOD'];
			if ($method == "OPTIONS") {
				//header('Access-Control-Allow-Origin: *');
				//header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
				http_response_code(200);
				die();
			}
			
			 // Allow from any origin
			/*if (isset($_SERVER['HTTP_ORIGIN'])) {
				// Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
				// you want to allow, and if so:
				header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
				header('Access-Control-Allow-Credentials: true');
				//header('Access-Control-Max-Age: 86400');    // cache for 1 day
			}
			
			// Access-Control headers are received during OPTIONS requests
			if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
				
				if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
					// may also be using PUT, PATCH, HEAD etc
					header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS");
				
				if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
					header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
			
				exit(0);
		}*/

			require_once(dirname(__FILE__) . '/../0.3/initialize.php');
			
			$_REST_INIT = true;
			
		} catch (Exception $e) {
			echo 'Exception: ',  $e->getMessage(), "\n";
		}
		
		if($config['history'])
			fn_rest_history_entry_current($_REST_SETTINGS);
		
		fn_hooks_call('rest:init:after');
	} catch(Exception $e)
	{
		fn_rest_response_exception($e);
	}
}

function fn_rest_exec_current($_REST_SETTINGS=Array())
{
	try
	{
		$rest = array_merge(Config::get('rest_settings'),$_REST_SETTINGS);
		$controllerName = $rest['controllerName'];
		
		$modManifest = Mods::get($controllerName);

		if(!$modManifest) fn_rest_response(400,default_error_response(Array(
					'error_no' => 5,
					'error_msg' => 'error.005.controller_load_failed',
					'error_data' => Array(
						'controller' => $controllerName,
					)
				)));

		//(controllerName([\/]+|$))
		//regex to get "controllerName/" or "controllerName" <- end of string
		//$realControllerNameRegex = '(' . implode('([\/]+|$))|(',array_merge([$modManifest['name']],$loadRes['alias'])) . '([\/]+|$))';
		//deprecated above
		$controllers = [$controllerName];
		$args = $rest['args'];
		$baseParams = $rest['baseParams'];
		
		$checks = Array();	
		$callArgs = Array(
			$controllers,
			'/',
			$baseParams['action'],
			'/'
		);
		if(count($args)) $callArgs[] = '&' . http_build_query($args);
		$checks = call_user_func_array('fn_rules_construct_subjects',$callArgs);
		$authConfig = fn_auth_get_config();
		$updateUserActivity_rules = $authConfig['no_user_activity'];
		list($updateUserActivity) = fn_rules_check($checks,$updateUserActivity_rules,Config::getD('libs.rest.user_activity.rule_check_options',[]));
		
		$authRes = fn_auth_check_jwt_auth($baseParams['authorization'],$updateUserActivity);
		$requiresAuth = fn_auth_check_if_is_required($controllerName,(string)$baseParams['action'],Config::getD('rest_settings.args',Array()));
		if($requiresAuth)
		{
			if(!$authRes) fn_rest_response(401,default_error_response(Array(
				'error_no' => 6,
				'error_msg' => 'error.006.requires_auth',
			)));
		}

		switch($_SERVER['REQUEST_METHOD'])
		{
			case 'POST':
				$_POST = (array)json_decode(file_get_contents('php://input'),true);
				break;
		}

		//if($baseParams["action"] == 'fetch_base') __vdump('rest_' . strtoupper($_SERVER['REQUEST_METHOD']),$controllerName,Array($baseParams,$args));
		$funcName = "fn_{$modManifest['name']}_handle_rest";
		$res = false;
		if(function_exists($funcName))
			$res = call_user_func_array($funcName,Array(strtoupper($_SERVER['REQUEST_METHOD']),$baseParams,$args));

		if(!$res)	fn_rest_response(400,default_error_response(Array(
			'error_no' => 4,
			'error_msg' => 'error.004.unsupported_method',
			'error_data' => Array(
				'action' => @$baseParams['action'],
				'controller' => $controllerName,
				'orig_action' => @$_GET["action"],
			)
		)));
	} catch(Exception $e)
	{
		fn_rest_response_exception($e);
	}
}

function fn_rest_init_exec($_REST_SETTINGS=Array())
{
	fn_rest_init($_REST_SETTINGS);
	fn_rest_exec_current($_REST_SETTINGS);
}

function fn_rest_response($code,$response,$headers=Array(),$options=Array())
{
	$options = array_merge(Array(
		'encode' => true,
	),$options);
	
	$_CURRENT_USER = fn_auth_get_current_user();
	$config = fn_rest_get_config();
	
	if(is_array($response)) $response['rest_uid'] = $GLOBALS['REST_CURRENT_UID'];
	$encodedResponse = $options['encode'] ? json_encode($response) : $response;
	if($config['history'])
	{
		$update = Array(
			'rest_response_text' => $encodedResponse,
			'rest_response_code' => $code,			
			'rest_response_encoded' => (string)(int)$options['encode'],
			'rest_user_id' => $_CURRENT_USER ? $_CURRENT_USER['id'] : 0,
			'rest_user_name' => $_CURRENT_USER ? $_CURRENT_USER['name'] : '',
			'rest_user_type' => $_CURRENT_USER ? $_CURRENT_USER['table'] : '',
		);
		
		$result = DB::update(SQL::tableName('rest_history'),$update,'rest_uid = %i',$GLOBALS['REST_CURRENT_UID']);
		
		
		/*$table = SQL::tableName('rest_history');
		$args = array_merge(
			Array("UPDATE {$table} SET {$table}.rest_response_text = %s, {$table}.rest_response_code = %i, {$table}.rest_user_id = %i, {$table}.rest_user_name = %s, {$table}.rest_user_type = %s WHERE {$table}.rest_uid = %i"),
			array_values($update),
			Array($GLOBALS['REST_CURRENT_UID'])
		);
		call_user_func_array("DB::query",$args);
		fn_log_write("- actualizo al terminar el request",$args,$GLOBALS['REST_CURRENT_UID']);*/
		//sleep(2);
		//__vdump("SAVE REST RESPONSE",$update);
		//__vdump($result);
	}
	
	$_HOOK_ARGS = Array(
		&$code,
		&$response,
		&$encodedResponse
	);
	fn_hooks_call('rest:response',$_HOOK_ARGS);
	
	if(!empty($headers))
		foreach($headers as $header)
			header($header);
	http_response_code($code);
	die($encodedResponse);
}

function fn_rest_check_history_table()
{
	$config = fn_rest_get_config();
	if($config['history'])
	{
		$tables = DB::tableList();
		if(!in_array('rest_history',$tables))
			fn_sql_run_file_once(dirname(__FILE__) . '/assets/sql/table.rest_history.sql');
	}
}

function fn_rest_history_entry_current()
{
	$restSettings = Config::get('rest_settings');

	M3::reqCore('URL');

	$table = SQL::tableName('rest_history');
	$row = Array(
		'protocol' => $restSettings['protocol'],
		'url' => fn_url_get_current(),
		'controller' => $restSettings['controllerName'],
		'action' => $restSettings['baseParams']['action'],
		'client' => $restSettings['baseParams']['client'],
		'lang' => $restSettings['baseParams']['lang'],
		'app_route' => __arrg('args.app_route',$restSettings,''),
		'args' => json_encode($restSettings['args']),
		'response_code' => 400,
		//'response_text' => json_encode(Array()),
	);
	
	M3::reqCore('Log');
	fn_log_write("- creo entrada history log",$row);
	
	fn_array_append_key_prefix($row,'rest_');
	DB::insert($table,$row);
	$GLOBALS['REST_CURRENT_UID'] = DB::insertId();
}

function fn_rest_get_config()
{
	return array_merge(Array(
		'history' => false,
		
		'user_activity' => Array(
			'rule_check_options' => Array(
			)
		),
	),Config::getD('libs.rest',Array()));
}

function fn_rest_response_exception($e)
{
	M3::reqCore('Response');
	
	$error_no = 0;
	$error_msg = 'error.000.rest_exception_catch';
	$typeof = get_class($e);
	
	$args = Array(
		'message' => $e->getMessage(),
		'code' => $e->getCode(),
		'typeof' => $typeof
	);
	
	if(!_PRODUCTION_)
		$args = array_merge($args,Array(
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'trace' => $e->getTrace(),
			));
			
	switch($typeof)
	{
		case 'mysqli_sql_exception':
			$error_no = 3;
			$error_msg = 'error.003.mysql_exception_catch';
			break;
	}
	
	$error = Array(
		'error_no' => $error_no,
		'data' => $args,
		'error_msg' => $error_msg,
	);
	
	fn_rest_response(400,default_error_response($error));
}


function fn_rest_response_exception_db_handler($hash)
{
    $query = $hash['query'];
    $runtime = $hash['runtime']; // runtime in ms
    $error = $hash['error']; // error message
    $Exception = $hash['exception']; // this exception will be thrown after hooks run

    echo "QUERY: $query ($runtime ms)\n";
    echo "ERROR: $error\n";
}


?>