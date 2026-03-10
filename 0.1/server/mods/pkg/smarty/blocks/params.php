<?php

function smarty_block_docs_params($params,$content,$template,&$repeat)
{	
	$params = array_merge(Array(
		'type' => null,
		'class' => [],
		'render_template' => true,
	),$params);
		
	if(!$repeat)
	{
		$nestedEntry = smarty_pop_nested($template);
		if($params['render_template'] === true) return builder_render_template_code('docs_params',$params,$template,$content);
		else
		{
			$cssclass = smarty_class_param($params);
			$content_parsed = smarty_content_formater($content,$params);
			ob_start();
			require(_BLD_BPATH_ . '/tpl/html/docs_params.php');
			return ob_get_clean();
		}
	}
	else smarty_append_nested($template,'docs_params',['type' => $params['type']],smarty_get_map_data_from_params($params));
}

function smarty_block_docs_param($params,$content,$template,&$repeat)
{
	$undefValue = uniqid('**foo_**');
	$params = array_merge(Array(
		'format' => false,
		'name' => '',
		'type' => [],
		'default' => $undefValue,
		'required' => false,
		'deprecated' => false,
		'deprecated_v' => false,
		'class' => [],
		
		'render_template' => true,
		
		//'prepend_map' => false,
		'map' => false,
	),$params);
	
	if(!$repeat)
	{
		//__vdump("-- current nested",$template->getTemplateVars('_nested'));
		$nestedEntry = smarty_nested_current($template);
		list($mapLink,$mapLinkOpen,$mapLinkClose) = smarty_auto_map_anchor($template,$params,$nestedEntry);
		//__vdump("doc_param mapLink",$mapLink);
		smarty_pop_nested($template);
		if($params['default'] === $undefValue) unset($params['default']);
		if(!is_array($params['type'])) $params['type'] = [$params['type']];
		
		if($params['render_template'] === true) return builder_render_template_code('docs_param',$params,$template,$content);
		else
		{
			$content_parsed = smarty_content_formater($content,$params);
			$cssclass = smarty_class_param($params);
			
			if($params['deprecated'] === true) $cssclass .= ' is-deprecated';
			if($params['required'] === true) $cssclass .= ' is-required';
			
			$nested = $template->getTemplateVars('_nested');
			//echo "<pre>";__vdump("VOY A BUSCAR NESTED",$nested,"---------------------","----------------------");echo "</pre>";
			$paramsNode = array_pop($nested);
			//__vdump("buscando parent 1",$paramsNode['_tag']);
			while($paramsNode['_tag'] !== 'docs_params' && !empty($nested))
			{
				$paramsNode = array_pop($nested);
				//__vdump("buscando parent 2",$paramsNode['_tag']);
			}

			if($paramsNode['_tag'] === 'docs_params')
			{
				//echo "<pre>";__vdump("buscando parent 3 ({$paramsNode['type']})",$paramsNode,$nested);echo "</pre>";
				if(isset($paramsNode['type'])) if($paramsNode['type'] !== null)
				{
					$prnt = array_pop($nested);
					//echo "<pre>";__vdump("buscando parent 4",$prnt);echo "</pre>";
					while(__arrg('type',$prnt,'') !== $paramsNode['type'] && !empty($nested))
					{
						$prnt = array_pop($nested);
						//echo "<pre>";__vdump("buscando parent 5",$prnt);echo "</pre>";
					}
					if(isset($prnt['params']))
					{
						$prnt['params'][] = $params;
						//echo "<pre>";__vdump("appendeo PARAM",$params,"en",$prnt);echo "</pre>";
						smarty_update_nested($template,$prnt);
					}
				}
			}
			
			ob_start();
			require(_BLD_BPATH_ . '/tpl/html/docs_param.php');
			return ob_get_clean();
		}
	}
	else smarty_append_nested($template,'docs_param',[],smarty_get_map_data_from_params($params));
}

?>