<?php

M3::reqVendor('PHP-JWT');
use \Firebase\JWT\JWT;

M3::reqCore('Rules');

$GLOBALS['__auth_rule_all__'] = '*';
$GLOBALS['CURRENT_USER'] = null;

function fn_auth_get_current_user()
{
	return $GLOBALS['CURRENT_USER'];
}

function fn_auth_password_hash($password,$algo=false,$options=Array())
{
	if($algo === false)
		$algo = Config::getD('core.Auth.password_hash.algo',PASSWORD_DEFAULT);
	
	$preMergeOptions = Config::getD('core.Auth.password_hash.pre_merge_options',false);
	
	$options = array_merge(
		$preMergeOptions ? $options : Array(),
		Config::getD('core.Auth.password_hash.options',Array()),
		$preMergeOptions ? Array() : $options//,
	);
	
	return password_hash($password,$algo,$options);
}

function fn_auth_password_verify($password,$hash)
{
	return password_verify($password,$hash);
}

function fn_auth_password_needs_rehash($password,$algo=false,$options=Array())
{
	if($algo === false)
		$algo = Config::getD('core.Auth.password_hash.algo',PASSWORD_DEFAULT);
	
	$preMergeOptions = Config::getD('core.Auth.password_hash.pre_merge_options',false);
	
	$options = array_merge(
		$preMergeOptions ? $options : Array(),
		Config::getD('core.Auth.password_hash.options',Array()),
		$preMergeOptions ? Array() : $options//,
	);
	
	return password_needs_rehash($password,$algo,$options);
}

/**

ESTO LO SAQUE DIRECTAMENTE DE LA DOC DE PHP Y ESTARÍA BUENO IMPLEMENTARLO EN UNA
FUNCTION

https://www.php.net/manual/en/function.password-hash.php

 * This code will benchmark your server to determine how high of a cost you can
 * afford. You want to set the highest cost that you can without slowing down
 * you server too much. 8-10 is a good baseline, and more is good if your servers
 * are fast enough. The code below aims for ≤ 50 milliseconds stretching time,
 * which is a good baseline for systems handling interactive logins.
 
$timeTarget = 0.05; // 50 milliseconds 

$cost = 8;
do {
    $cost++;
    $start = microtime(true);
    password_hash("test", PASSWORD_BCRYPT, ["cost" => $cost]);
    $end = microtime(true);
} while (($end - $start) < $timeTarget);

echo "Appropriate Cost Found: " . $cost;
*/

function fn_auth_get_config($config=Array())
{
	$aliasOptions = [];
	
	$aliasOptions_merge = Array(
		'requires_auth',
		'no_user_activity',
	);
	
	$config = array_merge(Array(
		'function_prefix' => '',
		
		'requires_auth' => Array(),
		'no_user_activity' => Array()
	),Config::getD('core.Auth',Array()),$config);
	
	foreach($aliasOptions as $alias)
		$config[$alias] = Config::get($alias,$config[$alias]);
		
	foreach($aliasOptions_merge as $alias)
		$config[$alias] = array_merge($config[$alias],Config::get($alias,[]));
	
	return $config;
}

function fn_auth_check_jwt_auth($token=null,$updateUserActivity=true)
{
	$result = false;
	
	$config = fn_auth_get_config();
	
	//__vdump("CHECK AUTH",$token,$updateUserActivity,$dieOnError);
	if($token != null)
	{
		try {
			$decoded = JWT::decode($token,Config::get("jwt_secret_key"), array('HS256'));
			$dataArray= json_decode(json_encode($decoded->data),true);
			$user = fn_auth_get_user_from_dataArrayJWT($dataArray);

			if($updateUserActivity) call_user_func_array("fn_{$config['function_prefix']}_update_activity",Array($user));
			$result = fn_auth_get_user_jwt($user); 
			
			$GLOBALS['CURRENT_USER'] = $user;
			unset($GLOBALS['CURRENT_USER']["password"]);
		}
		catch (Exception $e){
			//var_dump($e->code);
			
		}
	}

	if($result === false)
	{
		$GLOBALS['CURRENT_USER'] = null;
		return false;
	}
	return $result;
}

function fn_auth_get_user_from_dataArrayJWT($dataArray)
{
	$config = fn_auth_get_config();
	//__vdump("auth config",$config);
	//Mods::load("users");
	$funcName = "fn_{$config['function_prefix']}_get";
	//__vdump("Auth:: {$funcName}");
	list($user) = call_user_func_array($funcName,Array(Array(
		//"id" => $dataArray["id"],
		"id" => (int)$dataArray,
		"unique" => true,
	)));
	//__vdump("auth geteado",$user);
	return $user;
}

function fn_auth_get_user_jwt_prepareUserData($userData)
{
	return (int)$userData["id"];
}

function fn_auth_get_user_jwt($userData)
{
	$issued_at = time();
	$expiration_time = $issued_at + (float)Config::get("user_session_timeout")/1000;
	$issuer = Config::get("base_url");
	
	if(isset($userData["password"])) unset($userData["password"]);
	/*$userData = (int)$userData["id"];
	$userData = Array("id" => $userData["id"]);*/

	$token = array(
	   "iat" => $issued_at,
	   "exp" => $expiration_time,
	   "iss" => $issuer,
	   "data" => fn_auth_get_user_jwt_prepareUserData($userData),
	);
	$jwt = JWT::encode($token, Config::get("jwt_secret_key"));
	Config::set("current_user_jwt",$jwt);
	return $jwt;
}

function ___DEPRECATED__________fn_auth_add_rule($rule,$exceptions=Array())
{
	global $__CONFIG;
	if(!isset($__CONFIG['requires_auth'])) $__CONFIG['requires_auth'] = Array();
	if(!isset($__CONFIG['requires_auth'][$rule]))
		$__CONFIG['requires_auth'][$rule] = Array();
	$__CONFIG['requires_auth'][$rule] = array_merge($__CONFIG['requires_auth'][$rule],$exceptions);
}

function fn_auth_add_rule($rule,$exceptions=Array())
{
	$target = Config::get('requires_auth');
	fn_rules_add($target,$rule,$exceptions);
	Config::set('requires_auth',$target);
}

function fn_auth_add_rule_alt($rule,$alt,$exceptions)
{
	//__vdump("ADD ALT RULE",$rule,$alt,$exceptions);
	if($alt === false) return fn_auth_add_rule($rule,$exceptions);
	if(Config::getD(['requires_auth',$rule],false) === false)
		$rule = $alt;
	
	return fn_auth_add_rule($rule,$exceptions);
}

function fn_auth_add_global_rule($exceptions=Array(),$alt=false)
{
	//__vdump("ADD GLOBAL RULE",$exceptions,$alt);
	$rule = $GLOBALS['__rule_all_regex__'];
	return fn_auth_add_rule_alt($rule,$alt,$exceptions);
}

function fn_auth_check_if_is_required($controllers,$action,$args=Array())
{
	//__vdump("fn_auth_check_if_is_required",$controllers,$args);
	$debug = false;
	
	if($debug) __vdump("FN AUTH CHECK IF IS REQUIRED",$controllers,$action);
	
	$rules = Config::get('requires_auth',[]);
	$deny = false;
	
	if($debug) __vdump("RULES",$rules);
	
	if(!is_array($controllers)) $controllers = [$controllers];
	$checks = Array();
	
	$callArgs = Array(
		$controllers,
		'/',
		$action,
		'/'
	);
	if(count($args)) $callArgs[] = '&' . http_build_query($args);
	$checks = call_user_func_array('fn_rules_construct_subjects',$callArgs);
	
	if($debug) __vdump("CHECK AUTH","checks",$checks,"rules",$rules);
	
	list($result) = fn_rules_check($checks,$rules,Config::getD('core.Auth.rule_check_options',[]));
	return $result;
	
	
	/////////ACA VIENE TODO EL CODIGO ORIGINAL BORRAR DEPRECATED
	foreach($rules as $ruleKey => $ruleValue)
	{
		//if($debug) __vdump("FOREACH RULE","RULEKEY",$ruleKey,"RULE VALUE",$ruleValue,"--------------");
		$ruleException = $ruleValue;
		if(is_string($ruleValue))
		{
			$ruleKey = $ruleValue;
			$ruleException = Array();
		}
		
		if($debug) __vdump("- empiezo a chequear",$ruleKey,$args,"exceptions",$ruleException,"--",$action,"---");
		
		if(fn_auth_check_if_is_required_eval_rule($ruleKey,$controller,$action,$args,$debug))
		{
			if($debug) __vdump("DENY TRUE SOBRE RULE KEY");
			$deny = true;
		}
		else if($debug) __vdump("*** todo bien con esta regla");
		
		if($debug) __vdump("pre-res de reglas:" . ($deny? "true":"false"));
		
		if($debug) __vdump("CHECKEE TODAS LAS REGLAS Y EL RESULTADO ES DENY",$deny);
		
		if($deny)
		{
			if($debug) if(!empty($ruleException)) __vdump("CHEQUEO EXCEPTIONS",$ruleException);
			if(!empty($ruleException)) foreach($ruleException as $excp)
				if(fn_auth_check_if_is_required_eval_rule($excp,$controller,$action,$args,$debug))
				{
					if($debug) __vdump("DENY FALSE SOBRE RULE EXCEPTIONS");
					$deny = false;
				}
				
			if($debug) __vdump("res despues de las excepciones:" . ($deny? "true":"false")."\n\n\n");
		}
	}
	if($debug) __vdump("FIN",$deny);
	return $deny;
}

function fn_auth_check_if_is_required_eval_rule($rule,$controller,$action,$callArgs=Array(),$debug=false)
{
	if($debug) __vdump("-- eval rule",$rule,$controller,$action,$callArgs);
	//$rest = Config::get('rest_settings');
	
	$result = false;
	
	if($controller == $rule ||
		$GLOBALS['__auth_rule_all__'] == $rule ||
		"{$controller}/{$action}" == $rule ||
		"{$controller}/*" == $rule ||
		"*/{$action}" == $rule) $result = true;
		
	if($debug) __vdump("PRE RES","{$controller}/{$action}" == $rule,"{$controller}/{$action}",$rule,$result);
		
	$expl = explode(':',$rule);
	if(count($expl) > 1 && !empty($callArgs))
	{
		$args = $expl;
		array_shift($args);
		if($debug) __vdump("- check rule exceptions",$args);
		
		foreach($args as $arg)
		{
			$expl = explode("=",$arg);
			if(!isset($callArgs[$expl[0]])) $result = false;
			if(count($expl) === 1) $result = true;
			if(isset($callArgs[$expl[0]]))
				if($callArgs[$expl[0]] == $expl[1]) $result = true;
		}
	}
	
	if($debug) __vdump("--- eval rule result",$result);
	return $result;
}

?>