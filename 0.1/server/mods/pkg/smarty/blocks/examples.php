<?php

function smarty_block_docs_examples($params,$content,$template,&$repeat)
{	
	$params = array_merge(Array(
		'title' => false,
		'map' => '',
		'class' => [],
		'render_template' => true,
		'format' => false,
	),$params);
	
	if($params['render_template'] === true)
	{
		$params['map'] .= '/examples/';
		if($params['title'] === false) $params['title'] = 'EXAMPLES TITLE';
		if(!is_array($params['class'])) $params['class'] = [$params['class']];
		$params['class'][] = 'examples';
	}
	
	return smarty_block_docs_section($params,$content,$template,$repeat,['docs_examples']);
}

function smarty_block_docs_example($params,$content,$template,&$repeat)
{	
	$params = array_merge(Array(
		'title' => false,
		'map' => '',
		'class' => [],
		'render_template' => true,
		'format' => false,
	),$params);
	
	if($params['render_template'] === true)
	{
		$params['map'] .= '/examples/';
		if($params['title'] === false) $params['title'] = 'EXAMPLE TITLE';
		if(!is_array($params['class'])) $params['class'] = [$params['class']];
		$params['class'][] = 'example';
	}
	
	return smarty_block_docs_section($params,$content,$template,$repeat,['docs_example']);
}

function smarty_block_docs_example_src($params,$content,$template,&$repeat)
{	
	$params = array_merge(Array(
		'map' => false,
		'link' => false,
		'name' => false,
		
		'title' => false,
		'class' => [],
		'render_template' => true,
		'lang' => 'php',
	),$params);
	
	//$params['class'][] = 'solo';
	//
	
	if(!$repeat)
	{
		if($params['render_template'] === true) return builder_render_template_code('docs_example_src',$params,$template,$content);
		else
		{
			//require(__DIR__ . '/tpl/html/docs_example_src.php');
			$params['class'][] = 'example-src';
			$params['class'][] = 'solo';
		}
	}
	
	return smarty_block_code($params,$content,$template,$repeat);
}

?>