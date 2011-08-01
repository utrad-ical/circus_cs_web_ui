<?php
require_once('common.php');
Auth::checkSession();

$smarty = new SmartyEx();

try
{
	//------------------------------------------------------------------------------------------------------------------
	// Import $_GET variables and validation
	//------------------------------------------------------------------------------------------------------------------
	$params = array(); 
	$validator = new FormValidator();

	$validator->addRules(array(
		"pluginName" => array(
			"label" => 'Plug-in name',
			"type" => "cadname",
			"required" => true),
		"version" => array(
			"label" => 'version',	
			"type" => "version",
			"required" => true)
		));

	if($validator->validate($_GET))
	{
		$params += $validator->output;
	}
	else
	{
		throw new Exception (implode('<br/>', $validator->errors));
	}

	$user = Auth::currentUser();
	$params['userID'] = $user->user_id;
	//------------------------------------------------------------------------------------------------------------------

	$pdo = DBConnector::getConnection();

	// Description, input type
	$sqlStr = "SELECT pm.plugin_id, pm.type, cm.input_type, pm.description"
			. " FROM plugin_master pm, plugin_cad_master cm"
			. " WHERE cm.plugin_id=pm.plugin_id"
			. " AND pm.plugin_name=?"
			. " AND pm.version=?";
	$condArr = array($params['pluginName'], $params['version']);

	$result = DBConnector::query($sqlStr, $condArr, 'ARRAY_ASSOC');
	
	$params['pluginID']    = $result['plugin_id'];
	$params['pluginType']  = $result['type'];
	$params['inputType']   = $result['input_type'];
	$params['description'] = $result['description'];
	
	// Get required CAD series infomation from ruleset
	$sqlStr = "SELECT volume_id, ruleset FROM plugin_cad_series"
			. " WHERE plugin_id=?"
			. " ORDER BY volume_id ASC";
	$ruleList = DBConnector::query($sqlStr, array($params['pluginID']), 'ALL_ASSOC');
	$seriesNum = (count($ruleList) > 0) ? count($ruleList) : 0;
	
	$modalityArr       = array();
	$seriesFilterArr  = array();
	$selectedSrNumArr = array();

	for($k = 0; $k < $seriesNum; $k++)
	{
		$ruleSet = json_decode($ruleList[$k]['ruleset'], true);
		$seriesFilterNumArr[$k] = 0;
					
		foreach($ruleSet as $rules)
		{
			$ruleFilterGroup = $rules['filter']['group'];
			$ruleFilterMembers = $rules['filter']['members'];

			$parsedRules = array();

			foreach($ruleFilterMembers as $ruleFilter)
			{
				if($ruleFilter['key'] == 'modality')
				{
					$modalityArr[$k] = $ruleFilter['value'];
				}
				else
				{
					$parsedRules[] = $ruleFilter['key']
									. $ruleFilter['condition']
									. $ruleFilter['value'];
				}
			}
			$seriesFilterArr[$k][$seriesFilterNumArr[$k]++] = implode(', ', $parsedRules)
															. ' (' . $ruleFilterGroup . ')';
		}
	}

	// Executed cases
	$sqlStr = "SELECT MIN(executed_at)"
			. " FROM executed_plugin_list"
			. " WHERE plugin_id=?";
	$params['oldestDate'] = substr(DBConnector::query($sqlStr, array($params['pluginID']), 'SCALAR'), 0, 10);

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
			case -1:  $caseNum[1]  = $r[1];	break;
			case  1:  $caseNum[2] += $r[1];	break;
			case  2:  $caseNum[2] += $r[1];	break;
			case  3:  $caseNum[2] += $r[1];	break;
			case  4:  $caseNum[0]  = $r[1];	break;
		}
	}

	// TODO: Rewrote summary of feedback evaluation
	//$evalNumConsensual = $tpNumConsensual = $fnNumConsensual = 0;
	//$missedTPNum = $knownTPNum = $fnNumPersonal = 0;

	//if($caseNum > 0 && $resultType == 1)
	//{
	//	// Consensual based
	//	$sqlStr = "SELECT COUNT(*)"
	//		    . " FROM executed_plugin_list el, lesion_classification lf"
	//		    . " WHERE el.plugin_name=? AND el.version=?"
	//		    . " AND lf.job_id=el.job_id"
	//		    . " AND lf.is_consensual ='t'"
	//		    . " AND lf.interrupted ='f'";

	//	$stmt = $pdo->prepare($sqlStr);
	//	$stmt->execute($condArr);

	//	$evalNumConsensual = $stmt->fetchColumn();
	//	if($evalNumConsensual == "")  $evalNumConsensual = 0;

	//	$sqlStr = "SELECT COUNT(*)"
	//	        . " FROM executed_plugin_list el, lesion_classification lf"
	//			. " WHERE el.plugin_name=? AND el.version=?"
	//			. " AND lf.job_id=el.job_id"
	//			. " AND lf.is_consensual ='t'"
	//			. " AND lf.evaluation>=1"
	//			. " AND lf.interrupted ='f'";

	//	$stmt = $pdo->prepare($sqlStr);
	//	$stmt->execute($condArr);

	//	$tpNumConsensual = $stmt->fetchColumn();
	//	if($tpNumConsensual == "")  $tpNumConsensual = 0;

	//	$sqlStr = "SELECT SUM(fn.false_negative_num)"
	//			. " FROM executed_plugin_list el, fn_count fn"
	//			. " WHERE el.plugin_name=? AND el.version=?"
	//			. " AND fn.job_id=el.job_id"
	//			. " AND fn.is_consensual ='t'"
	//			. " AND fn.status>=1";

	//	$stmt = $pdo->prepare($sqlStr);
	//	$stmt->execute($condArr);

	//	$fnNumConsensual = $stmt->fetchColumn();
	//	if($fnNumConsensual == "")  $fnNumConsensual = 0;

	//	array_push($condArr, $userID);

	//	// Personal based
	//	$sqlStr = "SELECT lf.evaluation, COUNT(*) FROM executed_plugin_list el, lesion_classification lf"
	//			. " WHERE el.plugin_name=? AND el.version=?"
	//			. " AND lf.job_id=el.job_id"
	//			. " AND lf.entered_by=?"
	//			.  " AND lf.is_consensual ='f' AND lf.interrupted='f'"
	//			.  " GROUP BY lf.evaluation;";

	//	$stmt = $pdo->prepare($sqlStr);
	//	$stmt->execute($condArr);

	//	while($result = $stmt->fetch(PDO::FETCH_NUM))
	//	{
	//		if($result[0] == 1)       $knownTPNum  += $result[1];
	//		else if($result[0] == 2)  $missedTPNum += $result[1];
	//	}

	//	$sqlStr = "SELECT SUM(fn.false_negative_num)"
	//			. " FROM executed_plugin_list el, fn_count fn"
	//			. " WHERE el.plugin_name=? AND el.version=?"
	//			. " AND fn.job_id=el.job_id"
	//			. " AND fn.entered_by=?"
	//			. " AND fn.is_consensual ='f'"
	//			. " AND fn.status>=1";

	//	$stmt = $pdo->prepare($sqlStr);
	//	$stmt->execute($condArr);

	//	$fnNumPersonal = $stmt->fetchColumn();
	//	if($fnNumConsensual == "")  $fnNumPersonal = 0;
	//}
		
	//--------------------------------------------------------------------------------------------------------------
	// Settings for Smarty
	//--------------------------------------------------------------------------------------------------------------
	$smarty->assign('caseNum',             $caseNum);
	
	$smarty->assign('seriesNum',           $seriesNum);
	$smarty->assign('modalityArr',         $modalityArr);
	$smarty->assign('seriesFilterArr',     $seriesFilterArr);
	$smarty->assign('seriesFilterNumArr',  $seriesFilterNumArr);
	//--------------------------------------------------------------------------------------------------------------
}
catch(PDOException $e)
{
	$params['errorMessage'] = $e->getMessage();
}
catch(Exception $e)
{
	$params['errorMessage'] = $e->getMessage();
}
$pdo = null;

$smarty->assign('params', $params);
$smarty->display('plugin_info.tpl');

?>
