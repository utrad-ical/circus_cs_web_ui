<?php

class QueryFeedbackAction extends ApiActionBase
{
	protected static $public = true;

	protected static $rules = array(
		'jobID' => array('type' => 'int', 'required' => true),
		'kind' => array(
			'type' => 'select',
			'options' => array('all', 'personal', 'consensual', 'user'),
			'default' => 'all'
		),
		'userID' => array('type' => 'str'),
		'withData' => array('type' => 'bool')
	);

	protected function execute($params)
	{
		$jobID  = $params['jobID'];
		$kind   = $params['kind'];
		$with_data = $params['withData'];
		$userID = $params['userID'];

		// Retrieve the CAD Result
		$cadResult = new CadResult($jobID);
		if (!isset($cadResult->job_id))
			throw new ApiOperationException('Target job not found.');
		$feedback = $cadResult->queryFeedback($kind, $userID);

		$result = array();
		foreach ($feedback as $f) {
			$item = array(
				'enteredBy'    => $f->entered_by,
				'registeredAt' => $f->registered_at,
				'isConsensual' => $f->is_consensual
			);
			if ($with_data)
			{
				$f->loadFeedback();
				$item['feedback'] = array(
					'blockFeedback' => $f->blockFeedback,
					'additionalFeedback' => $f->additionalFeedback
				);
			}
			array_push($result, $item);
		}
		return $result;
	}
}