<?php
		
$render = function($docs,$baseMap='') use (&$render)
{
	ob_start();
	$maps = array_keys($docs);
	if(!empty($maps)) { ?>
	<ul class="main-menu <?php echo trim($baseMap) !== '' ? 'is-sub' : ''; ?>">
	<?php
		//__vdump("MENU RENDER",$maps);
		foreach($maps as $map)
		{
			$mapPrint = $map;
			$fullMap = "{$baseMap}{$map}/";
			$mapNode = __arrg($map,$docs);
			$isTerminal = isset($mapNode['map']);
			$terminalNode = false;
			if(!$isTerminal) $terminalNode = $mapNode;
			else if(trim($map) === '') continue;
			if(!$isTerminal && isset($mapNode['']))
			{
				$mapNode = $mapNode[''];
				//$mapPrint = $baseMap;
			}
			?><li class="<?=$terminalNode !== false ? 'has-sub' : '' ?>" data-hash="<?=$fullMap?>">
				<a class="perma" href="#<?=$fullMap?>">
					<span><?=$mapPrint?></span>
				</a>
				<?php  if($terminalNode !== false) echo $render($terminalNode,$fullMap,$render); ?>
			</li><?php
		}
	?>
	</ul>
	<?php
	}
	return ob_get_clean();
};

$render2 = function($source,$baseMap='') use (&$render2)
{
	ob_start();
	//$maps = array_keys($source);
	/*if(!empty($maps))*/ { ?>
	<!-- source start
	<?php __vdump($source) ?>
	source end -->
	<ul class="main-menu hideable <?php echo trim($baseMap) !== '' ? 'is-sub' : ''; ?>">
	<?php
		//__vdump("MENU RENDER",$maps);
		foreach($source as $map => $data)
		{
			$menuItem = true;
			$map = str_replace('.','/',$map);
			$fullMap = "{$baseMap}{$map}/";
			$title = __arrg('_title',$data,false);
			if($title === false) $menuItem = false;
			unset($data['_title']);
			$hasSub = !empty($data);
			?><li class="<?=$hasSub ? 'has-sub' : '' ?>">
				<?php if($menuItem) { ?>
					<a class="perma" href="#<?=$fullMap?>">
						<span class="base-map"><?=$baseMap?></span>
						<span class="pkg-title"><?=$title?></span>
						<?php if($hasSub) { ?>
							<span class="sub-toggler"></span>
						<?php } ?>
					</a>
				<?php } ?>
				<?php if($hasSub) { ?>
					<?php echo $render2($data,$fullMap);
				} ?>
			</li><?php
		}
	?>
	</ul>
	<?php
	}
	return ob_get_clean();
}

?>
<aside class="main-sidebar menu-active">
	<section class="main-sidebar-main-header">
		<!--<div class="title hideable">
			<span class="project">melon3</span>
			<span class="version">3.0.8.2.8</span>
		</div>-->
		<div class="tools">
			<a class="tool active" data-toggle="menu" title="Toggle menu">
				<span class="toggler">Toggle menu</span>
			</a>
			<a class="tool hideable" data-toggle="search" title="Toggle search">
				<span class="toggler">Toggle search</span>
			</a>
		</div>
		<div class="search-box hideable">
			<input type="text" id="search-input" placeholder="Search terms..."/>
		</div>
	</section>
	<?=/*$render($docs);*/$render2(fn_array_from_selectors($fullMap)) ?>
	<ul class="main-menu search">
		<li class='empty'>No results found...</li>
	</ul>
</aside>