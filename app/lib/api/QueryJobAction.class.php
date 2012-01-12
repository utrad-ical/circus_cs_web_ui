<?php

class QueryJobAction extends ApiActionBase
{
	const studyUID  = "studyuid";
	const seriesUID = "seriesuid";
	const jobID     = "jobid";
	const show      = "show";

	protected static $rules = array(
		'studyUID' => array('type' => 'array', 'childrenRule' => array('type' => 'string')),
		'seriesUID' => array('type' => 'array', 'childrenRule' => array('type' => 'string')),
		'jobID' => array('type' => 'array', 'childrenRule' => array('type' => 'int')),
		'show' => array('type' => 'select', 'options' => array('queue_list', 'error_list')),
	);

	protected function execute($params)
	{
		// Check for number of conditions specified
		if (count($params['studyUID'])) $conditions++;
		if (count($params['seriesUID'])) $conditions++;
		if (count($params['jobID'])) $conditions++;
		if ($params['show']) $conditions++;
		if ($conditions == 0)
			throw new ApiOperationException('Query condition not set.');
		if ($conditions > 1)
			throw new ApiOperationException('You can not combine more than one condition.');

		$result = array();

		if ($params['studyUID'])
		{
			$result = $this->query_job_study($params['studyUID']);
		}
		elseif ($params['seriesUID'])
		{
			$result = $this->query_job_series($params['seriesUID']);
		}
		elseif ($params['jobID'])
		{
			$result = $this->query_job($params['jobID']);
		}
		elseif ($params['show'] == 'queue_list')
		{
			$result = $this->queue_list();
		}
		elseif ($params['show'] == 'error_list')
		{
			$result = $this->error_list();
		}
		return $result;
	}

	function queue_list()
	{
		$sql = 'SELECT job_id FROM job_queue WHERE status >= 0';
		$jobIDs = DBConnector::query($sql, array(), 'ALL_COLUMN');
		return $this->query_job($jobIDs);
	}

	function error_list()
	{
		$sql = 'SELECT job_id FROM executed_plugin_list WHERE status = -1';
		$jobIDs = DBConnector::query($sql, array(), 'ALL_COLUMN');
		return $this->query_job($jobIDs);
	}

	function query_job(array $jobIDArr)
	{
		if (count($jobIDArr) == 0)
			return array();
		$placeHolders = implode(',', array_fill(0, count($jobIDArr), '?'));

		$sqlStr = 'select'
		. ' sl.study_instance_uid  as "studyUID",'
		. ' sl.series_instance_uid as "seriesUID",'
		. ' el.job_id              as "jobID",'
		. ' pm.plugin_name         as "pluginName",'
		. ' pm.version             as "pluginVersion",'
		. ' rp.policy_name         as "resultPolicy",'
		. ' jq.registered_at       as "registeredAt",'
		. ' el.executed_at         as "executedAt",'
		. ' el.status              as "status",'
		. ' jq.priority            as "priority"'
		. ' from executed_plugin_list el'
		. ' left join'
		. ' job_queue jq'
		. ' on el.job_id = jq.job_id'
		. ' left join'
		. ' executed_series_list es'
		. ' on el.job_id     = es.job_id'
		. ' left join'
		. ' series_list sl'
		. ' on es.series_sid = sl.sid'
		. ' left join'
		. ' plugin_master pm'
		. ' on el.plugin_id  = pm.plugin_id'
		. ' left join'
		. ' plugin_result_policy rp'
		. ' on el.policy_id  = rp.policy_id'
		. ' where el.job_id IN (' . $placeHolders . ')';

		$results = DBConnector::query($sqlStr, $jobIDArr, 'ALL_ASSOC');

		foreach ($results as &$item)
		{
			// Set waiting & priority
			if ($item['status'] == Job::JOB_NOT_ALLOCATED)
				$item['waiting'] = $this->get_waiting($item['registeredAt'], $item['priority']);
			if (is_null($item['priority'])) unset($item['priority']);

			// Set status
			$item['status'] = $this->get_status($item['status']);

		}
		return $results;
	}

	function query_job_study($studyArr)
	{
		$sqlStr = 'select'
		. '  sl.study_instance_uid,'
		. '  sl.series_instance_uid,'
		. '  esl.job_id'
		. ' from'
		. '  executed_series_list esl'
		. ' left join'
		. '  series_list sl'
		. ' on'
		. '  sl.sid = esl.series_sid'
		. ' where'
		. '  sl.study_instance_uid = ?';

		$jobIDArr = array();
		foreach ($studyArr as $s)
		{
			$result = DBConnector::query($sqlStr, array($s), 'ALL_ASSOC');
			foreach ($result as $r)
			{
				$jobIDArr[] = $r['job_id'];
			}
		}

		return $this->query_job($jobIDArr);
	}


	function query_job_series($seriesArr)
	{
		$sqlStr = 'select'
		. '  sl.study_instance_uid,'
		. '  sl.series_instance_uid,'
		. '  esl.job_id'
		. ' from'
		. '  executed_series_list esl'
		. ' left join'
		. '  series_list sl'
		. ' on'
		. '  sl.sid = esl.series_sid'
		. ' where'
		. '  sl.series_instance_uid = ?';

		$jobIDArr = array();
		foreach ($seriesArr as $s)
		{
			$result = DBConnector::query($sqlStr, array($s), 'ALL_ASSOC');
			foreach ($result as $r)
			{
				$jobIDArr[] = $r['job_id'];
			}
		}

		return $this->query_job($jobIDArr);
	}

	private function get_status($stat)
	{
		switch ($stat)
		{
			case -1:
				return "error";
			case 1:
				return "in_queue";
			case 2:
				return "processing";
			case 3:
				return "processing";
			case 4:
				return "finished";
		}
		return $stat;
	}

	private function get_waiting($reg, $pri)
	{
		// Count waiting
		$sqlStr = 'select count(*)'
		. ' from job_queue'
		. ' where priority > ?'
		. ' or (registered_at < ? and priority = ?)';
		$waiting = DBConnector::query($sqlStr, array($pri, $reg, $pri),'SCALAR');
		return (int)$waiting;
	}
}