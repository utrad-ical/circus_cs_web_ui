<?php
require_once('common.php');
Auth::checkSession();

$smarty = new SmartyEx();

try
{
	//--------------------------------------------------------------------------
	// Import $_GET variables and validation
	//--------------------------------------------------------------------------
	$validator = new FormValidator();
	$validator->addRules(array(
		"pluginName" => array(
			"label" => 'Plug-in name',
			"type" => "cadname",
			"required" => true
		),
		"version" => array(
			"label" => 'version',
			"type" => "version",
			"required" => true
		)
	));

	if($validator->validate($_GET))
	{
		$params = $validator->output;
	}
	else
	{
		throw new Exception(implode("\n", $validator->errors));
	}

	//--------------------------------------------------------------------------

	$pdo = DBConnector::getConnection();

	// Description, input type
	$sqlStr = "SELECT pm.plugin_id, pm.type, cm.input_type, pm.description"
			. " FROM plugin_master pm, plugin_cad_master cm"
			. " WHERE cm.plugin_id=pm.plugin_id"
			. " AND pm.plugin_name=?"
			. " AND pm.version=?";
	$condArr = array($params['pluginName'], $params['version']);
	$result = DBConnector::query($sqlStr, $condArr, 'ARRAY_ASSOC');

	if (!isset($result['plugin_id']))
		throw new Exception('Plugin not found');

	$params['pluginID']    = $result['plugin_id'];
	$params['pluginType']  = $result['type'];
	$params['inputType']   = $result['input_type'];
	$params['description'] = $result['description'];

	// Get required CAD series infomation from ruleset
	$rulesetList = PluginCadSeries::select(array('plugin_id' => $params['pluginID']));
	foreach ($rulesetList as $volume)
	{
		$item = array('volume_id' => $volume->volume_id, 'filters' => array());
		$rulesets = json_decode($volume->ruleset, true);
		foreach ($rulesets as $ruleset)
		{
			$item['filters'][] = $ruleset['filter'];
		}
		$volumes[] = $item;
	}

	// Executed cases
	$sqlStr = "SELECT DATE(MIN(executed_at))"
			. " FROM executed_plugin_list"
			. " WHERE plugin_id=?";
	$params['oldestDate'] = DBConnector::query($sqlStr, array($params['pluginID']));

	$sqlStr = "SELECT status, COUNT(*)"
			. " FROM executed_plugin_list"
			. " WHERE plugin_id=?"
			. " GROUP BY status";
	$result = DBConnector::query($sqlStr, array($params['pluginID']), 'ALL_NUM');

	$caseNum = array_fill(0, 3, 0);

	foreach($result as $r)
	{
		switch($r[0])
		{
			case -1:
				$caseNum['failed'] = $r[1];
				break;
			case  1:
			case  2:
			case  3:
				$caseNum['processing'] += $r[1];
				break;
			case  4:
				$caseNum['success'] = $r[1];
				break;
		}
	}

	//--------------------------------------------------------------------------
	// Settings for Smarty
	//--------------------------------------------------------------------------
	$smarty->assign('caseNum', $caseNum);
	$smarty->assign('volumes', $volumes);
	$smarty->assign('params', $params);
	$smarty->display('plugin_info.tpl');
	//--------------------------------------------------------------------------
}
catch(Exception $e)
{
	$smarty->assign('message', $e->getMessage());
	$smarty->display('critical_error.tpl');
}

?>
