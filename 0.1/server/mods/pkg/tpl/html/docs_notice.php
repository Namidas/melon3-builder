<div class="docs-notice type-<?=$params['type']?> <?=$cssclass?>">
	<?php if($title !== false) echo smarty_block_docs_title(array_merge($params,[
		'class' => __arrg('title_class',$params,[]),
		'render_template' => false,
		'lvl' => 6
		//'map_link' => $mapLink,
		//'lvl' => 'auto',
		//'map' => $,
		//'class' => 'main'
	]),"{$mapLinkOpen}{$title}{$mapLinkClose}",$template,$repeat)?>
	<div class="content"><?=trim($content)?></div>
</div>