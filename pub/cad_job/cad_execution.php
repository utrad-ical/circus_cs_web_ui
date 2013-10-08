<?php
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

	$params = $validator->output;

	if($params['srcList'] == 'todaysSeries')
		$params['listTabTitle'] = "Today's series";
	else
		$params['listTabTitle'] = "Series list";

	// Check plugin exists
	$plugin = Plugin::selectOne(array('plugin_name' => $params['cadName'], 'version' => $params['version']));
	if (!$plugin)
		throw new Exception($params['cadName'].' ver.'.$params['version'].' is not installed.');

	// Find the default policy
	$default_pol = $plugin->CadPlugin[0]->PluginResultPolicy;
	$initialPolicy = isset($default_pol->policy_name) ?
		$default_pol->policy_name : PluginResultPolicy::DEFAULT_POLICY;

	$primarySeries = Series::selectOne(array('series_instance_uid' => $params['seriesInstanceUID']));
	if (!$primarySeries)
		throw new Exception('Primary series does not exist');

	$avail_series = Job::findExecutableSeries($plugin, $primarySeries->series_instance_uid);

	$vols = $plugin->PluginCadSeries;
	$volumeInfo = array(); // keys: volume ID
	foreach ($vols as $vol)
	{
		$vid = $vol->volume_id;
		$volumeInfo[$vid] = array(
			'id' => $vid,
			'label' => $vol->volume_label,
			'ruleSetList' => json_decode($vol->ruleset, true),
			'targetSeries' => $avail_series[$vid],
		);
	}
	ksort($volumeInfo, SORT_NUMERIC);

	// Get CAD result policy
	$policies = PluginResultPolicy::select();

	// Get Patient Info and set anonymization mode
	Patient::$anonymizeMode = Auth::currentUser()->needsAnonymization();
	$patient = $primarySeries->Study->Patient;

	//--------------------------------------------------------------------------
	// Settings for Smarty
	//--------------------------------------------------------------------------
	$smarty->assign('volumeInfo', $volumeInfo);
	$smarty->assign('patient', $patient);
	$smarty->assign('policies', $policies);
	$smarty->assign('initialPolicy', $initialPolicy);
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

