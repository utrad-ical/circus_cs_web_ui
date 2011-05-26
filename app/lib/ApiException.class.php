<?php
class ApiException extends Exception
{
	private $_status;
	private $_errmsg;
	
	public function __construct($status, $errmsg, $message = "", $code = 0, Exception $previous = null)
	{
		$this->_status = $status;
		$this->_errmsg = $errmsg;
		
		parent::__construct($message, $code, $previous);
	}
	
	public function getStatus()
	{
		return $this->_status;
	}
	
	public function getErrmsg()
	{
		return $this->_errmsg;
	}
}
?>
