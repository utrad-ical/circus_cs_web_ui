<?php

/**
 * WebAPI response class.
 *
 * @author Yukihiro Ohno <y-ohno@j-mac.co.jp>
 */
class ApiResponse
{
	private $action = NULL;
	private $status = "";
	private $result = array();
	private $errmsg = "";
	
	private $is_error = TRUE;
	
	const STATUS_OK       = "OK";
	const STATUS_ERR_SYS  = "SystemError";
	const STATUS_ERR_OPE  = "OperationError";
	const STATUS_ERR_AUTH = "AuthError";
	
	static $status_list = array(
		self::STATUS_OK,
		self::STATUS_ERR_SYS,
		self::STATUS_ERR_OPE,
		self::STATUS_ERR_AUTH
	);
	
	
	public function setResult($action, $result)
	{
		$this->is_error = FALSE;
		$this->action = $action;
		$this->status = self::STATUS_OK;
		$this->result = $result;
	}
	
	
	public function setError($action, $errstat, $errmsg)
	{
		$this->is_error = TRUE;
		$this->action = $action;
		$this->status = $errstat;
		$this->errmsg = $errmsg;
	}
	
	
	public function isError()
	{
		return $this->is_error;
	}
	
	
	public function getJson()
	{
		$arr = array();
		if ($this->is_error)
		{
			if (isset($this->action))
			{
				$arr = array(
					"action" => $this->action,
					"status" => $this->status,
					"error"  => array("message" => $this->errmsg)
				);
			}
			else
			{
				$arr = array(
					"status" => $this->status,
					"error"  => array("message" => $this->errmsg)
				);
			}
		}
		else
		{
			if (isset($this->result))
			{
				$arr = array(
					"action" => $this->action,
					"status" => $this->status,
					"result" => $this->result
				);
			}
			else
			{
				$arr = array(
					"action" => $this->action,
					"status" => $this->status
				);
			}
		}
		
		return json_encode($arr);
	}
}
