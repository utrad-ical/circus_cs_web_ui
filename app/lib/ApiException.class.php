<?php

/**
 * WebAPI exception class.
 *
 * @author Yukihiro Ohno <y-ohno@j-mac.co.jp>
 */
class ApiException extends Exception
{
	protected $status;
	
	function __construct($message, $status = NULL, $code = 0)
	{
		parent::__construct($message, $code);
		$this->status = $status;
	}
	
	function getStatus()
	{
		return $this->status;
	}
}
?>
