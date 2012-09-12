<?php

/**
 * Base WebAPI Action class.
 * @author Yukihiro Ohno <y-ohno@j-mac.co.jp>
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
abstract class ApiActionBase
{
	/**
	 * Additional privilege required to use this action.
	 */
	protected static $required_privileges = array();

	protected static $rules = array();

	/**
	 * Determines whether this action can be used with 'basic' authentication.
	 * If this is true, this API is only usable with users with 'apiExec'
	 * privilege with 'basic' authentication.
	 * Instead, if this is false, the use of this API is protected for
	 * internal use.
	 */
	protected static $public = false;

	/**
	 * The current authenticated user.
	 * Do not use Auth::currentUser() inside the action classes,
	 * because the user may be authenticated without using session.
	 */
	protected $currentUser;

	/**
	 * Processes the action.
	 * @param array The input parameter passed from the client.
	 */
	public function doAction(array $api_request)
	{
		$this->authenticate($api_request);

		if (static::$public && !$this->currentUser->hasPrivilege(Auth::API_EXEC))
		{
			throw new ApiAuthException('Required privilege apiExec');
		}

		foreach (static::$required_privileges as $priv)
		{
			if (!$this->currentUser->hasPrivilege($priv))
			{
				throw new ApiAuthException('Required privilege ' . $priv);
			}
		}

		$params = $api_request['params'];

		if (count(static::$rules))
		{
			$validator = new FormValidator();
			$validator->addRules(static::$rules);
			if (!$validator->validate($params))
				throw new ApiOperationException(implode("\n", $validator->errors));
			$params = $validator->output;
		}

		return $this->execute($params);
	}

	/**
	 * The main entry point of each action.
	 * Indivisual action classes must override this and return the result when
	 * successful, or throw an exception if something wrong happens.
	 * @param mixed $params The input data
	 * @return mixed The return value of the action.
	 */
	abstract protected function execute($params);

	/**
	* Handles basic/session authentication.
	* If authentication succeeds, this method sets $this->currentUser and
	* return true.
	* @param array $api_request
	* @return bool true if the authentication succeeds. If authentication fails,
	* it throws an ApiAuthException exception.
	*/
	protected function authenticate(array $api_request)
	{
		$auth = $api_request['auth'];
		if (!is_array($auth))
		throw new ApiAuthException('Authentication required');
		switch (strtolower($auth['type']))
		{
			case 'basic':
				$user = Auth::checkAuth($auth['user'], $auth['pass']);
				if (!static::$public)
					throw new ApiAuthException('Authentication error: session required');
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