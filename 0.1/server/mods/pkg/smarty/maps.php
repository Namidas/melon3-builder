<?php

function smarty_get_map_data_from_params($params)
{
	return Array(
		'map' => __arrg('map',$params,false),
		'link' => __arrg('link',$params,false),
		'name' => __arrg('name',$params,false),
	);
}

function smarty_normalize_map($template,$mapData,$rel=true,$closeDash=true,$entryUpTo=false)
{
	$debug = false;
	$map = __arrg('map',$mapData,false);
	$link = __arrg('link',$mapData,false);
	if($link === false) $link = __arrg('name',$mapData,false);
	
	if($debug) __vdump("- MAP NORMALIZE","MAP DATA",$mapData,"map",$map,"---","link",$link,"---","rel",$rel,"--");
	if($map !== false)
	{
		if($debug) __vdump("1) mapa NO era falso");
		if(!is_array($map)) $map = get_map_from_string($map);
	}
	else
	{
		if($debug) __vdump("1) mapa era a falso");
		if($link === false)
		{
			if($debug) __vdump("2) link era a falso");
			$map = [];
		}
		else
		{
			if($debug) __vdump("2) link NO era a falso");
			if($rel === true)
			{
				if($debug) __vdump("3) \$rel es TRUE");
				$map = smarty_get_nested_map($template,$entryUpTo);
				if($debug) __vdump("4) asi quedo el map desde *smarty_get_nested_map*()",$map);
			}
			else
			{
				if($debug) __vdump("3) \$rel es...",$rel,"---->>> fin rel");
				if(is_array($rel)) $map = $rel;
				else $map = get_map_from_string($rel);
				if($debug) __vdump("4) asi quedo el map desde \$rel",$map);
			}
		}
		//$map = $link === false ? [] : ($rel === true ? smarty_get_nested_map($template) : (is_array($rel) ? $rel : explode('/',$rel)));
		//__vdump("ACA HAY BARDO 1?",$map);
	}
	if($link !== false)
	{
		if($debug) __vdump("5) deberia appendear un link",$link,"<<-- fin link");
		$map = array_merge($map,is_array($link) ? $link : explode('/',$link));
		//__vdump("ACA HAY BARDO 2?",$map,$link);
	}
	else if($debug) __vdump("5) NO APPENDEO LINK");
	if($debug) __vdump("6) mapa hasta acá",$map);
	if(!empty($map))
	{
		$map = fn_array_filter_trim($map);
		if($debug) __vdump("6.1) mapa con strings vacios filtrado",$map);
		$map = implode('/',$map) . ($closeDash?'/':'');
		if($debug) __vdump("6.2) mapa implotado en string",$map);
	}
	else $map = '';
	if($debug) __vdump("7) NORMALIZE RESULT",$map,"------------- NORMALIZE END");
	return $map;
}

function get_map_from_string($map,$filterLast=true,$clearEmptyStrings=true)
{
	$len = strlen($map);
	if($filterLast)
		if(substr($map,$len-1,1) === '/') $map = substr($map,0,$len-1);
	$map = explode('/',$map);
	return $clearEmptyStrings ? fn_array_filter_trim($map) : $map;
}

?>