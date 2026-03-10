<?php

class LiteralOutput /*implements JsonSerializable*/
{
	public $text = '';
	
	public function __construct($text)
	{
		$this->text = $text;
	}
	
	public function __toString()
	{
		return $this->text;
	}
	
	/*public function jsonSerialize()
	{
		echo "asd";
		//return $this->text;
	}*/
}

/*



$array = Array(
	'fields' => Array(
		'pic_big' => Array(
			'formatField' => 'avatar',
			'colSize' => 4,
			'class' => 'text-center',
			//'colSize' => 12,
			'fieldAlign' => 'center',
			'uploader' => Array(
				'saraza' => true,
			),
		),
		'group-1' => Array(
			'formatField' => 'group',
			'groupDirection' => 'row',
			'colSize' => 8,
			'groupItems' => Array(
				'user_id' => Array(
					'readonly' => true,
					//'colSize' => 4,
					'colSize' => 12,
					'breakLine' => true,
				),
				'user_email' => Array(
					'rules' => new LiteralOutput("[val => !!val || \$t('gen.form.field_error.gen')]"),
				),
				'user_name',
				'user_hash',
				'user_role', 
				'user_password',
				'user_password_re',
			)
		),
		
		'user_enabled' => Array(
			'formatField' => 'toggler',
			'colSize' => 6,
		),
		'user_panic' => Array(
			'formatField' => 'toggler',
			'colSize' => 6,
		),
		'user_last_login' => Array(
			'formatField' => 'date',
			'colSize' => 6,
			'readonly' => true,
		),
		'user_last_activity' => Array(
			'formatField' => 'date',
			'colSize' => 6,
			'readonly' => true,
		),
	),
	
	'inferedOrder' => Array(
		'pic_big',
		'group-1',
			'user_id',
			'user_name',
			'user_role',
			'user_email',
			'user_hash',
			'user_password',
			'user_password_re',
		'user_enabled',
		'user_panic',
		'user_last_activity',
		'user_last_login',

		//'user_last_activity',
		//'user_last_login',
	),
);


echo json_encode($array);

echo "\n\n";
die(fn_json_encode($array));*/

/*$asd = new LiteralOutput("hola que tal");
$ff = "ahola";
echo json_encode( $asd);
echo get_class($asd);
echo "\n";
echo get_class($ff);
die("--");
*/

function _loutput($text)
{
	return new LiteralOutput($text);
}

?>