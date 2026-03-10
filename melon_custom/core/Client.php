<?php

function fn_client_load($clientName)
{
	M3::reqCore('String');
	$paths = Config::get('client_path',[]);
	$loaded = false;
	if(empty($paths)) throw new Exception('fn_client_load - no path');
	
	//always load the latest on the cascade of paths
	$paths = array_reverse($paths);
	$clientPath = '';
	foreach($paths as $path)
	{
		$clientPath = fn_string_template($path,Array(
			'current_client_name' => $clientName
		),Array('merge_config' => true));
		$clientLoader = "{$clientPath}index.php";
		//__vdump("CLIENT PATH AND LOADER",$path,$clientPath,$clientLoader);
		if(is_readable($clientLoader))
		{
			$loaded = true;
			require_once($clientLoader);
			return $clientPath;
		}
	}
	throw new Exception("fn_client_load({$clientName}) - client not found");
}

function fn_client_set($clientName)
{
	$clientPath = fn_client_load($clientName);
	
	//__vdump("FN_CLIENT_SET",$clientName);
	
	Config::set('current_client_name',$clientName);
	Config::set('current_client_path',$clientPath);
	Config::set('current_client_url',str_replace(Config::get('base_path'),Config::get('base_url'),$clientPath));
	Config::set('current_client_shortname',str_replace('melon3.','',$clientName));
	
	//__vdump("--- CLIENT SET {$clientName}");
}

?>