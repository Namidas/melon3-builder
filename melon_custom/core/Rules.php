<?php

//$GLOBALS['__rule_all__'] = '/\*+((([\/]$)|$))/';
//$GLOBALS['__rule_all__'] = '*';
$GLOBALS['__rule_all_regex__'] = '/.+/';


/*
rules are set as $key => $val, where $key is a [RULE] and $val is a list of [EXCEPTIONS], which [RULE]s that will deny the original [RULE]
each can be either a string or an array
*/
function fn_rules_check($check,$rules,$opts=Array())
{
	$debug = false;
	
	//__vdump("fn_rules_check","check",$check,"-----","rules => exceptions",$rules);
	
	if($debug) __vdump("fn_rules_check",$check,$rules,"opts",$opts);
	
	$result = false;
	$data = Array(
		'rules' => Array(),
		'exceptions' => Array(),
	);
	
	if($debug) __vdump("RULES",$rules);
	
	if(is_string($check)) $check = [$check];
	
	foreach($rules as $rule => $exceptions)
	{
		if(is_string($rule)) $rule = [$rule];
		if(is_string($exceptions)) $exceptions = [$exceptions];
		
		if($debug) __vdump("- empiezo a chequear",$ruleKey,$args,"exceptions",$ruleException,"--",$action,"---");
		
		$tempRes = fn_rules_eval($check,$rule,$opts);
		if($tempRes !== false)
		{
			if($debug) __vdump("TRUE SOBRE RULE KEY");
			$data['rules'] = $tempRes;
			$result = true;
		}
		else if($debug) __vdump("*** todo bien con esta regla");
		
		if($debug) __vdump("pre-res de reglas:" . ($deny? "true":"false"));
		
		if($debug) __vdump("CHECKEE TODAS LAS REGLAS Y EL RESULTADO ES DENY",$deny);
		
		if($result)
		{
			if($debug) if(!empty($exceptions)) __vdump("CHEQUEO EXCEPTIONS",$ruleException);
			$tempRes = fn_rules_eval($check,$exceptions,$opts);
			if($tempRes !== false)
			{
				if($debug) __vdump("TRUE SOBRE RULE EXCEPTIONS");
				$data['exceptions'] = $tempRes;
				$result = false;
			}
			
			if($debug) __vdump("res despues de las excepciones:" . ($deny? "true":"false")."\n\n\n");
		}
	}
	if($debug) __vdump("FIN",$result,$data);
	return Array($result,$data);
}

function fn_rules_eval($check,$rules,$opts=Array())
{
	$opts = fn_rules_eval_get_opts($opts);
	
	$result = Array();
	
	$matchedOne = false;
	if(!empty($rules))
		foreach($rules as $rule)
		{
			if($matchedOne && $opts['match_first']) continue;
			foreach($check as $subject)
			{
				if($matchedOne && $opts['match_first']) continue;
				//__vdump("RULE",$rule,"SUBJECT",$subject);
				$match_func = 'preg_match';
				if($opts['match_all'] === true) $match_func .= '_all';
				$matches = Array();
				$match_func_args = Array(
					'/' . str_replace('/','\/',$rule) . '/',
					$subject,
					&$matches,
					$opts['flags'],
					$opts['offset']
				);
				$q = call_user_func_array($match_func,$match_func_args);
				//__vdump("regex result",$q);
				if($q)
				{
					$result[] = Array(
						'rule' => $rule,
						'subject' => $subject,
						'matches' => $matches,
						'amnt' => $q,
					);
					$matchedOne = true;
				}
			}
		}
	return empty($result) ? false : $result;
}

function fn_rules_add(&$target,$rule,$exceptions=Array())
{
	if(!isset($target[$rule]))
		$target[$rule] = Array();
	$target[$rule] = array_merge($target[$rule],$exceptions);
}

function fn_rules_add_alt(&$target,$rule,$alt,$exceptions)
{
	if($alt === false) return fn_rules_add($target,$rule,$exceptions);
	if(!isset($target[$rule])) return fn_rules_add($target,$alt,$exceptions);
}

function fn_rules_add_global(&$target,$exceptions=Array(),$alt=false)
{
	$rule = $GLOBALS['__rule_all_regex__'];
	return fn_rules_add_alt($target,$rule,$alt,$exceptions);
}

function fn_rules_construct_subjects(/*$val1,$separator1,$val2,$separator2,.....*/)
{
	$debug = false;
	
	$res = [];
	$args = func_get_args();
	
	if($debug) __vdump("CONSTRUCT CHECKS",$args);
	
	fn_rules_construct_subjects_loop($args,$checks,$res);
	if($debug) __vdump("construct checks END",$res);
	
	return $res;
}

function fn_rules_construct_subjects_loop(&$args,&$checks,&$res,$baseCheck='')
{
	$argCount = empty($args) ? 0 : count($args);
	if($argCount < 1) return $res;
	
	$value = array_shift($args);
	$separator = empty($args) ? '' : array_shift($args);
	$emptySeparator = $separator === '';
	$lastArg = empty($args);
	
	if(!is_array($value)) $value = [$value];	
	foreach($value as $val)
	{
		$currCheck = "{$baseCheck}{$val}";
		$res[] = $currCheck;
		if(!$emptySeparator)
		{
			$currCheck = "{$baseCheck}{$val}{$separator}";
			$res[] = $currCheck;
		}
		if(!$lastArg) fn_rules_construct_subjects_loop($args,$checks,$res,$currCheck);
	}
}

function fn_rules_eval_get_opts($opts=Array())
{
	return array_merge(Array(
		'flags' => 0,
		'offset'=> 0,
		
		'match_all' => false,
		'match_first' => true,
	),Config::getD('core.Rules.eval_opts',[]),$opts);
}

?>