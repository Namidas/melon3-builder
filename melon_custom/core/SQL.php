<?php

global $_SQL_RUN_FILES;
$_SQL_RUN_FILES = Array();

//meekrodb implementation
M3::reqVendor('meekrodb');
fn_sql_load_assets();

function fn_sql_load_assets()
{
	M3::reqCore('FileSystem');
	FileSystem::walkDirFiles(dirname(__FILE__) . '/assets/sql/','fn_sql_load_assets_loader');
	//fn_sql_run_file(functions.sql');
}

function fn_sql_load_assets_loader($path,$file,$relativePath)
{
	fn_sql_run_file_once($path.$file);
}

function fn_sql_run_file_once($filePath,$values=Array(),$options=Array())
{
	global $_SQL_RUN_FILES;
	if(!in_array($filePath,$_SQL_RUN_FILES))
		fn_sql_run_file($filePath,$values,$options);
}

function fn_sql_run_file($filePath,$values=Array(),$options=Array())
{
	$options = array_merge(Array(
		'assigns' => Array(),
	),$options);
	
	$fileInfo = pathinfo($filePath);
	
	switch($fileInfo['extension'])
	{
		case 'sql':
			$content = file_get_contents($filePath);
			break;
			
		case 'tpl':
			M3::reqCore('RenderEngine');
			$client = Array(
				'renderer' => Array(
					'template_dir' => Array("{$fileInfo['dirname']}/"),
				),
			);
			$rConfig = RenderEngine::getRendererConfig($client,Array(
				'main_tpl' => $fileInfo['basename'],
				'assigns' => array_merge(Array(
				),$options['assigns']),
			));
			$renderer = new RenderEngine($client,$rConfig);
			$content = $renderer->fetch();
			break;
	}
	
	global $_SQL_RUN_FILES;
	$_SQL_RUN_FILES[] = $filePath;
	return call_user_func_array('DB::query',array_merge(Array($content),$values));
}

function fn_sql_factory_fields($accept='*',$deny=Array(),$opts=Array())
{
	$opts = array_merge(Array(
		'prefix' => false,
		'table' => 'undef-table-name',
		
		//'unprefix' => true,
		'prefix_escape' => '_'
	),$opts);
	
	$res = Array();
	
	if(!is_array($accept)) $accept = Array($accept);
	if(!is_array($deny)) $deny = Array($deny);
	
	$acceptTemp = $accept;
	$denyTemp = $deny;
	
	$_ALL_FIELDS = false;
	
	//$GLOBALS['__games_foo_get_all_all'] = &$_ALL_FIELDS;
	//$GLOBALS['__games_foo_get_all_table'] = $opts['table'];
	
	$getAllFields = function(&$all,$table,$opts)
	{
		if($all !== false) return $all;
		$all = fn_sql_get_columns($table,false,$opts['prefix']);
		return $all;
	};
	
	$acceptAsterisk = in_array('*',$accept) || in_array("{$opts['table']}.*",$accept);
	if($acceptAsterisk) $acceptTemp = $getAllFields($_ALL_FIELDS,$opts['table'],$opts);
	
	$denyAsterisk = in_array('*',$deny) || in_array("{$opts['table']}.*",$deny);
	if($denyAsterisk) $denyTemp = $getAllFields($_ALL_FIELDS,$opts['table'],$opts);
	
	if($opts['prefix'] !== false)
	{
		$acceptTemp = fn_string_unprefix_array($acceptTemp,$opts['prefix']);
		$denyTemp = fn_string_unprefix_array($denyTemp,$opts['prefix']);
	}
	
	$res = array_diff($acceptTemp,$denyTemp);
	
	if($opts['prefix'] !== false)
		$res = fn_string_prefix_array($res,$opts['prefix'],$opts['prefix_escape']);
	
	return $res;
}

function fn_sql_get_columns($table,$DB=false,$unprefix=false)
{
	$result = array_keys(fn_sql_get_structure($table,$DB));
	if($unprefix === false) return $result;
	$result = fn_string_unprefix_array($result,$unprefix);
	/*//need to unprefix this
	$prefixLength = strlen($unprefix);
	foreach($result as &$key)
		$key = fn_string_unprefix($key,$unprefix,$prefixLength);*/
	return $result;
}

function fn_sql_get_structure($table,$DB=false)
{
	$query = 'DESCRIBE %l';
	switch($DB)
	{
		case false:
			$struct = DB::query($query,SQL::sanitize($table));
			break;
			
		default:
			$struct = $DB->query($query,SQL::sanitize($table));
			break;
	}
	if(!empty($struct)) $struct = fn_array_set_keys_from_value($struct,'Field');
	return $struct;
}

function fn_sql_get_schema($table,$DB=false)
{
	$query = "SELECT * FROM information_schema.tables WHERE table_schema = %s AND table_name = %s LIMIT 1";
	switch($DB)
	{
		case false:
			$dbName = DB::$dbName;
			$schema = DB::queryFirstRow($query,$dbName,$table);
			break;
			
		default:
			$dbName = $DB->$dbName;
			$schema = $DB->queryFirstRow($query,$dbName,$table);
			break;
	}
	return $schema;
}

function fn_sql_parse_options($opts,$fields,$table,&$dbWhere,$ignoreBooleanFalse=true)
{
	//"id" => Array(
		//"type" => "i",
		//"field" => "id",
	//)
	//"id" => "i",
	foreach($fields as $optsKey => $fieldData)
	{
		if(isset($opts[$optsKey])) if(($opts[$optsKey] !== false || !$ignoreBooleanFalse))
		{
			if(!is_array($fieldData)) $fieldData = Array(
				'type' => $fieldData,
				'field' => $optsKey,
			);
			switch($fieldData['type'])
			{
				//custom search string
				case 'ss':
					$dbWhere->add("LOWER({$table}.{$optsKey}) REGEXP %s",strtolower(implode("|",is_array($opts[$optsKey]) ? $opts[$optsKey] : Array($opts[$optsKey]))));
					break;
					
				//custom date implementation
				//may take an array with the fields from / to
				case 'lt':
				case 't':
					//__vdump("PARSE OPTIONS DATE",$optsKey,$opts[$optsKey]);
					if(!is_array($opts[$optsKey])) $opts[$optsKey] = Array($opts[$optsKey]);
					if(!empty($opts[$optsKey]))
					{
						$handler = $dbWhere->addClause("OR");
						foreach($opts[$optsKey] as $date)
						{
							//__vdump("DATE?",$date,date("Y-m-d",strtotime($date)),date("Y-m-d",strtotime($date . " 12:20:24")));
							if(is_string($date)) $handler->add("{$table}.{$optsKey} LIKE %ss",date("Y-m-d",strtotime($date)));
							if(is_array($date)) $handler->add("{$table}.{$optsKey} >= %t AND {$table}.{$optsKey} <= %t",$date['from'],$date['to']);
						}
					}
					break;
					
				default:
					$comp = is_array($opts[$optsKey]) ? ' IN %l':' = %';
					$dbWhere->add("{$table}.{$fieldData['field']}{$comp}{$fieldData['type']}",$opts[$optsKey]);
					break;
			}
		}
	}
}

/*function fn_sql_update_case()
{
}*/

function fn_sql_set_db_connection($dbName=null,$standAlone=false)
{
	if($dbName === null) $dbName = "default";
	$dbs = Config::get("sql_dbs");
	if(!$standAlone)
	{
		DB::$user = $dbs[$dbName]["user"];
		DB::$password = $dbs[$dbName]["pass"];
		DB::$dbName = $dbs[$dbName]["db_name"];
		DB::$host = $dbs[$dbName]["host"];
		DB::$encoding = "utf8";
	}
	else return new MeekroDB($dbs[$dbName]["host"],$dbs[$dbName]["user"],$dbs[$dbName]["pass"],$dbs[$dbName]["db_name"],null,"utf8");
}

class SQL
{
	const ARRAY_A = 'associative_array';
	const UNIQUE = 'unique';
	const SQL = "sql";
	const COUNT = "count";
	const INSERT = "insert";
	const UPDATE = "update";
	const DELETE = "delete";
	
	static $lastAction;
	static $affectedRows;
	
	static function findInSet($table,$field,$values)
	{
		//M3::trace("FIND IN SET");
		if(!is_array($values)) $values = Array($values);
		$queryes = Array();
		foreach($values as $val)
		{
			$query = "({$table}.{$field} = '{$val}' OR {$table}.{$field} LIKE '{$val},%' OR {$table}.{$field} LIKE '%,{$val},%' OR {$table}.{$field} LIKE '%,{$val}')";
			$queryes[] = $query;
		}
		//M3::trace($queryes);exit;
		if(count($queryes) > 1)
			return "(" . implode(" OR ",$queryes) . ")";
		else
			return array_shift($queryes);
	}
	
	static function querySingleOrArray($table,$field,$values,$wrapInQuotes=true)
	{
		if(is_string($wrapQuotes))
		{
			__vdump(debug_backtrace());
			die("cambie \$type usualmente string por \$wrapQuotes (false si es INT por ejemplo)----");
		}
		
		$query = "{$table}.{$field}";
		$op = $wrapInQuotes ? "'" : "";
		
		if(is_array($values))
		{
			$query .= " IN ({$op}";
			$query .= implode("{$op},{$op}",$values);
			$query .= "{$op})";
		}
		else $query .= " = {$op}{$values}{$op}";
		return $query;
	}
	
	static function tableName($table)
	{
		return Config::get("sql_table_prefix") . (Config::get("abm_online") ? $table : strtolower($table));
	}
	
	static function queryAArray($reffID,$query,$type=SQL::SQL,$dbName=null,$noBreakOnError = false)
	{
		return SQL::query($query,$type,$dbName,$noBreakOnError,$reffID);
	}
	
	static function query($query,$type=SQL::SQL,$dbName=null,$noBreakOnError = false,$assocArray=false)
	{
		if(Config::get("no_sql",false)) return null;
		
		$retorno = null;
		
		if($type == SQL::UNIQUE) $query = preg_replace('/LIMIT\s.*\d+[,]\d+/i', '_', $query) . " LIMIT 0,1";
		if($dbName == null) $dbName = Config::get("sql_name");
		
		SQL::connect($dbName,$link);
		
		//if (!($conexion = mysql_query($query,$link))) die(mysql_error()."<br>MySQL QUERY: '".$query."'");
		SQL::$lastAction = $link->query($query);
		SQL::$affectedRows = $link->affected_rows;

		if(!SQL::$lastAction)
		{
			if($noBreakOnError) return $link->error;
			else die($link->error . "<br>MySQL QUERY: '" . $query . "'");
		}
		
		if(Config::get("sql_log_all",false)) Log::write("SQL::query - {$_SERVER["REQUEST_URI"]} - {$query}\n");

		switch($type)
		{
			case SQL::ARRAY_A:
				$retorno = Array();
				if($row = SQL::$lastAction->fetch_assoc())
				{
					if($assocArray !== false) $retorno[$row[$assocArray]] = $row;
					else $retorno[] = $row;
					while ($row = SQL::$lastAction->fetch_assoc()) if($assocArray !== false) $retorno[$row[$assocArray]] = $row;
					else $retorno[] = $row;
				}
				else return null;
				break;
				
			case SQL::UNIQUE:
				if($row = SQL::$lastAction->fetch_assoc()) $retorno = $row;
				else $retorno = null;
				break;
			
			case SQL::INSERT:
				return $link->insert_id;
				break;
			
			case SQL::DELETE:
			case SQL::SQL:
				return SQL::$lastAction;
				break;
			
			case SQL::UPDATE:
			case SQL::COUNT:
				$retorno = SQL::$affectedRows;
				break;
		}

		//SQL::breakConnection(&$link);
		return $retorno;
	}
	
	static function connect($base,&$link)
	{
		$link = SQL::getDBLink($base);
	}
	
	static function sanitize($string)
	{
		if(is_array($string))
		{
			foreach($string as $k => &$v) $v = SQL::sanitize($v);
			return $string;
		}
		$link = DB::get();
		return $link->real_escape_string($string);
	}
	
	static function &getDBLink($base = null,$autoCreate=true)
	{
		global $__GLOBAL;
		
		if($base == null) $base = Config::get("sql_prefix");
		if($base == null) $base = Config::get("sql_name");
		$dbs = Config::get("sql_dbs");

		$link = null;
		
		$crear = false;
		
		if(@$__GLOBAL["sql_connected"]) $link = &$__GLOBAL["sql_link"];
		else $crear = $autoCreate;

		if($crear)
		{
			$link = new mysqli($dbs[$base]["host"],$dbs[$base]["user"],$dbs[$base]["pass"],$dbs[$base]["db_name"]);
			//$link = mysql_connect($dbs[$base]["host"],$dbs[$base]["user"],$dbs[$base]["pass"]) or die(mysql_error());
			//mysql_select_db($dbs[$base]["db_name"],$link);
		
			$__GLOBAL["sql_connected"] = true;
			$__GLOBAL["sql_link"] = $link;
		}
		
		return $link;
	}

	static function breakConnection(&$link = null)
	{
		if($link == null) $link = SQL::getDBLink(null,false);
		if($link != null) if(!$link->close()) die($link->error);
	}
	
	static function setCharset($charset)
	{
		$link = SQL::getDBLink();
		$link->set_charset($charset);
	}
	
	static function getAffectedRowsFromLink($link=false)
	{
		if($link == false) $link = SQL::getDBLink();
		return $link->affected_rows;
	}
}

?>