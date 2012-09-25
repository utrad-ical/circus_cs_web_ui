<?php

/**
 * Web API action for job invalidation.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class InvalidateJobAction extends ApiActionBase
{
	protected static $required_privileges = array(
		Auth::SERVER_OPERATION
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
		Job::lock();
		CadResult::lock();
		$db->query(
			'UPDATE executed_plugin_list SET status=? ' .
			'WHERE status IN (?, ?) AND job_id IN ' . $ids,
			array(Job::JOB_INVALIDATED, Job::JOB_SUCCEEDED, Job::JOB_PROCESSING)
		);
		$db->commit();

		return null;
	}
}