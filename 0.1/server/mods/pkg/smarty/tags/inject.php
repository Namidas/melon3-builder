<?php

function smarty_tag_inject($params,&$smarty)
{
	$contents = Array();
	$files = $params['files'];
	if(is_string($files))
		if(trim($files) != '') $files = Array($files);
		else $files = Array();
	if(!is_array($files)) $files = Array($files);
	
	foreach($files as $filePath)
		{
			$pInfo = pathinfo($filePath);
			/*array(4) {
			  ["dirname"]=>
			  string(30) "Z:\Gokmen\ceredis\_compile/gen"
			  ["basename"]=>
			  string(33) "users.owned_by_shop.hooks.php.tpl"
			  ["extension"]=>
			  string(3) "tpl"
			  ["filename"]=>
			  string(29) "users.owned_by_shop.hooks.php"
			}*/
			switch($pInfo['extension'])
			{
				case 'tpl':
					$content = $smarty->fetch($filePath);
					$subPInfo = pathinfo($pInfo['filename']);
					if($subPInfo['extension'] === 'php')
						$content = substr($content,5,-2);
					$contents[] = $content;
					break;
				case 'php':
					$contents[] = substr(file_get_contents($filePath),5,-2);
					break;
					
				default:
					$contents[] = file_get_contents($filePath);
					break;
			}
		}
		
	echo implode("\n",$contents);
}

?>