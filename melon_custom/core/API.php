<?php

class M3
{
	static function version($full=true)
	{
		$_VERSION = constant($full ? 'MELON3_FULLVERSION' :'MELON3_VERSION');
		return $_VERSION;
	}
	
	static function reqCore($cores)
	{
		$corePath = dirname(__FILE__). '/';
		if(is_string($cores))
			$cores = [$cores];
		
		foreach($cores as $coreName)
			switch($coreName)
			{
				/*case 'RenderEngine':
					require_once(dirname(__FILE__) . '/render_engine/RenderEngine.php');
					break;*/
				default:
					$file = "{$coreName}.php";
					require_once($file);
					break;
			}
	}
	
	static function reqVendor($baseName,$options=Array(),&$_CONTEXT=null)
	{
		$name = $baseName;
		$spl = explode('@',$name);
		$_VERSION = false;
		if(count($spl) > 1)
		{
			$name = $spl[0];
			$_VERSION = $spl[1];
		}
		
		$paths = Config::get('vendor_path',[]);
		if(empty($paths)) throw new Exception('M3::reqVendor / No vendor path');
		
		//load the first found (from the top, on the cascade of paths)
		$paths = array_reverse($paths);
		
		$loaded = false;
		foreach($paths as $path)
		{
			if($loaded) continue;
			$filePath = "{$path}{$name}/autoload.php";
			//__vdump("vendor {$baseName} / {$filePath}",$name,$_VERSION);
			if(is_readable($filePath))
			{
				require_once($filePath);
				$loaded = true;
			}
		}
		
		if(!$loaded)
			throw new Exception("M3::reqVendor({$baseName}) - not found");
	}
	
	/*static function reqLib($name,&$_CONTEXT=null)
	{
		__bpoint("REQ LIB IS DEPRECTATED");
		$libsPath = dirname(__FILE__) . '/../libs/';
		//__vdump($name,$libsPath);
		switch($name)
		{
			default:
				$file = "{$libsPath}{$name}/index.php";
				require_once($file);
				break;
		}
	}*/
	
	/*static function reqDependency($name)
	{
		
	}*/
	


	
	/*
	en realidad este es el método que habría que usar porque es totalmente
	innecesario dar todas esas vueltas que da el original,
	en realidad no necesito para nada pasar primero por el DateObject de Melon
	en nuevas versiones, esa funcion con el objeto debería llamarse
	parseDateObject o algo así para diferenciarla, y debería usarse solo en los
	casos en los que tengo strings más complejas donde se pueda confundir un
	parámetro con texto, por ejemplo "#{d}d" (por la razón que fuera que
	alguien quisiera hacer algo así)
	y en realidad esta función (actualmente parseDateNEW) también es totalmente
	redundante, y su única razón de existir es el shortcut de date("format",strtotime($date))
	*/
	static function parseDateNEW($date=false,$format="d F Y - H:i") { return date($format,strtotime($date)); }

	
	static function getSTDClass($base=Array())
	{
		return (object)$base;
	}
	
	static function setSTD(&$obj,$key,$value)
	{
		$obj->{$key} = $value;
	}

	
	/**

	-> trace()
	--> printTrace()
	   imprimir mensajes en pantalla con CSS

	-> ERROR_CRITICO
	   asesinar ejecución de app y reportar error crítico


	-- HTTP

	-> getBackURL()
	   obtiene la back URL desde los datos que tenga disponible

	-> mensaje($data,$type = "highlight")
	   redirecciona a página de mensajes con mensaje

	-> redirect($txt,$url,$time=5)
	   redirecciona según corresponda

	-> header_l($location,$timeout=0)
	   dumpea un header PHP

	-> header_JS($location,$timeout=0)
	   redirecciona utilizando JS

	-> fixPHP4_5_Globals()
	--> function fixPHP4_Globals()
	--> function fixPHP5_Globals()
	   arregla incompatibilidades php 4 - 5


	-> &getVariable($value)
	   devuelve un valor no variable como una variable, por ejemplo un booleano, especial para los casos en los
	   que me piden que sea una variable por referencia y no valor


	-- STRINGs

	-> template($tpl,$data)
	-> camelize($str, $capitalizeFirst = true, $allowed = 'A-Za-z0-9')
	-> truncar($string, $limit, $break=".", $pad="...")
	-> varName($str)


	-- ARRAYs
	-> find($hay,$need)

	**/

	public static $cssLoaded = false;
	static function trace()
	{
		/*if(!M3::$cssLoaded)
		{
			$error = HTML::loadCSS(Config::get("melon_url") . "core/css/API.css");
			M3::$cssLoaded = true;
		}*/

		$args = func_get_args();
		if(count($args)) foreach($args as $k => $v) M3::printTrace($k,$v,0,true);
	}

	static function printTrace($key,$value,$prep=0,$header=false)
	{
		$k = $key; $v = $value;
		$mt = $header ? 5 : 0;
		echo "<p class='trace" . (!$prep?" first":"") . "' style='margin-left:" . ($prep*12) . "px;margin-top:" . $mt . "px;border-left:" . $prep*$prep . "px solid #63ac80'>";
		if(!$header) echo "[<span class='key'>" . $k . "</span>] ";
		if(is_array($v))
		{
			echo '<span class="value">';
			echo "<i>ARRAY [" . count($v) . "]</i></p>";
			echo "</span>";
			if(count($v)) foreach($v as $kk => $vv) M3::printTrace($kk,$vv,$prep+1);
		}
		else
		{
			echo '<span class="value">';
			if(is_string($v) || is_numeric($v)) echo htmlspecialchars($v);
			else if(is_bool($v)) echo $v ? "true" : "false";
			echo "</span>";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='type'>| " . gettype($v) . " (" . strlen((string)$v) . ")</span>";
			echo "</p>";
		}
	}
	
	static function comment()
	{
		echo "<!--\n";
		$args = func_get_args();
		if(count($args)) foreach($args as $k => $v) var_dump($v);
		echo "\n-->";
	}

	static function pre_var_dump()
	{
		?><pre style="background-color: white;font-size:14px;color: black;line-height:120%;"><?php
		/*$args = func_get_args();
		if(count($args)) foreach($args as $k => $v) var_dump($v);*/
		call_user_func_array("M3::var_dump",func_get_args());
		?></pre><?php
	}

	static function var_dump()
	{
		$args = func_get_args();
		if(count($args)) foreach($args as $k => $v) var_dump($v);
	}
	
	
	
	static function templateString($tpl,$data,$debug=false)
	{
		foreach($data as $k => $v) if(!is_array($v))
		{
			//M3::trace($k,$v,$tpl);
			$tpl = @str_replace("#{".$k."}",$v,$tpl);
		}
		if($debug) M3::trace($tpl,$data);
		return preg_replace('/#\{.*?\}/', '', $tpl);
	}
}

function __vdump() { return call_user_func_array("M3::var_dump",func_get_args()); }
function __pvdump() { return call_user_func_array("M3::pre_var_dump",func_get_args()); }
function __trace() { return call_user_func_array("M3::trace",func_get_args()); }
function __comment() { return call_user_func_array("M3::comment",func_get_args()); }

function __bpoint() { return call_user_func_array("M3::var_dump",array_merge(
	[
		'---------- (start) BREAK POINT',
		'--params start'
	],func_get_args(),
	['-- params end','--- backtrace start'],
	debug_backtrace(),["---------- (end) BREAK POINT"])); }

?>