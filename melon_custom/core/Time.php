<?php

#######{prepend_content}

#docs:core/Time
#{
# ## core/Time
# Time related functions and helpers. Also defines the *TIME_ZONES* constant
#}

#docs:core/Time
#{
# ### fn_time_get_seconds_diff($d1='now',$d2='now',$getSeconds=true)
# Returns the time difference in seconds between the given dates *$d1* and *$d2*, when the third param *$getSeconds* is false it returns a _DateInterval_ object instead
#}
function fn_time_get_seconds_diff($d1='now',$d2='now',$getSeconds=true)
{
	//__vdump($date1,$date2);
	
	$date1 = new DateTime($d1);
	$date2 = new DateTime($d2);
	//var_dump($date2);
	$interval = $date1->diff($date2,true);
	//$interval = $date2->diff($date1,true);
	
	$diffSeconds = $interval->s + ($interval->h*60*60) + ($interval->i*60);
	
	//require_once(dirname(__FILE__) . '/core/Log.class.php');
	//Log::write('(ex: ' . date('Y-m-d') . " // {$d1} / {$d2}) " . $date1->format('Y-m-d H:i:s') . ' - ' . $date2->format('Y-m-d H:i:s') . ' - ' . $diffSeconds);
	
	if($getSeconds) return $diffSeconds;
	else return $interval;
}

#docs:core/Time
#{
# ### fn_time_get_date_object($time=false)
# Returns an associative array with all time format variables (from PHP date format) for the given *$time*, when false it uses the current *time()* instead.
# It also returns the custom value '_tt_' which holds the amount of days elapsed between the provided *$time* and *time()* (provided date and current date).
# It used to return name values (such as days, months, etc) using language variables instead of the PHP strings, but that feature has been removed and so this is functions is probably going to get deprecated...
#}
/*
Carácter de format	Descripción	Ejemplo de valores devueltos
Día	---	---
d	Día del mes, 2 dígitos con ceros iniciales	01 a 31
D	Una representación textual de un día, tres letras	Mon hasta Sun
j	Día del mes sin ceros iniciales	1 a 31
l ('L' minúscula)	Una representación textual completa del día de la semana	Sunday hasta Saturday
N	Representación numérica ISO-8601 del día de la semana (añadido en PHP 5.1.0)	1 (para lunes) hasta 7 (para domingo)
S	Sufijo ordinal inglés para el día del mes, 2 caracteres	st, nd, rd o th. Funciona bien con j
w	Representación numérica del día de la semana	0 (para domingo) hasta 6 (para sábado)
z	El día del año (comenzando por 0)	0 hasta 365
Semana	---	---
W	Número de la semana del año ISO-8601, las semanas comienzan en lunes (añadido en PHP 4.1.0)	Ejemplo: 42 (la 42ª semana del año)
Mes	---	---
F	Una representación textual completa de un mes, como January o March	January hasta December
m	Representación numérica de una mes, con ceros iniciales	01 hasta 12
M	Una representación textual corta de un mes, tres letras	Jan hasta Dec
n	Representación numérica de un mes, sin ceros iniciales	1 hasta 12
t	Número de días del mes dado	28 hasta 31
Año	---	---
L	Si es un año bisiesto	1 si es bisiesto, 0 si no.
o	Año según el número de la semana ISO-8601. Esto tiene el mismo valor que Y, excepto que si el número de la semana ISO (W) pertenece al año anterior o siguiente, se usa ese año en su lugar. (añadido en PHP 5.1.0)	Ejemplos: 1999 o 2003
Y	Una representación numérica completa de un año, 4 dígitos	Ejemplos: 1999 o 2003
y	Una representación de dos dígitos de un año	Ejemplos: 99 o 03
Hora	---	---
a	Ante meridiem y Post meridiem en minúsculas	am o pm
A	Ante meridiem y Post meridiem en mayúsculas	AM o PM
B	Hora Internet	000 hasta 999
g	Formato de 12 horas de una hora sin ceros iniciales	1 hasta 12
G	Formato de 24 horas de una hora sin ceros iniciales	0 hasta 23
h	Formato de 12 horas de una hora con ceros iniciales	01 hasta 12
H	Formato de 24 horas de una hora con ceros iniciales	00 hasta 23
i	Minutos, con ceros iniciales	00 hasta 59
s	Segundos, con ceros iniciales	00 hasta 59
u	Microsegundos (añadido en PHP 5.2.2). Observe que date() siempre generará 000000 ya que toma un parámetro de tipo integer, mientras que DateTime::format() admite microsegundos si DateTime fue creado con microsegundos.	Ejemplo: 654321
Zona Horaria	---	---
e	Identificador de zona horaria (añadido en PHP 5.1.0)	Ejemplos: UTC, GMT, Atlantic/Azores
I (i mayúscula)	Si la fecha está en horario de verano o no	1 si está en horario de verano, 0 si no.
O	Diferencia de la hora de Greenwich (GMT) en horas	Ejemplo: +0200
P	Diferencia con la hora de Greenwich (GMT) con dos puntos entre horas y minutos (añadido en PHP 5.1.3)	Ejemplo: +02:00
T	Abreviatura de la zona horaria	Ejemplos: EST, MDT ...
Z	Índice de la zona horaria en segundos. El índice para zonas horarias al oeste de UTC siempre es negativo, y para aquellas al este de UTC es siempre positivo.	-43200 hasta 50400
Fecha/Hora Completa	---	---
c	Fecha ISO 8601 (añadido en PHP 5)	2004-02-12T15:19:21+00:00
r	Fecha con formato » RFC 2822	Ejemplo: Thu, 21 Dec 2000 16:01:07 +0200
U	Segundos desde la Época Unix (1 de Enero del 1970 00:00:00 GMT)	Vea también time()
*/
function fn_time_get_date_object($time=false)
{
	global $__LANG_MONTHS,
			$__LANG_MONTHS_SHORT,
			$__LANG_DAYS,
			$__LANG_DAYS_SHORT;
	if($time === false) $time = time();
	$string = "d-D-j-l-N-S-w-z-W-F-m-M-n-t-L-o-Y-y-a-A-B-g-G-h-H-i-s-u-e-I-O-P-T-Z-c-r-u";
	$data = explode("-",date($string,
	$time));
	$keys = explode("-",$string);
	$output = Array();
	foreach($keys as $ind => $k) $output[$k] = $data[$ind];
	
	/*$output["F"] = $__LANG_MONTHS[$output["n"]-1];
	$output["M"] = $__LANG_MONTHS_SHORT[$output["n"]-1];
	$output["D"] = $__LANG_DAYS_SHORT[$output["w"]];
	$output["l"] = $__LANG_DAYS[$output["w"]];*/
	
	/*how many days ago from today to provided date $time*/
	$now = time();
	$your_date = $time;
	$datediff = $now - $your_date;
	$tt = round($datediff / (60 * 60 * 24));		
	$output["tt"] = $tt;
	
	return $output;
}

#docs:core/Time
#{
# ### fn_time_parse_date_template($date=false,$format=#'#{d} #{F} #{Y} - #{H}:#{i}')
# Returns the provided *$date* (*false* to use current) formatted with *$format*.
# Uses melon3 string templates.
# Internally uses *fn_time_get_date_object* so it's compatible with it's custom values.
# Since *fn_time_get_date_object* no longer returns lang variables for names, and unless you specifically need the *tt* value (days diff), there's really no reason to use this function over PHP's core date formating functions.
#}
function fn_time_parse_date_template($date=false,$format='#{d} #{F} #{Y} - #{H}:#{i}') { M3::reqCore('String'); return fn_string_template($format,fn_time_get_date_object(strtotime($date))); }

#docs:core/Time
#{
# ### TIME_ZONES *(constant)*
# List of timezones, it doesn't include country codes for each zone, UTC offsets or timezone abreviations (like GMT-3), so it's not really useful as-is.
#}
$_TIME_ZONES = Array(
	'Africa/Abidjan' => Array(
		'code' => 'Africa/Abidjan',
		'name' => 'Africa/Abidjan',
	),
	'Africa/Accra' => Array(
		'code' => 'Africa/Accra',
		'name' => 'Africa/Accra',
	),
	'Africa/Addis_Ababa' => Array(
		'code' => 'Africa/Addis_Ababa',
		'name' => 'Africa/Addis_Ababa',
	),
	'Africa/Algiers' => Array(
		'code' => 'Africa/Algiers',
		'name' => 'Africa/Algiers',
	),
	'Africa/Asmara' => Array(
		'code' => 'Africa/Asmara',
		'name' => 'Africa/Asmara',
	),
	'Africa/Bamako' => Array(
		'code' => 'Africa/Bamako',
		'name' => 'Africa/Bamako',
	),
	'Africa/Bangui' => Array(
		'code' => 'Africa/Bangui',
		'name' => 'Africa/Bangui',
	),
	'Africa/Banjul' => Array(
		'code' => 'Africa/Banjul',
		'name' => 'Africa/Banjul',
	),
	'Africa/Bissau' => Array(
		'code' => 'Africa/Bissau',
		'name' => 'Africa/Bissau',
	),
	'Africa/Blantyre' => Array(
		'code' => 'Africa/Blantyre',
		'name' => 'Africa/Blantyre',
	),
	'Africa/Brazzaville' => Array(
		'code' => 'Africa/Brazzaville',
		'name' => 'Africa/Brazzaville',
	),
	'Africa/Bujumbura' => Array(
		'code' => 'Africa/Bujumbura',
		'name' => 'Africa/Bujumbura',
	),
	'Africa/Cairo' => Array(
		'code' => 'Africa/Cairo',
		'name' => 'Africa/Cairo',
	),
	'Africa/Casablanca' => Array(
		'code' => 'Africa/Casablanca',
		'name' => 'Africa/Casablanca',
	),
	'Africa/Ceuta' => Array(
		'code' => 'Africa/Ceuta',
		'name' => 'Africa/Ceuta',
	),
	'Africa/Conakry' => Array(
		'code' => 'Africa/Conakry',
		'name' => 'Africa/Conakry',
	),
	'Africa/Dakar' => Array(
		'code' => 'Africa/Dakar',
		'name' => 'Africa/Dakar',
	),
	'Africa/Dar_es_Salaam' => Array(
		'code' => 'Africa/Dar_es_Salaam',
		'name' => 'Africa/Dar_es_Salaam',
	),
	'Africa/Djibouti' => Array(
		'code' => 'Africa/Djibouti',
		'name' => 'Africa/Djibouti',
	),
	'Africa/Douala' => Array(
		'code' => 'Africa/Douala',
		'name' => 'Africa/Douala',
	),
	'Africa/El_Aaiun' => Array(
		'code' => 'Africa/El_Aaiun',
		'name' => 'Africa/El_Aaiun',
	),
	'Africa/Freetown' => Array(
		'code' => 'Africa/Freetown',
		'name' => 'Africa/Freetown',
	),
	'Africa/Gaborone' => Array(
		'code' => 'Africa/Gaborone',
		'name' => 'Africa/Gaborone',
	),
	'Africa/Harare' => Array(
		'code' => 'Africa/Harare',
		'name' => 'Africa/Harare',
	),
	'Africa/Johannesburg' => Array(
		'code' => 'Africa/Johannesburg',
		'name' => 'Africa/Johannesburg',
	),
	'Africa/Juba' => Array(
		'code' => 'Africa/Juba',
		'name' => 'Africa/Juba',
	),
	'Africa/Kampala' => Array(
		'code' => 'Africa/Kampala',
		'name' => 'Africa/Kampala',
	),
	'Africa/Khartoum' => Array(
		'code' => 'Africa/Khartoum',
		'name' => 'Africa/Khartoum',
	),
	'Africa/Kigali' => Array(
		'code' => 'Africa/Kigali',
		'name' => 'Africa/Kigali',
	),
	'Africa/Kinshasa' => Array(
		'code' => 'Africa/Kinshasa',
		'name' => 'Africa/Kinshasa',
	),
	'Africa/Lagos' => Array(
		'code' => 'Africa/Lagos',
		'name' => 'Africa/Lagos',
	),
	'Africa/Libreville' => Array(
		'code' => 'Africa/Libreville',
		'name' => 'Africa/Libreville',
	),
	'Africa/Lome' => Array(
		'code' => 'Africa/Lome',
		'name' => 'Africa/Lome',
	),
	'Africa/Luanda' => Array(
		'code' => 'Africa/Luanda',
		'name' => 'Africa/Luanda',
	),
	'Africa/Lubumbashi' => Array(
		'code' => 'Africa/Lubumbashi',
		'name' => 'Africa/Lubumbashi',
	),
	'Africa/Lusaka' => Array(
		'code' => 'Africa/Lusaka',
		'name' => 'Africa/Lusaka',
	),
	'Africa/Malabo' => Array(
		'code' => 'Africa/Malabo',
		'name' => 'Africa/Malabo',
	),
	'Africa/Maputo' => Array(
		'code' => 'Africa/Maputo',
		'name' => 'Africa/Maputo',
	),
	'Africa/Maseru' => Array(
		'code' => 'Africa/Maseru',
		'name' => 'Africa/Maseru',
	),
	'Africa/Mbabane' => Array(
		'code' => 'Africa/Mbabane',
		'name' => 'Africa/Mbabane',
	),
	'Africa/Mogadishu' => Array(
		'code' => 'Africa/Mogadishu',
		'name' => 'Africa/Mogadishu',
	),
	'Africa/Monrovia' => Array(
		'code' => 'Africa/Monrovia',
		'name' => 'Africa/Monrovia',
	),
	'Africa/Nairobi' => Array(
		'code' => 'Africa/Nairobi',
		'name' => 'Africa/Nairobi',
	),
	'Africa/Ndjamena' => Array(
		'code' => 'Africa/Ndjamena',
		'name' => 'Africa/Ndjamena',
	),
	'Africa/Niamey' => Array(
		'code' => 'Africa/Niamey',
		'name' => 'Africa/Niamey',
	),
	'Africa/Nouakchott' => Array(
		'code' => 'Africa/Nouakchott',
		'name' => 'Africa/Nouakchott',
	),
	'Africa/Ouagadougou' => Array(
		'code' => 'Africa/Ouagadougou',
		'name' => 'Africa/Ouagadougou',
	),
	'Africa/Porto-Novo' => Array(
		'code' => 'Africa/Porto-Novo',
		'name' => 'Africa/Porto-Novo',
	),
	'Africa/Sao_Tome' => Array(
		'code' => 'Africa/Sao_Tome',
		'name' => 'Africa/Sao_Tome',
	),
	'Africa/Tripoli' => Array(
		'code' => 'Africa/Tripoli',
		'name' => 'Africa/Tripoli',
	),
	'Africa/Tunis' => Array(
		'code' => 'Africa/Tunis',
		'name' => 'Africa/Tunis',
	),
	'Africa/Windhoek' => Array(
		'code' => 'Africa/Windhoek',
		'name' => 'Africa/Windhoek',
	),
	'America/Adak' => Array(
		'code' => 'America/Adak',
		'name' => 'America/Adak',
	),
	'America/Anchorage' => Array(
		'code' => 'America/Anchorage',
		'name' => 'America/Anchorage',
	),
	'America/Anguilla' => Array(
		'code' => 'America/Anguilla',
		'name' => 'America/Anguilla',
	),
	'America/Antigua' => Array(
		'code' => 'America/Antigua',
		'name' => 'America/Antigua',
	),
	'America/Araguaina' => Array(
		'code' => 'America/Araguaina',
		'name' => 'America/Araguaina',
	),
	'America/Argentina/Buenos_Aires' => Array(
		'code' => 'America/Argentina/Buenos_Aires',
		'name' => 'America/Argentina/Buenos_Aires',
	),
	'America/Argentina/Catamarca' => Array(
		'code' => 'America/Argentina/Catamarca',
		'name' => 'America/Argentina/Catamarca',
	),
	'America/Argentina/Cordoba' => Array(
		'code' => 'America/Argentina/Cordoba',
		'name' => 'America/Argentina/Cordoba',
	),
	'America/Argentina/Jujuy' => Array(
		'code' => 'America/Argentina/Jujuy',
		'name' => 'America/Argentina/Jujuy',
	),
	'America/Argentina/La_Rioja' => Array(
		'code' => 'America/Argentina/La_Rioja',
		'name' => 'America/Argentina/La_Rioja',
	),
	'America/Argentina/Mendoza' => Array(
		'code' => 'America/Argentina/Mendoza',
		'name' => 'America/Argentina/Mendoza',
	),
	'America/Argentina/Rio_Gallegos' => Array(
		'code' => 'America/Argentina/Rio_Gallegos',
		'name' => 'America/Argentina/Rio_Gallegos',
	),
	'America/Argentina/Salta' => Array(
		'code' => 'America/Argentina/Salta',
		'name' => 'America/Argentina/Salta',
	),
	'America/Argentina/San_Juan' => Array(
		'code' => 'America/Argentina/San_Juan',
		'name' => 'America/Argentina/San_Juan',
	),
	'America/Argentina/San_Luis' => Array(
		'code' => 'America/Argentina/San_Luis',
		'name' => 'America/Argentina/San_Luis',
	),
	'America/Argentina/Tucuman' => Array(
		'code' => 'America/Argentina/Tucuman',
		'name' => 'America/Argentina/Tucuman',
	),
	'America/Argentina/Ushuaia' => Array(
		'code' => 'America/Argentina/Ushuaia',
		'name' => 'America/Argentina/Ushuaia',
	),
	'America/Aruba' => Array(
		'code' => 'America/Aruba',
		'name' => 'America/Aruba',
	),
	'America/Asuncion' => Array(
		'code' => 'America/Asuncion',
		'name' => 'America/Asuncion',
	),
	'America/Atikokan' => Array(
		'code' => 'America/Atikokan',
		'name' => 'America/Atikokan',
	),
	'America/Bahia' => Array(
		'code' => 'America/Bahia',
		'name' => 'America/Bahia',
	),
	'America/Bahia_Banderas' => Array(
		'code' => 'America/Bahia_Banderas',
		'name' => 'America/Bahia_Banderas',
	),
	'America/Barbados' => Array(
		'code' => 'America/Barbados',
		'name' => 'America/Barbados',
	),
	'America/Belem' => Array(
		'code' => 'America/Belem',
		'name' => 'America/Belem',
	),
	'America/Belize' => Array(
		'code' => 'America/Belize',
		'name' => 'America/Belize',
	),
	'America/Blanc-Sablon' => Array(
		'code' => 'America/Blanc-Sablon',
		'name' => 'America/Blanc-Sablon',
	),
	'America/Boa_Vista' => Array(
		'code' => 'America/Boa_Vista',
		'name' => 'America/Boa_Vista',
	),
	'America/Bogota' => Array(
		'code' => 'America/Bogota',
		'name' => 'America/Bogota',
	),
	'America/Boise' => Array(
		'code' => 'America/Boise',
		'name' => 'America/Boise',
	),
	'America/Cambridge_Bay' => Array(
		'code' => 'America/Cambridge_Bay',
		'name' => 'America/Cambridge_Bay',
	),
	'America/Campo_Grande' => Array(
		'code' => 'America/Campo_Grande',
		'name' => 'America/Campo_Grande',
	),
	'America/Cancun' => Array(
		'code' => 'America/Cancun',
		'name' => 'America/Cancun',
	),
	'America/Caracas' => Array(
		'code' => 'America/Caracas',
		'name' => 'America/Caracas',
	),
	'America/Cayenne' => Array(
		'code' => 'America/Cayenne',
		'name' => 'America/Cayenne',
	),
	'America/Cayman' => Array(
		'code' => 'America/Cayman',
		'name' => 'America/Cayman',
	),
	'America/Chicago' => Array(
		'code' => 'America/Chicago',
		'name' => 'America/Chicago',
	),
	'America/Chihuahua' => Array(
		'code' => 'America/Chihuahua',
		'name' => 'America/Chihuahua',
	),
	'America/Costa_Rica' => Array(
		'code' => 'America/Costa_Rica',
		'name' => 'America/Costa_Rica',
	),
	'America/Creston' => Array(
		'code' => 'America/Creston',
		'name' => 'America/Creston',
	),
	'America/Cuiaba' => Array(
		'code' => 'America/Cuiaba',
		'name' => 'America/Cuiaba',
	),
	'America/Curacao' => Array(
		'code' => 'America/Curacao',
		'name' => 'America/Curacao',
	),
	'America/Danmarkshavn' => Array(
		'code' => 'America/Danmarkshavn',
		'name' => 'America/Danmarkshavn',
	),
	'America/Dawson' => Array(
		'code' => 'America/Dawson',
		'name' => 'America/Dawson',
	),
	'America/Dawson_Creek' => Array(
		'code' => 'America/Dawson_Creek',
		'name' => 'America/Dawson_Creek',
	),
	'America/Denver' => Array(
		'code' => 'America/Denver',
		'name' => 'America/Denver',
	),
	'America/Detroit' => Array(
		'code' => 'America/Detroit',
		'name' => 'America/Detroit',
	),
	'America/Dominica' => Array(
		'code' => 'America/Dominica',
		'name' => 'America/Dominica',
	),
	'America/Edmonton' => Array(
		'code' => 'America/Edmonton',
		'name' => 'America/Edmonton',
	),
	'America/Eirunepe' => Array(
		'code' => 'America/Eirunepe',
		'name' => 'America/Eirunepe',
	),
	'America/El_Salvador' => Array(
		'code' => 'America/El_Salvador',
		'name' => 'America/El_Salvador',
	),
	'America/Fort_Nelson' => Array(
		'code' => 'America/Fort_Nelson',
		'name' => 'America/Fort_Nelson',
	),
	'America/Fortaleza' => Array(
		'code' => 'America/Fortaleza',
		'name' => 'America/Fortaleza',
	),
	'America/Glace_Bay' => Array(
		'code' => 'America/Glace_Bay',
		'name' => 'America/Glace_Bay',
	),
	'America/Goose_Bay' => Array(
		'code' => 'America/Goose_Bay',
		'name' => 'America/Goose_Bay',
	),
	'America/Grand_Turk' => Array(
		'code' => 'America/Grand_Turk',
		'name' => 'America/Grand_Turk',
	),
	'America/Grenada' => Array(
		'code' => 'America/Grenada',
		'name' => 'America/Grenada',
	),
	'America/Guadeloupe' => Array(
		'code' => 'America/Guadeloupe',
		'name' => 'America/Guadeloupe',
	),
	'America/Guatemala' => Array(
		'code' => 'America/Guatemala',
		'name' => 'America/Guatemala',
	),
	'America/Guayaquil' => Array(
		'code' => 'America/Guayaquil',
		'name' => 'America/Guayaquil',
	),
	'America/Guyana' => Array(
		'code' => 'America/Guyana',
		'name' => 'America/Guyana',
	),
	'America/Halifax' => Array(
		'code' => 'America/Halifax',
		'name' => 'America/Halifax',
	),
	'America/Havana' => Array(
		'code' => 'America/Havana',
		'name' => 'America/Havana',
	),
	'America/Hermosillo' => Array(
		'code' => 'America/Hermosillo',
		'name' => 'America/Hermosillo',
	),
	'America/Indiana/Indianapolis' => Array(
		'code' => 'America/Indiana/Indianapolis',
		'name' => 'America/Indiana/Indianapolis',
	),
	'America/Indiana/Knox' => Array(
		'code' => 'America/Indiana/Knox',
		'name' => 'America/Indiana/Knox',
	),
	'America/Indiana/Marengo' => Array(
		'code' => 'America/Indiana/Marengo',
		'name' => 'America/Indiana/Marengo',
	),
	'America/Indiana/Petersburg' => Array(
		'code' => 'America/Indiana/Petersburg',
		'name' => 'America/Indiana/Petersburg',
	),
	'America/Indiana/Tell_City' => Array(
		'code' => 'America/Indiana/Tell_City',
		'name' => 'America/Indiana/Tell_City',
	),
	'America/Indiana/Vevay' => Array(
		'code' => 'America/Indiana/Vevay',
		'name' => 'America/Indiana/Vevay',
	),
	'America/Indiana/Vincennes' => Array(
		'code' => 'America/Indiana/Vincennes',
		'name' => 'America/Indiana/Vincennes',
	),
	'America/Indiana/Winamac' => Array(
		'code' => 'America/Indiana/Winamac',
		'name' => 'America/Indiana/Winamac',
	),
	'America/Inuvik' => Array(
		'code' => 'America/Inuvik',
		'name' => 'America/Inuvik',
	),
	'America/Iqaluit' => Array(
		'code' => 'America/Iqaluit',
		'name' => 'America/Iqaluit',
	),
	'America/Jamaica' => Array(
		'code' => 'America/Jamaica',
		'name' => 'America/Jamaica',
	),
	'America/Juneau' => Array(
		'code' => 'America/Juneau',
		'name' => 'America/Juneau',
	),
	'America/Kentucky/Louisville' => Array(
		'code' => 'America/Kentucky/Louisville',
		'name' => 'America/Kentucky/Louisville',
	),
	'America/Kentucky/Monticello' => Array(
		'code' => 'America/Kentucky/Monticello',
		'name' => 'America/Kentucky/Monticello',
	),
	'America/Kralendijk' => Array(
		'code' => 'America/Kralendijk',
		'name' => 'America/Kralendijk',
	),
	'America/La_Paz' => Array(
		'code' => 'America/La_Paz',
		'name' => 'America/La_Paz',
	),
	'America/Lima' => Array(
		'code' => 'America/Lima',
		'name' => 'America/Lima',
	),
	'America/Los_Angeles' => Array(
		'code' => 'America/Los_Angeles',
		'name' => 'America/Los_Angeles',
	),
	'America/Lower_Princes' => Array(
		'code' => 'America/Lower_Princes',
		'name' => 'America/Lower_Princes',
	),
	'America/Maceio' => Array(
		'code' => 'America/Maceio',
		'name' => 'America/Maceio',
	),
	'America/Managua' => Array(
		'code' => 'America/Managua',
		'name' => 'America/Managua',
	),
	'America/Manaus' => Array(
		'code' => 'America/Manaus',
		'name' => 'America/Manaus',
	),
	'America/Marigot' => Array(
		'code' => 'America/Marigot',
		'name' => 'America/Marigot',
	),
	'America/Martinique' => Array(
		'code' => 'America/Martinique',
		'name' => 'America/Martinique',
	),
	'America/Matamoros' => Array(
		'code' => 'America/Matamoros',
		'name' => 'America/Matamoros',
	),
	'America/Mazatlan' => Array(
		'code' => 'America/Mazatlan',
		'name' => 'America/Mazatlan',
	),
	'America/Menominee' => Array(
		'code' => 'America/Menominee',
		'name' => 'America/Menominee',
	),
	'America/Merida' => Array(
		'code' => 'America/Merida',
		'name' => 'America/Merida',
	),
	'America/Metlakatla' => Array(
		'code' => 'America/Metlakatla',
		'name' => 'America/Metlakatla',
	),
	'America/Mexico_City' => Array(
		'code' => 'America/Mexico_City',
		'name' => 'America/Mexico_City',
	),
	'America/Miquelon' => Array(
		'code' => 'America/Miquelon',
		'name' => 'America/Miquelon',
	),
	'America/Moncton' => Array(
		'code' => 'America/Moncton',
		'name' => 'America/Moncton',
	),
	'America/Monterrey' => Array(
		'code' => 'America/Monterrey',
		'name' => 'America/Monterrey',
	),
	'America/Montevideo' => Array(
		'code' => 'America/Montevideo',
		'name' => 'America/Montevideo',
	),
	'America/Montserrat' => Array(
		'code' => 'America/Montserrat',
		'name' => 'America/Montserrat',
	),
	'America/Nassau' => Array(
		'code' => 'America/Nassau',
		'name' => 'America/Nassau',
	),
	'America/New_York' => Array(
		'code' => 'America/New_York',
		'name' => 'America/New_York',
	),
	'America/Nipigon' => Array(
		'code' => 'America/Nipigon',
		'name' => 'America/Nipigon',
	),
	'America/Nome' => Array(
		'code' => 'America/Nome',
		'name' => 'America/Nome',
	),
	'America/Noronha' => Array(
		'code' => 'America/Noronha',
		'name' => 'America/Noronha',
	),
	'America/North_Dakota/Beulah' => Array(
		'code' => 'America/North_Dakota/Beulah',
		'name' => 'America/North_Dakota/Beulah',
	),
	'America/North_Dakota/Center' => Array(
		'code' => 'America/North_Dakota/Center',
		'name' => 'America/North_Dakota/Center',
	),
	'America/North_Dakota/New_Salem' => Array(
		'code' => 'America/North_Dakota/New_Salem',
		'name' => 'America/North_Dakota/New_Salem',
	),
	'America/Nuuk' => Array(
		'code' => 'America/Nuuk',
		'name' => 'America/Nuuk',
	),
	'America/Ojinaga' => Array(
		'code' => 'America/Ojinaga',
		'name' => 'America/Ojinaga',
	),
	'America/Panama' => Array(
		'code' => 'America/Panama',
		'name' => 'America/Panama',
	),
	'America/Pangnirtung' => Array(
		'code' => 'America/Pangnirtung',
		'name' => 'America/Pangnirtung',
	),
	'America/Paramaribo' => Array(
		'code' => 'America/Paramaribo',
		'name' => 'America/Paramaribo',
	),
	'America/Phoenix' => Array(
		'code' => 'America/Phoenix',
		'name' => 'America/Phoenix',
	),
	'America/Port-au-Prince' => Array(
		'code' => 'America/Port-au-Prince',
		'name' => 'America/Port-au-Prince',
	),
	'America/Port_of_Spain' => Array(
		'code' => 'America/Port_of_Spain',
		'name' => 'America/Port_of_Spain',
	),
	'America/Porto_Velho' => Array(
		'code' => 'America/Porto_Velho',
		'name' => 'America/Porto_Velho',
	),
	'America/Puerto_Rico' => Array(
		'code' => 'America/Puerto_Rico',
		'name' => 'America/Puerto_Rico',
	),
	'America/Punta_Arenas' => Array(
		'code' => 'America/Punta_Arenas',
		'name' => 'America/Punta_Arenas',
	),
	'America/Rainy_River' => Array(
		'code' => 'America/Rainy_River',
		'name' => 'America/Rainy_River',
	),
	'America/Rankin_Inlet' => Array(
		'code' => 'America/Rankin_Inlet',
		'name' => 'America/Rankin_Inlet',
	),
	'America/Recife' => Array(
		'code' => 'America/Recife',
		'name' => 'America/Recife',
	),
	'America/Regina' => Array(
		'code' => 'America/Regina',
		'name' => 'America/Regina',
	),
	'America/Resolute' => Array(
		'code' => 'America/Resolute',
		'name' => 'America/Resolute',
	),
	'America/Rio_Branco' => Array(
		'code' => 'America/Rio_Branco',
		'name' => 'America/Rio_Branco',
	),
	'America/Santarem' => Array(
		'code' => 'America/Santarem',
		'name' => 'America/Santarem',
	),
	'America/Santiago' => Array(
		'code' => 'America/Santiago',
		'name' => 'America/Santiago',
	),
	'America/Santo_Domingo' => Array(
		'code' => 'America/Santo_Domingo',
		'name' => 'America/Santo_Domingo',
	),
	'America/Sao_Paulo' => Array(
		'code' => 'America/Sao_Paulo',
		'name' => 'America/Sao_Paulo',
	),
	'America/Scoresbysund' => Array(
		'code' => 'America/Scoresbysund',
		'name' => 'America/Scoresbysund',
	),
	'America/Sitka' => Array(
		'code' => 'America/Sitka',
		'name' => 'America/Sitka',
	),
	'America/St_Barthelemy' => Array(
		'code' => 'America/St_Barthelemy',
		'name' => 'America/St_Barthelemy',
	),
	'America/St_Johns' => Array(
		'code' => 'America/St_Johns',
		'name' => 'America/St_Johns',
	),
	'America/St_Kitts' => Array(
		'code' => 'America/St_Kitts',
		'name' => 'America/St_Kitts',
	),
	'America/St_Lucia' => Array(
		'code' => 'America/St_Lucia',
		'name' => 'America/St_Lucia',
	),
	'America/St_Thomas' => Array(
		'code' => 'America/St_Thomas',
		'name' => 'America/St_Thomas',
	),
	'America/St_Vincent' => Array(
		'code' => 'America/St_Vincent',
		'name' => 'America/St_Vincent',
	),
	'America/Swift_Current' => Array(
		'code' => 'America/Swift_Current',
		'name' => 'America/Swift_Current',
	),
	'America/Tegucigalpa' => Array(
		'code' => 'America/Tegucigalpa',
		'name' => 'America/Tegucigalpa',
	),
	'America/Thule' => Array(
		'code' => 'America/Thule',
		'name' => 'America/Thule',
	),
	'America/Thunder_Bay' => Array(
		'code' => 'America/Thunder_Bay',
		'name' => 'America/Thunder_Bay',
	),
	'America/Tijuana' => Array(
		'code' => 'America/Tijuana',
		'name' => 'America/Tijuana',
	),
	'America/Toronto' => Array(
		'code' => 'America/Toronto',
		'name' => 'America/Toronto',
	),
	'America/Tortola' => Array(
		'code' => 'America/Tortola',
		'name' => 'America/Tortola',
	),
	'America/Vancouver' => Array(
		'code' => 'America/Vancouver',
		'name' => 'America/Vancouver',
	),
	'America/Whitehorse' => Array(
		'code' => 'America/Whitehorse',
		'name' => 'America/Whitehorse',
	),
	'America/Winnipeg' => Array(
		'code' => 'America/Winnipeg',
		'name' => 'America/Winnipeg',
	),
	'America/Yakutat' => Array(
		'code' => 'America/Yakutat',
		'name' => 'America/Yakutat',
	),
	'America/Yellowknife' => Array(
		'code' => 'America/Yellowknife',
		'name' => 'America/Yellowknife',
	),
	'Antarctica/Casey' => Array(
		'code' => 'Antarctica/Casey',
		'name' => 'Antarctica/Casey',
	),
	'Antarctica/Davis' => Array(
		'code' => 'Antarctica/Davis',
		'name' => 'Antarctica/Davis',
	),
	'Antarctica/DumontDUrville' => Array(
		'code' => 'Antarctica/DumontDUrville',
		'name' => 'Antarctica/DumontDUrville',
	),
	'Antarctica/Macquarie' => Array(
		'code' => 'Antarctica/Macquarie',
		'name' => 'Antarctica/Macquarie',
	),
	'Antarctica/Mawson' => Array(
		'code' => 'Antarctica/Mawson',
		'name' => 'Antarctica/Mawson',
	),
	'Antarctica/McMurdo' => Array(
		'code' => 'Antarctica/McMurdo',
		'name' => 'Antarctica/McMurdo',
	),
	'Antarctica/Palmer' => Array(
		'code' => 'Antarctica/Palmer',
		'name' => 'Antarctica/Palmer',
	),
	'Antarctica/Rothera' => Array(
		'code' => 'Antarctica/Rothera',
		'name' => 'Antarctica/Rothera',
	),
	'Antarctica/Syowa' => Array(
		'code' => 'Antarctica/Syowa',
		'name' => 'Antarctica/Syowa',
	),
	'Antarctica/Troll' => Array(
		'code' => 'Antarctica/Troll',
		'name' => 'Antarctica/Troll',
	),
	'Antarctica/Vostok' => Array(
		'code' => 'Antarctica/Vostok',
		'name' => 'Antarctica/Vostok',
	),
	'Arctic/Longyearbyen' => Array(
		'code' => 'Arctic/Longyearbyen',
		'name' => 'Arctic/Longyearbyen',
	),
	'Asia/Aden' => Array(
		'code' => 'Asia/Aden',
		'name' => 'Asia/Aden',
	),
	'Asia/Almaty' => Array(
		'code' => 'Asia/Almaty',
		'name' => 'Asia/Almaty',
	),
	'Asia/Amman' => Array(
		'code' => 'Asia/Amman',
		'name' => 'Asia/Amman',
	),
	'Asia/Anadyr' => Array(
		'code' => 'Asia/Anadyr',
		'name' => 'Asia/Anadyr',
	),
	'Asia/Aqtau' => Array(
		'code' => 'Asia/Aqtau',
		'name' => 'Asia/Aqtau',
	),
	'Asia/Aqtobe' => Array(
		'code' => 'Asia/Aqtobe',
		'name' => 'Asia/Aqtobe',
	),
	'Asia/Ashgabat' => Array(
		'code' => 'Asia/Ashgabat',
		'name' => 'Asia/Ashgabat',
	),
	'Asia/Atyrau' => Array(
		'code' => 'Asia/Atyrau',
		'name' => 'Asia/Atyrau',
	),
	'Asia/Baghdad' => Array(
		'code' => 'Asia/Baghdad',
		'name' => 'Asia/Baghdad',
	),
	'Asia/Bahrain' => Array(
		'code' => 'Asia/Bahrain',
		'name' => 'Asia/Bahrain',
	),
	'Asia/Baku' => Array(
		'code' => 'Asia/Baku',
		'name' => 'Asia/Baku',
	),
	'Asia/Bangkok' => Array(
		'code' => 'Asia/Bangkok',
		'name' => 'Asia/Bangkok',
	),
	'Asia/Barnaul' => Array(
		'code' => 'Asia/Barnaul',
		'name' => 'Asia/Barnaul',
	),
	'Asia/Beirut' => Array(
		'code' => 'Asia/Beirut',
		'name' => 'Asia/Beirut',
	),
	'Asia/Bishkek' => Array(
		'code' => 'Asia/Bishkek',
		'name' => 'Asia/Bishkek',
	),
	'Asia/Brunei' => Array(
		'code' => 'Asia/Brunei',
		'name' => 'Asia/Brunei',
	),
	'Asia/Chita' => Array(
		'code' => 'Asia/Chita',
		'name' => 'Asia/Chita',
	),
	'Asia/Choibalsan' => Array(
		'code' => 'Asia/Choibalsan',
		'name' => 'Asia/Choibalsan',
	),
	'Asia/Colombo' => Array(
		'code' => 'Asia/Colombo',
		'name' => 'Asia/Colombo',
	),
	'Asia/Damascus' => Array(
		'code' => 'Asia/Damascus',
		'name' => 'Asia/Damascus',
	),
	'Asia/Dhaka' => Array(
		'code' => 'Asia/Dhaka',
		'name' => 'Asia/Dhaka',
	),
	'Asia/Dili' => Array(
		'code' => 'Asia/Dili',
		'name' => 'Asia/Dili',
	),
	'Asia/Dubai' => Array(
		'code' => 'Asia/Dubai',
		'name' => 'Asia/Dubai',
	),
	'Asia/Dushanbe' => Array(
		'code' => 'Asia/Dushanbe',
		'name' => 'Asia/Dushanbe',
	),
	'Asia/Famagusta' => Array(
		'code' => 'Asia/Famagusta',
		'name' => 'Asia/Famagusta',
	),
	'Asia/Gaza' => Array(
		'code' => 'Asia/Gaza',
		'name' => 'Asia/Gaza',
	),
	'Asia/Hebron' => Array(
		'code' => 'Asia/Hebron',
		'name' => 'Asia/Hebron',
	),
	'Asia/Ho_Chi_Minh' => Array(
		'code' => 'Asia/Ho_Chi_Minh',
		'name' => 'Asia/Ho_Chi_Minh',
	),
	'Asia/Hong_Kong' => Array(
		'code' => 'Asia/Hong_Kong',
		'name' => 'Asia/Hong_Kong',
	),
	'Asia/Hovd' => Array(
		'code' => 'Asia/Hovd',
		'name' => 'Asia/Hovd',
	),
	'Asia/Irkutsk' => Array(
		'code' => 'Asia/Irkutsk',
		'name' => 'Asia/Irkutsk',
	),
	'Asia/Jakarta' => Array(
		'code' => 'Asia/Jakarta',
		'name' => 'Asia/Jakarta',
	),
	'Asia/Jayapura' => Array(
		'code' => 'Asia/Jayapura',
		'name' => 'Asia/Jayapura',
	),
	'Asia/Jerusalem' => Array(
		'code' => 'Asia/Jerusalem',
		'name' => 'Asia/Jerusalem',
	),
	'Asia/Kabul' => Array(
		'code' => 'Asia/Kabul',
		'name' => 'Asia/Kabul',
	),
	'Asia/Kamchatka' => Array(
		'code' => 'Asia/Kamchatka',
		'name' => 'Asia/Kamchatka',
	),
	'Asia/Karachi' => Array(
		'code' => 'Asia/Karachi',
		'name' => 'Asia/Karachi',
	),
	'Asia/Kathmandu' => Array(
		'code' => 'Asia/Kathmandu',
		'name' => 'Asia/Kathmandu',
	),
	'Asia/Khandyga' => Array(
		'code' => 'Asia/Khandyga',
		'name' => 'Asia/Khandyga',
	),
	'Asia/Kolkata' => Array(
		'code' => 'Asia/Kolkata',
		'name' => 'Asia/Kolkata',
	),
	'Asia/Krasnoyarsk' => Array(
		'code' => 'Asia/Krasnoyarsk',
		'name' => 'Asia/Krasnoyarsk',
	),
	'Asia/Kuala_Lumpur' => Array(
		'code' => 'Asia/Kuala_Lumpur',
		'name' => 'Asia/Kuala_Lumpur',
	),
	'Asia/Kuching' => Array(
		'code' => 'Asia/Kuching',
		'name' => 'Asia/Kuching',
	),
	'Asia/Kuwait' => Array(
		'code' => 'Asia/Kuwait',
		'name' => 'Asia/Kuwait',
	),
	'Asia/Macau' => Array(
		'code' => 'Asia/Macau',
		'name' => 'Asia/Macau',
	),
	'Asia/Magadan' => Array(
		'code' => 'Asia/Magadan',
		'name' => 'Asia/Magadan',
	),
	'Asia/Makassar' => Array(
		'code' => 'Asia/Makassar',
		'name' => 'Asia/Makassar',
	),
	'Asia/Manila' => Array(
		'code' => 'Asia/Manila',
		'name' => 'Asia/Manila',
	),
	'Asia/Muscat' => Array(
		'code' => 'Asia/Muscat',
		'name' => 'Asia/Muscat',
	),
	'Asia/Nicosia' => Array(
		'code' => 'Asia/Nicosia',
		'name' => 'Asia/Nicosia',
	),
	'Asia/Novokuznetsk' => Array(
		'code' => 'Asia/Novokuznetsk',
		'name' => 'Asia/Novokuznetsk',
	),
	'Asia/Novosibirsk' => Array(
		'code' => 'Asia/Novosibirsk',
		'name' => 'Asia/Novosibirsk',
	),
	'Asia/Omsk' => Array(
		'code' => 'Asia/Omsk',
		'name' => 'Asia/Omsk',
	),
	'Asia/Oral' => Array(
		'code' => 'Asia/Oral',
		'name' => 'Asia/Oral',
	),
	'Asia/Phnom_Penh' => Array(
		'code' => 'Asia/Phnom_Penh',
		'name' => 'Asia/Phnom_Penh',
	),
	'Asia/Pontianak' => Array(
		'code' => 'Asia/Pontianak',
		'name' => 'Asia/Pontianak',
	),
	'Asia/Pyongyang' => Array(
		'code' => 'Asia/Pyongyang',
		'name' => 'Asia/Pyongyang',
	),
	'Asia/Qatar' => Array(
		'code' => 'Asia/Qatar',
		'name' => 'Asia/Qatar',
	),
	'Asia/Qostanay' => Array(
		'code' => 'Asia/Qostanay',
		'name' => 'Asia/Qostanay',
	),
	'Asia/Qyzylorda' => Array(
		'code' => 'Asia/Qyzylorda',
		'name' => 'Asia/Qyzylorda',
	),
	'Asia/Riyadh' => Array(
		'code' => 'Asia/Riyadh',
		'name' => 'Asia/Riyadh',
	),
	'Asia/Sakhalin' => Array(
		'code' => 'Asia/Sakhalin',
		'name' => 'Asia/Sakhalin',
	),
	'Asia/Samarkand' => Array(
		'code' => 'Asia/Samarkand',
		'name' => 'Asia/Samarkand',
	),
	'Asia/Seoul' => Array(
		'code' => 'Asia/Seoul',
		'name' => 'Asia/Seoul',
	),
	'Asia/Shanghai' => Array(
		'code' => 'Asia/Shanghai',
		'name' => 'Asia/Shanghai',
	),
	'Asia/Singapore' => Array(
		'code' => 'Asia/Singapore',
		'name' => 'Asia/Singapore',
	),
	'Asia/Srednekolymsk' => Array(
		'code' => 'Asia/Srednekolymsk',
		'name' => 'Asia/Srednekolymsk',
	),
	'Asia/Taipei' => Array(
		'code' => 'Asia/Taipei',
		'name' => 'Asia/Taipei',
	),
	'Asia/Tashkent' => Array(
		'code' => 'Asia/Tashkent',
		'name' => 'Asia/Tashkent',
	),
	'Asia/Tbilisi' => Array(
		'code' => 'Asia/Tbilisi',
		'name' => 'Asia/Tbilisi',
	),
	'Asia/Tehran' => Array(
		'code' => 'Asia/Tehran',
		'name' => 'Asia/Tehran',
	),
	'Asia/Thimphu' => Array(
		'code' => 'Asia/Thimphu',
		'name' => 'Asia/Thimphu',
	),
	'Asia/Tokyo' => Array(
		'code' => 'Asia/Tokyo',
		'name' => 'Asia/Tokyo',
	),
	'Asia/Tomsk' => Array(
		'code' => 'Asia/Tomsk',
		'name' => 'Asia/Tomsk',
	),
	'Asia/Ulaanbaatar' => Array(
		'code' => 'Asia/Ulaanbaatar',
		'name' => 'Asia/Ulaanbaatar',
	),
	'Asia/Urumqi' => Array(
		'code' => 'Asia/Urumqi',
		'name' => 'Asia/Urumqi',
	),
	'Asia/Ust-Nera' => Array(
		'code' => 'Asia/Ust-Nera',
		'name' => 'Asia/Ust-Nera',
	),
	'Asia/Vientiane' => Array(
		'code' => 'Asia/Vientiane',
		'name' => 'Asia/Vientiane',
	),
	'Asia/Vladivostok' => Array(
		'code' => 'Asia/Vladivostok',
		'name' => 'Asia/Vladivostok',
	),
	'Asia/Yakutsk' => Array(
		'code' => 'Asia/Yakutsk',
		'name' => 'Asia/Yakutsk',
	),
	'Asia/Yangon' => Array(
		'code' => 'Asia/Yangon',
		'name' => 'Asia/Yangon',
	),
	'Asia/Yekaterinburg' => Array(
		'code' => 'Asia/Yekaterinburg',
		'name' => 'Asia/Yekaterinburg',
	),
	'Asia/Yerevan' => Array(
		'code' => 'Asia/Yerevan',
		'name' => 'Asia/Yerevan',
	),
	'Atlantic/Azores' => Array(
		'code' => 'Atlantic/Azores',
		'name' => 'Atlantic/Azores',
	),
	'Atlantic/Bermuda' => Array(
		'code' => 'Atlantic/Bermuda',
		'name' => 'Atlantic/Bermuda',
	),
	'Atlantic/Canary' => Array(
		'code' => 'Atlantic/Canary',
		'name' => 'Atlantic/Canary',
	),
	'Atlantic/Cape_Verde' => Array(
		'code' => 'Atlantic/Cape_Verde',
		'name' => 'Atlantic/Cape_Verde',
	),
	'Atlantic/Faroe' => Array(
		'code' => 'Atlantic/Faroe',
		'name' => 'Atlantic/Faroe',
	),
	'Atlantic/Madeira' => Array(
		'code' => 'Atlantic/Madeira',
		'name' => 'Atlantic/Madeira',
	),
	'Atlantic/Reykjavik' => Array(
		'code' => 'Atlantic/Reykjavik',
		'name' => 'Atlantic/Reykjavik',
	),
	'Atlantic/South_Georgia' => Array(
		'code' => 'Atlantic/South_Georgia',
		'name' => 'Atlantic/South_Georgia',
	),
	'Atlantic/St_Helena' => Array(
		'code' => 'Atlantic/St_Helena',
		'name' => 'Atlantic/St_Helena',
	),
	'Atlantic/Stanley' => Array(
		'code' => 'Atlantic/Stanley',
		'name' => 'Atlantic/Stanley',
	),
	'Australia/Adelaide' => Array(
		'code' => 'Australia/Adelaide',
		'name' => 'Australia/Adelaide',
	),
	'Australia/Brisbane' => Array(
		'code' => 'Australia/Brisbane',
		'name' => 'Australia/Brisbane',
	),
	'Australia/Broken_Hill' => Array(
		'code' => 'Australia/Broken_Hill',
		'name' => 'Australia/Broken_Hill',
	),
	'Australia/Darwin' => Array(
		'code' => 'Australia/Darwin',
		'name' => 'Australia/Darwin',
	),
	'Australia/Eucla' => Array(
		'code' => 'Australia/Eucla',
		'name' => 'Australia/Eucla',
	),
	'Australia/Hobart' => Array(
		'code' => 'Australia/Hobart',
		'name' => 'Australia/Hobart',
	),
	'Australia/Lindeman' => Array(
		'code' => 'Australia/Lindeman',
		'name' => 'Australia/Lindeman',
	),
	'Australia/Lord_Howe' => Array(
		'code' => 'Australia/Lord_Howe',
		'name' => 'Australia/Lord_Howe',
	),
	'Australia/Melbourne' => Array(
		'code' => 'Australia/Melbourne',
		'name' => 'Australia/Melbourne',
	),
	'Australia/Perth' => Array(
		'code' => 'Australia/Perth',
		'name' => 'Australia/Perth',
	),
	'Australia/Sydney' => Array(
		'code' => 'Australia/Sydney',
		'name' => 'Australia/Sydney',
	),
	'Europe/Amsterdam' => Array(
		'code' => 'Europe/Amsterdam',
		'name' => 'Europe/Amsterdam',
	),
	'Europe/Andorra' => Array(
		'code' => 'Europe/Andorra',
		'name' => 'Europe/Andorra',
	),
	'Europe/Astrakhan' => Array(
		'code' => 'Europe/Astrakhan',
		'name' => 'Europe/Astrakhan',
	),
	'Europe/Athens' => Array(
		'code' => 'Europe/Athens',
		'name' => 'Europe/Athens',
	),
	'Europe/Belgrade' => Array(
		'code' => 'Europe/Belgrade',
		'name' => 'Europe/Belgrade',
	),
	'Europe/Berlin' => Array(
		'code' => 'Europe/Berlin',
		'name' => 'Europe/Berlin',
	),
	'Europe/Bratislava' => Array(
		'code' => 'Europe/Bratislava',
		'name' => 'Europe/Bratislava',
	),
	'Europe/Brussels' => Array(
		'code' => 'Europe/Brussels',
		'name' => 'Europe/Brussels',
	),
	'Europe/Bucharest' => Array(
		'code' => 'Europe/Bucharest',
		'name' => 'Europe/Bucharest',
	),
	'Europe/Budapest' => Array(
		'code' => 'Europe/Budapest',
		'name' => 'Europe/Budapest',
	),
	'Europe/Busingen' => Array(
		'code' => 'Europe/Busingen',
		'name' => 'Europe/Busingen',
	),
	'Europe/Chisinau' => Array(
		'code' => 'Europe/Chisinau',
		'name' => 'Europe/Chisinau',
	),
	'Europe/Copenhagen' => Array(
		'code' => 'Europe/Copenhagen',
		'name' => 'Europe/Copenhagen',
	),
	'Europe/Dublin' => Array(
		'code' => 'Europe/Dublin',
		'name' => 'Europe/Dublin',
	),
	'Europe/Gibraltar' => Array(
		'code' => 'Europe/Gibraltar',
		'name' => 'Europe/Gibraltar',
	),
	'Europe/Guernsey' => Array(
		'code' => 'Europe/Guernsey',
		'name' => 'Europe/Guernsey',
	),
	'Europe/Helsinki' => Array(
		'code' => 'Europe/Helsinki',
		'name' => 'Europe/Helsinki',
	),
	'Europe/Isle_of_Man' => Array(
		'code' => 'Europe/Isle_of_Man',
		'name' => 'Europe/Isle_of_Man',
	),
	'Europe/Istanbul' => Array(
		'code' => 'Europe/Istanbul',
		'name' => 'Europe/Istanbul',
	),
	'Europe/Jersey' => Array(
		'code' => 'Europe/Jersey',
		'name' => 'Europe/Jersey',
	),
	'Europe/Kaliningrad' => Array(
		'code' => 'Europe/Kaliningrad',
		'name' => 'Europe/Kaliningrad',
	),
	'Europe/Kiev' => Array(
		'code' => 'Europe/Kiev',
		'name' => 'Europe/Kiev',
	),
	'Europe/Kirov' => Array(
		'code' => 'Europe/Kirov',
		'name' => 'Europe/Kirov',
	),
	'Europe/Lisbon' => Array(
		'code' => 'Europe/Lisbon',
		'name' => 'Europe/Lisbon',
	),
	'Europe/Ljubljana' => Array(
		'code' => 'Europe/Ljubljana',
		'name' => 'Europe/Ljubljana',
	),
	'Europe/London' => Array(
		'code' => 'Europe/London',
		'name' => 'Europe/London',
	),
	'Europe/Luxembourg' => Array(
		'code' => 'Europe/Luxembourg',
		'name' => 'Europe/Luxembourg',
	),
	'Europe/Madrid' => Array(
		'code' => 'Europe/Madrid',
		'name' => 'Europe/Madrid',
	),
	'Europe/Malta' => Array(
		'code' => 'Europe/Malta',
		'name' => 'Europe/Malta',
	),
	'Europe/Mariehamn' => Array(
		'code' => 'Europe/Mariehamn',
		'name' => 'Europe/Mariehamn',
	),
	'Europe/Minsk' => Array(
		'code' => 'Europe/Minsk',
		'name' => 'Europe/Minsk',
	),
	'Europe/Monaco' => Array(
		'code' => 'Europe/Monaco',
		'name' => 'Europe/Monaco',
	),
	'Europe/Moscow' => Array(
		'code' => 'Europe/Moscow',
		'name' => 'Europe/Moscow',
	),
	'Europe/Oslo' => Array(
		'code' => 'Europe/Oslo',
		'name' => 'Europe/Oslo',
	),
	'Europe/Paris' => Array(
		'code' => 'Europe/Paris',
		'name' => 'Europe/Paris',
	),
	'Europe/Podgorica' => Array(
		'code' => 'Europe/Podgorica',
		'name' => 'Europe/Podgorica',
	),
	'Europe/Prague' => Array(
		'code' => 'Europe/Prague',
		'name' => 'Europe/Prague',
	),
	'Europe/Riga' => Array(
		'code' => 'Europe/Riga',
		'name' => 'Europe/Riga',
	),
	'Europe/Rome' => Array(
		'code' => 'Europe/Rome',
		'name' => 'Europe/Rome',
	),
	'Europe/Samara' => Array(
		'code' => 'Europe/Samara',
		'name' => 'Europe/Samara',
	),
	'Europe/San_Marino' => Array(
		'code' => 'Europe/San_Marino',
		'name' => 'Europe/San_Marino',
	),
	'Europe/Sarajevo' => Array(
		'code' => 'Europe/Sarajevo',
		'name' => 'Europe/Sarajevo',
	),
	'Europe/Saratov' => Array(
		'code' => 'Europe/Saratov',
		'name' => 'Europe/Saratov',
	),
	'Europe/Simferopol' => Array(
		'code' => 'Europe/Simferopol',
		'name' => 'Europe/Simferopol',
	),
	'Europe/Skopje' => Array(
		'code' => 'Europe/Skopje',
		'name' => 'Europe/Skopje',
	),
	'Europe/Sofia' => Array(
		'code' => 'Europe/Sofia',
		'name' => 'Europe/Sofia',
	),
	'Europe/Stockholm' => Array(
		'code' => 'Europe/Stockholm',
		'name' => 'Europe/Stockholm',
	),
	'Europe/Tallinn' => Array(
		'code' => 'Europe/Tallinn',
		'name' => 'Europe/Tallinn',
	),
	'Europe/Tirane' => Array(
		'code' => 'Europe/Tirane',
		'name' => 'Europe/Tirane',
	),
	'Europe/Ulyanovsk' => Array(
		'code' => 'Europe/Ulyanovsk',
		'name' => 'Europe/Ulyanovsk',
	),
	'Europe/Uzhgorod' => Array(
		'code' => 'Europe/Uzhgorod',
		'name' => 'Europe/Uzhgorod',
	),
	'Europe/Vaduz' => Array(
		'code' => 'Europe/Vaduz',
		'name' => 'Europe/Vaduz',
	),
	'Europe/Vatican' => Array(
		'code' => 'Europe/Vatican',
		'name' => 'Europe/Vatican',
	),
	'Europe/Vienna' => Array(
		'code' => 'Europe/Vienna',
		'name' => 'Europe/Vienna',
	),
	'Europe/Vilnius' => Array(
		'code' => 'Europe/Vilnius',
		'name' => 'Europe/Vilnius',
	),
	'Europe/Volgograd' => Array(
		'code' => 'Europe/Volgograd',
		'name' => 'Europe/Volgograd',
	),
	'Europe/Warsaw' => Array(
		'code' => 'Europe/Warsaw',
		'name' => 'Europe/Warsaw',
	),
	'Europe/Zagreb' => Array(
		'code' => 'Europe/Zagreb',
		'name' => 'Europe/Zagreb',
	),
	'Europe/Zaporozhye' => Array(
		'code' => 'Europe/Zaporozhye',
		'name' => 'Europe/Zaporozhye',
	),
	'Europe/Zurich' => Array(
		'code' => 'Europe/Zurich',
		'name' => 'Europe/Zurich',
	),
	'Indian/Antananarivo' => Array(
		'code' => 'Indian/Antananarivo',
		'name' => 'Indian/Antananarivo',
	),
	'Indian/Chagos' => Array(
		'code' => 'Indian/Chagos',
		'name' => 'Indian/Chagos',
	),
	'Indian/Christmas' => Array(
		'code' => 'Indian/Christmas',
		'name' => 'Indian/Christmas',
	),
	'Indian/Cocos' => Array(
		'code' => 'Indian/Cocos',
		'name' => 'Indian/Cocos',
	),
	'Indian/Comoro' => Array(
		'code' => 'Indian/Comoro',
		'name' => 'Indian/Comoro',
	),
	'Indian/Kerguelen' => Array(
		'code' => 'Indian/Kerguelen',
		'name' => 'Indian/Kerguelen',
	),
	'Indian/Mahe' => Array(
		'code' => 'Indian/Mahe',
		'name' => 'Indian/Mahe',
	),
	'Indian/Maldives' => Array(
		'code' => 'Indian/Maldives',
		'name' => 'Indian/Maldives',
	),
	'Indian/Mauritius' => Array(
		'code' => 'Indian/Mauritius',
		'name' => 'Indian/Mauritius',
	),
	'Indian/Mayotte' => Array(
		'code' => 'Indian/Mayotte',
		'name' => 'Indian/Mayotte',
	),
	'Indian/Reunion' => Array(
		'code' => 'Indian/Reunion',
		'name' => 'Indian/Reunion',
	),
	'Pacific/Apia' => Array(
		'code' => 'Pacific/Apia',
		'name' => 'Pacific/Apia',
	),
	'Pacific/Auckland' => Array(
		'code' => 'Pacific/Auckland',
		'name' => 'Pacific/Auckland',
	),
	'Pacific/Bougainville' => Array(
		'code' => 'Pacific/Bougainville',
		'name' => 'Pacific/Bougainville',
	),
	'Pacific/Chatham' => Array(
		'code' => 'Pacific/Chatham',
		'name' => 'Pacific/Chatham',
	),
	'Pacific/Chuuk' => Array(
		'code' => 'Pacific/Chuuk',
		'name' => 'Pacific/Chuuk',
	),
	'Pacific/Easter' => Array(
		'code' => 'Pacific/Easter',
		'name' => 'Pacific/Easter',
	),
	'Pacific/Efate' => Array(
		'code' => 'Pacific/Efate',
		'name' => 'Pacific/Efate',
	),
	'Pacific/Fakaofo' => Array(
		'code' => 'Pacific/Fakaofo',
		'name' => 'Pacific/Fakaofo',
	),
	'Pacific/Fiji' => Array(
		'code' => 'Pacific/Fiji',
		'name' => 'Pacific/Fiji',
	),
	'Pacific/Funafuti' => Array(
		'code' => 'Pacific/Funafuti',
		'name' => 'Pacific/Funafuti',
	),
	'Pacific/Galapagos' => Array(
		'code' => 'Pacific/Galapagos',
		'name' => 'Pacific/Galapagos',
	),
	'Pacific/Gambier' => Array(
		'code' => 'Pacific/Gambier',
		'name' => 'Pacific/Gambier',
	),
	'Pacific/Guadalcanal' => Array(
		'code' => 'Pacific/Guadalcanal',
		'name' => 'Pacific/Guadalcanal',
	),
	'Pacific/Guam' => Array(
		'code' => 'Pacific/Guam',
		'name' => 'Pacific/Guam',
	),
	'Pacific/Honolulu' => Array(
		'code' => 'Pacific/Honolulu',
		'name' => 'Pacific/Honolulu',
	),
	'Pacific/Kanton' => Array(
		'code' => 'Pacific/Kanton',
		'name' => 'Pacific/Kanton',
	),
	'Pacific/Kiritimati' => Array(
		'code' => 'Pacific/Kiritimati',
		'name' => 'Pacific/Kiritimati',
	),
	'Pacific/Kosrae' => Array(
		'code' => 'Pacific/Kosrae',
		'name' => 'Pacific/Kosrae',
	),
	'Pacific/Kwajalein' => Array(
		'code' => 'Pacific/Kwajalein',
		'name' => 'Pacific/Kwajalein',
	),
	'Pacific/Majuro' => Array(
		'code' => 'Pacific/Majuro',
		'name' => 'Pacific/Majuro',
	),
	'Pacific/Marquesas' => Array(
		'code' => 'Pacific/Marquesas',
		'name' => 'Pacific/Marquesas',
	),
	'Pacific/Midway' => Array(
		'code' => 'Pacific/Midway',
		'name' => 'Pacific/Midway',
	),
	'Pacific/Nauru' => Array(
		'code' => 'Pacific/Nauru',
		'name' => 'Pacific/Nauru',
	),
	'Pacific/Niue' => Array(
		'code' => 'Pacific/Niue',
		'name' => 'Pacific/Niue',
	),
	'Pacific/Norfolk' => Array(
		'code' => 'Pacific/Norfolk',
		'name' => 'Pacific/Norfolk',
	),
	'Pacific/Noumea' => Array(
		'code' => 'Pacific/Noumea',
		'name' => 'Pacific/Noumea',
	),
	'Pacific/Pago_Pago' => Array(
		'code' => 'Pacific/Pago_Pago',
		'name' => 'Pacific/Pago_Pago',
	),
	'Pacific/Palau' => Array(
		'code' => 'Pacific/Palau',
		'name' => 'Pacific/Palau',
	),
	'Pacific/Pitcairn' => Array(
		'code' => 'Pacific/Pitcairn',
		'name' => 'Pacific/Pitcairn',
	),
	'Pacific/Pohnpei' => Array(
		'code' => 'Pacific/Pohnpei',
		'name' => 'Pacific/Pohnpei',
	),
	'Pacific/Port_Moresby' => Array(
		'code' => 'Pacific/Port_Moresby',
		'name' => 'Pacific/Port_Moresby',
	),
	'Pacific/Rarotonga' => Array(
		'code' => 'Pacific/Rarotonga',
		'name' => 'Pacific/Rarotonga',
	),
	'Pacific/Saipan' => Array(
		'code' => 'Pacific/Saipan',
		'name' => 'Pacific/Saipan',
	),
	'Pacific/Tahiti' => Array(
		'code' => 'Pacific/Tahiti',
		'name' => 'Pacific/Tahiti',
	),
	'Pacific/Tarawa' => Array(
		'code' => 'Pacific/Tarawa',
		'name' => 'Pacific/Tarawa',
	),
	'Pacific/Tongatapu' => Array(
		'code' => 'Pacific/Tongatapu',
		'name' => 'Pacific/Tongatapu',
	),
	'Pacific/Wake' => Array(
		'code' => 'Pacific/Wake',
		'name' => 'Pacific/Wake',
	),
	'Pacific/Wallis' => Array(
		'code' => 'Pacific/Wallis',
		'name' => 'Pacific/Wallis',
	),
	'UTC' => Array(
		'code' => 'UTC',
		'name' => 'UTC',
	),
);
define('TIME_ZONES',$_TIME_ZONES);

?>