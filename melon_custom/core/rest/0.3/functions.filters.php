<?php

function fn_rest_parse_filters($filters,$definitions=Array())
{
	$resultFilters = Array();
	if(trim($filters) != "")
	{
		$filters = json_decode($filters,true);
		//__vdump("PARSE REST FILTERS",$filters);
		foreach($filters as $key => $value)
		{
			$filterType = "string";
			if(isset($definitions[$key])) $filterType = $definitions[$key];
			$funcName = "fn_rest_parse_filters_{$filterType}";
			$parsedFilter = call_user_func_array($funcName,Array($value,$key));
			if(!empty($parsedFilter)) $resultFilters[$key] = $parsedFilter;
		}
	}
	return $resultFilters;
}



function fn_rest_parse_filters_string($value,$field)
{
	$expl = explode(",",$value);
	$parsedFilter = Array();
	foreach($expl as $ex)
		if(trim($ex) != "")
			$parsedFilter[] = trim($ex);
	return $parsedFilter;
}

function fn_rest_parse_filters_date($value,$field)
{
	//__vdump("PARSE DATE",$value);
	if(!is_array($value)) $value = Array($value);
	$parsedFilter = $value;
	//$expl = explode(",",$value);
	//foreach($expl as $ex)
		//if(trim($ex) != "")
			//$parsedFilter[] = $ex;
	return $parsedFilter;
}

?>