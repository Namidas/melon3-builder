<a
	href="#<?=$link?>"
	class="map-link"
	title="view: <?="{$params['map']}/{$params['link']}"?>"
	>
		<?=$link?> > 
		<?=$params['content'] === false ? $params['link'] : $params['content']?>
	</a>