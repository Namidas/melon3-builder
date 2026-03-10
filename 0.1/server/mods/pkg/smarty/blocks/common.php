<?php

function smarty_block_docs_title($params,$content,$template,&$repeat)
{	
	$params = array_merge(Array(
		'format' => false,
		'lvl' => 0, //auto
		'nest_lvl' => true,
		'map' => false,
		'link' => false,
		'render_template' => true,
		'class' => [],
	),$params);
	
	if(!$repeat)
	{
		if($params['render_template'] === true) return builder_render_template_code('docs_title',$params,$template,$content);
		else
		{
			$lvl = $params['lvl'];
			if($lvl === 'auto')
				$lvl = smarty_get_nested_lvl($template) + 1;
			if($lvl > 6) $lvl = 6;
			if($lvl < 1) $lvl = 1;
			
			$cssclass = smarty_class_param($params);
			
			if(!is_array($params['class'])) $params['class'] = [$params['class']];
			$content_parsed = smarty_content_formater($content,$params);
			ob_start();
			require(_BLD_BPATH_ . '/tpl/html/docs_title.php');
			return ob_get_clean();
		}
	}
}


function smarty_title_extra_params($nestedEntry,$params)
{
	$returns = __arrg('return',$params);
	if(is_array($returns)) $returns = implode('/',$returns);
	
	$titleExtra = [];
	if(!empty($nestedEntry['params']))
	{
		foreach($nestedEntry['params'] as $funcParam)
		{
			$temp = [
				__arrg('deprecated',$funcParam,false) ? '<span class="deprecated" title="deprecated">' : '',
				'<span class="param-types">',
				is_array($funcParam['type']) ? implode('/',$funcParam['type']) : $funcParam['type'],
				'</span>',
				"<span class=\"param-name\">{$funcParam['name']}</span>",
				isset($funcParam['default']) ? '<span class="param-default"> = ' . fn_print_var($funcParam['default']) . '</span>' : '',
				__arrg('deprecated',$funcParam,false) ? '</span>' : '',
			];
			$titleExtra[] = implode('',$temp);
		}
	}
	if(empty($titleExtra)) $titleExtra = '';
	else $titleExtra = "<span class=\"title-extra {$params['type']}\">(" . implode(', ',$titleExtra) . ')</span>';
	
	if($returns !== null) $titleExtra .= "<span class=\"function-return\">: <span class=\"type\">{$returns}</span></span>";
	return $titleExtra;
}

function smarty_block_anchor($params,$content,$template,&$repeat)
{	
	$debug = false;
	if(!$repeat)
	{
		$params = array_merge(Array(
			'format' => false,
			'map' => false,
			'link' => false,
			'render_template' => true,
			'href' => false,
			'target' => false,
			'title' => true,
			'empty_anchor' => true,
		),$params);
		
		if($params['render_template'] === true) return builder_render_template_code('a',$params,$template,$content);
		else
		{
			$href = $params['href'];
			//$isSelf = false;
			$map = smarty_normalize_map($template,$params,true,true);
			$title = $params['title'];
			$target = $params['target'];
			
			$cssclass = '';
			
			if($debug) __vdump("MAP NORMALIZADO?",$map);
			if($href === false)
			{
				$href = "#{$map}";
				if($title === true) $title = 'click to view in docs';
			}
			else
			{
				if($target === false) $target = '_blank';
				if($title === true) $title = "click to visit {$href}";
			}

			$content_parsed = smarty_content_formater($content,$params);
			
			if(strpos($content_parsed,'docs-code') !== false)
				$cssclass .= ' docs-code-anchor';
			
			if($params['empty_anchor'] === true && trim($content_parsed) === '')
				$cssclass .= ' empty-anchor';
			
			require(_BLD_BPATH_ . '/tpl/html/docs_anchor.php');
		}
	}
}

function smarty_block_italic($params,$content,$template,&$repeat)
{	
	if(!$repeat)
	{
		$params = array_merge(Array(
			'format' => false,
			'render_template' => true,
		),$params);
		
		if($params['render_template'] === true) return builder_render_template_code('i',$params,$template,$content);
		else
		{
			$content_parsed = smarty_content_formater($content,$params);
			require(_BLD_BPATH_ . '/tpl/html/docs_italic.php');
		}
	}
}

function smarty_block_bold($params,$content,$template,&$repeat)
{	
	if(!$repeat)
	{
		$params = array_merge(Array(
			'format' => false,
			'render_template' => true,
		),$params);
		
		if($params['render_template'] === true) return builder_render_template_code('b',$params,$template,$content);
		else
		{
			$content_parsed = smarty_content_formater($content,$params);
			require(_BLD_BPATH_ . '/tpl/html/docs_bold.php');
		}
	}
}

function smarty_block_emphasis($params,$content,$template,&$repeat)
{	
	if(!$repeat)
	{
		$params = array_merge(Array(
			'render_template' => true,
		),$params);
		
		if($params['render_template'] === true) return builder_render_template_code('em',$params,$template,$content);
		else
		{
			require(_BLD_BPATH_ . '/tpl/html/docs_emphasis.php');
		}
	}
}

?>