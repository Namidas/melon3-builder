<?php

function fn_pgk_handle_rest_post($config,$args)
{
	if(isset($config['action'])) switch($config['action'])
	{
		default:
			fn_rest_response(400,default_error_response(Array(
				'error_no' => 2,
				'error_msg' => 'gen.error.unknown_action',
				'error_data' => Array(
					'action' => @$config['action'],
					'orig_action' => @$_POST["action"],
				)
			)));
			break;
	}
	else
	{
		fn_rest_response(400,default_error_response(Array(
			'error_no' => 1,
			'error_msg' => 'error.no_action',
		)));
	}
}

?>