<?php

/**
 * Internal action to export feedback data in various formats.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class ExportFeedbackAction extends ApiActionBase {
	protected static $rules = array(
		'plugin_name' => 'string',
		'version' => 'string',
		'cad_date' => 'array',
		'feedback_mode' => '[all|consensual]'
	);

	function execute($params) {
		$plugin = Plugin::selectOne(array(
			'plugin_name' => $params['plugin_name'],
			'version' => $params['version']
		));
		if (!$plugin) {
			throw new ApiOperationException('Specified plugin not found');
		}
		set_include_path(get_include_path() . PATH_SEPARATOR . $plugin->configurationPath());

		$start_time = microtime(true);

		$condition = array('plugin_id' => $plugin->plugin_id);

		if ($params['cad_date'][1]) { // date min
			$condition['executed_at >='] = $params['cad_date'][1];
		}
		if ($params['cad_date'][2]) { // date max
			$max = date('Y-m-d', strtotime($params['cad_date'][2] . ' +1 day'));
			$condition['executed_at <'] = $max;
		}

		$jobs = CadResult::select($condition);

		$result = array();
		foreach ($jobs as $job) {
			$job_data = array(
				'job_id' => $job->job_id,
				'executed_at' => $job->executed_at,
				'exec_user' => $job->exec_user,
				'feedback' => array()
			);
			$fbs = $job->queryFeedback($params['feedback_mode']);
			foreach ($fbs as $fb)
			{
				$fb->loadFeedback();
				$fb_data = array(
					'is_consensual' => $fb->is_consensual,
					'entered_by' => $fb->entered_by,
					'blockFeedback' => $fb->blockFeedback,
					'additionalFeedback' => $fb->additionalFeedback
				);
				$job_data['feedback'][] = $fb_data;
			}
			$result[] = $job_data;
		}

		$query_time = (microtime(true) - $start_time);

		return array(
			'query_time' => $query_time,
			'data' => $result
		);
	}
}