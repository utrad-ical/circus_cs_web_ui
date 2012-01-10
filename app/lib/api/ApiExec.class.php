<?php

/**
 * WebAPI execution class.
 * @author Yukihiro Ohno <y-ohno@j-mac.co.jp>
 */
class ApiExec
{
	protected $currentUser;

	public $action;

	public function doAction($api_request)
	{
		$this->action = $api_request['action'];
		$this->authenticate($api_request);

		// Autoload action class.
		if (!preg_match('/^[A-Za-z]+$/', $this->action))
			throw new ApiOperationException("Requested action is not defined.");
		$cls = ucfirst($this->action) . "Action";
		if (!file_exists("../../app/lib/api/$cls.class.php"))
			throw new ApiOperationException("Requested action is not defined.");

		$api = new $cls($this);

		$required_privileges = $api->requiredPrivileges();
		foreach ($required_privileges as $priv)
			if (!$this->currentUser->hasPrivilege($priv))
				throw new ApiAuthException('Required privilege ' . $priv);

		$res = $api->execute($api_request['params']);
		return $res;
	}

	/**
	 * Returns the instance of User currently logged-in.
	 * @return User The User instance.
	 */
	public function currentUser()
	{
		return $this->currentUser;
	}

	/**
	 * Handles basic/session authentication.
	 * If authentication succeeds, this method sets $this->currentUser and
	 * return true.
	 * @param array $api_request
	 * @return bool true if the authentication succeeds. If authentication fails,
	 * it throws an ApiAuthException exception.
	 */
	protected function authenticate($api_request)
	{
		$auth = $api_request['auth'];
		if (!is_array($auth))
			throw new ApiAuthException('Authentication required');
		switch (strtolower($auth['type']))
		{
			case 'basic':
				$user = Auth::checkAuth($auth['user'], $auth['pass']);
				if (!$user->user_id)
					throw new ApiAuthException('Basic authentication failed');
				$this->currentUser = $user;
				break;
			case 'session':
				$user = Auth::checkSession(false);
				$user = Auth::currentUser();
				if (!$user->user_id)
					throw new ApiAuthException('Session not established. login first.');
				$this->currentUser = $user;
				break;
			default:
				throw new ApiAuthException('Authentication type not valid');
				break;
		}
		return true;
	}
}

