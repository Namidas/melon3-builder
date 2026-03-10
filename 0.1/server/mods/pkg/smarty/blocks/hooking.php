<?php

function smarty_block_docs_hooking($params,$content,$template,&$repeat)
{	
	$params = array_merge(Array(
		'title' => false,
		'map' => '',
		'class' => [],
		'render_template' => true,
	),$params);
	
	if($params['render_template'] === true)
	{
		$params['map'] .= '/hooking/';
		if($params['title'] === false) $params['title'] = 'Hooking';
		if(!is_array($params['class'])) $params['class'] = [$params['class']];
		$params['class'][] = 'docs-hooking';
	}
	
	return smarty_block_docs_section($params,$content,$template,$repeat,['docs_hooking']);
}

function smarty_block_docs_hook($params,$content,$template,&$repeat)
{	
	$params = array_merge(Array(
		'type' => 'php',
		'name' => '',
		'class' => [],
		'render_template' => true,
	),$params);
	
	if($params['render_template'] === true)
	{
		$params['title'] = $params['name'];
		//$params['map'] = "FOOOOOO/{$params['name']}/";
		if(!is_array($params['class'])) $params['class'] = [$params['class']];
		$params['class'][] = 'docs-hook';
	}
	
	$nestedOpts = Array(
		'docs_hook',
		Array(
			'type' => "{$params['type']}-hook",
			'params' => [],
		)
	);
	
	return smarty_block_docs_section($params,$content,$template,$repeat,$nestedOpts);
		
	/*if(!$repeat)
	{
		smarty_close_index($template);
		$nestedEntry = smarty_pop_nested($template);
		if($params['render_template'] === true) return builder_render_template_code('docs_section',$params,$template,$content);
		else
		{
			$lvl = smarty_get_nested_lvl($template) + 1;
			if($lvl > 6) $lvl = 6;
			
			$cssclass = smarty_class_param($params);
			$content_parsed = smarty_content_formater($content,$params);
			ob_start();
			require(__DIR__ . '/tpl/html/docs_hook.php');
			return ob_get_clean();
		}
	}
	else
	{
		smarty_append_index($template,$params,'docs_hook');
		smarty_append_nested($template,'docs_hook',['params' => [] , 'type' => 'function']);
	}*/
}

?>