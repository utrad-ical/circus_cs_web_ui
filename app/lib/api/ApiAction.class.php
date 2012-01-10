<?php

/**
 * Base WebAPI Action class.
 *
 * @author Yukihiro Ohno <y-ohno@j-mac.co.jp>
 */
abstract class ApiAction
{
	protected static $required_privileges = array(
		Auth::API_EXEC
	);

	protected $owner;

	public function __construct($owner)
	{
		$this->owner = $owner;
	}

	public function requiredPrivileges()
	{
		return static::$required_privileges;
	}

	abstract public function execute($api_request);
}