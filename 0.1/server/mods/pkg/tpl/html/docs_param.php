<li class="param-item <?=$cssclass?>">
	<div class="param-main">
		<span class="param-name"><?="{$mapLinkOpen}{$params['name']}{$mapLinkClose}"?></span>
		<?php if(!empty($params['type'])) { ?>
			<ul class="param-types">
				<?php foreach($params['type'] as $pt) { ?>
					<li><?=$pt?></li>
				<?php } ?>
			</ul>
		<?php } ?>
		<?php if($params['deprecated'] === true) { ?><span class="deprecated-param">deprecated<?php if($params['deprecated_v'] !== false) { ?> in v<?=$params['deprecated_v']?><?php } ?></span><?php } ?>
		<?php if(isset($params['default'])) { ?>
			<div class="flex-space"></div>
			<span class="param-default">
				<span class="value"><?=fn_print_var($params['default'])?></span> 
				<span class="type">(<?=gettype($params['default'])?>)</span>
			</span>
		<?php } ?>
	</div>
	<span class="param-desc"><?=$content_parsed?></span>
</li>