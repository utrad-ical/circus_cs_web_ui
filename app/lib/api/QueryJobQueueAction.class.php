<?php

class QueryJobQueueAction extends ApiActionBase
{
	protected static $required_privileges = array(
		Auth::SERVER_OPERATION
	);

	protected function execute($params)
	{
		$jobs = Job::select(
			array('status >=' => Job::JOB_NOT_ALLOCATED),
			array('order' => array('registered_at'))
		);

		$job_list = array();
		foreach($jobs as $job)
		{
			$item = $job->getData();

			$plugin = $job->Plugin;
			$item['plugin_name'] = $plugin->fullName();
			$item['plugin_type'] = $plugin->pluginType();

			Patient::$anonymizeMode = $this->currentUser->needsAnonymization();

			if($plugin->type == Plugin::CAD_PLUGIN)
			{
				$series = $job->Series;
				$primarySeries = $series[0];
				$item['patient_id'] = $primarySeries->Study->Patient->patient_id;
				$item['study_id'] = $primarySeries->Study->study_id;
				$item['series_id'] = $primarySeries->series_number;
			}
			else
			{
				$item['patient_id'] = $item['study_id'] = $item['series_id'] = '-';
			}
			$job_list[] = $item;
		}

		return array('jobs' => $job_list);
	}
}