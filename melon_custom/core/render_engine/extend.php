<?php

$modifiers = Array(
	'encode' => 'smarty_modifier_encode',
);

$blocks = Array(
	'style' => 'smarty_block_style'
);

foreach($modifiers as $k => $v)
	$this->smarty->registerPlugin(Smarty\Smarty::PLUGIN_MODIFIER, $k, $v);
	
foreach($blocks as $k => $v)
	$this->smarty->registerPlugin(Smarty\Smarty::PLUGIN_BLOCK, $k, $v);

?>