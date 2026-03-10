<?php

define('_BLD_BPATH_',__DIR__);

M3::reqCore('LiteralOutput');
M3::reqCore('JSON');
M3::reqCore('Array');

require_once(__DIR__ . '/smarty/indexs.php');
require_once(__DIR__ . '/smarty/maps.php');
require_once(__DIR__ . '/smarty/nested.php');

require_once(__DIR__ . '/smarty/tags/common.php');
require_once(__DIR__ . '/smarty/tags/license.php');
require_once(__DIR__ . '/smarty/tags/inject.php');

require_once(__DIR__ . '/smarty/blocks/section.php');
require_once(__DIR__ . '/smarty/blocks/common.php');
require_once(__DIR__ . '/smarty/blocks/config.php');
require_once(__DIR__ . '/smarty/blocks/notice.php');
require_once(__DIR__ . '/smarty/blocks/class.php');
require_once(__DIR__ . '/smarty/blocks/list.php');
require_once(__DIR__ . '/smarty/blocks/examples.php');
require_once(__DIR__ . '/smarty/blocks/hooking.php');
require_once(__DIR__ . '/smarty/blocks/params.php');


/*M3::reqCore('Styles');
$props = ['prop-1' => 'value-1','prop-2' => 'value-2'];
__vdump("css",fn_styles_css_encode($props));
__vdump("css",fn_styles_css_encode($props));
__vdump("scss",fn_styles_scss_encode($props));
die();*/



function smarty_block_todo($params,$content,$template,&$repeat)
{	
	//ob_clean();
	//__vdump("TIRO MAGIA","",$template,"","--- FIN MAGIA");
	//die();
	$params = array_merge(Array(
		'title' => false,
		'map' => '',
		'class' => [],
		'render_template' => true,
	),$params);
	
	if($params['render_template'] === true)
	{
		$params['map'] .= '/todo/';
		if($params['title'] === false) $params['title'] = 'TODO';
		if(!is_array($params['class'])) $params['class'] = [$params['class']];
		$params['class'][] = 'todo';
	}
	
	return smarty_block_docs_section($params,$content,$template,$repeat,['todo',[]]);
}







function smarty_content_formater($content,$params)
{
	return $content;
	die("NO TENDRIA QUE ESTAR ACA");
	if(__arrg('format',$params,false) === false) return $content;
	//__vdump("FORMATTER: {$content}");
	$parsedContent = $content;
	switch($params['format'])
	{
		case 'md':
			require_once(__DIR__ . '/formatter_md.php');
			$parsedContent = fn_pkg_get_formatted_md($content,__arrg('format_options',$params,[]));
			break;
	}
	return $parsedContent;
}

function smarty_block_docs($params,$content,$template,&$repeat)
{	
	$params = array_merge(Array(
		'map' => '',
		'title' => false,
		'render_template' => true,
		'type' => null,
		'return' => null,
	),$params);
		
	if($params['render_template'] === false)
	{
	}
	
	if(!$repeat)
	{
		//__vdump("PRE SET DOC",$template->getTemplateVars('_nested'));
		smarty_close_index($template);
		
		$nestedEntry = smarty_nested_current($template);
		list($mapLink,$mapLinkOpen,$mapLinkClose) = smarty_auto_map_anchor($template,array_merge($params,['class' => 'docs']),$nestedEntry);
		smarty_pop_nested($template);
		
		//__vdump("SMARTY POST",$nestedEntry);die();
		if($params['render_template'] === true)
		{
			//__vdump("SARAZO",$content);die();
			smarty_set_doc($params['render_template'] === true ? builder_render_template_code('docs',$params,$template,$content) : $content,array_merge(Array(
				'file' => $template->getTemplateVars('file'),
			),$params));
			return '';
		}
		else
		{
			$titlePre = '';
			if(__arrg('type',$nestedEntry,false) !== false)
			{
				$titlePre = "<span class=\"title-pre entry-type {$nestedEntry['type']}\">{$nestedEntry['type']}</span>";
				//$titleExtra = "<span class=\"title-pre entry-type {$nestedEntry['type']}\">{$nestedEntry['type']}</span>";
			}
			$titleExtra = smarty_title_extra_params($nestedEntry,$params);
			
			ob_start();
			require(__DIR__ . '/tpl/html/docs.php');
			return ob_get_clean();
		}
	}
	else
	{
		smarty_append_nested($template,'docs',['params' => [] , 'type' => $params['type']],smarty_get_map_data_from_params($params));
		smarty_append_index($template,$params,'docs');
	}
}




function smarty_block_code($params,$content,$template,&$repeat)
{	
	$params = array_merge(Array(
		'class' => '',
		'lang' => 'undefined',
		
		'render_template' => true,
	),$params);
		
	if(!$repeat)
	{
		$nestedEntry = smarty_pop_nested($template);
		if($params['render_template'] === true) return builder_render_template_code('code',$params,$template,$content);
		else
		{
			$cssclass = smarty_class_param($params);
			$content_parsed = smarty_content_formater($content,$params);
			ob_start();
			
			require(__DIR__ . '/tpl/html/docs_code.php');
			return ob_get_clean();
		}
	}
	else smarty_append_nested($template,'code',[],smarty_get_map_data_from_params($params));
}

function smarty_block_php($params,$content,$template,&$repeat)
{	
	$params = array_merge(Array(
		'class' => '',
		'lang' => 'php',
		
		'render_template' => true,
	),$params);
	
	return smarty_block_code($params,$content,$template,$repeat);
}







function smarty_auto_map_anchor($template,$params,$entry,$content='')
{
	$debug = false;
	$mapData = smarty_get_map_data_from_params($params);
	if($debug) __vdump("AUTO MAP ANCHOR NORMALIZEEEE",$mapData);
	$link = smarty_normalize_map($template,$mapData,true,true,$entry);
	if($debug) __vdump($link,"<<<< *** AUTO MAP ANCHOR NORMALIZE END!!!!");
	
	
	/*$prep = __arrg('prepend_map',$params,false);
	$link = '';
	if($prep !== false)
		$link =  is_array($prep) ? implode('/',$prep) : $prep;
	if($params['map'] !== false)
		$link .=  is_array($params['map']) ? implode('/',$params['map']) : $params['map'];
	else if($prep !== false && isset($params['name']))
		$link .= "{$params['name']}/";*/
	
	/**********if($prep !== false)
	{
		__vdump("prep?",$prep,$link,$params,"---");
		if($params['name'] === 'type') die();
	}**********/
	if(trim($link) === '' || trim($link) === '/') return '';
	else
	{
		//if(substr($link,-1,1) !== '/') $link .= '/';
		$cssclass = smarty_class_param($params);
		ob_start();
		?><a href="javascript:copyToClipboard(location.origin + location.pathname + '#<?=$link?>')" class="map-anchor <?=$cssclass?>" name="<?=$link?>" title="copy link to clipboard">
			<span class="map-anchor-span">
				<span class="map-anchor-span-link"><?=$link?></span>
			</span>
		<?php
		$anchorOpen = ob_get_clean();
		$anchorClose = '</a>';
		$anchor = "{$anchorOpen}{$content}{$anchorClose}";
		return [
			$anchor,
			$anchorOpen,
			$anchorClose,
		];
	}
}



function smarty_block_docs_source($params,$content,$template,&$repeat)
{	
	if(!$repeat)
	{
		$params = array_merge(Array(
			'render_template' => true,
		),$params);
		
		if($params['render_template'] === true)
		{
			$file = $template->getTemplateVars('file');
			$project = &$GLOBALS[$template->getTemplateVars('project_uid')];
			if(!isset($project['file_content_prepend'][$file])) $project['file_content_prepend'][$file] = [];
			$project['file_content_prepend'][$file][] = $content;
			//__vdump("smarty prepend hola");//die();
			return builder_render_template_code('docs_source',$params,$template,$content);
		}
		else
		{
			require(__DIR__ . '/tpl/html/docs_source.php');
		}
	}
}

function smarty_set_doc($content,$params)
{
	$map = $params['map'];
	if(!isset($GLOBALS['_docs'])) $GLOBALS['_docs'] = [];
	//if(!isset($GLOBALS['_docs'][$map])) $GLOBALS['_docs'][$map] = [];
	$docMap = explode('/',$map);
	//__vdump("DOCMAP",$docMap);die();
	$doc2Set = Array(
		'map' => $map ,
		'file' => $params['file'],
		'content' => $content
	);
	//__vdump("SET DOC",$content,$params,$map,"DOC MAP",$docMap,"DOC2SET",$doc2Set);
	__arrs($docMap,$doc2Set,$GLOBALS['_docs']);
}


function builder_render_template_code($tagName,$params,$template,$content=null)
{
	try
	{
		$pms = array_merge($params,Array(
			'render_template' => false,
		));
		$tag = "/*%{$tagName}";
		foreach($pms as $k => $v)
		{
			//if($k === '_tag') continue;
			
			$quote = '';
			if($v === null) $v = 'null';
			else switch(gettype($v))
			{
				case 'string':
					$quote = '"';
					break;
					
				case 'array':
					$v = fn_var_export($v,true,true,['pretty' => false]);
					break;
					
				case 'boolean':
					$v = $v ? 'true' : 'false';
					break;
					
			}
			//$v = fn_var_export($v,false,true);
			$tag .= " {$k}={$quote}{$v}{$quote}";
		}
		if($content !== null) $tag .= "%*/{$content}/*%/{$tagName}%*/";
		else $tag .= '%*/';	
		return $tag;
	}
	catch(Exception $e) {
		__vdump("builder_render_template_code EXCEPTION",$e);
	}
}

function smarty_class_param($params)
{
	if(!isset($params['class'])) return '';
	return is_array($params['class']) ? implode(' ',$params['class']) : $params['class'];
}









function smarty_modifier_literal_output($string)
{
    return _loutput($string);
}

function smarty_modifier_fn_print_var($data,$opts=[])
{
    return fn_print_var($data,$opts);
}

function smarty_modifier_fn_var_export($source,$return=false,$brackets=false,$extraOptions=[],$debug=false)
{
    return fn_var_export($source,$return,$brackets,$extraOptions,$debug);
}

function smarty_modifier_fn_array_get($selector,$source,$defaultValue=null,$glue=false)
{
    return __arrg($selector,$source,$defaultValue,$glue);
}

function smarty_modifier_array_merge()
{
	return call_user_func_array('array_merge',func_get_args());
}

function smarty_modifier_ucwords()
{
	return call_user_func_array('ucwords',func_get_args());
}

function smarty_modifier_fn_string_template()
{
	return call_user_func_array('fn_string_template',func_get_args());
}


?>