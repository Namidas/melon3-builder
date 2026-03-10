<section data-lvl="<?=$lvl?>" class="docs-section docs-hook <?=$cssclass?> lvl<?=$lvl?>">
	<?php if($params['title'] !== false) echo smarty_block_docs_title(array_merge($params,[
		'render_template' => false,
		'lvl' => 'auto',
		//'map' => $,
		//'class' => 'main'
	]),$params['title'],$template,$repeat)?>
	<div class="content"><?=trim($content)?></div>
</section>