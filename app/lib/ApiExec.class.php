<?php

/**
 * WebAPI execution class.
 *
 * @author Yukihiro Ohno <y-ohno@j-mac.co.jp>
 */
class ApiExec
{
	const LOGIN          = "login";
	const COUNT_IMAGES   = "countImages";
	const QUERY_JOB      = "queryJob";
	const EXECUTE_PLUGIN = "executePlugin";
	
	static $action_list = array(
		self::LOGIN,
		self::COUNT_IMAGES,
		self::QUERY_JOB,
		self::EXECUTE_PLUGIN
	);
	
	public static function doAction($api_request)
	{
		$action = $api_request['action'];
		
		if ($action != self::LOGIN)
		{
			include("api_login.php");
			$api_result = api_login($api_request);
			
			if ($api_result->isError())
			{
				return $api_result;
			}
		}
		
		switch ($action)
		{
			case self::LOGIN:
				include("api_login.php");
				$api_result = api_login($api_request);
				return $api_result;
			break;
			
			case self::COUNT_IMAGES:
				include("api_count_images.php");
				$api_result = count_images($api_request);
				return $api_result;
			break;
			
			case self::QUERY_JOB:
				include("api_query_job.php");
				$api_result = query_job($api_request);
				return $api_result;
			break;
			
			case self::EXECUTE_PLUGIN:
				include("api_execute_plugin.php");
				$api_result = execute_plugin($api_request);
				return $api_result;
			break;
			
			default:
				$res = new ApiResponse();
				$res->setError($action, ApiResponse::STATUS_ERR_OPE, "Requested action is not defined.");
				return $res;
			break;
		}
	}
}
?>
