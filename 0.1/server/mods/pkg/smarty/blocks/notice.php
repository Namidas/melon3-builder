<?php

function smarty_block_docs_notice($params,$content,$template,&$repeat,$nestedOpts=false)
{	
	$params = array_merge(Array(
		'type' => '',
		'class' => [],
		'title' => false,
		'title_class' => [],
		'render_template' => true,
	),$params);
	
	if($nestedOpts === false)
		$nestedOpts = Array(
			'docs_notice',
			[]
		);
		
	if(!$repeat)
	{
		//smarty_close_index($template);
		$nestedEntry = smarty_nested_current($template);
		list($mapLink,$mapLinkOpen,$mapLinkClose) = smarty_auto_map_anchor($template,$params,$nestedEntry);
		smarty_pop_nested($template);
		
		if($params['render_template'] === true) return builder_render_template_code('notice',$params,$template,$content);
		else
		{
			$titlePre = '';
			$titleExtra = smarty_title_extra_params($nestedEntry,$params);
			if(__arrg('type',$nestedEntry,false) !== false)
			{
				$titlePre = "<span class=\"title-pre entry-type {$nestedEntry['type']}\">{$nestedEntry['type']}</span>";
				//$titleExtra = "<span class=\"title-pre entry-type {$nestedEntry['type']}\">{$nestedEntry['type']}</span>";
			}
			
			$title = '';
			if($params['title'] !== false) $title = $params['title'];
			$title = "{$titlePre}{$title}{$titleExtra}";
			if(trim($title) === '') $title = false;
			//__vdump("SECTION TITLE","pre",$titlePre,"extra",$titleExtra,"title",$params['title'],"FINAL",$title);
			
			$lvl = smarty_get_nested_lvl($template) + 1;
			if($lvl > 6) $lvl = 6;
			
			$cssclass = smarty_class_param($params);
			$content_parsed = smarty_content_formater($content,$params);
			ob_start();
			require(_BLD_BPATH_ . '/tpl/html/docs_notice.php');
			return ob_get_clean();
		}
	}
	else
	{
		//smarty_append_index($template,$params,$nestedOpts[0]);
		call_user_func_array('smarty_append_nested',array_merge([&$template],$nestedOpts,[smarty_get_map_data_from_params($params)]));
	}
}

function smarty_block_docs_info($params,$content,$template,&$repeat)
{
	return smarty_block_docs_notice(array_merge($params,['type' => 'info']),$content,$template,$repeat);
}

function smarty_block_docs_warning($params,$content,$template,&$repeat)
{
	return smarty_block_docs_notice(array_merge($params,['type' => 'warning']),$content,$template,$repeat);
}

?>