<?php

class QueryJobQueueAction extends ApiAction
{
	public function requiredPrivileges()
	{
		return array(Auth::SERVER_OPERATION);
	}

	public function execute($api_request)
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

			Patient::$anonymizeMode = Auth::currentUser()->needsAnonymization();

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

		$res = new ApiResponse();
		$res->setResult($action, array('jobs' => $job_list));
		return $res;
	}
}