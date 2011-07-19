<?php

class QueryFeedbackAction extends ApiAction
{
	protected static $required_privileges = array(
		Auth::API_EXEC
	);


	function requiredPrivileges()
	{
		return self::$required_privileges;
	}


	function execute($api_request)
	{
		$params = $api_request['params'];
		$action = $api_request['action'];

		if($this->check_params($params) == FALSE)
		{
			throw new ApiException("Invalid parameter.", ApiResponse::STATUS_ERR_OPE);
		}

		$jobID  = $params['jobID'];
		$kind   = $params['kind'];
		if (!isset($kind)) {
			$kind = "all";
		}
		$userID = $params['userID'];

		// Retrieve the CAD Result
		$cadResult = new CadResult($jobID);
		if (!isset($cadResult->job_id))
		{
			$result = null;
		}
		$feedback = $cadResult->queryFeedback($kind, $userID);

		$result = array();
		foreach ($feedback as $f) {
			array_push(
				$result,
				array(
					'enteredBy'    => $f->entered_by,
					'registeredAt' => $f->registered_at,
					'isConsensual' => $f->is_consensual
				)
			);
		}

		if (count($result) <= 0)
		{
			$res = new ApiResponse();
			$res->setResult($action, null);
			return $res;
		}

		$res = new ApiResponse();
		$res->setResult($action, $result);
		return $res;
	}


	private function check_params($params)
	{
		if(!isset($params['jobID'])) {
			return false;
		}

		$kind = $params['kind'];
		if (isset($kind) && !in_array($kind, array("all", "personal", "consensual", "user"))) {
			return false;
		}

		return true;
	}

}

?>
