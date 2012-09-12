<?php

class LoginAction extends ApiActionBase
{
	protected static $public = true;

	protected function execute($params)
	{
		$mode = $params['mode'];

		$result = null;
		switch ($mode)
		{
			case "getOnetime":
				$userid = $this->currentUser->user_id;
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
				Auth::createSession($this->currentUser);
				break;
		}

		return $result;
	}
}

