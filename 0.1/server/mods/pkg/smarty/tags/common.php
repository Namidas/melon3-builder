<?php

function smarty_tag_docs_map_link_auto($params,$template)
{	
	$params = array_merge(Array(
		'map' => false,
		'link' => '',
		'relmap' => false,
		
		'content' => false,
		'render_template' => true,
	),$params);
	
	if($params['render_template'] === true) return builder_render_template_code('docs_map_link_auto',$params,$template);
	else
	{
		$map = trim($params['map']);
		$link = ($map !== '' ? "{$map}/" : '') . $params['link'];
		require(_BLD_BPATH_ . '/tpl/html/docs_map_link_auto.php');
	}
}

function smarty_tag_docs_line_break($params,$template)
{	
	$params = array_merge(Array(
		'render_template' => true,
	),$params);
	
	if($params['render_template'] === true) return builder_render_template_code('br',$params,$template);
	else require(_BLD_BPATH_ . '/tpl/html/docs_line_break.php');
}

?>