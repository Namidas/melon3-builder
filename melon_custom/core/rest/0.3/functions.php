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

		require_once(dirname(__FILE__) . '/initialize.php');
		

		/*if($_GET["action"] == "get_component")
		{
			Mods::load("projects");
			$res = PROJECTS::getView($baseParams['client'],$controllerName,Array(
				'name' => $args['name'],
			));
			header('Content-Type: text/javascript');
			die($res);
		}*/
		
		$_REST_INIT = true;
		
	} catch (Exception $e) {
		echo 'Exception: ',  $e->getMessage(), "\n";
	}
	
	if($config['history'])
		fn_rest_history_entry_current($_REST_SETTINGS);
	
	fn_hooks_call('rest:init:after');
}

function fn_rest_exec_current($_REST_SETTINGS=Array())
{
	$rest = array_merge(Config::get('rest_settings'),$_REST_SETTINGS);
	$upUAct = Config::get('no_user_activity',Array());

	$args = $rest['args'];
	$controllerName = $rest['controllerName'];
	$baseParams = $rest['baseParams'];
	$controllerAction = $controllerName . '/' . (string)$baseParams['action'];
	$updateUserActivity = !in_array($controllerAction,$upUAct);
	
	//__vdump($controllerAction,$updateUserActivity,in_array($controllerAction,$reqAuth),$baseParams);

	//$loadRes = Mods::load($controllerName);
	$loadRes = Mods::get($controllerName);

	if(!$loadRes) fn_rest_response(400,default_error_response(Array(
				'error_no' => 5,
				'error_msg' => 'gen.error.controller_load_failed',
				'error_data' => Array(
					'controller' => $controllerName,
				)
			)));

	$authRes = fn_auth_check_jwt_auth($baseParams['authorization'],$updateUserActivity);
	$requiresAuth = fn_auth_check_if_is_required($controllerName,(string)$baseParams['action'],Config::getD('rest_settings.args',Array()));
	if($requiresAuth)
	{
		if(!$authRes) fn_rest_response(401,default_error_response(Array(
			'error_no' => 3,
			'error_msg' => 'error.no_user',
		)));
	}

	switch($_SERVER['REQUEST_METHOD'])
	{
		case 'POST':
			$_POST = (array)json_decode(file_get_contents('php://input'),true);
			break;
	}

	//if($baseParams["action"] == 'fetch_base') __vdump('rest_' . strtoupper($_SERVER['REQUEST_METHOD']),$controllerName,Array($baseParams,$args));

	$res = Mods::call('rest_' . strtoupper($_SERVER['REQUEST_METHOD']),$controllerName,Array($baseParams,$args));
	if(!$res)	fn_rest_response(400,default_error_response(Array(
		'error_no' => 2,
		'error_msg' => 'gen.error.unknown_action',
		'error_data' => Array(
			'action' => @$baseParams['action'],
			'controller' => $controllerName,
			'orig_action' => @$_GET["action"],
		)
	)));
	__vdump($res);
	die($res);
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
	),Config::getD('libs.rest',Array()));
}

//M3::reqLib('PHP-JWT');
//use \Firebase\JWT\JWT;

/*

function fn_sql_getTableDescription($table,$db=null)
{
	if($db === null) $db = DB::class;
	
	$res = $db::query("DESCRIBE {$table}");
	return $res;
}

function get_autocompletes()
{
	$autocompletes = Array();
	$tables = Array(
		SQL::tableName("tblProjekte") => 'Pr',
		SQL::tableName("tblObjekte") => 'Ob',
	);
	
	$fields = Array(
		"Land",
		"Kanton",
		"Ort",
	);
	
	foreach($fields as $field)
	{
		$acf = strtolower($field);
		$values = Array();
		foreach($tables as $table => $pr)
		{
			$res = DB::query("SELECT DISTINCT {$table}.{$pr}{$field} FROM {$table}");
			if(!empty($res)) foreach($res as $r) if($r[$pr.$field] != null) if(trim((string)$r[$pr.$field]) != "") $values[] = $r[$pr.$field];
			$autocompletes[$acf] = $values;
		};
		$autocompletes[$acf] = array_unique($autocompletes[$acf]);
		sort($autocompletes[$acf],SORT_STRING | SORT_FLAG_CASE );
	}
	return $autocompletes;
}

function get_randPic($type,$width=88,$height=88,$amnt=1)
{
	global $__GLOBAL;
	require_once(Config::get('core_path') . 'FileSystem.class.php');
	require_once(Config::get('core_path') . '_dynimg.php');
	
	if(!isset($__GLOBAL["rand_pics_{$type}"]))
		$__GLOBAL["rand_pics_{$type}"] = glob(Config::get("randpics_path") . "{$type}/*.jpg");
	//__vdump(Config::get("randpics_path") . "{$type}/*.jpg",$__GLOBAL["rand_pics_{$type}"]);
	$count = count($__GLOBAL["rand_pics_{$type}"]);
	
	$return = Array();
	for($x=0;$x<$amnt;$x++)
	{
		$file = str_replace(Config::get('randpics_path'),"",str_replace("{$type}/","",$__GLOBAL["rand_pics_{$type}"][array_rand($__GLOBAL["rand_pics_{$type}"])]));
		$newFile = DynIMG::getURL(Config::get('randpics_path') . "{$type}/",$file,Array(
			"width" => $width,
			"height" => $height,
			"zc" => true,
			"resizeUp" => true,
		));
		
		if(!in_array($newFile,$return))
		{
			$return[] = $newFile;
		}
		else
		{
			//var_dump("IN ARRAY");
			if($amnt <= $count)
				$amnt++;
		}
	}
	
	if($amnt == 1) return array_shift($return);
	return $return;
}*/

/*

function default_getView($client,$ctx,$opts,$path=false)
{
	$opts = array_merge(Array(
		"fetchAsArray" => false,
	),$opts);
	
	if($path === false) $path = "{$ctx}/";
	
	$viewNames = @$opts["name"];
	if(is_string($viewNames)) $viewNames = Array($viewNames);
	
	require_once(Config::get('core_path') . 'render_engine/RenderEngine.php');
	
	$ret = Array();

	foreach($viewNames as $vName)
	{
		//__vdump($ctx,$vName);
		
		$rConfig = RenderEngine::getRendererConfig($client,Array(
			'main_tpl' => "{$path}{$vName}.tpl",
			'assigns' => Array()
		));
		
		//__vdump("PRE CREATE CONFIG",$rConfig);
		$renderer = new RenderEngine($client,$rConfig);
		$renderer->cache_id = Array($ctx,"view",$vName);

		try
		{
			$content = $renderer->fetch($rConfig['main_tpl']);
		}
		catch (Exception $e)
		{
			__vdump("CATCHO",$e);
		}
		
		$ret["{$ctx}.{$vName}"] = Array(
			//'content' => $content,
			'hash' => md5($content)
		);
		//__vdump($rConfig['main_tpl'],"projects|view|{$ctx}.{$vName}",$renderer->smarty->isCached($rConfig['main_tpl'],"projects|view|{$ctx}.{$vName}"));
		if(!$opts["fetchAsArray"]) return $content;
	}
	
	return $ret;
}




*/



?>