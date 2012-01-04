<?php

/**
 * WebAPI exception class.
 *
 * @author Yukihiro Ohno <y-ohno@j-mac.co.jp>
 */
class ApiException extends Exception
{
	function __construct($message, $code = "")
	{
		parent::__construct($message, 0);
		$this->code = $code;
	}
}