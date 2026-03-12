<?php

$distPath = __DIR__ . '/_dist/';
$docsPath = __DIR__ . '/_docs/';
$path = __DIR__ . '/';

define('_PROJECT_PRODUCTION_',false);

$v = '0';
$sv = '1';
$fv = "{$v}.{$sv}";
define('_MELON3_VERSION_','3.0.8.2.8');
define('_PROJECT_TITLE_','Foo Project');
define('_PROJECT_NAME_','melon3.foo_project');
define('_PROJECT_VERSION_',$v);
define('_PROJECT_SUBVERSION_',$sv);
define('_PROJECT_FULLVERSION_',$fv);

define('_COMPILABLE_USERS_PATH_','Z:/Namida/melon3/' . _MELON3_VERSION_ . '/mods/_users_compilable/');
define('_COMPILABLE_USERS_INJECT_BASE_PATH_',__DIR__ . '/mods/');
require_once(_COMPILABLE_USERS_PATH_ . '_inc.php');

define('_BASE_URL_',_PROJECT_PRODUCTION_ ? 'http://www.foo.com/' : 'http://localhost/foo-project/_dist/public_html/');
define('_PATH_PREFIX_',_PROJECT_PRODUCTION_ ? '/home/' : 'Z:/');

define('_DBS_INFO_',Array(
	'default' => _PROJECT_PRODUCTION_ ? Array(
		'host' => 'localhost',
		'user' => '',
		'pass' => '',
		'db_name' => '',
	) : Array(
		'host' => 'localhost',
		'user' => 'root',
		'pass' => '12345',
		'db_name' => 'my_db'
	),
));

$GLOBALS['_build'] = Array(
	'title' => _PROJECT_TITLE_,
	'name' => _PROJECT_NAME_,
	'version' => _PROJECT_FULLVERSION_,
	
	'build_source' => $path,
	'build_path' => $distPath,
	
	//globals for compiler smarty
	'smarty' => Array(
		'assigns' => Array(),
	),
	
	'compile' => Array(
		Array(
			'from' => Array(
				'glob' => '*.php',
				'dirs' => false,
				'step' => false,
				'base_path' => $path,
			)
		),
		
		"{$path}_build_inc/docs.php"
	),
	
	'compile_exclude' => Array(
		__DIR__ . '/_build.php',
		Array(
			'from' => Array(
				'glob' => [
					'_dist/*',
					'_dist/*/*.*',
					'*/cache/*',
					//'*/cache/*.*'
				],
				'dirs' => true, //set to true to copy empty dirs
				'files' => true,
				'step' => false,
				'base_path' => $path,
			)
		),
	),
	
	'copy' => Array(
		Array(
			'from' => Array(
				//'glob' => '*.php',
				'glob' => ['*.*','.*'],
				'dirs' => false, //set to true to copy empty dirs
				'files' => true,
				'step' => false,
				'base_path' => $path,
			)
		),
	),
	
	'copy_exclude' => Array(
		Array(
			'from' => Array(
				'glob' => [
					'_*',
					'cache/*'
				],
				'dirs' => true, //set to true to copy empty dirs
				'files' => true,
				'step' => false,
				'base_path' => $path,
			)
		),
		//Array(
//			'from' => Array(
				//'glob' => [
//					'_dist/*.*',
					//'_dist/*/*.*'
					//'_dist/*.*',
					//'_dist/*/*.*'
				//],
				//'dirs' => true, //set to true to copy empty dirs
				//'files' => true,
				//'step' => false,
				//'base_path' => $path,
			//)
		//),
	),
	
	'compilers' => Array(
		'php' => Array(
			'handler' => 'fn_pkg_compile_smarty',
			'assigns' => Array(
				//I could have specific assigns here, but we'll use the global ones for smarty (above)
			)
		),
		'scss' => Array(),
	),
	
	'docs' => Array(
		'path' => $docsPath,
		//'build_path' =>  __DIR__ . '/_build_inc/',
		//'build_source' => 
		'formats' => Array(
			'html' => [
				//'single_file' => true,
				//'multiple_files' => true,
			],
		),
		
		'copy' => Array(
			Array(
				'from' => __DIR__ . '/_build_inc/docs_assets/',
				'to' => "{$docsPath}assets/",
			)
		),
		
		'compile' => Array(
			Array(
				'from' => Array(
					'glob' => '*.scss',
					'dirs' => false,
					'step' => false,
					'base_path' => "{$path}_build_inc/",
				),
				'to' => '#{path}/#{filename}.css'
			),
		),
	),

	'hooks' => Array(
		'pkg:compile:smarty:pre' => 'users_compilable_extend_smarty'
	)
);

$_BUILD = &$GLOBALS['_build'];

?>