<?php

$files = Array(
	'block.style',
	'modifier.encode',
);

foreach($files as $f)
	require_once(__DIR__ . "/{$f}.php");

?>