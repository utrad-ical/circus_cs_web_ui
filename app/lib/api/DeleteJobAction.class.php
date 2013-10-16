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
		CadResult::lock();
		Job::lock();

		$target = new Job($params['jobID']);
		if (!isset($target->job_id))
			throw new ApiOperationException('Target job not found (may be already deleted).');
		if ($target->status == Job::JOB_NOT_ALLOCATED ||
			$target->status == Job::JOB_ALLOCATED)
		{
			Job::delete($target->job_id);
			CadResult::delete($target->job_id);
		}
		else if($target->status == Job::JOB_PROCESSING)
		{
			Job::abortJob($target->job_id);
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