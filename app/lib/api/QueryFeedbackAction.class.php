<?php

class QueryFeedbackAction extends ApiActionBase
{
	protected static $public = true;

	protected function execute($params)
	{
		if($this->check_params($params) == FALSE)
		{
			throw new ApiOperationException("Invalid parameter.");
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
			throw new ApiOperationException('Target job not found.');
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
		return $result;
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