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
		"id" => array(
			"label" => 'Plug-in ID',
			"type" => "int",
			"required" => true
		)
	));

	if(!$validator->validate($_GET))
		throw new Exception(implode("\n", $validator->errors));
	$params = $validator->output;

	$plugin = new Plugin($params['id']);
	if (!isset($plugin->plugin_id))
		throw new Exception('Plugin not found');
	$plugin_id = $plugin->plugin_id;

	if ($plugin->type == Plugin::CAD_PLUGIN)
	{
		// Get required CAD series infomation from ruleset
		$rulesetList = $plugin->PluginCadSeries;
		foreach ($rulesetList as $volume)
		{
			$item = array(
				'volume_id' => $volume->volume_id,
				'label' => $volume->volume_label,
				'filters' => array()
			);
			$rulesets = json_decode($volume->ruleset, true);
			foreach ($rulesets as $ruleset)
			{
				$item['filters'][] = $ruleset['filter'];
			}
			$volumes[] = $item;
		}
	}


	// Executed cases
	$sqlStr = "SELECT DATE(MIN(executed_at))"
			. " FROM executed_plugin_list"
			. " WHERE plugin_id=?";
	$params['oldestDate'] = DBConnector::query($sqlStr, array($plugin_id));

	$sqlStr = "SELECT status, COUNT(*)"
			. " FROM executed_plugin_list"
			. " WHERE plugin_id=?"
			. " GROUP BY status";
	$result = DBConnector::query($sqlStr, array($plugin_id), 'ALL_NUM');

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
	$smarty->assign('plugin', $plugin);
	$smarty->assign('caseNum', $caseNum);
	$smarty->assign('volumes', $volumes);
	$smarty->assign('params', $params);
	$smarty->display('plugin_info.tpl');
	//--------------------------------------------------------------------------
}
catch(PDOException $e)
{
	$smarty->assign('message', 'Database error.');
	$smarty->display('critical_error.tpl');
}
catch(Exception $e)
{
	$smarty->assign('message', $e->getMessage());
	$smarty->display('critical_error.tpl');
}

