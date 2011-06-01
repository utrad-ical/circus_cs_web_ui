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

	protected static $currentUser;

	public static function doAction($api_request)
	{
		$action = $api_request['action'];

		$auth_result = self::authenticate($api_request);
		if ($auth_result !== true) {
			$api_result = new ApiResponse();
			$api_result->setError($action, ApiResponse::STATUS_ERR_AUTH, $auth_result);
			return $api_result;
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

	/**
	 * Handles basic/session authentication.
	 * If authentication succeeds, this method sets self::$currentUser and
	 * return true.
	 * @param array $api_request
	 * @return true if the authentication succeeds. If authentication fails,
	 * this returns the error message (in string format).
	 */
	protected static function authenticate($api_request)
	{
		$auth = $api_request['auth'];
		try
		{
			if (!is_array($auth))
				throw new Exception('Authentication required');
			switch (strtolower($auth['type']))
			{
				case 'basic':
					$user = Auth::checkAuth($auth['user'], $auth['pass']);
					if (!$user->user_id)
						throw new Exception('Basic authentication failed');
					self::$currentUser = $user;
					break;
				case 'session':
					$user = Auth::checkSession(false);
					$user = Auth::currentUser();
					if (!$user->user_id)
						throw new Exception('Session not established. login first.');
					self::$currentUser = $user;
					break;
				default:
					throw new Exception('Authentication type not valid');
					break;
			}
			// if (!$user->hasPrivilege(Auth::ApiExec))
			// 	throw new Exception('This user cannot execute web API.')
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}
		return true;
	}
}
?>
