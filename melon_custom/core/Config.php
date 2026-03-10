<?php

M3::reqCore('Array');

class Config
{
	static function get($var = null,$defaultValue=null,$writeOnDefault=false)
	{
		global $__CONFIG;
		if($var == null) return $__CONFIG;
		if(!isset($__CONFIG[$var])) Config::set($var,$defaultValue,$writeOnDefault);
		//M3::var_dump("CONFIG::GET",$var,$__CONFIG[$var]);
		return $__CONFIG[$var];
	}
	
	static function getD($var = null,$defaultValue=null)
	{
		global $__CONFIG;
		return __arrg($var,$__CONFIG,$defaultValue);
	}
	
	static function set($var,$value=null,$write=false)
	{
		global $__CONFIG;
		if(is_array($var))
		{
			$__CONFIG = array_merge($__CONFIG,$var);
			return $var;
		}
		else
		{
			$__CONFIG[$var] = $value;
			return $__CONFIG[$var];
		}
	}
	
	static function setD($var,$value)
	{
		global $__CONFIG;
		return __arrs($var,$value,$__CONFIG);
	}
	
	static function load()
	{
		global $CurrentUser, $__CONFIG;
		$gCFG = SQL::query("SELECT var,value FROM " . Config::get("sql_table_prefix") . "mconfigs WHERE user = 0",SQL::ARRAY_A);
		if(count($gCFG)) foreach($gCFG as $cfg) $__CONFIG[$cfg["var"]] = $cfg["value"];
	}
	
	static function loadForUser()
	{
	}
}

?>