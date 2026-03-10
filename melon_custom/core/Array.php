<?php


function fn_array_filter_trim($source,$filterNull=true)
{
	$debug = false;
	if($debug) __vdump("FILTER TRIM SOURCE",$source);
	return array_filter($source,function($it) { 
		if(!is_string($it))
		{
			if($it === null) return $filterNull ? false : true;
			else return true;
		}
		return trim($it) !== '';  });
}

function fn_array_map_keys($source,$baseMap='')
{
	$currentMap = trim($baseMap) !== '' ? "{$baseMap}." : '';
	$maps = [];
	$sourceKeys = array_keys($source);
	if(!empty($sourceKeys))
		foreach($sourceKeys as $i => $k)
		{
			$v = $source[$k];
			$map = "{$currentMap}{$k}";
			$maps[] = $map;
			if(gettype($v) === 'array')
				$maps = array_merge($maps,fn_array_map_keys($v,$map));
		}
	return $maps;
}

function fn_array_map_keys_with($source,$callback,$baseMap='')
{
	$currentMap = trim($baseMap) !== '' ? "{$baseMap}." : '';
	$maps = [];
	$sourceKeys = array_keys($source);
	if(!empty($sourceKeys))
		foreach($sourceKeys as $i => $k)
		{
			$v = $source[$k];
			$map = "{$currentMap}{$k}";
			list($addMap,$recursive) = call_user_func_array($callback,[$map,$k,$v]);
			//__vdump("para $map",$addMap,$recursive);
			if($addMap) $maps[] = $map;
			if(gettype($v) === 'array' && $recursive)
				$maps = array_merge($maps,fn_array_map_keys_with($v,$callback,$map));
		}
	return $maps;
}

function fn_array_from_selectors()
{
	$output = Array();
	$args = func_get_args();
	if(!empty($args)) foreach($args as $arg)
		foreach($arg as $k => $v)
			fn_array_set($k,$v,$output);
	return $output;
}

function fn_array_to_vars()
{
	$output = Array();
	$args = func_get_args();
	$aq = func_num_args();
	if($aq) foreach($args as $arg)
	{
		foreach($arg as $k => $v)
		{
			if(!is_array($v)) $output[] = Array("var" => $k , "value" => $v);
			else
			{
				$vars = fn_array_to_vars($v);
				foreach($vars as &$entry) $entry["var"] = "$k." . $entry["var"];
				unset($entry);
				$output = array_merge($output,$vars);
			}
		}
	}
	return $output;
}
	
function fn_array_from_vars()
{
	$output = Array();
	$args = func_get_args();
	//__vdump("from vars args",$args);
	$aq = func_num_args();
	
	
	
	__vdump($aq);
	
	if($aq) 
		foreach($args as $arg) 
	foreach($arg as $var) 
	fn_array_set($var["var"],$var["value"],$output);
	return $output;
}

/*function fn_array_unshift()
{
	
}*/

function fn_array_filter_keys(&$array,$accept)
{
	$GLOBALS['_fn_array_filter_keys_temp_accept'] = $accept;
	$array = array_filter($array,function($key){
		return in_array($key,$GLOBALS['_fn_array_filter_keys_temp_accept']);
	},ARRAY_FILTER_USE_KEY);
	unset($GLOBALS['_fn_array_filter_keys_temp_accept']);
}

/*function fn_array_filter_keys(&$array, $keys = array())
{ 
	// If array is empty or not an array at all, don't bother 
	// doing anything else. 
	if(empty($array) || (! is_array($array))) { 
		return false ;
	} 

	// If $keys is a comma-separated list, convert to an array. 
	if(is_string($keys)) { 
		$keys = explode(',', $keys); 
	} 

	// At this point if $keys is not an array, we can't do anything with it. 
	if(! is_array($keys)) { 
		return false; 
	} 

	// array_diff_key() expected an associative array. 
	$assocKeys = array(); 
	foreach($keys as $key) { 
		$assocKeys[$key] = true; 
	} 

	$array = array_diff_key($assocKeys,$array); 
	
	return true;
}*/
	

function fn_array_set($var,$value,&$target)
{
	$vars = !is_array($var) ? explode('.',$var)  : $var;
	$vq = count($vars);
	//__vdump("set",$var,$value);
	if($vq > 1)
	{
		if(!isset($target[$vars[0]])) $target[$vars[0]] = Array();
		if(!is_array($target[$vars[0]])) $target[$vars[0]] = Array($target[$vars[0]]);
		$current = &$target[$vars[0]];
		for($x = 1; $x < $vq - 1; $x++)
		{
			if(!isset($current[$vars[$x]])) $current[$vars[$x]] = Array();
			if(!is_array($current)) $current = Array($current);
			$current = &$current[$vars[$x]];
		}
		//__vdump("current pre error",$current);
		$current[$vars[$vq-1]] = $value;
		unset($current);
	}
	else $target[$vars[0]] = $value;
	//__vdump("set end");
	return $target;
}

function fn_array_merge_recursive()
{
	$args = func_get_args();
	$count = func_num_args();
	
	$output = Array();
	if($count) foreach($args as $array)
	{
		if(!is_array($array))
		{
			$output = $array;
			continue;
		}
		
		if(count($array))
		{
			foreach($array as $k => $v) if(is_string($k))
			{
				if(!isset($output[$k])) $output[$k] = $v;
				else $output[$k] = fn_array_merge_recursive($output[$k],$v);
			}
			else $output[] = $v;
		}
	}
	return $output;
}

function fn_array_unset(&$array,$keys)
{
	$array = array_diff_key($array, array_flip($keys));
}

function fn_array_unset_val(&$array,$keys,$unsetValue)
{
	//$array = array_diff_key($array, array_flip($keys));
	foreach($array as $key => &$value)
	{
		if(in_array($key,$keys))
		{
			$value = $unsetValue;
		}
	}
}

function fn_array_unset_selector($selector,&$source,$offset=false,$splitter='.')
{
	//__vdump("-- inicio __arrg",$var);
	$arrayVars = is_array($selector) ? $selector : explode($splitter,$selector);
	if($offset !== false)
	{
		$varCount = count($arrayVars);
		if($varCount - abs($offset) <= 0) $offset = 0;
		if($offset) array_splice($arrayVars,$offset);
	}
	$varCount = count($arrayVars);
	
	if($varCount)
	{
		if(!isset($source[$arrayVars[0]])) return null;
		$val = &$source[$arrayVars[0]];
		
		if($varCount === 1)
		{
			unset($source[$arrayVars[0]]);
			return $val;
		}
		
		for($x=1;$x<$varCount;$x++)
		{
			if(!isset($val[$arrayVars[$x]])) return null;
			else
			{
				if($x === $varCount - 1)
				{
					$temp = $val[$arrayVars[$x]];
					unset($val[$arrayVars[$x]]);
					$val = $temp;
				}
				else $val = &$val[$arrayVars[$x]];
			}
		}
		return $val;
	}
	return null;
}


function fn_array_remove_key_prefix(&$array,$prefix)
{
	/*$keys = array_keys($array);
	$values = array_values($array);
	$prefixLength = strlen($prefix);
	foreach($keys as &$key)
		$key = fn_string_unprefix($key,$prefix,$prefixLength);
		//if(strpos($key,$prefix) === 0)
			//$key = substr($key,strlen($prefix));

	$array = array_combine($keys,$values);
	return $array;*/
	$array = array_combine(
		fn_string_unprefix_array(array_keys($array),$prefix),
		array_values($array)
	);
	
	return $array;
}

function fn_array_remove_key_prefix_ret($array,$prefix)
{
	return fn_array_remove_key_prefix($array,$prefix);
}

function fn_array_append_key_prefix(&$array,$prefix)
{
	$keys = array_keys($array);
	$values = array_values($array);
	foreach($keys as &$key)
		if(strpos($key,$prefix) !== 0)
			$key = "{$prefix}{$key}";

	$array = array_combine($keys,$values);
	return $array;
}

function fn_array_append_value_prefix(&$array,$prefix)
{
	$keys = array_keys($array);
	$values = array_values($array);
	foreach($values as &$value)
		if(is_string($value))
			if(strpos($value,$prefix) !== 0)
				$value = "{$prefix}{$value}";

	$array = array_combine($keys,$values);
	return $array;
}

/*
	set array keys from a nested (or not) value using a selector
*/
//static function setKeyFromValue($array,$selector) { return NArray::setKeysFromValue($array,$selector); }
function fn_array_set_keys_from_value($array,$selector)
{
	$final = Array();
	$ind = 0;
	if(!empty($array)) foreach($array as $it)
	{
		$keyVal = NArray::get($selector,$it,null);
		if($keyVal === null)
		{
			$ind++;
			$keyVal = "missing-value-for-key-{$ind}";
		}
		$final[$keyVal] = $it;
	}
	return $final;
}

Class NArray
{
	
	static function getValues($array,$var)
	{
		global $__GLOBAL;
		$__GLOBAL['__n_array_getValues_current_var_'] = $var;
		$ret = array_map(function($a){
			global $__GLOBAL;
			return __arrg($__GLOBAL['__n_array_getValues_current_var_'],$a);
		},$array);
		unset($__GLOBAL['__n_array_getValues_current_var_']);
		return $ret;
	}
	
	static function get($var,$source,$defaultValue=null,$offset=false,$asArray=false,$splitter=".")
	{
		//__vdump("-- inicio __arrg",$var);
		$arrayVars = is_array($var) ? $var : explode($splitter,$var);
		if($offset !== false)
		{
			$varCount = count($arrayVars);
			if($varCount - abs($offset) <= 0) $offset = 0;
			if($offset) array_splice($arrayVars,$offset);
		}
		$varCount = count($arrayVars);
		
		/*$returnValue = $defaultValue;
		if($varCount)
		{
			if(!isset($source[$var]))
			{
				$nextKey = array_shift($arrayVars);
				if(isset($source[$nextKey]))
					$returnValue = NArray::get(implode($splitter,$arrayVars),$source[$nextKey],$defaultValue);
			}
			else $returnValue = $source[$var];
		}
		
		return $returnValue;*/
		
		//__vdump("pre ciclar?",$varCount);

		if($varCount)
		{
			$base = @$source[$arrayVars[0]];
			$val = $base === null ? $defaultValue : $base;
			//__vdump("TESTO IF $varCount",$arrayVars[0],$base);
			if($base === null || $varCount == 1)
				//if($asArray && !is_array($val)) return !$offset ? $source : Array($var => $val);
				if($asArray && !is_array($val)) return $source;
				else return $val;

			//__vdump("-- pre ciclo en serio");
			for($x=1;$x<$varCount;$x++)
			{
				//__vdump("- ciclo",$arrayVars[$x]);
				if(!isset($val[$arrayVars[$x]])) return $defaultValue;
				else $val = $val[$arrayVars[$x]];
			}
			
			if($asArray && !is_array($val)) $val = $source;
			return $val;
		}
		return $defaultValue;
	}
	
	
	
	//
	/*static function toVars()
	{
		$output = Array();
		$args = func_get_args();
		$aq = func_num_args();
		if($aq) foreach($args as $arg)
		{
			foreach($arg as $k => $v)
			{
				if(!is_array($v)) $output[] = Array("var" => $k , "value" => $v);
				else
				{
					$vars = NArray::toVars($v);
					foreach($vars as &$entry) $entry["var"] = "$k." . $entry["var"];
					unset($entry);
					$output = array_merge($output,$vars);
				}
			}
		}
		return $output;
	}*/
	
	/*static function fromVars()
	{
		$output = Array();
		$args = func_get_args();
		$aq = func_num_args();
		if($aq) foreach($args as $arg) foreach($arg as $var) NArray::set($var["var"],$var["value"],$output);
		return $output;
	}*/
	
	static function stringify($source)
	{
		if(!is_array($source)) return (string)$source;
		return http_build_query($source);
	}
	
	
	
	/** ARRAY FUNCTIONS **/
	/** ARRAY FUNCTIONS **/
	/** ARRAY FUNCTIONS **/
	/** ARRAY FUNCTIONS **/
	
	static function find($hay,$need,$want = null)
	{
		$key = null;
		$val = null;
		
		if(is_array($need)) foreach($need as $k => $v) { $key = $k; $val = $v; }
		else $val = $need;
		
		if(!count($hay)) return null;
		
		foreach($hay as $h) foreach($h as $k => $v)
		{
			if($key != null) if($k == $key && $val == $v) return $want == null ? $h : $h[$want];
			if($key == null) if($val == $v) return $want == null ? $h : $h[$want];
		}
		return null;
	}
	
	static function findMultiple($hay,$need,$want = null)
	{
		$output = Array();
		
		$key = null;
		$val = null;
		
		if(is_array($need)) foreach($need as $k => $v) { $key = $k; $val = $v; }
		else $val = $need;
		
		if(!count($hay)) return null;
		
		foreach($hay as $h) foreach($h as $k => $v)
		{
			if($key != null) if($k == $key && $val == $v) $output[] = $want == null ? $h : $h[$want];
			if($key == null) if($val == $v) $output[] = $want == null ? $h : $h[$want];
		}
		return $output;
	}
	
	static function pos($hay,$need)
	{
		$key = null;
		$val = null;
		
		if(is_array($need)) foreach($need as $k => $v) { $key = $k; $val = $v; }
		else $val = $need;
		
		if(!count($hay)) return null;
		
		$ind = -1;
		foreach($hay as $h)
		{
			$ind++;
			foreach($h as $k => $v) if($key == $k && $v == $val) return $ind;
		}
		
		return null;
	}
	
	static function filter($hay,$need)
	{
		$key = null;
		$val = null;
		
		if(is_array($need)) foreach($need as $k => $v) { $key = $k; $val = $v; }
		else $val = $need;
		
		$output = Array();
		
		if(!count($hay)) return null;
		
		foreach($hay as $h) foreach($h as $k => $v)
		{
			if($key != null) if($k == $key && $val == $v) $output[] = $h;
			if($key == null) if($val == $v) $output[] = $h;
		}
		
		return $output;
	}
	
	
	
	
	
	
	
	
	static function array_remove_keys($array, $keys = array()) { 

		// If array is empty or not an array at all, don't bother 
		// doing anything else. 
		if(empty($array) || (! is_array($array))) { 
			return $array; 
		} 

		// If $keys is a comma-separated list, convert to an array. 
		if(is_string($keys)) { 
			$keys = explode(',', $keys); 
		} 

		// At this point if $keys is not an array, we can't do anything with it. 
		if(! is_array($keys)) { 
			return $array; 
		} 

		// array_diff_key() expected an associative array. 
		$assocKeys = array(); 
		foreach($keys as $key) { 
			$assocKeys[$key] = true; 
		} 

		return array_diff_key($array, $assocKeys); 
	} 
	
	
	
	
	
	
	
	static function array_split($array, $pieces=2) 
	{   
		if ($pieces < 2) 
			return array($array); 
		$newCount = ceil(count($array)/$pieces); 
		$a = array_slice($array, 0, $newCount); 
		$b = NArray::array_split(array_slice($array, $newCount), $pieces-1); 
		return array_merge(array($a),$b); 
	}
	
	
	static function sort(&$array,$key,$type="string",$reverse=false)
	{
		global $__GLOBAL;
		$__GLOBAL["narray::sort::key"] = $key;
		$__GLOBAL["narray::sort::type"] = $type;
		usort($array,function($a,$b){
			global $__GLOBAL;
			$key = $__GLOBAL["narray::sort::key"];
			$type = $__GLOBAL["narray::sort::type"];
			//M3::trace($key,NArray::get($key,$a),NArray::get($key,$b),$a["content"]["name"]["es"],$b["content"]["name"]["es"]);
			switch($type)
			{
				case "string":
					return strcmp(NArray::get($key,$a),NArray::get($key,$b));
					break;
				case "int":
					return NArray::get($key,$a)> NArray::get($key,$b);
					break;
			}
		});
		
		if($reverse) $array = array_reverse($array);
	}
	
	static function sortArray($array,$orderArray,$keepKeys=true)
	{
		$ordered = array();
		foreach ($orderArray as $key) {
			if (array_key_exists($key, $array))
			{
				//if($keepKeys)
					$ordered[$key] = $array[$key];
				/*else
					$ordered[] = $array[$key];*/
				unset($array[$key]);
			}
		}
		return $ordered + $array;
	}
	
	static function unsetKey($key,&$array)
	{
		unset($array[$key]);
	}
	
	/*static function getLast(&$from,$asReff=false)
	{
		if(empty($from)) return null;
		$count = count($from);
		M3::trace("GET LAST",$asReff);
		if($asReff) $item = &$from[$count-1];
		$item = $from[$count-1];
		return $item;
	}*/
}

/*aliases*/
function __arrg() { return call_user_func_array("NArray::get",func_get_args()); }
function __arrs($var,$value,&$target) { return fn_array_set($var,$value,$target); }
//function __arrlast(&$from,$asReff=false) { return NArray::getLast($from,$asReff); }


?>