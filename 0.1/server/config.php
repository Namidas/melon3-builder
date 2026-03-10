<?php

//require_once(dirname(__FILE__) . '/functions.php');

define('_PRODUCTION_',false);

// google application credentials
//$google_credentials_path = 'Z:/Namida/_credentials/google_oauth.json';
//putenv("GOOGLE_APPLICATION_CREDENTIALS={$google_credentials_path}");

$_MELON_VERSION = '3.0.8.2.8';
$_PROJECT_TITLE = 'ChotGPT';
$_PROJECT_NAME = "melon3.chotgpt";

$_BASE_URL = 'https://5.189.139.187/~azutgcd/demos/ChotGPT/';
$_BASE_URL = 'http://localhost/Namida/ChotGPT/';

$projectBasePath = dirname(__FILE__) . '/';
$_JWT_SECRET_KEY = 'melon3-chotgpt';
$pathPrefix = constant('_PRODUCTION_') ? '/home/azutgcd/' : 'Z:/Namida/';
$_MELON3_BASEPATH = "{$pathPrefix}melon3/{$_MELON_VERSION}/";

$_PATHS = Array(
	'base' => $projectBasePath,	
	'melon' => __DIR__ . '/../../melon_custom/', //"{$_MELON3_BASEPATH}sys/",
	'client' => Array(
		"{$_MELON3_BASEPATH}client/#{current_client_name}/",
		"{$projectBasePath}client/#{current_client_name}/",
	),
	'mods' => Array(
		"{$_MELON3_BASEPATH}mods/",
		"{$projectBasePath}mods/"
	),
	'vendor' => Array(
		"{$_MELON3_BASEPATH}vendor/",
		"{$projectBasePath}vendor/"
	),	
	'themes' => Array(
		Array('path' => "{$_MELON3_BASEPATH}themes/", 'url' => "{$_BASE_URL}themes/"),
		Array('path' => "{$projectBasePath}themes/" , 'url' => "{$_BASE_URL}themes/"),
	),
	
	//these are used globally (and below you can define client-specific on 'render_engine'
	'templates' => Array(
		//estos estaban alverre ? **PROBARRRRR
		"{$_MELON3_BASEPATH}templates/client/#{current_client_name}/",
		"{$projectBasePath}templates/client/#{current_client_name}/",
	),
	
	//these are used globally (and below you can define client-specific on 'render_engine'
	'lang' => Array(
		//estos estaban alverre ? **PROBARRRRR
		"{$_MELON3_BASEPATH}lang/",
		//"{$_MELON3_BASEPATH}lang/client/#{current_client_name}/",
		"{$projectBasePath}lang/",
		//"{$projectBasePath}templates/client/#{current_client_name}/",
	),
	
	'media' => "{$projectBasePath}media/",
);

$_SQL_TABLE_PREFIX = '';

$_DBS = Array(
	'default' => constant('_PRODUCTION_') ? Array(
		'host' => 'localhost',
		'user' => 'grawita_demos',
		'pass' => 'bywwi6cb5OQf',
		'db_name' => 'grawita_chotgpt',
	) : Array(
		'host' => 'localhost',
		'user' => 'root',
		'pass' => '12345',
		'db_name' => 'chotgpt'
	),
);

$_GLOBAL_RENDERER_CONFIG = Array(
	'renderer' => Array(
		'template_dir' => $_PATHS['templates'],
	),
	
	'engine' => Array(
		'cache_dir' => "{$projectBasePath}cache/#{current_client_name}/smarty/cache/",
		'compile_dir' => "{$projectBasePath}cache/#{current_client_name}/smarty/templates_c/",
	)
);

$__CONFIG = Array(
	'title' => $_PROJECT_TITLE,
	'melon' => Array(
      'version' => $_MELON_VERSION
    ),
	
	'base_url' => $_BASE_URL,
	'base_path' => $_PATHS['base'],
	'media_path' => $_PATHS['media'],
	'media_url' => "{$_BASE_URL}media/",
	'melon_path' => $_PATHS['melon'],
	
	'client_path' => $_PATHS['client'],
	'vendor_path' => $_PATHS['vendor'],
	
	'mods' => Array(
		//'sys',
		//'melon3_client_app',
		'pkg'
	),
	
	'client_config' => Array(
		'melon3.vue3_quasar' => Array(
			'available_themes' => Array(
				'chot_gpt',
				),
			'theme' => 'chot_gpt'
			//client-specific configs, gets merged with client/config.php if present
		)
	),
	
	'mods_path' => $_PATHS['mods'],
	'themes_path' => $_PATHS['themes'],
	
	'sql_dbs' => $_DBS,
	'sql_table_prefix' => $_SQL_TABLE_PREFIX,
	'sql_engine' => 'mysqli',
	'sql_log_all' => false,
	
	'jwt_secret_key' => $_JWT_SECRET_KEY,
	'user_session_timeout' => 60*10*1000, //valid for 10 minutes
	
	'renderer_config' => Array(
		'*' => $_GLOBAL_RENDERER_CONFIG,
		
		'compiler' => Array(
			'engine' => Array(
				'left_delimiter' => '/*%',
				'right_delimiter' => '%*/',
			),
			'renderer' => Array(
				'use_cache' => false,
			),
		),
		
		'melon3.vue3' => Array(
			'extends' => ['*'],
			'engine' => Array(
				'left_delimiter' => '<%',
				'right_delimiter' => '%>',
				//'cache_dir' => "{$basePath}cache/#{rest_settings.baseParams.client}/smarty/cache/",
				//'compile_dir' => "{$basePath}cache/#{rest_settings.baseParams.client}/smarty/templates_c/",
			),
			
			'renderer' => Array(
				//'template_dir' => $_PATHS['templates'],
				'main_tpl' => 'base.tpl',
				'assigns' => Array(),
			),
		),
		
		'melon3.vue3_quasar' => Array(
			'extends' => ['melon3.vue3'],
			/*'renderer' => Array(
				'template_dir' => Array(
					dirname(__FILE__) . '/../../tpl_quasar_v2/',
				),
				'template_dir_remove' => Array(
					dirname(__FILE__) . '/../../tpl/',
				),
			),*/
		),
	),
	
	'requires_auth' => Array(),
	
	'available_locales' => Array(
		'en-US' => Array(
			'name' => 'English',
			'flag' => 'gb',
			'messages' => Array()
		),
	),
	'default_locale' => 'en-US',
	
	'core' => Array(
		'Auth' => Array(
			'function_prefix' => 'users2',
		),
		'Lang' => Array(
			'die_on_error' => true,
			'add_path' => true,
			'paths' => $_PATHS['lang']
		),
	),
	
	'vendor' => Array(
		'rest' => Array(
			'history' => false,
		)
	),
);

?>