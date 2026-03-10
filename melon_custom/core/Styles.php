<?php

/* here goes the license, check  smarty_tag_license on melon3_builder */

function fn_styles_parse($filePath,$options=Array())
{
	M3::reqCore('String');
	
	$debug = false;
	
	$options = array_merge(Array(
		'include_env' => false,
		'type' => false,
		'renderer_config' => false,
	),$options);
	
	$styles = '';

	$type = $options['type'];
	$callableType = is_callable($type);
	
	if($callableType)
		$styles = $type($filePath,$options);
	else
	{
		$stringer = 'string:';
		$stringerLen = strlen($stringer);
		if(fn_string_starts_with($filePath,$stringer))
		{
			$content = substr($filePath,$stringerLen);
			if($type === false) $type = 'css';
		}
		else
		{
			$fileInfo = pathinfo($filePath);
			if(!is_readable($filePath))
			{
				__vdump("FILEPATH: {$filePath}");
				__bpoint('---------------');
				throw new Exception('sys.style_parse.no_file',666);
			}
		
			if($type === false)
				$type = $fileInfo['extension'];
		
			$content = file_get_contents($filePath);
		}
		
		$env = Array();
		$textEnv = '';

		if($options['include_env'])
			list($env,$textEnv) = fn_styles_get_env($type);
		
		switch($type)
		{	
			case 'css':
				$styles = "{$textEnv}{$content}";
				break;
				
			case 'scss':
				M3::reqVendor('scssphp');
				$scss = new ScssPhp\ScssPhp\Compiler();
				//__vdump("MANDO A COMPILAR SCSS","{$textEnv}{$content}");
				$styles = $scss->compile("{$textEnv}{$content}");
				break;
				
			case 'tpl':
				M3::reqCore('RenderEngine');
				M3::reqCore('JSON');
		
				$client = Array(
					'renderer' => Array(
						'template_dir' => Array($fileInfo['dirname'] . '/')
					),
				);
				
				$optionsRendererConfig = Array();
				if($options['renderer_config'] !== false)
				{
					$optionsRendererConfig = $options['renderer_config'];
					unset($options['renderer_config']);
				}
				
				$tplAssigns = Array('options' => $options);
				if($options['include_env'])
					$tplAssigns = array_merge($tplAssigns,Array(
						'env' => $env,
						'css_env' => $textEnv,
						'scss_env' => fn_styles_scss_encode($env)
					));
					
				$rConfig = RenderEngine::getRendererConfig($client,fn_array_merge_recursive(Array(
					'main_tpl' => $fileInfo['basename'],
					'assigns' => $tplAssigns
				),$optionsRendererConfig));
				
				//__vdump("RENDERER CONFIG {$filePath}",$rConfig,$fileInfo);
				
				$renderer = new RenderEngine($client,$rConfig);
				$styles = $renderer->fetch();
				break;
				
			default:
				throw new Exception("fn_styles_parseFile no handler '{$type}'");
				break;
		}
	}
	return $styles;
}






#docs:core/Styles
#{
# ### fn_styles_get_env($env=Array(),$options=Array())
# Returns an array with current environment variables as an associative array, and the string of it's representation of style definitions on the selected format.
# The default environment is
# - '***version'***' => M3::version()
# - ''***base_url'***' => Config::get('base_url')
# - ''***common_assets_url'***' => Config::get('common_assets_url','undef')
# 
# #### *$env* array/string
# Associative array with extra variables to append to the environment.
# When this param is a *string*, then it's used as '*type*' for *$options* (you can still pass down additional options with the *$options* params and therefore override this value as well), useful when you don't need any additional vars or options and you can just do `fn_styles_get_env('scss')` (for instance, to fetch the env on SCSS format)
# #### *$options* array/string
# Associative array of options for the function.
# When this param is a *string*, then *$options* is set as `$options = ['type' => $options]`, useful when you have additional env variables but don't need the options, so you can do something like `fn_styles_get_env($customEnv,'scss')` (to fetch the env on SCSS format, in this case).
# The available options (when this param is an array) are:
# - '***css_vars***': defaults to false, wether to include (or not) the `:root` CSS variables in the text output
# - '***scss_map***': defaults to false, wether to include (or not) the SCSS *$env* map (with the whole environment as members of it) in the text output
# ---------------
# #### Hooking
# ##### `styles:get_env.pre` (&$env,$options)
# Called right before the actual processing/rendering of the text environment a good entry point for mods and plugins to append/change CSS environment variables.
# - ***&$env***: current environment as an associative array
# - ***$options***: options array passed down to *fn_styles_get_env*
# 
# This hook is quite useful for instance in situations where you need to 'system' URLs as variables inside your styles, for instance if you're using themes and you need a var with the URL specific to the theme (instead of the public base URL), you could do something like this
# `fn_hooks_set('styles:get_env.pre',function(&$env,$options){
# 		$env['theme_url'] = Config::get('themes_url') . 'my-theme-url/';
# });`
#}
function fn_styles_get_env($env=Array(),$options=Array())
{
	M3::reqCore('Hooks');
	$debug = false;
	
	M3::reqCore('RenderEngine');
	M3::reqCore('JSON');
	
	if($debug) __vdump("fn_styles_get_env","env",$env,"options",$options);
	
	$typeEnv = false;
	if(is_string($env))
	{
		$typeEnv = $env;
		$env = Array();
	}
	elseif(is_string($options))
	{
		$typeEnv = $options;
		$options = Array();
	}
	
	if($debug) __vdump("fn_styles_get_env","typeEnv",$typeEnv);
	
	if($typeEnv !== false) switch($typeEnv)
	{
		case 'css':
			$options['css_vars'] = true;
			break;
			
		case 'scss':
			$options = array_merge(Array(
				//'scss_vars' => true,
				'scss_map' => true,
				'css_vars' => true,
			),$options);
			break;
			
		case 'tpl':
			$options = array_merge(Array(
				//'scss_vars' => true,
				'scss_map' => false,
				'css_vars' => true,
			),$options);
			break;
	}
	
	$options = array_merge(Array(
		//'scss_vars' => false,
		'css_vars' => false,
		'scss_map' => false,
	),$options);
	
	$env = array_merge(Array(
		'version' => M3::version(),
		'base_url' => Config::get('base_url'),
		'common_assets_url' => Config::get('common_assets_url','undef'),
	),$env);
	
	if($debug) __vdump("fn_styles_get_env pre parse","env",$env,"options",$options);
	
	$_HOOK_ARGS = Array(
		'env' => &$env,
		'options' => $options
	);
	fn_hooks_call('styles:get_env.pre',$_HOOK_ARGS);
	
	$textEnv = fn_styles_parse(dirname(__FILE__) . '/assets/styles/env.scss.tpl',Array(
		'renderer_config' => Array(
			'assigns' => Array(
				'options' => $options,
				'env' => $env,
			)
		)));
		
	//__vdump("text env?",$textEnv);
	
	return Array(
		$env,
		$textEnv
	);
}

#docs:core/Styles
#{
# ### fn_styles_scss_encode($source,$options=Array())
# Takes an associative array and encodes it as SCSS map (returned as string)
# Wrapper/helper function of *fn_print_var* pre-configured for this specific format.
# The default options for *fn_print_var* used are:
# `Array(
# 	'open_object' => '(',
# 	'close_object' => ')',
# 	'open_array' => '(',
# 	'close_array' => ')',
# 	'wrap_key' => '',
# )`
# 
# #### *$source* array
# Array to encode
# #### *$options* array
# Additional custom options for *fn_print_var*
#}
function fn_styles_scss_encode($source,$options=Array())
{
	M3::reqCore('JSON');
	if($options === true) 
	{
		$res = [];
		foreach($source as $key => $val)
			$res[] = "\${$key}: " . fn_print_var($val) . ';';
		return implode("\n",$res);
	}
	if(!is_array($options)) $options = [$options];
	$result = fn_print_var($source,array_merge(Array(
		'open_object' => '(',
		'close_object' => ')',
		'open_array' => '(',
		'close_array' => ')',
		'wrap_key' => '',
	),$options));
	return trim($result);
}

#docs:core/Styles
#{
# ### fn_styles_css_encode($source,$options=Array())
# Takes an associative array and encodes it as CSS props (separated by ';'), returned as string
# Wrapper/helper function of *fn_print_var* pre-configured for this specific format.
# The default options for *fn_print_var* used are:
# `Array(
# 	'open_object' => '',
# 	'close_object' => '',
# 	'open_array' => '',
# 	'close_array' => '',
# 	'wrap_key' => '',
# 	'separator' => ';',
# 	'separate_last' => true,
# )`
# #### *$source* array
# Array to encode
# #### *$options* array/string
# Additional custom options for *fn_print_var*
# This param can have the special value 'vars' (*string*, instead of *array*), which then defaults *$options* to `['key_pre' => '--']`, this way *$source* props are written down as CSS variables instead of CSS props (this feature is internally used by *fn_styles_get_env* to transform env vars into CSS vars).
#}
function fn_styles_css_encode($source,$options=Array())
{
	M3::reqCore('JSON');
	if($options === true) $options = ['key_pre' => '--'];
	if(!is_array($options)) $options = [$options];
	$options = array_merge(Array(
		'open_object' => '',
		'close_object' => '',
		'open_array' => '',
		'close_array' => '',
		'wrap_key' => '',
		'separator' => ';',
		'separate_last' => true,
	),$options);
	
	return fn_print_var($source,$options);
}

?>