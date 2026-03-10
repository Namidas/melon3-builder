<?php ob_start();
require(__DIR__ . '/inc/header.php'); ?>
<body>
	<?php require(__DIR__ . '/inc/menu.php'); ?>
	<main>
		<?php foreach($compiled as $map => $content) { if(trim($content) === '')  continue; ?>
			<section>
				<?=$content?>
			</section>
		<?php } ?>
	</main>
</body>
<?php require(__DIR__ . '/inc/footer.php');
$fileContent = ob_get_clean();
fn_filesystem_put_contents("{$docsBasePath}index.html",$fileContent);
