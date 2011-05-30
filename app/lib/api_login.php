<?php
function api_login($api_request)
{
	$api_auth = $api_request['auth'];
	
	$authtype = $api_auth['type'];
	$authuser = $api_auth['user'];
	$authpass = $api_auth['pass'];
	
	$action = $api_request['action'];
	$params = $api_request['params'];
	$mode = $params['mode'];
	
	switch ($authtype) {
		case "basic":
			try
			{
				$is_success = basic_auth($authuser, $authpass, $mode);
				if ($is_success)
				{
					$res = new ApiResponse();
					$res->setResult($action, NULL);
					return $res;
				}
				else
				{
					$res = new ApiResponse();
					$res->setError($action, ApiResponse::STATUS_ERR_AUTH, $authtype . " authentication failed.");
					return $res;
				}
			}
			catch (PDOException $e)
			{
				$res = new ApiResponse();
				$res->setError($action, ApiResponse::STATUS_ERR_SYS, "Database connection error.");
				return $res;
			}
			
		break;
		
		case "session":
			// Restore session from cookie
			;
		break;
		
		default:
			$res = new ApiResponse();
			$res->setError($action, ApiResponse::STATUS_ERR_OPE, "authtype " . $authtype . " is not defined.");
			return $res;
		break;
	}
}

function basic_auth($user, $pass, $mode)
{
	$valid_user = Auth::checkAuth($user, $pass);
	if (!$valid_user)
	{
		return FALSE;
	}
	
	// Add check "WebApiExec" privilege process
	
	// params.mode: "getOnetime"
	//    return onetime password but session isnt created
	// params.mode: "newSession" or params.mode isnt set
	//    create session cookie
	if($mode == "")
	{
	}
	
	return TRUE;
}
?>
