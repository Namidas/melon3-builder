<?php

function smarty_block_style($params,$content,$template,&$repeat)
{	
	if(!$repeat)
	{
		$params = array_merge(Array(
			'name' => '',
			'id' => '',
			'save' => false,
			'lang' => 'css',
			'tag' => false,
			'blob' => false,
			
			'backtrace' => !constant('_PRODUCTION_'),
		),$params);
		
		$styles = fn_styles_parse("string:{$content}",['type' => $params['lang']]);
		
		//append backtrace if needed
		if($params['backtrace'])
		{
			$lines = Array(
				"\n",
				"/*block_style_backtrace*/",
				//debug_backtrace(),
				"\n",
				"smarty:",
				"-- SMARTY CURRENT FILE Y SARAZA",
				"\n--backtrace end */\n"
			);
			$styles .= implode("\n",$lines);
		}
		
		if($params['save'] === true)
		{
			FileSystem::mkdir(dirname($params["save"]));
			file_put_contents($params["save"],$styles);
			
			$savePath = $params['save'];
			$saveURL = fn_url_from_path($savePath);
			
			if($params['tag'] !== false)
			{
				//print the tag pointing to the saved file
				return smarty_block_style_get__link_tag($saveURL,$params);
			}
		}
		else
		{
			if($params['tag'] === false) return $styles;
			else
			{
				if($params['blob'] === false)
					return smarty_block_style_get__style_tag($styles,$params);
				else return smarty_block_style_get__link_tag('data:text/css;base64,'.base64_encode($styles),$params);
			}
		}
	}
}

function smarty_block_style_get__link_tag($url,$params)
{
	$tagParams = array_merge(Array(
			'type' => 'text/css',
			'rel' => 'stylesheet',
			'href' => $url
		),
		$params['tag'] === true ? [] : $params['tag']
	);
	$tag = '<link ';
	foreach($tagParams as $k => $v) $tag .= "{$k}=\"{$v}\" ";
	$tag .= ' />';
	return $tag;
}

function smarty_block_style_get__style_tag($content,$params)
{
	$tagParams = array_merge(Array(
		),
		$params['tag'] === true ? [] : $params['tag']
	);
	$tag = '<style ';
	foreach($tagParams as $k => $v) $tag .= "{$k}=\"{$v}\" ";
	$tag .= " >{$content}</style>";
	return $tag;
}

function smarty_block_style__parse($params,$content,&$smarty,&$repeat,$assigns)
{
	$save = $params["save"] != false && Config::get("abm_online") == false;
	
	if(isset($content))
	{
		require_once(Config::get("libs_path").'scssphp/scssphp.class.php');
		$scss = new ScssPhp\ScssPhp\Compiler();
		$compiled = $scss->compile($content);
	}
	else $compiled = "smarty_block_style__parse :: no-content-error";
	
	//aca tendria que preguntar por la config
	if(!$save)
	{
		$template = $smarty->createTemplate("html/style." . (isset($content)?"close":"open") . ".tpl");
		$template->assign($assigns);
		if(isset($content))
		{
			echo $compiled;
		}
		else
		{
			
		}
		echo $template->fetch();
	}
	else
	{
		if($save && isset($content))
		{
			FileSystem::mkdir(dirname($params["save"]));
			file_put_contents($params["save"],$compiled);
		}
	}
}

?>