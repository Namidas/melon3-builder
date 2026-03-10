<?php

/* here goes the license, check  smarty_tag_license on melon3_builder */



function fn_std_array($base=Array())
{
	return (object)$base;
}
	
	
function fn_std_set(&$target,$key,$value)
{
	$target->{$key} = $value;
}

function fn_print_var($source,$opts=Array())
{
	//__vdump("***print var");
	$opts = array_merge(Array(
		//these are default JSON configs
		'open_object' => '{',
		'close_object' => '}',
		'open_array' => '[',
		'close_array' => ']',
		'pair_separator' => ': ',
		'separator' => ',',
		'separate_last' => false,
		'nest_base' => true,
		
		'pretty' => !_PRODUCTION_,
		'pretty_line_break' => "\n",
		'pretty_tab' => '	',
		'pretty_current_depth' => false,
		
		'default_handler' => 'json_encode',
		'default_handler_args' => Array(),
		
		
		'wrap_key' => "'",
		'key_pre' => '',
		'key_post' => '',
	),$opts);
	
	$isBase = $opts['pretty_current_depth'] === false;
	if($isBase) $opts['pretty_current_depth'] = 513;
	
	$depthTab = '';
	$baseTab = $opts['pretty'] ? $opts['pretty_tab'] : '';
	$lineBreak = $opts['pretty'] ? $opts['pretty_line_break'] : '';
	
	//if(($isBase === true && $opts['nest_base'] === true) || !$isBase)
	//if($opts['pretty_current_depth'] !== 512)
		for($x = 0; $x < 512 - $opts['pretty_current_depth']; $x++) $depthTab .= $baseTab;
	
	$result = '';
	
	switch(gettype($source))
	{
		/*case 'boolean':
		case 'integer':
		case 'double':
		case 'string':
		case 'resource':
		case 'NULL':
		case 'unknown type':*/
			
		case 'array':
			$keys = array_keys($source);
			$keysCount = count($keys);
			$isObject = false;
			
			//$result = '';
			//if($depth < 512) $result .= "{$lineBreak}{$baseTab}{$depthTab}";
			
			foreach($keys as $k) if(gettype($k) === 'string') $isObject = true;
			$enclose = ($isBase === true && $opts['nest_base'] === true) || !$isBase;
			
			if($enclose)
			{
				if($isObject) $result .= "{$baseTab}{$depthTab}{$opts['open_object']}";
				//else $result .= "{$baseTab}{$depthTab}{$opts['open_array']}";
				else $result .= $opts['open_array'];
				$result .= $lineBreak;
			}
			
			$index = 0;
			foreach($source as $key => $value)
			{
				if($isObject) $result .= "{$baseTab}{$depthTab}{$opts['wrap_key']}{$opts['key_pre']}{$key}{$opts['key_post']}{$opts['wrap_key']}{$opts['pair_separator']}" . fn_print_var($value,array_merge($opts,Array('pretty_current_depth' => $opts['pretty_current_depth'] - 1)));
				else $result .= fn_print_var($value,array_merge($opts,Array('pretty_current_depth' => $opts['pretty_current_depth'] - 1)));
				if($index < $keysCount-1 || $opts['separate_last'] === true) $result .= "{$opts['separator']}{$lineBreak}";
				$index++;
			}
			
			if($enclose)
			{
				if($isObject) $result .= $opts['close_object'];
				else $result .= $opts['close_array'];
			}
			
			break;
			
		case 'object':
			switch(get_class($source))
			{
				case 'LiteralOutput':
					$result = $source->text;
					break;
				default:
					$result = call_user_func_array($opts['default_handler'],array_merge(Array($source),$opts['default_handler_args']));
					break;
			}
			break;
			
		case 'string':
			$result = "'{$source}'";
			break;
		
		default:
			$result = call_user_func_array($opts['default_handler'],array_merge(Array($source),$opts['default_handler_args']));
			break;
	}
	return $result;
}


function fn_json_encode($source,$flags = 0,$depth = 512)
{
	return fn_print_var($source,['default_handler_args' => [$flags,$depth]]);
}


function fn_var_export($source,$return=false,$brackets=false,$extraOptions=[],$debug=false)
{
	$printConfig = array_merge(Array(
		'open_object' => $brackets ? '[' : 'Array(',
		'close_object' => $brackets ? ']' : ')',
		'open_array' => $brackets ? '[' : 'Array(',
		'close_array' => $brackets ? ']' : ')',
		'pair_separator' => ' => ',
		'default_handler' => 'var_export',
		'default_handler_args' => [true]
	),$extraOptions);
	$res = fn_print_var($source,$printConfig);
	
	if($debug === true)
	{
		__vdump("","","",$res);
		die("--- fn_var_export debug");
	}
	
	if($return === true) return $res;
	echo $res;
}

?>