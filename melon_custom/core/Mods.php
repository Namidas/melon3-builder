<?php

function fn_mods_common_delete_records($ids,$type,$table,$key,$return=true)
{
	$_CURRENT_USER = fn_auth_get_current_user();
	DB::update($table,Array(
		'_deleted' => '1',
		'_deleted_time' => DB::sqleval('NOW()'),
		'_deleted_by' => $_CURRENT_USER['id'],
	),"{$table}.{$key} IN %l{$type}",$ids);
	return $return;
}

class Mods
{
	const MOD = "mods";
	const PLUG = "plugs";
	public static $scripts = Array(//all scripts loaded
		"mods" => Array(),
		"plugs" => Array(),
	);

	public static $Config = Array();

	static function updateConfig($more=Array())
	{
		$defConfig = Array(
			Config::get("base_path") . "mods/",
		);

		Mods::$Config = array_merge($defConfig,(array)Config::get("Scripts"),$more);
		Config::set("Scripts",Mods::$Config);
	}

	static function get($id=false)
	{
		if($id === false) return Mods::$scripts;
		else
		{
			foreach(Mods::$scripts as $t) if(count($t)) foreach($t as $k => $v) if($k == $id) return $v;
			return null;
		}
		return null;
	}

	static function getMod($id=false)
	{
		if($id === false) return Mods::$scripts["mods"];
		else return @Mods::$scripts["mods"][$id];
		return null;
	}

	public static $loaded = false;
	static function load($id=false,$_MANIFEST_INNER=Array())
	{
		if($id === false)
		{
			$mods = Config::get('mods');
			#change-to:delete
			#{
			$mods = array_merge(Config::get('mods_gen',Array()),$mods);
			#}
			//__vdump("--- LOAD",$mods);
			foreach($mods as $modKey => $modValue)
			{
				$_MANIFEST_INNER = Array();
				if(is_int($modKey)) $modName = $modValue;
				else
				{
					$modName = $modKey;
					$_MANIFEST_INNER = $modValue;
					//__vdump("load {$modName}");
				}
				Mods::load($modName,$_MANIFEST_INNER);
			}
			return;
		}
		
		$paths = Config::get('mods_path');
		if(!is_array($paths)) $paths = Array($paths);
		
		//load the first found (from the top, on the cascade of paths)
		$paths = array_reverse($paths);
		
		/*#change-to:delete
		#{
		$genMods = Config::get('mods_gen',Array());
		//__vdump("GEN MODS",$genMods);
		$genModsPath = Config::get('mods_gen_path');
		if(!empty($genMods)) $paths[] = $genModsPath;
		#}*/
		
		$loaded = false;
		
		//__vdump($id,$paths);
		
		if(!empty($paths)) foreach($paths as $path)
		{
			if($loaded) continue;
			$filePath = "{$path}{$id}/index.php";
			//__vdump($filePath);
			//__vdump("READABLE",is_readable($filePath));
			if(is_readable($filePath))
			{
				//__vdump("***** MANDO A CARGAR {$filePath}");
				//$_MANIFEST_INNER = Config::getD(
				require_once($filePath);
				$loaded = true;
				$_MANIFEST['path'] = "{$path}{$id}/";
				if(!isset($_MANIFEST['alias']))
					$_MANIFEST['alias'] = [];
				elseif(is_string($_MANIFEST['alias']))
					$_MANIFEST['alias'] = [$_MANIFEST['alias']];
				//__vdump("FINAL MANIFEST",$_MANIFEST);
				Mods::set($_MANIFEST);
			}
		}
		//__vdump("LOADED",$loaded);
		if(!$loaded)
		{
			throw new Exception("Mods::load({$id}) - not found"); return false;
		}
		else return true;
	}

	static function call($method,$mod,$args=Array())
	{
		$manifest = Mods::getMod($mod);
		$__method = "fn_{$manifest['name']}_{$method}";
		//__vdump("call",$__method);
		if(function_exists($__method)) return call_user_func_array($__method,array_merge($args,Array($mod)));
		return null;
	}
	static function getFileIfExists($file,$mod)
	{
		return Mods::getFile($file,$mod,false,false);
	}
	
	static function getFile($file,$mod,$require=false,$throwErrorIfNotExists=true,$once=false)
	{
	
		$flds = Config::get("Scripts");
		/* no estoy seguro de este hardcodeo, pero sino no me cargaba
		online, en el front, los fileIfExists (aunque si en el back)*/
		$flds[] = Config::get("melon_path") . "mods/";
		$genModsPath = Config::get("gen_mods_path");
		$genModsInclude = Config::get("gen_mods_include","*");
		//M3::trace($flds);
		$found = Array();
		foreach($flds as $dir)
		{
			$fpath = "{$dir}{$mod}/";
			if(is_readable($fpath))
			{
				if(is_readable("{$fpath}{$file}"))
				{
					$found[] = "{$fpath}{$file}";
					if($require)
					{
						if($once) require_once("{$fpath}{$file}");
						else require("{$fpath}{$file}");
					}
				}
			}
		}
		if(empty($found) && $throwErrorIfNotExists) M3::trace("NO SE ENCONTRO ARCHIVO Mods::getFile {$mod}/{$file}");
		return $found;
	}
	
	static function set($manifest)
	{
		if(isset(Mods::$scripts['mods'][$manifest['name']]))
			die("--- [{$manifest['name']}] mod already defined");
		Mods::$scripts['mods'][$manifest['name']] = $manifest;
		
		if(!empty($manifest['alias']))
			foreach($manifest['alias'] as $alias)
				if(isset(Mods::$scripts['mods'][$alias]))
					die("--- [{$alias}] mod alias already defined");
				else Mods::$scripts['mods'][$alias] = &Mods::$scripts['mods'][$manifest['name']];
		
	}
}

Mods::load();

?>