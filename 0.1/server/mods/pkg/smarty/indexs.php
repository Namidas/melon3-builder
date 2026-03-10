<?php

function smarty_append_index($template,$params,$tag)
{
	//echo "<pre>";__vdump("APPEND INDEX: {$tag} // {$params['map']}");echo "</pre>";
	$node = smarty_nested_current($template);
	$nodeMap = smarty_normalize_map($template,$params,true,true,$node);
	$title = __arrg('title',$params,false);
	if($title === false)
	{
		$expl = explode('/',$params['map']);
		$title = trim(array_pop($expl));
		while($title === '' && !empty($expl))
			$title = trim(array_pop($expl));
	}
	$pointMap = implode('.',fn_array_filter_trim(explode('/',$nodeMap)));
	$GLOBALS['_index'][$pointMap] = ['_title' => $title];
	return;
	
	
	/*echo "<pre>";__vdump("APPEND INDEX: {$params['map']} / {$tag}");echo "</pre>";
	//$current = $template->getTemplateVars('_index_current');
	$current = &$GLOBALS['_index_current'];
	$title = __arrg('title',$params,false);
	if($title === false)
	{
		$expl = explode('/',$params['map']);
		$title = trim(array_pop($expl));
		while($title === '' && !empty($expl))
			$title = trim(array_pop($expl));
	}
	$entry = Array(
		'map' => $params['map'],
		'title' => $title,
		'items' => []
	);
	if($current === false) $current = $entry;
	else $current['items'][$entry['map']] = $entry;
	//$template->assign('_index_current',$current);
	echo "<pre>";__vdump("ENTRY",$entry);__vdump("CURRENT",$current);echo "</pre>";*/
}

function smarty_close_index($template)
{
	//$index = $template->getTemplateVars('_index');
	//$current = $template->getTemplateVars('_index_current');
	/*$index = &$GLOBALS['_index'];
	$current = &$GLOBALS['_index_current'];
	echo "<pre>";__vdump("CLOSE INDEX",$current);echo "</pre>";
	if($current !== false)
	{
		$index[$current['map']] = $current;
		//$template->assign('_index',$index);
		//$template->assign('_index_current',false);
		$current = false;
	}*/
}

?>