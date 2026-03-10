<?php

function fn_pkg_handle_rest_get($config,$args)
{
	if(isset($config['action'])) switch($config['action'])
	{
		case 'get_project':
			$path = $args['path'];
			if(!in_array(substr($path,-1),['\\','/']))
				$path .= '/';
			$manifestPath = "{$path}_build.php";
			if(!is_readable($path))
				fn_rest_response(400,default_error_response(Array(
					'error_no' => 1,
					'error_msg' => 'error.project_path_no_readable',
					'data' => Array(
						'path' => $path,
						//'manifest' => $manifestPath
					)
				)));
				
			if(!is_readable($manifestPath))
				fn_rest_response(400,default_error_response(Array(
					'error_no' => 1,
					'error_msg' => 'error.path_no_build',
					'data' => Array(
						'path' => $path,
						'manifest' => $manifestPath
					)
				)));
				
			$project = fn_pkg_load_project($manifestPath);
			$project['parsed_files'] = fn_pkg_parse_manifest_files($project);
			
			fn_rest_response(200,default_response(Array(
				'status' => 1,
				'data' => $project
			)));
			break;
			
		case 'build_project':
			$path = $args['path'];
			if(!in_array(substr($path,-1),['\\','/']))
				$path .= '/';
			$manifestPath = "{$path}_build.php";
			
			if(!is_readable($path))
				fn_rest_response(400,default_error_response(Array(
					'error_no' => 1,
					'error_msg' => 'error.project_path_no_readable',
					'data' => $path
				)));
				
			if(!is_readable($manifestPath))
				fn_rest_response(400,default_error_response(Array(
					'error_no' => 1,
					'error_msg' => 'error.path_no_build',
				)));
				
			$buildRes = fn_pkg_build_project($manifestPath);
			
			fn_rest_response(200,default_response(Array(
				'status' => 1,
				'data' => $buildRes
			)));
			break;
			
		default:
			fn_rest_response(400,default_error_response(Array(
				'error_no' => 2,
				'error_msg' => 'error.unknown_action',
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