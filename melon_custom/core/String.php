<?php

#######{prepend_content}

function fn_string_normalize_path($path)
{
	return str_replace([
			'\\',
		],[
			'/',
		],$path);
}

#docs:core/String
#{
# ## core/String
# String related functions and helpers.
#}

#docs:core/String
#{
# ### fn_string_camelize($source,$options=Array())
# Returns the camelized version of *$string*
# #### *$source* string
# Source string to camelize
# #### *$options* array
# - '***camelizeFirst***': defaults *false*, wether to uppercase (or not) the first word as well
# - '***split***': defaults *['-','_']*, array of strings to be considered as word delimiter
# - '***separator***': defaults '*(some random gibberish string)*', the option '*split*' is replaced with this (on *$source*) and the resulting string is exploded with this same option, so just use whatever random string you can think of and be sure that is not present in *$source*
#}
function fn_string_camelize($source,$options=Array())
{
	$options = Array(
		'camelizeFirst' => false,
		'split' => ['-','_'],
		'separator' => '****////////***************'
	);
	
	$words = explode($options['separator'],str_replace($options['split'],$options['separator'],$source));
	if(empty($words)) return '';
	$firstWord = array_shift($words);
	$words = array_map('ucfirst', $words);
	if($options['camelizeFirst'] === true) $firstWord = ucfirst($firstWord);
	return $firstWord . implode('',$words);
}

#docs:core/String
#{
# ### fn_string_starts_with($haystack,$needle)
# Returns *true* if *$haystack* begins with *$needle*, *false* otherwise.
# Helper function, internally does `return strpos($haystack,$needle) === 0`.
# #### *$haystack* string
# Source string to check
# #### *$needle* string
# String to check if it's at position 0 of *$haystack*
#}
function fn_string_starts_with($haystack,$needle)
{
	return strpos($haystack,$needle) === 0;
}

#docs:core/String
#{
# ### fn_string_prefix($string,$prefix)
# Returns *$string* prefixed by *$prefix* if it doesn't already start with it.
# For example: `fn_string_prefix('world','hello_')` will return the same value ('*hello_world*') as `fn_string_prefix('hello_world','hello_')` (since, on the second case, the source string is already prefixed with the given prefix).
# A typical example would be inserting a row into a DB, where columns are usually named as (for instance): user_id, user_name, user_email, etc, you may want your user/developer to be able to feed your inserting function with just 'id', 'name', 'email', etc, assuming you get row data as an array, you can then prefix all keys at once (although in this example and most use cases you would use *fn_array_append_key_prefix* instead, which in turn internally uses this function)
# #### *$string* string
# Source string to prefix
# #### *$prefix* string
# Prefix to prepend to *$string* if it's not prefixed already (internally uses *fn_string_starts_with* to check this)
#}
function fn_string_prefix($string,$prefix)
{
	if(!fn_string_starts_with($string,$prefix))
		$string = "{$prefix}{$string}";
	return $string;
}

#docs:core/String
#{
# ### fn_string_prefix_array($array,$prefix)
# Returns all values from *$array* prefixed by *$prefix* (for the values that don't already start with it), it's the same as *fn_string_prefix* but iterates over an array of strings instead of a single string.
# For example: `fn_string_prefix('world','hello_')` will return the same value ('*hello_world*') as `fn_string_prefix('hello_world','hello_')` (since, on the second case, the source string is already prefixed with the given prefix).
# A typical example would be inserting a row into a DB, where columns are usually named as (for instance): user_id, user_name, user_email, etc, you may want your user/developer to be able to feed your inserting function with just 'id', 'name', 'email', etc, assuming you get row data as an array, you can then prefix all keys at once (although in this example and most use cases you would use *fn_array_append_key_prefix* instead, which in turn internally uses this function)
# #### *$array* array
# Array of strings to prefix
# #### *$prefix* string
# Prefix to prepend if it's not prefixed already (internally uses *fn_string_starts_with* to check this)
#}
function fn_string_prefix_array($array,$prefix/*,$escape=false*/)
{
	foreach($array as &$string)
	//{
		//if($escape !== false)
			//if(fn_string_starts_with($string,$escape))
				//continue;
		$string = fn_string_prefix($string,$prefix);
	//}
	return $array;
}

#docs:core/String
#{
# ### fn_string_unprefix($source,$prefix,$prefixLength=false)
# Returns the unprefixed (removed from position 0) version of the source string.
# #### *$source* string
# String to remove prefix from
# #### *$prefix* string
# Prefix to remove from *$source*
# #### *$prefixLength* int
# Length of *$prefix*, if *false* then it's calculated on demand.
# This param is useful when running the function inside a loop (for instance internally on *fn_string_unprefix_array*, so it doesn't calculate the length of the same prefix multiple times)
#}
function fn_string_unprefix($source,$prefix,$prefixLength=false)
{
	if($prefixLength === false) $prefixLength = strlen($prefix);
	if(fn_string_starts_with($source,$prefix))
		$source = substr($source,$prefixLength);
	return $source;
}

#docs:core/String
#{
# ### fn_string_unprefix_array($array,$prefix)
# Returns an array with the unprefixed (removed from position 0) version of all the strings in it.
# #### *$array* array
# Array of strings to remove prefix from
# #### *$prefix* string
# Prefix to remove
#}
function fn_string_unprefix_array($array,$prefix)
{
	$prefixLength = strlen($prefix);
	foreach($array as &$string)
		$string = fn_string_unprefix($string,$prefix,$prefixLength);
	return $array;
}

#docs:core/String
#{
# ### fn_string_serialize($value,$base64Encode=true)
# Returns the serialized version of *$value*. It internally uses, but difers from, PHP's *serialize* in the fact that it will by default also base64 encode the serialized string, to avoid problems while storing the string somewhere else (like a SQL table).
# #### *$value* mixed
# Value to serialize.
# #### *$base64Encode* bool
# Wether to base64 encode (or not) the serialized value.
#}
function fn_string_serialize($value,$base64Encode=true)
{
	if($base64Encode) return base64_encode(serialize($value));
	else return serialize($value);
}
	
#docs:core/String
#{
# ### fn_string_unserialize($value,$base64Encode=true)
# Returns the unserialized version of *$value*. It internally uses, but difers from, PHP's *unserialize* in the fact that it will by default also base64 decode the string before unserializing it, to avoid problems while storing the string somewhere else (like a SQL table).
# #### *$value* mixed
# Value to unserialize.
# #### *$base64Encode* bool
# Wether to base64 encode (or not) the serialized value.
#}
function fn_string_unserialize($value,$base64Decode=true)
{
	if($base64Decode) return unserialize(base64_decode($value));
	else return unserialize($value);
}

#docs:core/String
#{
# ### fn_string_slug($str,$lang=false,$replace=array(), $delimiter='-')
# Returns the URL slug version of *$str*.
# #### *$str* string
# String to convert to slug
# #### *$lang* string/bool
# If *$lang* is a *string* (non bool *false*) then it checks if it is contained in a list of special languages (it assumes *$lang* is a language code), and if that's the case runs the conversion through a different regex with different modifiers (/mu)
# #### *$replace* array
# Array of strings to be removed from the slug. Actually these values get replaced with a blank space, which later gets replaced with a "-", effectivelly removing these strings
# #### *$delimiter* string
# Glue string to join the parts of the slug
#}
function fn_string_slug($str,$lang=false,$replace=array(), $delimiter='-')
{
	$useSpecial = false;
	if($lang) if(in_array($lang,Array('cn'))) $useSpecial = true;
	if($useSpecial)
	{
		$re = '/(\\s|\\'.$delimiter.')+/mu';
		$str2 = @trim($str);
		$subst = $delimiter;
		$result = preg_replace($re, $subst, $str2);
		return $result;
	}
	else
	{	
		setlocale(LC_ALL, 'en_US.UTF8');
		//
		if( !empty($replace) ) {
			$str = str_replace((array)$replace, ' ', $str);
		}
		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);	
		$clean=strip_tags($clean);
		$clean = preg_replace('/[^a-zA-Z0-9\/_|+ -]/', '', $clean);
		$clean = strtolower(trim($clean, '-'));
		$clean = preg_replace('/[\/_|+ -]+/', $delimiter, $clean);
	}
	return $clean;
}

function fn_string_template($tpl,$data=Array(),$config=Array())
{
	//__vdump("templ",$tpl,$data);
	$config = array_merge(Array(
		'debug' => false,
		'merge_config' => false,
		'var_get' => 'auto',
		'clean' => false,
	),$config);
	
	//__vdump("fn_string_template",$tpl,"string data",$data);
	
	if($config['merge_config'])
		$data = array_merge(Config::get(),$data);
	
	preg_match_all('/#{(.[^}]*)}/',$tpl,$templateVariables);
	//__vdump("FOUND TEMPLATE VARIABLES",$templateVariables);
	if(count($templateVariables) > 1)
		if(!empty($templateVariables[1])) foreach($templateVariables[1] as $tplVar)
		{
			$value = null;
			//__vdump("---- {$tplVar}");
			switch($config['var_get'])
			{
				case 'dictionary':
					$value = __arrg($tplVar,$data,null);
					break;
					
				case 'static':
					$value = isset($data[$tplVar]) ? $data[$tplVar] : null;
					break;
					
				case 'auto':
					//if($config['debug']) __vdump("entro por acá");
					$expl = explode('.',$tplVar);
					//if($config['debug']) __vdump($expl);
					if(count($expl) > 1) $value = __arrg($tplVar,$data,null);
					else $value = isset($data[$tplVar]) ? $data[$tplVar] : null;
					//if($config['debug']) __vdump($value);
					break;
			}
			
			if(!is_string($value)) $value = $config['clean'] ? '' : '#{' . $tplVar . '}';
			//if($config['debug']) __vdump("--- tiro replace",'#{' . $tplVar . '}',$value);
			//__vdump("VALUE {$tplVar}",$value);
			/*if(trim($value) != '' || $config['clean'])*/ $tpl = str_replace('#{' . 
			$tplVar . '}',
			$value
			,$tpl);
		}
	
	//if($config['debug']) __vdump("matches",$templateVariables);
	
	if($config['clean'])
		$tpl = preg_replace('/#\{.*?\}/', '', $tpl);
	
	return $tpl;
}

?>