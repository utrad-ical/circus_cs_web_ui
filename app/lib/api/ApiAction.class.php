<?php

/**
 * Base WebAPI Action class.
 *
 * @author Yukihiro Ohno <y-ohno@j-mac.co.jp>
 */
abstract class ApiAction
{
	protected static $required_privileges;

	abstract public function requiredPrivileges();
	abstract public function execute($api_request);
}

?>