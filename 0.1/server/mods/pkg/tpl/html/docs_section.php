<section data-lvl="<?=$lvl?>" class="docs-section <?=$cssclass?> lvl<?=$lvl?>">
	<?php if($title !== false) echo smarty_block_docs_title(array_merge($params,[
		'class' => __arrg('title_class',$params,[]),
		'render_template' => false,
		//'map_link' => $mapLink,
		//'lvl' => 'auto',
		//'map' => $,
		//'class' => 'main'
	]),"{$mapLinkOpen}{$title}{$mapLinkClose}",$template,$repeat)?>
	<div class="content"><?=trim($content)?></div>
</section>