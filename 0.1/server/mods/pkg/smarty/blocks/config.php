<?php

function smarty_block_docs_configs($params,$content,$template,&$repeat)
{	
	$params = array_merge(Array(
		'class' => [],
		'render_template' => true,
	),$params);
		
	if(!$repeat)
	{
		$nestedEntry = smarty_pop_nested($template);
		if($params['render_template'] === true) return builder_render_template_code('configs',$params,$template,$content);
		else
		{
			$cssclass = smarty_class_param($params);
			$content_parsed = smarty_content_formater($content,$params);
			ob_start();
			require(_BLD_BPATH_ . '/tpl/html/docs_configs.php');
			return ob_get_clean();
		}
	}
	else smarty_append_nested($template,'configs',[],smarty_get_map_data_from_params($params));
}

function smarty_block_docs_config($params,$content,$template,&$repeat)
{
	$undefValue = uniqid('**foo_**');
	$params = array_merge(Array(
		'format' => false,
		'class' => [],
		'type' => [],
		
		'render_template' => true,
		
		'deprecated' => false,
		
		//'prepend_map' => false,
		'map' => false,
	),$params);
	
	if(!$repeat)
	{
		$nestedEntry = smarty_nested_current($template);
		list($mapLink,$mapLinkOpen,$mapLinkClose) = smarty_auto_map_anchor($template,$params,$nestedEntry);
		smarty_pop_nested($template);
		if($params['render_template'] === true) return builder_render_template_code('config',$params,$template,$content);
		else
		{
			$content_parsed = smarty_content_formater($content,$params);
			$cssclass = smarty_class_param($params);
			
			if(!is_array($params['type'])) $params['type'] = [$params['type']];
			
			ob_start();
			require(_BLD_BPATH_ . '/tpl/html/docs_config.php');
			return ob_get_clean();
		}
	}
	else smarty_append_nested($template,'config',[],smarty_get_map_data_from_params($params));
}

?>