<?php

function fn_filesystem_copydir($from,$to,$options=Array()) {
	$options = array_merge(Array(
		'empty_dir' => true,
	),$options);
	
	if(is_dir($from))
	{
		$objects = scandir($from);
		foreach ($objects as $object)
		{
			if ($object != "." && $object != "..")
			{
				$path = "{$path}/{$object}";
				$toPath = "{$to}/{$object}";
				if (filetype($path) == "dir")
				{
					if($options['empty_dir'] === true) FileSystem::mkdir($path);
					fn_filesystem_copydir($path,$toPath); 
				}
				else fn_filesystem_copy($path,$toPath);
			}
		}
		reset($objects);
		rmdir($path);
	}
}

function fn_filesystem_copy($from,$to)
{
	if(!is_readable($from)) return false;
	$pinfo = pathinfo($to);
	if(!file_exists($pinfo['dirname'])) FileSystem::mkdir($pinfo['dirname']);
	return copy($from,$to);
}

/* wraps php's file_put_contents on a path check to avoid errors */
function fn_filesystem_put_contents($path,$content,$flags=0,$context=null)
{
	$pinfo = pathinfo($path);
	if(!file_exists($pinfo['dirname'])) FileSystem::mkdir($pinfo['dirname']);
	return file_put_contents($path,$content,$flags,$context);
}

/*recursively remove path and all it's contents*/
function fn_filesystem_rrmdir($path) {
	if(is_dir($path))
	{
		$objects = scandir($path);
		foreach ($objects as $object)
		{
			if ($object != "." && $object != "..")
			{
				if (filetype($path."/".$object) == "dir") 
					fn_filesystem_rrmdir($path."/".$object); 
				else unlink("{$path}/{$object}");
			}
		}
		reset($objects);
		rmdir($path);
	}
}

/*walkdir base $path and unlink given $file recursively on path and *all* subpaths*/
function fn_filesystem_walkdir_unlink($path,$file)
{
	fn_filesystem_unlink($path.$file);
	
	fn_filesystem_walkdir($path,function($path,$subPath){
		fn_filesystem_unlink($path.$subPath);
	},Array(
		'dirs' => false,
		'glob' => $file
	));
}

/*unlink $filePath if it exists, same as default unlink() only you don't have to perform
any additional checks*/
function fn_filesystem_unlink($filePath)
{
	if(is_readable($filePath))
		if(!is_dir($filePath))
			if(is_file($filePath))
				return unlink($filePath);
	return false;
}

/* recursively scan and walk a path and all it contents, then call given handler with
whatever it finds along the way the $options allows you to choose in which cases you'd
like the handler to be called */
function fn_filesystem_walkdir($basePath,$handler,$options=Array(),&$args=null,$initialBasePath=false)
{
	$args = !empty($args) ? $args : Array();
	
	$args[] = 'chau';
	
	//__vdump("WALKDIR ARGS",$args);
	$options = array_merge(Array(
		'files' => true, //call on files
		'dirs' => true, //call on dirs
		'step' => true, //call on each walked path
		'glob' => '*',
		'rel_path' => '',
		'base_path' => false,
	),$options);
	
	if($options['base_path'] !== false)
	{
		$basePath = $options['base_path'];
	}
	
	if(!is_array($options['glob'])) $options['glob'] = [$options['glob']]; 
	
	$files = [];
	$dirs = glob("{$basePath}*",GLOB_MARK|GLOB_ONLYDIR);
	
	foreach($options['glob'] as $optGlob)
		$files = array_merge($files,glob("{$basePath}{$optGlob}",GLOB_MARK));
	
	//this calls the dirs because of walkdir
	if(!empty($dirs)) foreach($dirs as $subPath)
	{
		$currentRelPath = str_replace($basePath,'',$subPath);
		$tempRelPath = $options['rel_path'] . $currentRelPath;
		
		if($options['step'])
			call_user_func_array($handler,array_merge(Array(
				$basePath,
				$currentRelPath,
				$tempRelPath,
				true, //$isDir
				false, //$isGlob,
				$options,
				$initialBasePath
			),$args));
			
		fn_filesystem_walkdir($subPath,$handler,array_merge($options,Array('base_path' => false, 'rel_path' => $tempRelPath)),$args,$initialBasePath === false ? $basePath : $initialBasePath);
	}
	
	//
	if($options['files'] || $options['dirs']) if(!empty($files)) foreach($files as $file)
	{
		$currentRelPath = str_replace($basePath,'',$file);
		$tempRelPath = $options['rel_path'] . $currentRelPath;
		$isDir = is_dir($file);
		
		$call = ($isDir && $options['dirs']) || (!$isDir && $options['files']);
		if($call)
			call_user_func_array($handler,array_merge(Array(
				$basePath,
				$currentRelPath,
				$tempRelPath,
				$isDir, //$isDir,
				true, //$isGlob
				$options,
				$initialBasePath
			),$args));
	}
}

class FileSystem
{
	public static $_file_if_defaults = Array(//all scripts loaded
		'method' => 'require_once',
		'conditions' => Array(
			'readable',
		),
		'all_files' => false,
	);
	
	static function getPathIf($path,&$_CONTEXT=Array(),$options=Array())
	{
		return FileSystem::fileIf($path,$_CONTEXT,array_merge($options,Array(
			'method' => 'get_path',
		)));
	}
	
	static function reqIf($path,&$_CONTEXT=Array(),$once=true,$options=Array())
	{
		return FileSystem::fileIf($path,$_CONTEXT,array_merge($options,Array(
			'method' => $once ? 'require_once' : 'require',
		)));
	}
	
	static function incIf($path,&$_CONTEXT=Array(),$once=true,$options=Array())
	{
		return FileSystem::fileIf($path,$_CONTEXT,array_merge($options,Array(
			'method' => $once ? 'include_once' : 'include',
		)));
	}
	
	/*
	$path is the path to the file to read
	if it's an array it's assumed it's in the shop of 0: path, 1: fileName
	both can be either a string or an array of strings
	
		$path = 'some/file/path/my-file.php'
		$path = ['some/file/path/','my-file.php'];
		$path = [['some/file/path/','another/file/path/'],'my-file.php'];
		
	this is specially useful to extend (whatever) with multiple sources path
	and recursively require whatever file on whatever paths found (from list)
	*/
	static function fileIf($path,&$_CONTEXT=Array(),$options=Array())
	{
		//__vdump("file if",$path,$options);
		$options = array_merge(FileSystem::$_file_if_defaults,$options);
		
		$valid = FileSystem::_file_if_eval_condition($path,$options['conditions']);
		//__vdump("valid",$valid);
		$res = Array();
		if($valid !== false)
			foreach($valid as $filePath)
				switch($options['method'])
				{
					case 'require': require($filePath); break;
					case 'require_once': require_once($filePath); break;
					case 'include': include($filePath); break;
					case 'include_once': include_once($filePath); break;
					case 'get_path': $res[] = $filePath; break;
					default: return false; break;
				}
		
		$returnRes = ['get_path'];
		return in_array($options['method'],$returnRes) ? $res : true;
	}
	
	static function _file_if_eval_condition($basePath,$conditions)
	{
		//it's a static path
		if(is_string($basePath)) $basePath = [$basePath,''];
		$paths = $basePath[0];
		$files = $basePath[1];
		if(is_string($paths)) $paths = [$paths];
		if(is_string($files)) $files = [$files];
		
		$valid = Array();
		
		//__vdump("BASE PATH",$basePath);
		
		foreach($paths as $path)
			foreach($files as $file)
			{
				$currentFilePath = "{$path}{$file}";
				//__vdump("fileIf",$currentFilePath);
				if(!empty($conditions))
					foreach($conditions as $condition)
						switch($condition)
						{
							case 'readable':
								if(is_readable($currentFilePath))
									$valid[] = $currentFilePath;
						}
			}
			
		return empty($valid) ? false : $valid;
	}
	
	static function getFirstFilePath($paths,$file)
	{
		$res = FileSystem::getAllFilePaths($paths,$file);
		return empty($res) ? false : array_shift($res);
	}
	
	static function getAllFilePaths($paths,$file)
	{
		$result = Array();
		//__vdump($paths);
		if(!is_array($paths)) $paths = Array($paths);
		foreach($paths as $path)
		{
			$filePath = "{$path}{$file}";
			//__vdump("check",$filePath,"--");
			if(is_readable($filePath))
				$result[] = $filePath;
		}
		return $result;
	}
	
	/*
	static function curl_get_file($source,$dest)
	{
		$ch = curl_init($source);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		$rawdata=curl_exec($ch);
		curl_close ($ch);
		if(file_exists($dest)){
			unlink($dest);
		}
		$fp = fopen($dest,'x');
		fwrite($fp, $rawdata);
		fclose($fp);
	}
	
	static function curl_get_raw($source)
	{
		$ch = curl_init($source);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		$rawdata=curl_exec($ch);
		curl_close ($ch);
		return $rawdata;
	}
	
	static function curl_post($url,$data=Array())
	{
		$ch = curl_init();
		curl_setopt_array($ch,Array(
		
			CURLOPT_URL => 
			$url,
			
			CURLOPT_POST => true,
			
			CURLOPT_POSTFIELDS => $data,
			
			CURLOPT_RETURNTRANSFER => true
			
		));

		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}*/

	static function walkAll($path,$callback)
	{

	}

	static function walkDir()
	{
		$args = array_values(func_get_args());

		if(empty($args)) return;
		else $path = array_shift($args);
		$ff = array_shift($args);
		$scan = scandir($path);

		if(!empty($scan)) foreach($scan as $sc)
		{
			if($sc == "." || $sc == "..") continue;
			$dirpath = "{$path}{$sc}/";
			//__vdump($dirpath);
			if(!is_dir($dirpath)) continue;

			call_user_func_array($ff,array_merge(Array($dirpath),$args));
			//FileSystem::walkDir($dirpath,$ff);
		}
	}

	static function walkDirFiles()
	{
		$args = array_values(func_get_args());
		if(empty($args)) return;
		else $path = array_shift($args);
		
		$ff = array_shift($args);
		if(!empty($args))
		{
			$relativePath = array_shift($args);
			if(trim($relativePath) === '') $relativePath = '';
		}
		else $relativePath = '';
		
		$scan = scandir($path);

		$return = Array();

		if(!empty($scan)) foreach($scan as $sc)
		{
			if($sc == "." || $sc == "..") continue;
			$dirpath = "{$path}{$sc}/";
			$file = "{$path}{$sc}";
			if(is_dir($dirpath))
			{
				if(!is_readable($file))  continue;
				$return = array_merge($return,FileSystem::walkDirFiles($dirpath,$ff,"{$relativePath}{$sc}/"));
			}
			else
			{
				if(!is_readable($file)) continue;
				$return[] = call_user_func_array($ff,array_merge(Array($path,$sc,$relativePath),$args));
			}
		}

		return $return;
	}

	static function walkDirUnlink($path,$file)
	{
		FileSystem::unlink($path.$file);
		FileSystem::walkDir($path,function($spath,$file) { FileSystem::unlink($spath.$file); },$file);
	}

	const ACCEPT_UPLOAD_IMAGE_ALL = Array(
		'.jpg','.jpeg',
		'.png',
		'.gif',
		'.svg',
		'.webp',
	);
	
	static function handleUploadType($type,$options=Array())
	{
		$options = array_merge(Array(
			'folder' => '',
			'upload_dir' => Config::get('media_path'),
			'upload_url' => Config::get('media_url'),
		),$options);
		
		switch($type)
		{
			case 'image':
				$options = array_merge(Array(
					'accept' => FileSystem::ACCEPT_UPLOAD_IMAGE_ALL,
					'get_url' => Array(
						'width' => 500,
						'height' => 500,
						'zc' => true,
						'resizeUp' => true,
					)
				),$options);
				break;
		}
			
		try {
			M3::reqCore('URL');
			//__vdump("PRE HANDLE UPLOAD",$options);
			$file = FileSystem::handleUpload($options);

			//__vdump("UPLOAD",$file,fn_url_from_path($file['dirname']) . '/',Config::get("base_path"),Config::get("base_url"));
			$file['base_url'] = fn_url_from_path($file['dirname']) . '/';
			$file['full_url'] = "{$file['base_url']}{$file['basename']}";

			if($options['get_url'] !== false)
			{
				M3::reqCore('Image');
				$file['url'] = Image::getURL(Config::get('media_path').$options['folder'],$file['basename'],$options['get_url']);
			}
			//__vdump("FINAL FILE",$file);
			unset($file['dirname']);
			return $file;
		}
		catch(Exception $e) {
			$code = $e->getCode();
			$responseCode = 413; //payload too large
			switch($code)
			{
				case 1001:
					$responseCode = 415;
					break;
					
				case 1002: //no files
					$responseCode = 418; //I'm a teapot
					break;
					
				case 1003: //default
					break;
			}
			throw new Exception($e->getMessage(),$responseCode);
		}
	}
	
	static function handleUpload($options=Array())
	{
		M3::reqLib('EasyPHPUpload');

		$options = array_merge(Array(
			//"param_name" => "media",
			"folder" => "",
			"upload_dir" => Config::get("media_path"),
			"upload_url" => Config::get("media_url"),
			"create_path" => true,
			
			"accept" => false,
			
			"source" => "post",
			"multi" => false,
			"rename_file" => true,
			"replace" => false,
			"check_name" => true,
		),$options);
		
		if(empty($options['accept']))
			throw new Exception('gen.upload.error.empty_accept',1001);
		
		$maxSize = Config::get("upload_max_file_size",1.1 * 1024 * 1024);
		
		$files = $_FILES;
		if(!empty($files))
		{
			if(!$options['multi']) $files = Array(array_shift($files));
		}
		else throw new Exception('gen.upload.error.empty_files',1002);
		foreach($files as $file)
		{
			if($file['size'] > $maxSize) throw new Exception('gen.upload.error.max_file_size',1003);
			
			if($options['create_path'])
				FileSystem::mkdir($options['upload_dir'] . $options['folder']);
			
			$upload = new file_upload;
			$upload->upload_dir = $options['upload_dir'] . $options['folder'];
			$upload->extensions = $options['accept'];
			$upload->max_length_filename = 50;
			$upload->rename_file = $options['rename_file'];
			$upload->the_temp_file = $file['tmp_name'];
			$upload->the_file = $file['name'];
			$upload->http_error = $file['error'];
			$upload->replace = $options['replace'];
			$upload->do_filename_check = $options["check_name"];
			//$pathInfo = pathinfo($file['name']);
			$new_name = $options['rename_file'] ? uniqid('')/* . '.' . $pathInfo['extension']*/ : '';
			if($upload->upload($new_name))
			{
				$full_path = $upload->upload_dir.$upload->file_copy;
				//$info = $upload->get_uploaded_file_info($full_path);
				return pathinfo($full_path);
			}
			else
			{
				switch($upload->error_code)
				{
					case 11:
						throw new Exception('gen.upload.error.no_accept',1004);
						break;
				}
			}
		}
		/*
		

		$data = Array();

		$upload_handler = new UploadHandler($options);

		$preHeader = Array(
			'Pragma: no-cache',
			'Cache-Control: no-store, no-cache, must-revalidate',
			'Content-Disposition: inline; filename="files.json"',
			'X-Content-Type-Options: nosniff',
			'Access-Control-Allow-Origin: *',
			'Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE',
			'Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size'
		);

		switch ($_SERVER['REQUEST_METHOD']) {
			case 'OPTIONS':
				break;
			case 'HEAD':
			case 'GET':
				$data = $upload_handler->get();
				break;
			case 'POST':
				if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
					$data = $upload_handler->delete();
				} else {
					$data = $upload_handler->post();
				}
				break;
			case 'DELETE':
				$data = $upload_handler->delete();
				break;
			default:
				//header('HTTP/1.1 405 Method Not Allowed');
		}
		
		$maxSize = Config::get("max_upload_file_size",1.1 * 1024 * 1024);
		//M3::var_dump($maxSize,$data["info"][0]->size);
		
		//M3::var_dump(,$maxSize,$data["info"]["size"] > $maxSize);
		$data["info"][0]->error = false;
		if($data["info"][0]->size > $maxSize)
		{
			$data["info"][0]->error = true;
			$data["info"][0]->error_type = "file_too_big";
			FileSystem::walkDirUnlink($options["upload_dir"],$data["info"][0]->name);
		}
		//die("FILE TOO BIG");
		$data["header"] = array_merge($preHeader,$data["header"]);
		return $data;*/
	}
	
	/*static function handleBase64Upload($base64EncodedPNGs,$options=Array())
	{
		$uploadedFiles = Array();
		if(!empty($base64EncodedPNGs))
			foreach($base64EncodedPNGs as $b64PNG)
			{
				$img = str_replace('data:image/png;base64,', '', $b64PNG);
				$img = str_replace(' ', '+', $img);
				$data = base64_decode($img);
				$file = FileSystem::getUniqueFileName($options["upload_dir"],uniqid(),"png","_#{counter}");
				$success = file_put_contents($options["upload_dir"] . $file, $data);
				if($success) $uploadedFiles[] = $file;
			}
		return $uploadedFiles;
	}*/

	static function rename($src,$target)
	{
		return @rename($src,$target);
	}

	static function mkdir($pathname,$mode = 0777,$recursive = true)
	{
		$ret = false;
		if(!is_readable($pathname)) $ret = mkdir($pathname,$mode,$recursive);
		return $ret;
	}

	static function unlink()
	{
		$ret = Array();
		$args = func_get_args();
		if(count($args)) foreach($args as $k => $v) if(!is_array($v)) $ret[$v] = FileSystem::__unlink($v);
		else array_merge($ret,call_user_func_array("FileSystem::unlink",$v));
		return $ret;
	}

	static function __unlink($file) { return @unlink($file); }

	static function copy($source,$dest,$context=false)
	{
		if($context !== false) $res = @copy($source,$dest,$context);
		else $res = @copy($source,$dest);
		return $res;
	}

	static function duplicate($source,$context=false)
	{
		$new_file_name = "";
		$info = pathinfo($source);
		$file_name = $info["filename"];
		$ext = $info["extension"];
		$counter = 0;
		$dir = $info["dirname"] . "/";

		//while(is_readable($dir.$file_name."({$counter}).".$ext)) $counter++;
		//$new_file_name = $file_name."({$counter}).".$ext;
		$new_file_name = FileSystem::getUniqueFileName($dir,$file_name,$ext);

		$res = FileSystem::copy($source,$dir.$new_file_name,$context);
		if(!$res) return $res;
		return $new_file_name;
	}
	
	static function getUniqueFileName($dir,$file_name,$ext,$format=" (#{counter})")
	{
		if(!is_readable($dir.$file_name.".".$ext)) return $file_name.".".$ext;
		$counter = 1;
		$currentCounter = fn_string_template($format,Array("counter" => $counter));
		while(is_readable($dir.$file_name.$currentCounter.".".$ext))
		{
			$counter++;
			$currentCounter = fn_string_template($format,Array("counter" => $counter));
		}
		$new_file_name = $file_name.$currentCounter.".".$ext;
		return $new_file_name;
	}
}

?>
