<?php
$params = array('toTopDir' => "../");
include_once("../common.php");
Auth::checkSession();

$seriesUIDArr = array();
$modalityArr = array();
$seriesFilterNumArr = array();
$seriesFilterArr = array();

$smarty = new SmartyEx();

try
{
	//------------------------------------------------------------------------------------------------------------------
	// Import $_GET variables and validation
	//------------------------------------------------------------------------------------------------------------------
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

	if($validator->validate($_GET))
	{
		$params += $validator->output;
		$studyUIDArr[]  = $params['studyInstanceUID'];
		$seriesUIDArr[] = $params['seriesInstanceUID'];
	}
	else
	{
		throw new Exception (implode('<br/>', $validator->errors));
	}

	$params['mode'] = '';
	$params['inputType'] = 0;
	$params['errorMessage'] = "";

	if($params['srcList'] == 'todaysSeries')	$params['listTabTitle'] = "Today's series";
	else										$params['listTabTitle'] = "Series list";

	$user = Auth::currentUser();
	$params['userID'] = $user->user_id;
	//------------------------------------------------------------------------------------------------------------------

	$pdo = DBConnector::getConnection();

	//----------------------------------------------------------------------------------------------------------
	// Get initial value from database
	//----------------------------------------------------------------------------------------------------------
	$sqlStr = "SELECT * FROM series_join_list WHERE series_instance_uid=?";
	$result = DBConnector::query($sqlStr, $seriesUIDArr[0], 'ARRAY_ASSOC');

	$params['patientID']   = $result['patient_id'];
	$params['patientName'] = $result['patient_name'];

	// Check plugin
	$sqlStr = "SELECT pm.plugin_id, pm.type, pm.exec_enabled, cm.input_type"
			. " FROM plugin_master pm, plugin_cad_master cm"
			. " WHERE cm.plugin_id=pm.plugin_id"
			. " AND pm.plugin_name=? AND pm.version=?";
	$plugin = DBConnector::query($sqlStr, array($params['cadName'], $params['version']), 'ALL_ASSOC');
	
	if(count($plugin) != 1)
	{
		throw new Exception($params['cadName'].' ver.'.$params['version'].' is not defined.');
	}
	else if($plugin[0]['type'] != 1)
	{
		throw new Exception($params['cadName'].' ver.'.$params['version'].' is CAD plug-in.');
	}
	else if($plugin[0]['input_type'] < 0 || 2 < $plugin[0]['input_type'])
	{
		throw new Exception('Input type is incorrect ('.$params['cadName'].' ver.'.$params['version'].')');
	}
	else if(!$plugin[0]['exec_enabled'])
	{
		throw new Exception($params['cadName'] . ' ver.' . $params['version'] . ' is not allowed to execute.');
	}
	
	$params['pluginID']  = $plugin[0]['plugin_id'];
	$params['inputType'] = $plugin[0]['input_type'];
	$params['mode'] = 'confirm';

	$seriesUIDStr = $seriesUIDArr[0];
	
	if($params['inputType'] != 0)
	{
		$defaultSelectedSrUID = array();
		$defaultSelectedSrUID[0] = $seriesUIDArr[0];

		$seriesList = array();
		$seriesList[0][0][0] = $result['series_instance_uid'];
		$seriesList[0][0][1] = $result['study_id'];
		$seriesList[0][0][2] = $result['series_number'];
		$seriesList[0][0][3] = $result['series_date'];
		$seriesList[0][0][4] = $result['series_time'];
		$seriesList[0][0][5] = $result['image_number'];
		$seriesList[0][0][6] = $result['series_description'];
		
		// Get the number of required series
		$sqlStr = "SELECT DISTINCT volume_id FROM plugin_cad_series WHERE plugin_id=? ORDER BY volume_id ASC";
		$volumeIdList = DBConnector::query($sqlStr, array($params['pluginID']), 'ALL_COLUMN');

		$seriesNum = count($volumeIdList);
		$seriesFilter = new SeriesFilter();
		
		$selectedSrNumArr = array_fill(0, $seriesNum, 0);

		for($k=0; $k<$seriesNum; $k++)
		{
			// Get ruleset
			$sqlStr = "SELECT ruleset FROM plugin_cad_series"
					. " WHERE plugin_id=?"
					. " AND volume_id=?";
			$ruleList = DBConnector::query($sqlStr, array($params['pluginID'], $k), 'ALL_ASSOC');

			if(count($ruleList) <= 0)
			{
				throw new Exception("Ruleset for series " . $k+1 . " is not found.");
			}
			else // TODO: show ruleset from JSON
			{
				foreach($ruleList as $r)
				{
					$ruleSet = json_decode($r['ruleset'], true);
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
			}

			if($k > 0)
			{
				// Get series join list
				$s = new SeriesJoin();
				
				if($params['inputType'] == 1)
				{
					$sdata = $s->find(array("study_instance_uid" => $params['studyInstanceUID']));
				}
				else if($params['inputType'] == 2)
				{
					$sdata = $s->find(array("patient_id" => $params['patientID']));
				}
				
			    $matchedSrCnt = 0;

				for($j = 0; $j < count($sdata); $j++)
				{
					$seriesData = $sdata[$j]->getData();

					// rule maching
					foreach($ruleList as $r)
					{
						$ruleSet = json_decode($r['ruleset'], true);

						if($seriesFilter->processRuleSets($seriesData, $ruleSet))
						{
							$cnt = 0;

							for($i = 0; $i < $k; $i++)
							{
								if($seriesData['series_instance_uid'] != $defaultSelectedSrUID[$i]) $cnt++;
							}

							if($cnt == $k)
							{
								if($matchedSrCnt == 0) $defaultSelectedSrUID[$k] = $seriesData['series_instance_uid'];
								$matchedSrCnt++;
							}
						
							if($seriesData['series_instance_uid'] != $defaultSelectedSrUID[0])
							{
								$seriesList[$k][$selectedSrNumArr[$k]][0] = $seriesData['series_instance_uid'];
								$seriesList[$k][$selectedSrNumArr[$k]][1] = $seriesData['study_id'];
								$seriesList[$k][$selectedSrNumArr[$k]][2] = $seriesData['series_number'];
								$seriesList[$k][$selectedSrNumArr[$k]][3] = $seriesData['series_date'];
								$seriesList[$k][$selectedSrNumArr[$k]][4] = $seriesData['series_time'];
								$seriesList[$k][$selectedSrNumArr[$k]][5] = $seriesData['image_number'];
								$seriesList[$k][$selectedSrNumArr[$k]][6] = $seriesData['series_description'];
								$selectedSrNumArr[$k]++;
							}
						}
					} // end foreach: $ruleList
				} // end for: $j

				if($matchedSrCnt == 0)
				{
					$params['mode'] = 'error';
					continue;
				}
			}
		} // end for: $k
		
		if($params['mode'] != 'error')
		{
			for($k=1; $k<$seriesNum; $k++)
			{
				if($selectedSrNumArr[$k] != 1)
				{
					$params['mode'] = 'select';
					$seriesUIDStr = $seriesUIDArr[0];
					break;
				}
				$seriesUIDStr .= '^' . $defaultSelectedSrUID[$k];
			}
			$numSelectedSrStr = implode('^', $selectedSrNumArr);
		}
	}
	
	if($_SESSION['anonymizeFlg'] == 1)
	{
		$params['patientID']   = PinfoScramble::encrypt($params['patientID'], $_SESSION['key']);
		$params['patientName'] = PinfoScramble::scramblePtName();
	}

	// Get CAD result policy
	$dummy = new PluginResultPolicy();
	$policies = $dummy->find();
	$policyArr = array();

	foreach ($policies as $policy)
	{
		$policyArr[] = array(
			'id' => $policy->policy_id,
			'name' => $policy->policy_name
		);
	}

	//--------------------------------------------------------------------------------------------------------------
	// Settings for Smarty
	//--------------------------------------------------------------------------------------------------------------
	$smarty->assign('seriesList', $seriesList);
	$smarty->assign('seriesNum',  $seriesNum);

	$smarty->assign('seriesUIDStr',         $seriesUIDStr);
	$smarty->assign('selectedSrNumArr',     $selectedSrNumArr);
	$smarty->assign('numSelectedSrStr',     $numSelectedSrStr);
	
	$smarty->assign('selectedSrStr',        $selectedSrStr);
	$smarty->assign('defaultSelectedSrUID', $defaultSelectedSrUID);
	
	$smarty->assign('modalityArr',          $modalityArr);
	
	$smarty->assign('seriesFilterArr',      $seriesFilterArr);
	$smarty->assign('seriesFilterNumArr',   $seriesFilterNumArr);

	$smarty->assign('policyArr',            $policyArr);
	//--------------------------------------------------------------------------------------------------------------

}
catch(PDOException $e)
{
	$params['mode'] = 'error';
	$params['errorMessage'] = $e->getMessage();
}
catch(Exception $e)
{
	$params['mode'] = 'error';
	$params['errorMessage'] = $e->getMessage();
}

$pdo = null;

$smarty->assign('params', $params);
$smarty->display('cad_job/cad_execution.tpl');

?>
