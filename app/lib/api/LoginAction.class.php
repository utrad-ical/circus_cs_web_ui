<?php

class LoginAction extends ApiAction
{
	protected static $required_privileges = array(
		Auth::API_EXEC
	);
	
	
	function requiredPrivileges()
	{
		return self::$required_privileges;
	}
	
	
	function execute($api_request)
	{
		$params = $api_request['params'];
		$action = $api_request['action'];
		$mode = $params['mode'];
		
		$user = ApiExec::currentUser();
		if(!isset($user)) {
			throw new ApiException('Authentication required');
		}
		
		$result = null;
		switch ($mode)
		{
			case "getOnetime":
				$userid = $user->user_id;
				$time = date("Y-m-d H:i:s");
				$ip = getenv("REMOTE_ADDR");
				
				// Delete old onetime password
				$sqlStr = "DELETE FROM user_onetime"
						. " WHERE user_id = ? "
						. " OR registered_at < ?";
				DBConnector::query(
					$sqlStr,
					array($userid, date("Y-m-d H:i:s", time()-60))
				);
				
				// Generate one-time password
				$onetime = md5(uniqid().mt_rand());
				
				// Register one-time password
				$sqlStr = "INSERT INTO user_onetime"
						. " (user_id, ip_address, onetime_pass, registered_at)"
						. " VALUES (?, ?, ?, ?)";
				DBConnector::query(
					$sqlStr,
					array($userid, $ip, $onetime, $time)
				);
				
				$result = array("onetime" => $onetime);
				break;
				
			case "newSession":
			default:
				session_start();
				Auth::createSession($user);
				break;
		}
		
		$res = new ApiResponse();
		$res->setResult($action, $result);
		return $res;
	}
}

?>
