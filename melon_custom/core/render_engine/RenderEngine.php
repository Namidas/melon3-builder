<?php

M3::reqVendor('smarty');
use Smarty\Smarty;


class RenderEngine
{
	/* nuevo *//* nuevo *//* nuevo *//* nuevo */
	/* nuevo *//* nuevo *//* nuevo *//* nuevo */
	/* nuevo *//* nuevo *//* nuevo *//* nuevo */
	/* nuevo *//* nuevo *//* nuevo *//* nuevo */
	/*static $smartySingleton = null;
	
	static function getSmarty()
	{
		$smarty = new Smarty;
		$smarty->addTemplateDir(dirname(__FILE__).'/../../libs/smarty/tpl/');
		$smarty->addTemplateDir($this->template_dir);
		//var_dump($smarty->getTemplateDir());
		$this->cacheDir($this->cache_dir);
		$this->caching($this->use_cache);
		$this->cacheLifeTime($this->cache_lifetime);
		
		foreach($baseConfig as $k => $v)
			$smarty->{$k} = $v;
	}
	
	static function getSmartySingleton()
	{
	}*/
	
	
	
	
	
	
	
	private $base_dir = "";
	
	public $smarty = null;
	
	private function config_defaults()
	{
		return Array(
			"base_dir" => ''/*dirname( __FILE__ ) . "/"*/,
			"main_tpl" => "base.tpl",
			"cache_id" => Array(""),
			"use_cache" => false,
			"compile_dir" => null/*dirname( __FILE__ ) . "/templates_c/"*/,
			"template_dir" => null/*dirname( __FILE__ ) . "/tpl/"*/,
			"cache_dir" => null/*dirname( __FILE__ ) . "/cache/"*/,
			//"cache_lifetime" => 60*60*24*30,
			"cache_lifetime" => Smarty::CACHING_LIFETIME_SAVED,
			"assigns" => Array(),
		);
	}
	
	static function assignEnv($client)
	{
		global $__GLOBAL;
		$env = Array(
			//'__CURRENT_USER' => fn_auth_get_current_user(),
			//'__GLOBAL' => $__GLOBAL,
		);
		return $env;	
	}
	
	static function getDefaultRendererConfig()
	{
		return Array(
			'engine' => Array(
				'left_delimiter' => '<%',
				'right_delimiter' => '%>',
				//'cache_dir' => false,
				'cache_dir' => null,
				'compile_dir' => null,
				//'cache_dir' => dirname( __FILE__ ) . 'cache/default/smarty/cache/',
				//'compile_dir' => dirname( __FILE__ ) . 'cache/default/smarty/templates_c/',
			),
			
			'renderer' => Array(
				'template_dir' => Array(
					//dirname(__FILE__) . '/../assets/templates/',
				),
				//'main_tpl' => 'base.tpl',
				'assigns' => Array(),
			),
			
			'extends' => [],
		);
	}
	
	static function getRendererBaseConfig($client,$field=false)
	{
		//__vdump("--- CONFIG START {$client}");
		$renderers = Config::get("renderer_config");
		
		/*$globalConfig = __arrg('*',$renderers,[]);
		unset($renderers['*']);*/
		
		$config = fn_array_merge_recursive(
			RenderEngine::getDefaultRendererConfig(),
			$client !== false ?
				is_array($client) ? $client : __arrg([$client],$renderers,[])
				:
				[]
		);
		
		//__vdump("CONFIG MERGEADA",$config, __arrg($client,$renderers,[]));

		/*if($client === false) $config = RenderEngine::getDefaultRendererConfig();
		elseif(is_array($client)) $config = array_merge(RenderEngine::getDefaultRendererConfig(),$client);
		elseif(!isset($renderers[$client])) die("unhandled error no existe config para el renderer de cliente: {$client}");
		else $config = array_merge(Array(
			"extends" => false,
		),$renderers[$client]);*/
		
		/*$config = array_merge($globalConfig,$config,
			Array(
				'renderer' => Array(
					'template_dir' => array_merge(
						__arrg('renderer.template_dir',$globalConfig,[]),
						__arrg('renderer.template_dir',$config,[])
					),
					'template_dir_remove' => array_merge(
						__arrg('renderer.template_dir_remove',$globalConfig,[]),
						__arrg('renderer.template_dir_remove',$config,[])
					),
				)
			)
		);*/
		
		//__vdump("hola",$config);

		if(!empty($config['extends']))
		{
			if(!is_array($config["extends"])) $config["extends"] = Array($config["extends"]);
			foreach($config["extends"] as $extensionName)
			{
				$config = fn_array_merge_recursive(RenderEngine::getRendererBaseConfig($extensionName,false),$config);
				//__vdump("CICLO, extendi con {$extensionName}",$config);
			}
			unset($config["extends"]);
		}
		
		
		//__vdump("FIN EXTENDS",__arrg('extends',$config));
		
		if(!empty($config["renderer"]["template_dir_remove"]))
		{
			Config::set("__temp_template_dir_filter",$config["renderer"]["template_dir_remove"]);
			$config["renderer"]["template_dir"] = array_filter($config["renderer"]["template_dir"],function($dir) {
				if(in_array($dir,Config::get("__temp_template_dir_filter"))) return false;
				return true;
			});
			unset($config["renderer"]["template_dir_remove"]);
		}
		
		//__vdump("pre saraza",$config);
		
		if(!empty(__arrg('renderer.template_dir',$config,[])))
			$config['renderer']['template_dir'] = array_values($config['renderer']['template_dir']);
		
		//__vdump("CONFIG {$client}",$config);
		
		if($field) return $config[$field];
		else return $config;
	}
	
	/* SMARTY */
	public $main_tpl = "index.tpl"; //archivo principal del tpl
	public $cache_id = Array(); //ID del caché a mostrar actualmente
	public $has_cache = false; //hay caché disponible ?
	public $use_cache = false; //usar caché ?
	
	private $assigns = Array();
	
	private $template_dir = '';
	private $cache_dir = '';
	private $cache_lifetime = null;
	
	public function __construct($client,$opts=Array(),$smarty=null)
	{
		//__vdump("start CONSTRUCTOR RENDER ENGINE");
		$this->smarty = $smarty;
		
		/*$renderers = Config::get("renderer_config");
		if(!isset($renderers[$client])) die("unhandled error no existe config para el renderer de cliente: {$client}");
		$baseConfig = $renderers[$client]["engine"];*/
		//__vdump("FETCHEO BASE CONFIG {$client}");
		$baseConfig = RenderEngine::getRendererBaseConfig($client,'engine');
		//__vdump("RESULT BASE CONFIG {$client}",$baseConfig);
		$baseConfig['cache_dir'] = is_string($baseConfig['cache_dir']) ? fn_string_template($baseConfig['cache_dir'],Array(),Array('merge_config' => true)) : null;
		$baseConfig['compile_dir'] = is_string($baseConfig['compile_dir']) ? fn_string_template($baseConfig['compile_dir'],Array(),Array('merge_config' => true)) : null;
		//__vdump("ya templetee",$baseConfig['cache_dir']);
		$opts = array_merge($this->config_defaults(),$opts);
		//__vdump("CONSTRUCT",$opts);
		$this->setOpts($opts);
		
		if($this->smarty == null)
		{
			//__trace(dirname(__FILE__).'/libs/smarty/tpl/');
			$this->smarty = new Smarty;
			require_once(__DIR__ . '/ext/inc.php');
			require(__DIR__ . '/extend.php');
			$this->smarty->addTemplateDir(dirname(__FILE__).'/../../libs/smarty/tpl/');
			$this->smarty->addTemplateDir($this->template_dir);
			//__vdump("TEMPLATE DIR?",$this->template_dir);
			//var_dump($this->smarty->getTemplateDir());
			$this->cacheDir($this->cache_dir);
			//$this->templateDir(/*$this->base_dir . */$this->template_dir);
			$this->caching($this->use_cache);
			$this->cacheLifeTime($this->cache_lifetime);
			
			foreach($baseConfig as $k => $v)
			{
				switch($k)
				{
					/*case 'left_delimiter':
						$this->smarty->setLeftDelimiter($v);
						break;
					case 'right_delimiter':
						$this->smarty->setRightDelimiter($v);
						break;
					case 'cache_dir':
						$this->smarty->setCacheDir($v);
						break;
					*/
					default:
						$methodName = fn_string_camelize("set-{$k}");
						call_user_func_array(Array($this->smarty,$methodName),[$v]);
						//$this->smarty->{$k} = $v;
						break;
				}
			}
				
			//$smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
		}
		//__vdump("end CONSTRUCTOR RENDER ENGINE");
	}
	
	public function setOpts($opts=Array())
	{
		$this->base_dir = $opts["base_dir"];
		$this->main_tpl = $opts["main_tpl"];
		$this->cache_id = $opts["cache_id"];
		$this->use_cache = $opts["use_cache"];
		$this->template_dir = $opts["template_dir"];
		$this->cache_dir = $opts["cache_dir"];
		$this->cache_lifetime = $opts["cache_lifetime"];
		$this->assigns = $opts["assigns"];
	}
	
	public function getOpts($opts=Array())
	{
		return Array(
			"base_dir" => $this->base_dir,
			"main_tpl" => $this->main_tpl,
			"cache_id" => $this->cache_id,
			"use_cache" => $this->use_cache,
			"template_dir" => $this->template_dir,
			"cache_dir" => $this->cache_dir,
			"cache_lifetime" => $this->cache_lifetime,
			"assigns" => $this->assigns,
		);
	}
	
	
	public function fetch($tpl = false)
	{
		$this->smarty->assign($this->assigns);
		$cacheID = implode("|",$this->cache_id);
		$this->smarty->cache_id = $cacheID;
		//__vdump("FETCHER!!!!",$cacheID);
		return $this->smarty->fetch($tpl ? $tpl : $this->main_tpl,$cacheID);
	}
	
	public function getSub($opts=Array())
	{
		$opts = array_merge($this->getOpts(),$opts);
		return new RenderEngine($opts,$this->smarty->createTemplate($opts));
	}
	
	public function assign($var,$value)
	{
		$this->assigns[$var] = $value;
	}
	
	/* setters getters generales */
	/* setters getters generales */
	/* setters getters generales */
	/* setters getters generales */
	/* setters getters generales */
	public function cacheDir($set=false)
	{
		//__vdump("RenderEngine->cacheDir()",$set);
		if($set !== false) 
		{
			$this->cache_dir = $set;
			$this->smarty->setCacheDir($this->cache_dir);
		}
		return $this->cache_dir;
	}
	
	public function caching($set=false)
	{
		//__vdump("RenderEngine->caching()",$set,$this->cache_lifetime);
		if($set !== false) 
		{
			$this->use_cache = $set;
			$this->smarty->caching = $this->cache_lifetime;/*$this->cache_dir;*/
		}
		return $this->use_cache;
	}
	
	public function cacheLifeTime($set=false)
	{
		if($set !== false) 
		{
			$this->cache_lifetime = $set;
			$this->smarty->cache_lifetime = $this->cache_lifetime;
		}
		return $this->cache_lifetime;
	}
	
	public function templateDir($set=false)
	{
		if($set !== false) 
		{
			$this->template_dir = $set;
			$this->smarty->setTemplateDir($this->template_dir);
		}
		return $this->template_dir;
	}
	
	public function getRenderer() { return $this->smarty; }
	
	static function getRendererConfig($client,$config=Array(),$baseAssigns=true)
	{
		M3::reqCore('String');
		///__vdump("cliente",$client);
		/*$renderers = Config::get("renderer_config");
		if(!isset($renderers[$client])) die("unhandled error no existe config para el renderer de cliente: {$client}");
		$baseConfig = $renderers[$client]["renderer"];*/
		//__vdump("pre get base");
		$baseConfig = RenderEngine::getRendererBaseConfig($client,"renderer");
		
		//__vdump("Base renderer config",$baseConfig);
		//$templateDirs = Config::get(",Array('tpl/'));
		
		/*$config = array_merge(Array(
			//"base_dir" => ,
			"template_dir" => array_reverse($templateDirs),
			"main_tpl" => "base.tpl",
			"assigns" => Array(),
		),$config);*/
		
		$config = array_merge($baseConfig,$config);
		
		if($baseAssigns)
		{
			global $__GLOBAL,
				$CurrentUser;
				
			$config["assigns"] = array_merge(RenderEngine::assignEnv($client),Array(
				/*"__MOD" => $__MOD,
				"__GLOBAL" => $__GLOBAL,
				"CurrentUser" => $CurrentUser,*/
			),$config["assigns"]);
		}
		
		if(!empty($config['template_dir'])) foreach($config['template_dir'] as &$temp)
			$temp = fn_string_template($temp,Array(),Array('merge_config' => true));
		
		//__vdump("FINAL RENDERER CONFIG",$config);
		
		return $config;
	}
}

?>