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
	const QUERY_FEEDBACK = "queryFeedback";
	const INTERNAL_EXECUTE_PLUGIN = "InternalExecutePlugin";
	const SERIES_RULESET = "seriesRuleset";
	const QUERY_JOB_QUEUE = "queryJobQueue";

	static $action_list = array(
		self::LOGIN          => "LoginAction",
		self::COUNT_IMAGES   => "CountImagesAction",
		self::QUERY_JOB      => "QueryJobAction",
		self::EXECUTE_PLUGIN => "ExecutePluginAction",
		self::QUERY_FEEDBACK => "QueryFeedbackAction",
		self::INTERNAL_EXECUTE_PLUGIN => "InternalExecutePluginAction",
		self::SERIES_RULESET => "SeriesRuleSetAction",
		self::QUERY_JOB_QUEUE => "QueryJobQueueAction"
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

		try
		{
			$cls = self::$action_list[$action];
			if(!isset($cls))
			{
				throw new ApiException("Requested action is not defined.", ApiResponse::STATUS_ERR_OPE);
			}

			$api = new $cls;
			$required_privileges = $api->requiredPrivileges();
			foreach ($required_privileges as $priv)
			{
				if (!self::$currentUser->hasPrivilege($priv))
					throw new ApiException('Required privilege '.$priv, ApiResponse::STATUS_ERR_OPE);
			}
			$res = $api->execute($api_request);
			return $res;
		}
		catch (ApiException $e)
		{
			$api_result = new ApiResponse();
			$api_result->setError($action, $e->getCode(), $e->getMessage());
			return $api_result;
		}
	}

	/**
	 * Returns the instance of User currently logged-in.
	 * @return User The User instance.
	 */
	public static function currentUser()
	{
		return self::$currentUser;
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
				throw new ApiException('Authentication required');
			switch (strtolower($auth['type']))
			{
				case 'basic':
					$user = Auth::checkAuth($auth['user'], $auth['pass']);
					if (!$user->user_id)
						throw new ApiException('Basic authentication failed');
					self::$currentUser = $user;
					break;
				case 'session':
					$user = Auth::checkSession(false);
					$user = Auth::currentUser();
					if (!$user->user_id)
						throw new ApiException('Session not established. login first.');
					self::$currentUser = $user;
					break;
				default:
					throw new ApiException('Authentication type not valid');
					break;
			}
		}
		catch (ApiException $e)
		{
			return $e->getMessage();
		}
		return true;
	}
}

