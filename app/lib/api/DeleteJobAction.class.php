<?php

class DeleteJobAction extends ApiActionBase
{
	protected static $required_privileges = array(Auth::PROCESS_MANAGE);

	protected static $rules = array(
		'jobID' => array('type' => 'int', 'required' => true)
	);

	protected function execute($params)
	{
		$pdo = DBConnector::getConnection();
		$pdo->beginTransaction();

		$target = new Job($params['jobID']);
		if (!isset($target->job_id))
			throw new ApiOperationException('Target job not found (may be already deleted).');
		if ($target->status == Job::JOB_NOT_ALLOCATED)
		{
			Job::delete($target->job_id);
			CadResult::delete($target->job_id);
		}
		else
		{
			throw new ApiOperationException(
				'The target job can not be deleted (status: ' . $target->status . ')');
		}
		$pdo->commit();
		return null;
	}
}