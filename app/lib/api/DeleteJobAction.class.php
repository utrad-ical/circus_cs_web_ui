<?php

class DeleteJobAction extends ApiActionBase
{
	protected static $required_privileges = array(Auth::SERVER_SETTINGS);

	protected function execute($params)
	{
		if (!is_numeric($params['jobID']))
			throw new ApiOperationException('Job ID not specified');

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