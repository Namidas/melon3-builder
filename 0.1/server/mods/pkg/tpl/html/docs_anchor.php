<a
	class="docs-anchor <?=$cssclass?>"
	href="<?=$href?>"
	target="<?=$target === false ? '_self' : $target?>"
	<?php if($title !== false) { ?>title="<?=$title?>"<?php } ?>
><?=$content_parsed?></a>