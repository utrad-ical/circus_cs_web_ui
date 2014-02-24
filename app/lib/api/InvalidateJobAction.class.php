<?php

/**
 * Web API action for job invalidation.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class InvalidateJobAction extends ApiActionBase
{
	protected static $required_privileges = array(
		Auth::DATA_DELETE
	);

	protected static $rules = array(
		'jobID' => array(
			'type' => 'array',
			'childrenRule' => array('type' => 'int')
		)
	);

	protected static $public = true;

	public function execute($params)
	{
		$jobs = $params['jobID'];
		foreach ($jobs as &$id) {
			$id = intval($id);
		}

		$ids = '(' . implode(',', $jobs) . ')';
		$db = DBConnector::getConnection();

		$db->beginTransaction();
		CadResult::lock();
		ExecutedSeries::lock();
		Job::lock();
		JobSeries::lock();

		$sth = $db->prepare(
			'UPDATE executed_plugin_list SET status=? ' .
			'WHERE status=? AND job_id IN ' . $ids
		);
		$sth->execute(array(Job::JOB_INVALIDATED, Job::JOB_SUCCEEDED));
		$db->commit();

		return true;
	}
}