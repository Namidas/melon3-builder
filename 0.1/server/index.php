<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once(__DIR__ . '/config.php');

$cores = Array(
	'Config',
	'Mods',
	'Hooks',
	'rest'
);
require_once("{$__CONFIG['melon_path']}loader.php");

fn_rest_init();
fn_rest_exec_current();

?>