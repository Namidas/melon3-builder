<?php

function smarty_block_docs_section($params,$content,$template,&$repeat,$nestedOpts=false)
{	
	$params = array_merge(Array(
		'map' => false,
		'link' => false,
		'name' => false,
		
		'class' => [],
		'title' => false,
		'title_class' => [],
		'render_template' => true,
	),$params);
	
	//echo "<pre>";__vdump("DOCS SECTION INIT NESTED PARAMS",$nestedOpts);echo "</pre>";
	if($nestedOpts === false)
		$nestedOpts = Array(
			'docs_section',
			[]
		);
	//echo "<pre>";__vdump("FINAL NESTED PARAMS",$nestedOpts);echo "</pre>";
		
	if(!$repeat)
	{
		smarty_close_index($template);
		$nestedEntry = smarty_nested_current($template);
		//__vdump("DOCS_SECTION NESTED ENTRY",$nestedEntry);
		list($mapLink,$mapLinkOpen,$mapLinkClose) = smarty_auto_map_anchor($template,$params,$nestedEntry);
		//__vdump("MAP LINK",$mapLink);
		smarty_pop_nested($template);
		
		if($params['render_template'] === true) return builder_render_template_code($nestedOpts[0],$params,$template,$content);
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
			require(_BLD_BPATH_ . '/tpl/html/docs_section.php');
			return ob_get_clean();
		}
	}
	else
	{
		smarty_append_index($template,$params,$nestedOpts[0]);
		call_user_func_array('smarty_append_nested',array_merge([&$template],$nestedOpts,[smarty_get_map_data_from_params($params)]));
	}
}

?>