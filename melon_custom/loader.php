<?php

if(!isset($cores)) $cores = [];
require_once(dirname(__FILE__) . '/manifest.php');
if(!empty($cores))
{
	require_once(__DIR__ . '/core/API.php');
	M3::reqCore($cores);
}

?>