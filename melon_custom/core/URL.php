<?php
/* here goes the license, check  smarty_tag_license on melon3_builder */

function fn_url_from_path($path) { return str_replace(' ','%20',str_replace(Config::get('base_path'),Config::get('base_url'),$path)); }


function fn_url_to_path($url) { return str_replace('%20',' ',str_replace(Config::get('base_url'),Config::get('base_path'),$url)); }


function fn_url_get_current($includeParams=true)
{
	$pageURL = 'http';
	 if (@$_SERVER['HTTPS'] == 'on') {$pageURL .= 's';}
	 $pageURL .= '://';
	 if ($_SERVER['SERVER_PORT'] != '80') {
	  $pageURL .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
	 } else {
	  $pageURL .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	 }
	 
	 if(!$includeParams)
		$pageURL = fn_url_remove_params($pageURL);
	 
	 return $pageURL;
}


function fn_url_get_params($url = false)
{
	if($url === false) $_SERVER['REQUEST_URI'];
	$parts = parse_url($url);
	$query = Array();
	@parse_str($parts['query'],$query);
	return $query;
}


function fn_url_remove_params($url)
{
	$start = strpos($url,'?');
	 if($start !== -1)
		 $url = substr($url,0,$start);
	return $url;
}










?>