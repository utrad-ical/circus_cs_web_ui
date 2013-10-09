<?php
require_once('common.php');
Auth::checkSession();

try
{
	// News block
	$plugins = array_map(
		function($item) { return $item->getData(); },
		Plugin::select(array(), array('limit' => 5, 'order' => array('install_dt DESC')))
	);

	// Plug-in execution block
	$sqlStr = "SELECT COUNT(*) AS count, to_char(MIN(executed_at), 'YYYY-MM-DD')
		AS first FROM executed_plugin_list WHERE status = ?";
	$execStats = DBConnector::query($sqlStr, Job::JOB_SUCCEEDED, 'ARRAY_ASSOC');

	if($execStats['count'] > 0)
	{
		$sqlStr = "SELECT pm.plugin_name, pm.version, COUNT(el.job_id) as cnt"
				. " FROM executed_plugin_list el, plugin_master pm"
				. " WHERE pm.plugin_id = el.plugin_id"
				. " GROUP BY plugin_name, version ORDER BY COUNT(job_id) DESC LIMIT 3";
		$cadExecutionData = DBConnector::query($sqlStr, null, 'ALL_ASSOC');
	}

	// Message board
	$topMessage = ServerParam::getVal('top_message');

	// latest missed TP
	$user = Auth::currentUser();
	Patient::$anonymizeMode = $user->needsAnonymization();
	if ($user->hasPrivilege(Auth::PERSONAL_FEEDBACK_ENTER) && $user->show_missed != 'none')
	{
		$own = $user->show_missed == 'own' ? $own = "AND fb.entered_by = ?" : '';
		$missed_jobs = DBConnector::query(
			"SELECT el.job_id, cc.candidate_id AS display_id
			FROM executed_plugin_list el
				INNER JOIN feedback_list fb ON el.job_id = fb.job_id
				INNER JOIN candidate_classification cc ON fb.fb_id = cc.fb_id
			WHERE fb.is_consensual = 'f' AND cc.evaluation = 2 $own
			ORDER BY fb.registered_at DESC
			LIMIT 3",
			$own ? array($user->user_id) : array(),
			'ALL_ASSOC'
		);

		$recentMissed = array();
		foreach ($missed_jobs as $item) {
			$job = new CadResult($item['job_id']);
			$prn = $job->Plugin->presentation();
			$dp = $prn->displayPresenter();
			if (!($dp instanceof LesionCandDisplayPresenter))
				break;
			if (!($prn->feedbackListener() instanceof SelectionFeedbackListener))
				break;
			$displays = $job->getDisplays();
			$display = $displays[$item['display_id']];
			$crop = null;
			$attr = $job->getAttributes();
			if (isset($attr['crop_org_x']) && isset($attr['crop_org_y']) &&
				isset($attr['crop_width']) && isset($attr['crop_height'])) {
				$crop = array(
					'x' => (int)$attr['crop_org_x'],
					'y' => (int)$attr['crop_org_y'],
					'width' => (int)$attr['crop_width'],
					'height' => (int)$attr['crop_height'],
				);
			}
			$opt = array();
			if (isset($attr['window_level'])) $opt['wl'] = (float)$attr['window_level'];
			if (isset($attr['window_width'])) $opt['ww'] = (float)$attr['window_width'];
			$recentMissed[] = array(
				'job' => $job,
				'display' => $display,
				'crop' => $crop,
				'opt' => $opt
			);
		}
	}

	$smarty = new SmartyEx();
	$smarty->assign(array(
		'plugins' => $plugins,
		'execStats' => $execStats,
		'cadExecutionData' => $cadExecutionData,
		'topMessage' => $topMessage,
		'recentMissed' => $recentMissed
	));
	$smarty->display('home.tpl');
}
catch (PDOException $e)
{
	var_dump($e->getMessage());
}