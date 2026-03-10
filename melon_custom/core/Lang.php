<?php

function fn_lang_parse_file($filePath,$templateData=Array(),$config=Array())
{
	$config = fn_lang_get_config($config);
	
	M3::reqVendor('Intl');
	M3::reqCore('String');
	
	$templateData = array_merge(Array(
		//'base_path' => Config::get('lang_path'),
		'lang' => Config::get('lang',Config::get('default_locale')),
	),$templateData);
	
	$parsedPath = fn_string_template($filePath,$templateData);
	$noFile = false;
	
	/*__vdump(
		"parsePoFile",
		$filePath,
		$config,
		$templateData,
		"default_locale",
		Config::get('default_locale'),
		"parsedPath: {$parsedPath}"
	);*/
	
	if($config['add_path'])
	{
		$available = FileSystem::getPathIf([$config['paths'],$parsedPath]);
		//__vdump("AVAILABLE",$available,$config['paths'],$parsedPath);
		if(empty($available)) $noFile = true;
	}
	else if(!is_readable($parsedPath)) $noFile = true;
		else $available = [$parsedPath];
	
	if($noFile &&  $config['die_on_error'])
	{
		fn_rest_response(400,
		default_error_response(Array(
			'error_no' => 1,
			'error_msg' => 'error.no_lang_file',
			'data' => Array(
				'path' => $filePath,
				'parsed' => $parsedPath,
			)
		)));
	}
	
	$langData = Array();
	foreach($available as $path)
	{
		$langArray = Intl::parse_po_file($path);
		$langFileHeaders = array_shift($langArray);
		if(!empty($langArray)) foreach($langArray as $ld) if(isset($ld["msgid"]))
			$langData[$ld["msgid"]] = $ld["msgstr"];
	}
	
	//__vdump("mando a groupear",$langData);
	if($config['group']) $langData = fn_array_from_selectors($langData);
	//__vdump("groupeado",$langData);
	return $langData;
}

function fn_lang_get_config($base=Array())
{
	return array_merge(Array(
		'die_on_error' => true,
		'add_path' => false,
		'paths' => [],
		
		//make nested groups of translations using keys as selectors 'some.nested'
		'group' => true,
	),Config::getD('core.Lang',Array()),$base);
}






/*
este es el array de datos que se pasa a URL::parse para construir el fullpath
al mo que corresponda

ARRAY [6]
[bound_path] lang\     | string (1)
[category] 5     | integer (1)
[lc_category] LC_MESSAGES     | string (1)
[domain] paralelo55     | string (1)
[subpath] LC_MESSAGES/paralelo55.mo     | string (1)
[locale] es     | string (1)

*/

class Lang
{
	static function printEntry($str)
	{
		if(!Lang::$loaded) return "--lang-no-loaded--";
		echo Lang::getEntry($str);
	}

	static function getEntry()
	{
		$args = func_get_args();
		$translations = Array();
		if(!Lang::$loaded) return "--lang-no-loaded--";
		foreach($args as $str)
			$translations[$str] = T_gettext($str);
		return count($args) > 1 ? $translations : array_shift($translations);
	}

	static $loaded = false;
	static $currentLang = null;

	static function load($domain=false,$lang=false,$location=false,$projectDir=false)
	{
		if(Lang::$loaded) return true;

		M3::reqLib('gettext');

		if(isset($_POST["lang"])) Config::set("lang",$_POST["lang"]);
		else Config::set("lang",Config::get("lang_default","en"));
		//Config::set("supported_locales_count",count(Config::get("supported_locales")));

		if(!$domain) $domain = Config::get("lang_domain","melon");
		if(!$lang) $lang = Config::get("lang",Config::get("lang_default","es"));
		if(!$location) $location = Config::get("lang_location","lang");

		if(!$projectDir) $projectDir = Config::get("current_path");
		define('PROJECT_DIR', $projectDir);
		define('LOCALE_DIR', $location);
		define('DEFAULT_LOCALE', Config::get("lang_default","es"));

		$supported_locales = Config::get("supported_locales",Array());
		$encoding = 'UTF-8';

		$locale = $lang;
//var_dump($locale);var_dump($domain);var_dump(LOCALE_DIR);
		T_setlocale(LC_MESSAGES, $locale);
		T_bindtextdomain($domain, LOCALE_DIR);
		T_bind_textdomain_codeset($domain, $encoding);
		T_textdomain($domain);

		//$location, tiene la dirección actual del lenguaje
		//$locale tiene el lenguaje actual, ej: 'br'
		//$domain tiene el dominio actual, 'uetv' o 'paralelo55'

		Lang::$loaded = true;
		Lang::$currentLang = Array(
			"code" => $lang,
			"domain" => $domain,
			"location" => $location
		);

		return true;
	}

	static function getEnabledLocales($includeDisabled=false)
	{
		$langdata = Lang::getSupportedLocalesData();
		foreach($langdata as &$ld) $ld["status"] = 0;
		$temp = explode(",",Config::get("enabled_locales"));
		$enabled = Array();
		if(empty($temp)) return $langdata;

		foreach($temp as $lcode) if(trim($lcode) != "") if(isset($langdata[$lcode]))
		{
			$langdata[$lcode]["status"] = 1;
			$enabled[$lcode] = $langdata[$lcode];
		}
		return $includeDisabled ? $langdata : $enabled;
	}

	static function getSupportedLocalesData()
	{
		global $__GLOBAL;
		$data = Array();
		if(!isset($__GLOBAL["supported_locales_data"]))
		{
			foreach(Config::get("supported_locales") as $sup)
			{
				//empezó a tirar acá un error que no debería, asique voy a forzar
				//$data[] = Array("code" => strtolower($sup) , "country" => $__GLOBAL["country_lang_list"][strtoupper($sup)][0] , "lang" => $__GLOBAL["country_lang_list"][strtoupper($sup)][1]);
				$data[strtolower($sup)] = Array("code" => strtolower($sup) , "country" => @$__GLOBAL["country_lang_list"][strtoupper($sup)][0] , "lang" => @$__GLOBAL["country_lang_list"][strtoupper($sup)][1]);
			}
			$__GLOBAL["supported_locales_data"] = $data;
		}
		else $data = $__GLOBAL["supported_locales_data"];
		return $data;
	}


	//melon.8.2.4
	static function getDefaultLanguage($useAll = false,$defaultLang = "es")
	{
		$lang_default = Config::get("lang_default",$defaultLang);
		$defLang = Config::get("def_content_lang",$lang_default);
		if($defLang == "all" && !$useAll) $defLang = $lang_default;
		return $defLang;
	}

	static function forEachLang()
	{
		$args = func_get_args();
		if(empty($args)) return;
		else $ff = $args[0];
		unset($args[0]);
		$ldata = Lang::getSupportedLocalesData();
		$ind = -1;
		foreach($ldata as $ld)
		{
			$ind++;
			call_user_func_array($ff,array_merge(Array($ld,$ind),$args));
		}
	}
	
	
	/*static function getConfig($config=Array())
	{
		return $config = array_merge(Array(
			'die_on_error' => true,
			'add_path' => false,
			'paths' => Array(''),
		),Config::getD('core.Lang',Array()),$config);
	}*/
	
	/*static function parsePoFile($filePath,$templateData=Array(),$config=Array())
	{
		__bpoint("should use fn_lang_parse_file");die("---");
		$config = Lang::getConfig($config);
		
		M3::reqLib('Intl');
		M3::reqCore('String');
		$templateData = array_merge(Array(
			//"base_path" => Config::get("lang_path"),
			"lang" => Config::get('lang',Config::get('default_locale')),
		),$templateData);
		
		//__vdump("parsePoFile",$filePath,$config,$templateData,"default_locale",Config::get('default_locale'));
	
		$finalPath = '';
		if($config['add_path'])
			foreach($config['paths'] as $basePath)
			{
				if($finalPath !== '') continue;
				$temp = fn_string_template("{$basePath}{$filePath}",$templateData);
				if(is_readable($temp)) $finalPath = $temp;
				//__vdump("foreach",$temp,$filePath,is_readable($temp));
			}
		else $finalPath = fn_string_template($filePath,$templateData);
		
		//__vdump("LANG FINAL PATH",$finalPath);
			
		if(!is_readable($finalPath) && $config['die_on_error'])
		{
			fn_rest_response(400,default_error_response(Array(
				'error_no' => 1,
				'error_msg' => 'error.no_lang_file',
				'data' => Array(
					'path' => $filePath,
					'parsed' => $finalPath,
				)
			)));
		}
		
		
		$langArray = Intl::parse_po_file($finalPath);
		//__vdump("ARRAY",$langArray);
		$langFileHeaders = array_shift($langArray);
		$langData = Array();
		if(!empty($langArray)) foreach($langArray as $ld) if(isset($ld["msgid"]))
			$langData[$ld["msgid"]] = $ld["msgstr"];
		//__vdump("PARSED LANG DATA",$templateData,$langData);
		return $langData;
	}*/
}

function __print($str) { Lang::printEntry($str);}
function __get($str) { $args = func_get_args(); return call_user_func_array("Lang::getEntry",$args); }
function __getuppr($str) { return __uppr(__get($str)); }
function __uppr($str) { return mb_strtoupper($str,"UTF-8"); }
function __lwr($str) { return mb_strtolower($str,"UTF-8"); }
?>
