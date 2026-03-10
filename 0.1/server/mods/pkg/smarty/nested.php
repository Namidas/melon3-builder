<?php

function smarty_get_nested_map($template,$upToEntry=false)
{
	$debug = false;
	$nested = $template->getTemplateVars('_nested');
	if($debug)
	{
		__vdump("GET NESTED MAP FROM",$nested,"----------------");
		__vdump("UP TO?",$upToEntry,"---------------------");
	}
	
	$map = [];
	$currentRel = '';
	$found = false;
	foreach($nested as $n)
	{
		if($found === true) continue;
		if($upToEntry !== false)
		{
			if($n['_uid'] === $upToEntry['_uid'])
			{
				$found = true;
				continue;
			}
		}
		
		$mapData = __arrg('_map',$n,false);
		//__vdump("inner map data",$mapData);
		if($mapData !== false)
		{
			if($debug) __vdump("A) vuelta de FOREACH");
			$hasSomething = false;
			$check = [
				'map',
				'link',
				'name'
			];
			foreach($check as $c) if(__arrg($c,$mapData,false) !== false) $hasSomething = true;
			if(!$hasSomething) continue;
			if($debug) __vdump("MANDO A NORMALIZAR CON REL: {$currentRel}<<<<<",$mapData);
			$norm = smarty_normalize_map($template,$mapData,$currentRel,true,$upToEntry);
			if($debug) __vdump("- TEMP currentRel: {$currentRel}");
			if($debug) __vdump("- TEMP NORM: {$norm}");
			$map = get_map_from_string($norm);
			$currentRel = $norm;
			if($debug) __vdump("B) resultado FOREACH",$map,$currentRel);
		}
	}
	if($debug) __vdump("C) ******** RESULT NESTED MAP",$map,"******************** ******************** \n******************** \n******************** \n******************** FIN");
	return $map;
	
	
	
	
	if(!empty($nested))
	{
		$last = array_pop($nested);
		if($ignoreCurrent === true)
			$last = array_pop($nested);
		
		return $last['map'];
	}
	return '';
	
	$nestedEntry = smarty_pop_nested($template);
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
}

function smarty_append_nested(&$template,$tag,$extra=[],$map=[])
{
	$entry = array_merge(Array(
		'_tag' => $tag,
		'_uid' => uniqid('nested_'),
		'_map' => $map
	),$extra);
	$template->append('_nested',$entry);
	return $entry;
}

function smarty_update_nested(&$template,$uid,$newValue=null)
{
	if(is_array($uid))
	{
		$newValue = $uid;
		$uid = $uid['_uid'];
	}
	$nested = $template->getTemplateVars('_nested');
	foreach($nested as &$n)
		if($n['_uid'] === $uid)
			$n = $newValue;
	$template->assign('_nested',$nested);
}

function smarty_nested_current($template)
{
	$nested =$template->getTemplateVars('_nested');
	return empty($nested) ? false : $nested[count($nested)-1];
}

function smarty_pop_nested($template)
{
	$nested = $template->getTemplateVars('_nested');
	//__vdump("POPEO START",$nested);
	$entry = array_pop($nested);
	//__vdump("POPEO FIN",$nested);
	$template->assign('_nested',$nested);
	return $entry;
}

function smarty_get_nested_lvl($template)
{
	return count($template->getTemplateVars('_nested'));
}

?>