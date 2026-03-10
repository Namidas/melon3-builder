<?php

function smarty_modifier_encode($source,$type='json',$typeArgs=[])
{
	/*$args = func_get_args();
	$source = array_shift($args);
	$type = $source,$handler=false,$typeArgs*/
	
	if(is_callable($type))
		return call_user_func_array($type,array_merge([$source],$typeArgs));
	
	
	$coreEncoders = Array(
		'css' => 'fn_styles_css_encode',
		'scss' => 'fn_styles_scss_encode',
		'json' => 'fn_json_encode',
		'var' => 'fn_var_export',
		'print' => 'fn_print_var',
	);
	
	$func = false;
	if(in_array($type,array_keys($coreEncoders)))
		$func = $coreEncoders[$type];
	elseif(function_exists($type))
		$func = $type;
	else throw new Exception("RenderEngine extension 'encode', handler '{$type}' not found");
	
	return call_user_func_array($func,array_merge([$source],$typeArgs));
}


/*function smarty_block_hook($params, $content, &$smarty,&$repeat)
{
	if(!isset($params['name'])) die("__smarty hook with no name");
    $global_smarty = $smarty->smarty ? $smarty->smarty : $smarty;

	$mods = Mods::get();
	$mods = $mods['mods'];
	$hookName = str_replace(':','_',$params['name']);
	
	$pre = Array();
	$post = Array();
	$override = Array();
	
	foreach($mods as $modName => $_MANIFEST)
	{
		foreach($global_smarty->template_dir as $tplDir)
		{
			$paths = Array("{$tplDir}{$modName}/hooks/");
			$_HOOK_ARGS = Array(
				'modName' => $modName,
				'paths' => &$paths,
				'tplDir' => $tplDir,
				'hookName' => $hookName,
			);
			fn_hooks_call('smarty:hook:get_paths',$_HOOK_ARGS);
			foreach($paths as $path)
			{
				//__vdump("PROBANDO PATH: {$path} // {$hookName}",is_readable("{$path}{$hookName}.pre.tpl"));
				if(is_readable("{$path}{$hookName}.pre.tpl"))
					$pre[] = "{$path}{$hookName}.pre.tpl";
				if(is_readable("{$path}{$hookName}.post.tpl"))
					$post[] = "{$path}{$hookName}.post.tpl";
				if(is_readable("{$path}{$hookName}.override.tpl"))
					$override[] = "{$path}{$hookName}.override.tpl";
				//__vdump("---");
			}
		}
	}
	
	//it's the opening sequence
	if($repeat)
	{
		if(!empty($override))
		{
			foreach($override as $tpl)
				$content = $smarty->fetch($tpl);
		}
		else if(!empty($pre)) foreach($pre as $tpl)
			$content = $smarty->fetch($tpl) . $content;
	}
	else
	{
		if(empty($override) && !empty($post))
			foreach($post as $tpl)
				$content = $content . $smarty->fetch($tpl);
	}
	
	return !$repeat && !empty($override) ? '' : $content;
}*/

?>