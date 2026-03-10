<?php

function smarty_block_docs_list($params,$content,$template,&$repeat)
{	
	$params = array_merge(Array(
		'type' => null,
		'class' => [],
		'render_template' => true,
	),$params);
	
	$type = fn_array_unset_selector('type',$params);
		
	if(!$repeat)
	{
		$nestedEntry = smarty_pop_nested($template);
		if($params['render_template'] === true) return builder_render_template_code($type,$params,$template,$content);
		else
		{
			$cssclass = smarty_class_param($params);
			$content_parsed = smarty_content_formater($content,$params);
			ob_start();
			require(_BLD_BPATH_ . '/tpl/html/docs_list.php');
			return ob_get_clean();
		}
	}
	else smarty_append_nested($template,$type,[],smarty_get_map_data_from_params($params));
}

function smarty_block_docs_ol($params,$content,$template,&$repeat)
{
	return smarty_block_docs_list(array_merge($params,['type' => 'ol']),$content,$template,$repeat);
}

function smarty_block_docs_ul($params,$content,$template,&$repeat)
{
	return smarty_block_docs_list(array_merge($params,['type' => 'ul']),$content,$template,$repeat);
}

function smarty_block_docs_li($params,$content,$template,&$repeat)
{
	$undefValue = uniqid('**foo_**');
	$params = array_merge(Array(
		'format' => false,
		'class' => [],
		
		'render_template' => true,
		
		//'prepend_map' => false,
		'map' => false,
	),$params);
	
	if(!$repeat)
	{
		$nestedEntry = smarty_nested_current($template);
		list($mapLink,$mapLinkOpen,$mapLinkClose) = smarty_auto_map_anchor($template,$params,$nestedEntry);
		smarty_pop_nested($template);
		if($params['render_template'] === true) return builder_render_template_code('li',$params,$template,$content);
		else
		{
			$content_parsed = smarty_content_formater($content,$params);
			$cssclass = smarty_class_param($params);
			
			ob_start();
			require(_BLD_BPATH_ . '/tpl/html/docs_list_item.php');
			return ob_get_clean();
		}
	}
	else smarty_append_nested($template,'li',[],smarty_get_map_data_from_params($params));
}

?>