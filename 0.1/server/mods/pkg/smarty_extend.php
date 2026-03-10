<?php

require_once(__DIR__ . '/functions_smarty.php');

$smarty = $renderer->getRenderer();

$modifiers = Array(
	//'encode' => 'smarty_modifier_encode',
	'loutput' => 'smarty_modifier_literal_output',
	'fn_string_template' => 'smarty_modifier_fn_string_template',
	'fn_print_var' => 'smarty_modifier_fn_print_var',
	'fn_var_export' => 'smarty_modifier_fn_var_export',
	'__arrg' => 'smarty_modifier_fn_array_get',
	
	'array_merge' => 'smarty_modifier_array_merge',
	'ucwords' => 'smarty_modifier_ucwords'
);

$blocks = Array(
	'docs' => 'smarty_block_docs',
	'docs_section' => 'smarty_block_docs_section',
	'docs_hooking' => 'smarty_block_docs_hooking',
	'docs_hook' => 'smarty_block_docs_hook',
	'docs_params' => 'smarty_block_docs_params',
	'docs_param' => 'smarty_block_docs_param',
	'docs_source' => 'smarty_block_docs_source',
	'docs_title' => 'smarty_block_docs_title',
	'a' => 'smarty_block_anchor',
	'i' => 'smarty_block_italic',
	'b' => 'smarty_block_bold',
	'em' => 'smarty_block_emphasis',
	'code' => 'smarty_block_code',
	'php' => 'smarty_block_php',
	'todo' => 'smarty_block_todo',
	'docs_examples' => 'smarty_block_docs_examples',
	'docs_example' => 'smarty_block_docs_example',
	'docs_example_src' => 'smarty_block_docs_example_src',
	
	'ol' => 'smarty_block_docs_ol',
	'ul' => 'smarty_block_docs_ul',
	'li' => 'smarty_block_docs_li',
	
	'docs_class_definition' => 'smarty_block_docs_class_definition',
	'docs_class_member' => 'smarty_block_docs_class_member',
	
	'notice' => 'smarty_block_docs_notice',
	'info' => 'smarty_block_docs_info',
	'warning' => 'smarty_block_docs_warning',
	
	'configs' => 'smarty_block_docs_configs',
	'config' => 'smarty_block_docs_config'
);

$tags = Array(
	'license' => 'smarty_tag_license',
	'docs_map_link_auto' => 'smarty_tag_docs_map_link_auto',
	'br' => 'smarty_tag_docs_line_break',
	
	'inject' => 'smarty_tag_inject',
);

foreach($modifiers as $k => $v)
	$smarty->registerPlugin(Smarty\Smarty::PLUGIN_MODIFIER, $k, $v);
	
foreach($blocks as $k => $v)
	$smarty->registerPlugin(Smarty\Smarty::PLUGIN_BLOCK, $k, $v);
	
foreach($tags as $k => $v)
	$smarty->registerPlugin(Smarty\Smarty::PLUGIN_FUNCTION, $k, $v);



?>