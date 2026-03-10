<article class="docs-block">
	<?php
	$mapx = explode('/',$params['map']);
	$map = array_filter($mapx,function($i) { return trim($i) !== ''; });
	$title = __arrg('title',$params,false);
	$mapAppend = '';
	if($title === false)
	{
		$title = array_pop($map);
		$mapAppend = "{$title}/";
	}
	
	ob_start();
	?>
	<?php if(!empty($map)) { ?><ul class="docs-map">
		<?php foreach($map as $m) { ?>
		<?php if(trim($m) !== '') { ?><li><?=$m?></li><?php } ?>
		<?php } ?>
	</ul><?php } ?>
	<?=$titlePre?>
	<?=$title?>
	<?=$titleExtra?>
	<?php $titleContent = ob_get_clean(); 	?>
	<?=smarty_block_docs_title(array_merge($params,[
			'render_template' => false,
			'map' => (!empty($map) ? implode('/',$map) . '/' : '') . $mapAppend,
			'class' => 'main'
		]),"{$mapLinkOpen}{$titleContent}{$mapLinkClose}",$template,$repeat)?>
	<div class="article-content"><?=trim($content)?></div>
</article>