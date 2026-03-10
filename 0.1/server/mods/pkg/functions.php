<?php

function fn_pkg_load_project($manifestPath)
{
	require($manifestPath);
	
	$transformPaths = [
		'build_source',
		'build_path'
	];
	
	foreach($transformPaths as $path)
		$_BUILD[$path] = fn_string_normalize_path($_BUILD[$path]);
	
	return $_BUILD;
}

function fn_pkg_build_project($manifestPath)
{
	//__vdump("# BUILD PROJECT");
	$GLOBALS['_docs'] = [];
	
	$project = fn_pkg_load_project($manifestPath);
	
	if(!empty(__arrg('hooks',$project,[])))
		foreach($project['hooks'] as $hookName => $hookHandler)
			fn_hooks_set($hookName,$hookHandler);
	
	$project['file_content_prepend'] = [];
	
	//__vdump("FILES",$files);die();
	
	$buildRes = Array(
		'steps' => [],
	);
	
	$buildRes['steps'][] = 'remove_build_path';
	fn_filesystem_rrmdir($project['build_path']);
	$buildRes['steps'][] = 'fetching_affected_files';
	$buildRes['steps'][] = 'create_empty_build_path';
	FileSystem::mkdir($project['build_path']);
	
	
	
	fn_pkg_copy_compile_project($project,$buildRes);
	
	$buildRes['steps'][] = 'finished';
	
	//__vdump("PRE PROJECT DOCS",$GLOBALS['_docs'],$project,"BUILD RES",$buildRes);die();
	
	/*$compiledDocs = */fn_pkg_compile_docs($project,$GLOBALS['_docs']);
}

function fn_pkg_copy_compile_project($project,&$buildRes,$debug=false)
{
	$debug = false;
	
	if($debug) __vdump("fn_pkg_copy_compile_project",$project);
	$files = fn_pkg_parse_manifest_files($project);
	/*$compile = array_map(function($map) {
			return $map['from'];
		},__arrg('compile',$files,[]));*/
		
	if($debug)
	{
		__vdump("COPY ORIGINAL",$files['copy'],"COMPILE ORIGINAL",$files['compile']);
	}
		
	$compile = [];
	$copy = [];
	
	$excludes = ['copy','compile'];
	foreach($excludes as $exclude)
	{
		$$exclude = array_keys(__arrg($exclude,$files,[]));
		
		$tempExclude = "{$exclude}ExcludeTemp";
		$temp = "{$exclude}Exclude";
		
		$$tempExclude = array_keys(__arrg("{$exclude}_exclude",$files,[]));
		$$temp = [];
		foreach($$tempExclude as $ct)
		{
			if(is_dir($ct))
			{
				if($debug) __vdump("IS DIR {$ct}");
				$tempFiles = [];
				$args = [&$tempFiles];
				fn_filesystem_walkdir($ct,function($basePath,$currentRelPath,$tempRelPath,$isDir,$isGlob,$options,$initialBasePath,&$files){
					if(!$isDir)
						$files[] = fn_string_normalize_path("{$basePath}{$currentRelPath}");
				},[],$args);
				if($debug) __vdump("TEMP FILES",$tempFiles);
				$$temp = array_merge($$temp,$tempFiles);
			}
			else $$temp[] = $ct;
		}
		if($debug) __vdump("-- temp {$exclude}_exclude",$$temp);
		/*if($exclude === 'compile')
			__vdump("-- temp {$exclude}_exclude",$$temp);*/
		$$exclude = array_diff($$exclude,$$temp);
	}
	
	
	if($debug) __vdump("compile final",$compile);
	
	/*$compileExclude = array_keys(__arrg('compile_exclude',$files,[]));
	$compile = array_diff($compile,$compileExclude);
	
	$copy = array_keys(__arrg('copy',$files,[]));
	$copyExcludeTemp = array_keys(__arrg('copy_exclude',$files,[]));
	$copyExclude = [];
	foreach($copyExcludeTemp as $ct)
	{
		if(is_dir($ct))
		{
			$tempFiles = [];
			$args = [&$tempFiles];
			fn_filesystem_walkdir($ct,function($basePath,$currentRelPath,$tempRelPath,$isDir,$isGlob,$options,&$files){
				if(!$isDir)
					$files[] = "{$basePath}{$currentRelPath}";
			},[],$args);
			$copyExclude = array_merge($copyExclude,$tempFiles);
		}
		else $copyExclude[] = $ct;
	}
	$copy = array_diff($copy,$copyExclude);*/
	
	$copiedFiles = [];
	$compiledFiles = [];
	
	if($debug) __vdump("copy ya aplanado y filtrado?",$copy,"compile ya aplanado y filtrado?",$compile);
	
	$compilerAssigns = Array(
		'ENV' => [],
	);
	
	$_COMPILED_CONTENTS = [];
	
	if(!empty($compile))
	{
		$buildRes['steps'][] = 'start_file_compile';	
		$buildRes['steps'][] = &$compiledFiles;
	}
	
	if(!empty($copy))
	{
		$buildRes['steps'][] = 'start_file_copy';	
		$buildRes['steps'][] = &$copiedFiles;
	}
	
	//cambio a que compile antes ?
	if(!empty($compile))
	{
		foreach($compile as $filePathFrom)
		{
			if(!isset($_COMPILED_CONTENTS[$filePathFrom]))
			{
				$compiledFiles[] = $filePathFrom;
				$_COMPILED_CONTENTS[$filePathFrom] = fn_pkg_compile_file($project,$filePathFrom,$compilerAssigns);
			}
		}
	}
	
	if(!empty($copy)) foreach($copy as $filePathFrom)
	{
		//$file = str_replace($project['build_source'],$project['build_path'],$filePathFrom);
		
		$file = $files['copy'][$filePathFrom]['to'];
		if($debug) __vdump("FILE COPY TO: {$file}");
		if($debug) __vdump("COPIO {$filePathFrom} > {$file}");
		
		$mustCompile = in_array($filePathFrom,$compile);
		if($debug) __vdump("compile?",$mustCompile);
		if(!$mustCompile)
		{
			if($debug) __vdump("--- NO DEBO COMPILAR {$filePathFrom}",$compile);
			$copiedFiles[] = $filePathFrom;
			fn_filesystem_copy($filePathFrom,$file);
		}
		else
		{
			$fileCompile = __arrg(['compile',$filePathFrom,'to'],$files,$filePathFrom);
			$outputPath = $file === $fileCompile ? $file : $fileCompile;
			/*if($debug) 
			{
				__vdump("COMPILE FROM TO {$filePathFrom} > {$file} > {$fileCompile}");
				__vdump("COMPILE OUTPUT: {$outputPath}");
				__vdump("-- DEBO COMPILAR {$filePathFrom}");
			}
			if(!isset($_COMPILED_CONTENTS[$filePathFrom]))
			{
				$compiledFiles[] = $filePathFrom;
				$_COMPILED_CONTENTS[$filePathFrom] = fn_pkg_compile_file($project,$filePathFrom,$compilerAssigns);
			}*/
			$copiedFiles[] = "{$filePathFrom} (from compile)";
			
			/*if($debug)
			{
				__vdump("PUTEO COMPILADO {$filePathFrom} EN {$outputPath}");
				//die();
			}*/
			
			$info = pathinfo($file);
			//if($debug) __vdump("FILE INFO DE OUTPUT",$info);
			$outputPath = fn_string_normalize_path(fn_string_template($outputPath,array_merge($info,[
					//'base' => $buildSource,
					//'build' => __arrg('build_path',$project,''),
					//'rel_file' => $relFile,
					'path' => fn_string_normalize_path("{$info['dirname']}/")
				])));
			//if($debug) __vdump("OUTPUT PATH: {$outputPath}");
			fn_filesystem_put_contents($outputPath,$_COMPILED_CONTENTS[$filePathFrom]);
		}
	}
	
	if($debug) __vdump("-- copy compile final res",$buildRes);
}

function fn_pkg_compile_file($project,$file,&$assigns=Array())
{
	//__vdump("COMPILE {$file}");
	$content = '';
	
	$pathInfo = pathinfo($file);
	$compiler = __arrg(['compilers',$pathInfo['extension']],$project,false);
	
	if($compiler === false) throw new Exception("fn_pkg_compile_file error, '{$pathInfo['extension']}' compiler definition not found");
	
	$handler = __arrg('handler',$compiler,"fn_pkg_compile_{$pathInfo['extension']}");
	if(!is_callable($handler)) throw new Exception("fn_pkg_compile_file error, '{$handler}' compiler callable not found");
	
	$finalAssigns = array_merge($assigns,__arrg('assigns',$compiler,[]));
	
	$content = call_user_func_array($handler,[
		$project,
		$file,
		$pathInfo,
		$assigns
	]);
	
	return $content;
}

function fn_pkg_compile_scss($project,$file,$pathInfo,$assigns=Array())
{
	M3::reqCore('Styles');
	return fn_styles_parse($file);
}

function fn_pkg_compile_smarty($project,$file,$pathInfo,$assigns=Array())
{
	$content = '';
	$project_uid = uniqid('project_');
	$GLOBALS[$project_uid] = &$project;
	
	
	try
	{
		M3::reqCore('RenderEngine');
		$buildSource = fn_string_normalize_path($project['build_source']);
		$relFile = fn_string_normalize_path(str_replace($buildSource,'',$file));
		$compilerAssigns = array_merge(Array(
			'file' => $file,
			'pathinfo' => $pathInfo,
			'rel_file' => $relFile,
			'rel_pkg' => fn_string_normalize_path(str_replace($buildSource,'',$pathInfo['dirname'])) . '/' . $pathInfo['filename'],
			'project_uid' => $project_uid,
		),__arrg('smarty.assigns',$project,[]),$assigns);
		$rConfig = RenderEngine::getRendererConfig(Config::getD('renderer_config.compiler',[]),Array(
			'assigns' => $compilerAssigns
		));
		//__vdump("COMPILER ASSIGNS",$compilerAssigns,$file,$project);
		//__vdump("- mando a compilar: {$file}");
		$renderer = new RenderEngine('compiler',$rConfig);
		require(__DIR__ . '/smarty_extend.php');
		
		$_HOOK_ARGS = Array(
			&$renderer
		);
		fn_hooks_call('pkg:compile:smarty:pre',$_HOOK_ARGS);
		
		$content = $renderer->fetch($file);
		//__vdump("rendered content",$content);
	}
	catch (Exception $e)
	{
		__vdump("COMPILE EXCEPTION: {$e}");
	}
	if(!empty(__arrg(['file_content_prepend',$file],$project,[])))
	{
		$prep = '';
		foreach($project['file_content_prepend'][$file] as $fp)
		{
			$prep .= "{$fp}\n\n";
			//__vdump("PREPENDEO",$fp);
		}
		$content = str_replace('#######{prepend_content}',$prep,$content);
	}
	return $content;
}

function fn_pkg_compile_docs($project,$docs)
{
	//__vdump("OB_CLEANED","# fn_pkg_compile_docs");
	//__vdump($docs);die();
	$docsProject = __arrg('docs',$project,false);
	if($docsProject === false) return false;
	
	$docsProject = array_merge($docsProject,Array(
		'compilers' => __arrg('compilers',$project,[]),
	));
	
	$formats = __arrg('formats',$docsProject,[]);
	$compiled = [];
	$buildRes = [];
	//__vdump("-- pre copy compile");
	fn_pkg_copy_compile_project($docsProject,$buildRes,true);
	//__vdump("-- post copy compile");
	
	//__vdump("buildRes?",$buildRes);
	
	if(!empty($formats))
	{
		$docsMaps = fn_array_map_keys_with($docs,function($map,$k,$v){
			return [
			 trim($k) !== '',
			 !isset($v['map'])
			];
		});
		foreach($formats as $formatName => $config)
		{
			$handler = __arrg('handler',$config,"fn_pkg_compile_docs_{$formatName}");
			$compiled[$formatName] = call_user_func_array($handler,[$project,$docs,$docsMaps,$config]);
		}
	}
	return $compiled;
}

function fn_pkg_compile_docs_html($project,$docs,$maps=false,$config=Array())
{
	$compiled = [];
	
	$config = array_merge(Array(
		'merged' => true,
	),$config);
	
	if($maps === false) $maps = fn_array_map_keys_with($docs,function($map,$k,$v){
			return [
			 trim($k) !== '',
			 !isset($v['map'])
			];
		});

	//__vdump($maps);
	$fullMap = Array();
	if(!empty($maps)) foreach($maps as $map)
	{
		$fullMap[$map] = ['_title' => $map];
		//__vdump("BASE: {$map}");
		M3::reqCore('RenderEngine');
		//__vdump("MAP?",$map);
		$mapNode = __arrg($map,$docs);
		$isTerminal = isset($mapNode['map']);
		if(!$isTerminal && isset($mapNode['']))
		{
			$mapNode = $mapNode[''];
		}
		//__vdump("NODE",$mapNode);
		
		$indexMap = [];
		$indexCurrent = false;
		
		$GLOBALS['_index'] = &$indexMap;
		$GLOBALS['_index_current'] = &$indexCurrent;
		
		$compilerAssigns = array_merge(Array(
			//'file' => $file,
			//'pathinfo' => $pathInfo,
			//'rel_file' => $relFile,
			//'rel_pkg' => str_replace('\\','/',str_replace($project['build_source'],'',$pathInfo['dirname'])) . '/' . $pathInfo['filename']
			'_format' => 'html',
			'_nested' => [],
			//'_index' => &$indexMap,
			//'_index_current' => &$indexCurrent,
		)/*,$assigns*/);
		$rConfig = RenderEngine::getRendererConfig(Config::getD('renderer_config.compiler',[]),Array(
			'assigns' => $compilerAssigns
		));
		$renderer = new RenderEngine('compiler',$rConfig);
		require(__DIR__ . '/smarty_extend.php');
		$content = '';
		if(!isset($mapNode['content']));// __vdump("map node sin content",$mapNode);
		else
		{
			/*$pinfo = pathinfo($mapNode['file']);
			$tmpFilePath = __DIR__ . '/temp/' . uniqid('') . '_' . $pinfo['basename'];
			fn_filesystem_put_contents($tmpFilePath,$mapNode['content']);
			$content = $renderer->fetch($tmpFilePath);
			fn_filesystem_unlink($tmpFilePath);*/
			$content = $renderer->fetch("string:{$mapNode['content']}");
		}
		
		$compiled[$map] = $content;
		
		//$indexMap = $renderer->smarty->getTemplateVars('_index');
		//$indexCurrent = $renderer->smarty->getTemplateVars('_index_current');
		//__vdump("{$map} INDEX MAP",$indexMap);
		/*__vdump("INDEX CURRENT",$indexCurrent,"-----\n");
		__vdump("FINAL ASSIGN",$compilerAssigns);*/
		//__vdump("nested {$map} INDEX MAP",$renderer->smarty->getTemplateVars('_nested'));
		$fullMap = array_merge($fullMap,$indexMap);
	}
	//__vdump("--- FIN",$fullMap,"FROM VARS");
	//__vdump(fn_array_from_selectors($fullMap));
	$docsBasePath = $project['docs']['path'];
	//FileSystem::rmdir($docsBasePath);
	FileSystem::mkdir($docsBasePath);
	if($config['merged']) require(__DIR__ . '/tpl/html/file_merged.php');
	
	return $compiled;
}

function fn_pkg_parse_manifest_files($project)
{	
	$parse = Array(
		'compile',
		'compile_exclude',
		'copy',
		'copy_exclude',
	);

	$parsed_files = [];

	foreach($parse as $parse_key)
	{
		$debug = $parse_key === 'compile';
		$debug = false;
		if($debug) __vdump("PARSE KEY: {$parse_key}",__arrg($parse_key,$project,false));
		$parsed_files[$parse_key] = [];
		if(__arrg($parse_key,$project,false) !== false)
			foreach($project[$parse_key] as $keyValue)
			{
				//__vdump("ENTRO AL FOREACH",$keyValue);
				$to = false;
				if(is_array($keyValue))
				{
					//__vdump("IS ARRAY");
					$from = __arrg('from',$keyValue);
					$to = __arrg('to',$keyValue,false);
				}
				else
				{
					$from = $keyValue;
					//__vdump("IS STRING");
				}
				
				if($to === false)
					$to = '#{build}#{rel_file}';
				
				if(is_string($from)) if(is_dir($from))
				{
					//__vdump("HOLA");
					$to .= '#{rel_file}';
				}
				
				$normalize = ['from','to'];
				foreach($normalize as $norm)
				{
					if(is_string($$norm)) $$norm = fn_string_normalize_path($$norm);
					else
					{
						if(isset($$norm['glob'])) $$norm['glob'] = fn_string_normalize_path($$norm['glob']);
						if(isset($$norm['base_path'])) $$norm['base_path'] = fn_string_normalize_path($$norm['base_path']);
					}
				}
				
				$isOptions = is_array($from);
				if($debug) __vdump("from?",$from,"to?",$to,"isOptions",$isOptions);
				if($isOptions)
				{
					$args = Array(
						&$parsed_files[$parse_key],
						$project,
						$to,
						is_dir($to),
						$debug
					);
					//die("--- asd");
					if($debug) __vdump("PRE ERROR");
					fn_filesystem_walkdir(__arrg('build_source',$project,''),function($basePath,$currentRelPath,$tempRelPath,$isDir,$isGlob,$options,$initialBasePath,&$results,$project,$to,$isDirTo,$debug){
							$buildSource = __arrg('build_source',$project,'');
							if(trim($buildSource) === '') $buildSource = $initialBasePath;
							$fileFrom = fn_string_normalize_path("{$buildSource}{$tempRelPath}");
							
							if($debug) __vdump("walk dir {$initialBasePath} / $basePath / fileFrom: {$fileFrom}",$project);
							//$relFile = str_replace($basePath,'',$fileFrom);
							$relFile = $tempRelPath;
							$fileInfo = pathinfo($fileFrom);
							//__vdump("TO {$to}");
							if($isDirTo) __vdump("IS DIR TO",$isDirTo);
							if($isDirTo) $fileFrom .= $fileInfo['basename'];
							//__vdump("walkdir fileinfo",$fileInfo);
							$toParsed = fn_string_normalize_path(fn_string_template($to,array_merge($fileInfo,[
										'base' => $buildSource,
										'build' => __arrg('build_path',$project,''),
										'rel_file' => $relFile,
									])));
							if($debug) __vdump("FILE INFO?",$fileInfo,$to,"TO PARSED: {$toParsed}");
							$results[$fileFrom] = [
								'from' => $fileFrom,
								'to' => $toParsed,
							];
						},$from,$args);
					if($debug) __vdump("POST ERROR");
					//__vdump("FILES?",$parsed_files[$parse_key],"--------------");
				}
				else
				{
					$readable = is_readable($from);
					$isdir = is_dir($from);
					//__vdump("string from glob? {$from}",is_readable($from),glob($from));
					//if($isdir && $readable) $from .= '*';
					if($parse_key === 'copy_exclude') __vdump("FROM {$from}");
					if($isdir)
					{
						//__vdump("COPY DIR FROM TO {$from} / {$to}");die();
						//list($result,$affected) = fn_filesystem_copydir($from,$to);
						//$parsed_files[$parse_key][] = array_merge($parsed_files[$parse_key],);
						$args = [&$parsed_files[$parse_key],$project,$to];
						if($parse_key === 'copy_exclude') __vdump("IS DIR {$from}");
						fn_filesystem_walkdir($from,function($basePath,$currentRelPath,$tempRelPath,$isDir,$isGlob,$options,$initialBasePath,&$files,$project,$to){
							if(!$isDir)
							{
								$filePath = fn_string_normalize_path("{$basePath}{$currentRelPath}");
								$fileInfo = pathinfo($filePath);
								//__vdump("file info",$fileInfo);
								//__vdump("este walk dir",$basePath,$currentRelPath,$tempRelPath,$filePath,"----");
								$files[$filePath] = Array(
									'from' => $filePath,
									'to' => fn_string_normalize_path(fn_string_template($to,array_merge($fileInfo,[
										'base' => __arrg('build_source',$project,''),
										'build' => __arrg('build_path',$project,''),
										'rel_file' => $tempRelPath,
									])))
								);
								//__vdump("walkdir {$filePath}",$basePath,$currentRelPath,$tempRelPath);
							}
						},[],$args);
					}
					else
					{
						//__vdump("entro al else con {$from}");
						$parsed_files[$parse_key][$from] = ['from' => $from, 'to' => $to];
					}
				}
			}
	}
	
	return $parsed_files;
}

?>