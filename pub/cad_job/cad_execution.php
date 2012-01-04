<?php
$params = array('toTopDir' => "../");
include_once("../common.php");
Auth::checkSession();

$smarty = new SmartyEx();

try
{
	//--------------------------------------------------------------------------
	// Import $_GET variables and validation
	//--------------------------------------------------------------------------
	$validator = new FormValidator();

	$validator->addRules(array(
		"cadName" => array(
			"type" => "cadname",
			"required" => true,
			"errorMes" => "[ERROR] 'CAD name' is invalid."),
		"version" => array(
			"type" => "version",
			"required" => true,
			"errorMes" => "[ERROR] 'Version' is invalid."),
		"studyInstanceUID" => array(
			"type" => "uid",
			"required" => true,
			"errorMes" => "[ERROR] 'Study instance UID' is invalid."),
		"seriesInstanceUID" => array(
			"type" => "uid",
			"required" => true,
			"errorMes" => "[ERROR] 'Series instance UID' is invalid."),
		"srcList" => array(
			"type" => "select",
			"options" => array("todaysSeries", "series"),
			'default'  => "series",
			'oterwise' => "series")
	));

	if (!$validator->validate($_GET))
		throw new Exception (implode("\n", $validator->errors));

	$params += $validator->output;

	if($params['srcList'] == 'todaysSeries')	$params['listTabTitle'] = "Today's series";
	else										$params['listTabTitle'] = "Series list";

	// Check plugin
	$plugin = Plugin::selectOne(array('plugin_name' => $params['cadName'], 'version' => $params['version']));
	if (!$plugin)
		throw new Exception($params['cadName'].' ver.'.$params['version'].' is not installed.');
	if ($plugin->type != 1)
		throw new Exception($plugin->fullName() . ' is not CAD plug-in.');
	if(!$plugin->exec_enabled)
		throw new Exception($plugin->fullName() . ' is not allowed to execute.');

	// Check CAD series input type
	$input_type = DBConnector::query(
		'SELECT input_type FROM plugin_cad_master WHERE plugin_id=?',
		$plugin->plugin_id,
		'SCALAR'
	);
	if (!is_int($input_type) || $input_type < 0 || 2 < $input_type)
		throw new Exception('Input type is incorrect (' . $plugin->fullName() . ')');

	$primarySeries = Series::selectOne(array('series_instance_uid' => $params['seriesInstanceUID']));
	if (!($primarySeries instanceof Series))
		throw new Exception('Target primary series does not exist.');

	// Set anonymization mode
	Patient::$anonymizeMode = Auth::currentUser()->needsAnonymization();
	$patient = $primarySeries->Study->Patient;

	//--------------------------------------------------------------------------
	//  Build volume information
	//--------------------------------------------------------------------------
	$vols = $plugin->PluginCadSeries;
	$volumeInfo = array(); // keys: volume ID
	foreach ($vols as $vol)
	{
		$volumeInfo[$vol->volume_id] = array(
			'id' => $vol->volume_id,
			'label' => $vol->volume_label,
			'ruleSetList' => json_decode($vol->ruleset, true),
		);
	}

	// (1) Primary volume, which is already specified
	$volumeInfo[0]['targetSeries'] = array($primarySeries);

	// (2) Complementary volume(s), which may need manual selection
	if ($input_type > 0)
	{
		if ($input_type == 1) // within the same study
			$where = array('study_id', $primarySeries->Study->study_id);
		if ($input_type == 2) // within the same patient
			$where = array('patient_id', $primarySeries->Study->Patient->patient_id);
		$candidates = DBConnector::query(
			"SELECT * FROM series_join_list WHERE {$where[0]}=? " .
			"ORDER BY study_date DESC, series_number ASC",
			$where[1],
			'ALL_ASSOC'
		);
		$fp = new SeriesFilter();
		foreach ($vols as $v)
		{
			$vid = $v->volume_id;
			if ($vid == 0)
				continue; // skip primary series, which is manually specified
			$targets = array();
			foreach ($candidates as $s)
			{
				if ($s['series_instance_uid'] == $primarySeries->series_instance_uid)
					continue; // exclude the primary series
				if ($fp->processRuleSets($s, $volumeInfo[$vid]['ruleSetList']))
					$targets[] = new Series($s['series_sid']);
			}
			$volumeInfo[$vid]['targetSeries'] = $targets;
		}
	}
	ksort($volumeInfo, SORT_NUMERIC);

	// Get CAD result policy
	$policies = PluginResultPolicy::select();

	//--------------------------------------------------------------------------
	// Settings for Smarty
	//--------------------------------------------------------------------------
	$smarty->assign('volumeInfo', $volumeInfo);
	$smarty->assign('patient', $patient);
	$smarty->assign('policies', $policies);
	//--------------------------------------------------------------------------

}
catch(Exception $e)
{
	$smarty->assign('params', $params);
	$smarty->assign('message', $e->getMessage());
	$smarty->display('critical_error.tpl');
	exit;
}

if ($plugin) $smarty->assign('plugin', $plugin);
$smarty->assign('params', $params);
$smarty->display('cad_job/cad_execution.tpl');

?>
