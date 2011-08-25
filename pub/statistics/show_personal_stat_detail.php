<?php
session_start();

include("../common.php");

//-----------------------------------------------------------------------------------------------------------------
// Import $_POST variables and validation
//-----------------------------------------------------------------------------------------------------------------
$mode = (isset($_GET['mode']) && $_GET['mode'] == 'redraw') ? 'redraw' : "";

$params = array();
$validator = new FormValidator();

$validator->addRules(array(
	"dateFrom" => array(
		"type" => "date",
		"errorMes" => "'Series date' is invalid."),
	"dateTo" => array(
		"type" => "date",
		"errorMes" => "'Series date' is invalid."),
	"cadName" => array(
		"type" => "cadname",
		"otherwise"=> "all"),
	"version" => array(
		"type" => "version",
		"otherwise" => "all"),
	"minSize" => array(
		'type' => 'numeric',
		'min' => '0.0',
		'default' => '0.0'),
	"maxSize" => array(
		'type' => 'numeric',
		'min' => '0.0',
		'default' => '100000'),
	"dataStr" => array(
		"type" => "string",
		"regex" => "/^[-\d\s\^]+$/",
		"errorMes" => "Input data (dataStr) is invalid."),
	"knownTpFlg" => array(
		"type" => "select",
		"options" => array('0', '1'),
		"default" => "1",
		"otherwise" => "1"),
	"missedTpFlg" => array(
		"type" => "select",
		"options" => array('0', '1'),
		"default" => "1",
		"otherwise" => "1"),
	"subTpFlg" => array(
		"type" => "select",
		"options" => array('0', '1'),
		"default" => "1",
		"otherwise" => "1"),
	"fpFlg" => array(
		"type" => "select",
		"options" => array('0', '1'),
		"default" => "1",
		"otherwise" => "1"),
	"pendingFlg" => array(
		"type" => "select",
		"options" => array('0', '1'),
		"default" => "1",
		"otherwise" => "1")
	));

if($validator->validate($_POST))
{
	$params = $validator->output;
	$params['errorMessage'] = "&nbsp;";

	if(isset($params['minSize']) && isset($params['maxSize']) && $params['minSize'] > $params['maxSize'])
	{
		//$params['errorMessage'] = "Range of 'Size (diameter)' is invalid.";
		$tmp = $params['minSize'];
		$params['minSize'] = $params['maxSize'];
		$params['maxSize'] = $tmp;
	}
}
else
{
	$params = $validator->output;
	$params['errorMessage'] = implode('<br/>', $validator->errors);
}

if($_SESSION['allStatFlg'])		$userID = $_POST['evalUser'];
else							$userID = $_SESSION['userID'];
//--------------------------------------------------------------------------------------------------------


// Set minimum volume and maximum volume
$minVolume = 4.0 / 3.0 * pi() * pow($params['minSize']/2.0, 3);
$maxVolume = 4.0 / 3.0 * pi() * pow($params['maxSize']/2.0, 3);

$dstData = array('errorMessage' => $params['errorMessage'],
				 'caseNum'      => 0,
				 'theadHtml'    => "",
				 'tbodyHtml'    => "",
                 'dataStr'      => "");

// Get selection value from presentation.json
$fileName = $WEB_UI_ROOT . $DIR_SEPARATOR . 'plugin'
			. $DIR_SEPARATOR . $params['cadName'] . '_v.' . $params['version']
			. $DIR_SEPARATOR . 'presentation.json';

$jsonData = json_decode(file_get_contents($fileName), TRUE);


//var_dump($evaluation);


if($params['errorMessage'] == "&nbsp;")
{
	try
	{
		$pdo = DBConnector::getConnection();

		// Get result table name
		$resultTableName = "";

		if($params['version'] != "all")
		{
			$sqlStr = "SELECT cm.result_table FROM plugin_master pm, plugin_cad_master cm"
					. " WHERE cm.plugin_id=pm.plugin_id AND pm.plugin_name=? AND pm.version=?";
			$resultTableName =  DBConnector::query($sqlStr, array($params['cadName'], $params['version']), 'SCALAR');
		}

		//--------------------------------------------------------------------------------------------------------------
		// Create user list
		//--------------------------------------------------------------------------------------------------------------
		$userList = array();
		$sqlParams = array();
		
		if($userID == "all")
		{
			$sqlStr = "SELECT DISTINCT fl.entered_by"
					. " FROM executed_plugin_list el, feedback_list fl, plugin_master pm,"
					. " executed_series_list es, series_list sr"
					. " WHERE es.job_id=el.job_id"
					. " AND es.volume_id=0"
					. " AND sr.sid = es.series_sid"
					. " AND pm.job_id=el.job_id"
					. " AND pm.plugin_name=?";

			$sqlParams[] = $params['cadName'];

			if($params['version'] != "all")
			{
				$sqlStr.= " AND pm.version=?";
				$sqlParams[] = $params['version'];
			}

			if($params['dateFrom'] != "")
			{
				$sqlStr .= " AND sr.series_date>=?";
				$sqlParams[] = $params['dateFrom'];
			}

			if($params['dateTo'] != "")
			{
				$sqlStr .= " AND sr.series_date<=?";
				$sqlParams[] = $params['dateTo'];
			}

			$sqlStr .= " AND lf.job_id=el.job_id"
					.  " AND fl.is_consensual='f'"
					.  " AND fl.status=1"
					.  "ORDER BY fl.entered_by ASC";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);
			$userList = $stmt->fetchAll(PDO::FETCH_COLUMN);
		}
		else $userList[] = $userID;
		$userNum = count($userList);
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// Create html of result table
		//--------------------------------------------------------------------------------------------------------------
		$dstData['plotLegend'] = array();
		
		for($k=0; $k<$userNum; $k++)
		{
			$personalEvalArr   = array();
			$consensualEvalArr = array();

			foreach($jsonData['SelectionFeedbackListener']['personal'] as $item)
			{
				$personalEvalArr += array("{$item['value']}"
											=> array(0, str_replace('&nbsp;', NULL, $item['label'])));
				$dstData['plotLegend'][] = str_replace('&nbsp;', NULL, $item['label']);
			}
			$dstData['plotLegend'][] = 'redrawBtn';

			foreach($jsonData['SelectionFeedbackListener']['consensual'] as $item)
			{
				$consensualEvalArr += array("{$item['value']}"
											=> array(0, str_replace('&nbsp;', NULL, $item['label'])));
			}

			$fnNum = 0;
			$totalNum = 0;
			//----------------------------------------------------------------------------------------------------
			$sqlParamCntEval = array();
			$sqlParamCntFN   = array();

			$sqlStrCntEval = "SELECT DISTINCT(el.job_id)"
						   . " FROM executed_plugin_list el, executed_series_list es,"
						   . " plugin_master pm, feedback_list fl, candidate_classification cc,"
						   . " series_list sr";

			if($params['version'] != "all")	$sqlStrCntEval .= ', "' . $resultTableName . '" cad';

			$sqlStrCntEval .= " WHERE pm.plugin_id=el.plugin_id"
						   .  " AND fl.job_id=el.job_id AND cc.fb_id=fl.fb_id"
						   .  " AND es.job_id=el.job_id AND es.volume_id=0"
					       .  " AND sr.sid = es.series_sid"
						   .  " AND pm.plugin_name=?";

			$sqlStrCntFN  = "SELECT SUM(fn.fn_num)"
						  . " FROM executed_plugin_list el, executed_series_list es,"
						  . " plugin_master pm, feedback_list fl, fn_count fn, series_list sr"
						  . " WHERE pm.plugin_id=el.plugin_id"
						  . " AND fl.job_id=el.job_id AND fn.fb_id=fl.fb_id"
						  . " AND es.job_id=el.job_id AND es.volume_id=0"
						  . " AND sr.sid = es.series_sid"
						  . " AND pm.plugin_name=?";

			$sqlParamCntEval[] = $params['cadName'];
			$sqlParamCntFN[]   = $params['cadName'];

			if($params['version'] != "all")
			{
				$sqlStrCntEval .= " AND pm.version=?";
				$sqlStrCntFN   .= " AND pm.version=?";

				$sqlParamCntEval[] = $params['version'];
				$sqlParamCntFN[]   = $params['version'];
			}

			if($params['dateFrom'] != "")
			{
				$sqlStrCntEval .= " AND sr.series_date>=?";
				$sqlStrCntFN   .= " AND sr.series_date>=?";

				$sqlParamCntEval[] = $params['dateFrom'];
				$sqlParamCntFN[]   = $params['dateFrom'];
			}

			if($params['dateTo'] != "")
			{
				$sqlStrCntEval .= " AND sr.series_date<=?";
				$sqlStrCntFN   .= " AND sr.series_date<=?";

				$sqlParamCntEval[] = $params['dateTo'];
				$sqlParamCntFN[]   = $params['dateTo'];
			}

			if($params['version'] != "all")
			{
			  	$sqlStrCntEval .= " AND cad.job_id=el.job_id"
							   .  " AND (cad.sub_id=cc.candidate_id)"
							   .  " AND cad.volume_size>=? AND cad.volume_size<=?";

				$sqlParamCntEval[] = $minVolume;
				$sqlParamCntEval[] = $maxVolume;
			}

			$tmpStr = " AND fl.entered_by=?"
					. " AND fl.is_consensual='f'"
					. " AND fl.status=1";

			$sqlStrCntEval .= $tmpStr . " ORDER BY el.job_id ASC";
			$sqlStrCntFN   .= $tmpStr;

			$sqlParamCntEval[] = $userList[$k];
			$sqlParamCntFN[]   = $userList[$k];
			
			$fnNum = DBConnector::query($sqlStrCntFN, $sqlParamCntFN, 'SCALAR');
			if($fnNum == "" || $fnNum < 0)  $fnNum = 0;
			$totalNum += $fnNum;

			$stmt = $pdo->prepare($sqlStrCntEval);
			$stmt->execute($sqlParamCntEval);
			$jobIdList = $stmt->fetchAll(PDO::FETCH_COLUMN);

			$dstData['caseNum'] = count($jobIdList);

			foreach($jobIdList as $jobID)
			{
				$sqlParams = array();
				$sqlStr = "SELECT cc.evaluation, COUNT(*)"
						. " FROM executed_plugin_list el, feedback_list fl,"
						. " candidate_classification cc";

				if($params['version'] != "all")  $sqlStr .= ', "' . $resultTableName . '" cad';

				$sqlStr .= " WHERE el.job_id =?"
						.  " AND fl.job_id=el.job_id"
						.  " AND cc.fb_id=fl.fb_id";
				$sqlParams[] = $jobID;

				if($params['version'] != "all")
				{
					$sqlStr .= " AND cad.job_id=el.job_id"
							.  " AND cad.sub_id=cc.candidate_id"
							.  " AND cad.volume_size>=?"
							.  " AND cad.volume_size<=?";
					$sqlParams[] = $minVolume;
					$sqlParams[] = $maxVolume;
				}

				$sqlStr .= " AND fl.entered_by=?"
						.  " AND fl.is_consensual='f'"
						.  " AND fl.status=1"
						.  " GROUP BY cc.evaluation"
						.  " ORDER BY cc.evaluation";
				$sqlParams[] = $userList[$k];

				$stmtDetail = $pdo->prepare($sqlStr);
				$stmtDetail->execute($sqlParams);
				$evalResult = $stmtDetail->fetchAll(PDO::FETCH_NUM);

				foreach($evalResult as $result)
				{
					$personalEvalArr[$result[0]][0] += $result[1];
					$totalNum += $result[1];
					
					if($personalEvalArr[$result[0]][1] == "missed TP" && $result[1] > 0)
					{
						$sqlParams = array();
						
						$sqlStr = "SELECT cc.candidate_id"
								. " FROM feedback_list fl, candidate_classification cc";
						
						if($params['version'] != "all")  $sqlStr .= ', "' . $resultTableName . '" cad';

						$sqlStr .= " WHERE fl.job_id=?"
								.  " AND fl.entered_by=?"
								.  " AND fl.is_consensual='f'"
								.  " AND fl.status=1"
								.  " AND cc.fb_id=fl.fb_id"
						        .  " AND cc.evaluation=?";
						$sqlParams[] = $jobID;
						$sqlParams[] = $userList[$k];
						$sqlParams[] = $result[0];

						if($params['version'] != "all")
						{
							$sqlStr .= " AND cad.job_id=fl.job_id"
									.  " AND cad.sub_id=cc.candidate_id"
								    .  " AND cad.volume_size>=?"
								    .  " AND cad.volume_size<=?";
							$sqlParams[] = $minVolume;
							$sqlParams[] = $maxVolume;
						}

						$stmtDetail = $pdo->prepare($sqlStr);
						$stmtDetail->execute($sqlParams);

						while($candID = $stmtDetail->fetchColumn())
						{
							$sqlStr = "SELECT cc.evaluation"
									. " FROM feedback_list fl,"
									. " candidate_classification cc"
									. " WHERE fl.job_id=?"
									. " AND fl.is_consensual='t'"
									. " AND fl.status=1"
									. " AND cc.fb_id=fl.fb_id"
									. " AND cc.candidate_id=?";

							$stmtEvalDetail = $pdo->prepare($sqlStr);
							$stmtEvalDetail->execute(array($jobID, $candID));

							if($stmtEvalDetail->rowCount() > 0)
							{
								$tmp = $stmtEvalDetail->fetchColumn();
								$consensualEvalArr[$tmp][0]++;
							}
							else  $consensualEvalArr["0"][0]++; // pending
						}
					}
				} // end foreach
			} // end foreach

			// html of thead
			$dstData['theadHtml']   = '<tr>'
									. '<th rowspan="2">User</th>'
									. '<th rowspan="2">Case</th>';
									
			foreach($personalEvalArr as $item)
			{
				$dstData['theadHtml'] .= '<th rowspan="2">' . $item[1] . '</th>';
			}
			
			$dstData['theadHtml']  .= '<th rowspan="2">FN</th>'
									. '<th rowspan="2">Total</th>'
									. '<th colspan="' . count($consensualEvalArr) . '">'
									. 'Detail of missed TP</th>'
									. '</tr>'
									. '<tr>';
			
			foreach($consensualEvalArr as $item)
			{
				$dstData['theadHtml'] .= '<th>' . $item[1] . '</th>';
			}
			
			$dstData['theadHtml'] .= '</tr>';

			// html of tbody
			$dstData['tbodyHtml'] = '<tr';

			if($k%2==1) $dstData['tbodyHtml'] .= ' class="column"';

			$dstData['tbodyHtml']  .= '>'
									. '<td>' . $userList[$k] . '</td>'
									. '<td>' . $dstData['caseNum'] . '</td>';

			foreach($personalEvalArr as $item)
			{
				$dstData['tbodyHtml'] .= '<td>' . $item[0] . '</td>';
			}

			$dstData['tbodyHtml']  .= '<td>' . $fnNum . '</td>'
									.  '<td>' . $totalNum . '</td>';

			foreach($consensualEvalArr as $item)
			{
				$dstData['tbodyHtml'] .= '<td>' . $item[0] . '</td>';
			}

			$dstData['tbodyHtml'] .= '</tr>';
		} // end for(k)
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// Create scatter plot
		//--------------------------------------------------------------------------------------------------------------
		if($userNum == 1 && $params['version'] != "All" && $params['dataStr'] == "")
		{
			$sqlParams = array($minVolume, $maxVolume, $params['cadName'], $params['version'], $userList[0]);

			$sqlStr = "SELECT el.job_id, cc.evaluation,"
					. " cad.location_x, cad.location_y, cad.location_z"
					. " FROM executed_plugin_list el,"
					. "executed_series_list es,"
					. "feedback_list fl,"
					. "candidate_classification cc,"
					. "plugin_master pm,"
					. "series_list sr,"
					. $resultTableName . " cad"
					. " WHERE el.job_id=es.job_id"
					. " AND el.job_id=cad.job_id"
					. " AND el.job_id=fl.job_id"
					. " AND cc.fb_id=fl.fb_id"
					. " AND cad.sub_id=cc.candidate_id"
					. " AND cad.volume_size>=?"
					. " AND cad.volume_size<=?"
					. " AND es.volume_id=0"
					. " AND es.series_sid=sr.sid"
					. " AND pm.plugin_id=el.plugin_id"
					. " AND pm.plugin_name=?"
					. " AND pm.version=?"
					. " AND fl.is_consensual='f'"
					. " AND fl.status=1"
					. " AND fl.entered_by=?";

			if($params['dateFrom'] != "")
			{
				$sqlStr .= " AND sr.series_date>=?";
				$sqlParams[] = $params['dateFrom'];
			}

			if($params['dateTo'] != "")
			{
				$sqlStr .= " AND sr.series_date<=?";
				$sqlParams[] = $params['dateTo'];
			}

			$sqlStr .= "ORDER BY cc.evaluation ASC, el.job_id ASC, cc.candidate_id ASC";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);

			$tmpDataArr = array();

			$sqlStr = "SELECT MAX(case when key='crop_org_x' then value else null end),"
					. "MAX(case when key='crop_org_y' then value else null end),"
					. "MAX(case when key='crop_org_z' then value else null end),"
					. "MAX(case when key='crop_width' then value else null end),"
					. "MAX(case when key='crop_height' then value else null end),"
					. "MAX(case when key='crop_depth' then value else null end),"
					. "MAX(case when key='start_img_num' then value else '1' end)"
					. " FROM executed_plugin_attributes WHERE job_id=? GROUP BY job_id";
					
			$stmtAttr = $pdo->prepare($sqlStr);

			while($result = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$stmtAttr->bindValue(1, $result['job_id']);
				$stmtAttr->execute();
				$attrArr = $stmtAttr->fetch(PDO::FETCH_NUM);

				$tmpDataArr[] = $result['evaluation'];
				$tmpDataArr[] = (real)($result['location_x'] - $attrArr[0]) / (real)$attrArr[3];
				$tmpDataArr[] = (real)($result['location_y'] - $attrArr[1]) / (real)$attrArr[4];
				$tmpDataArr[] = (real)(($result['location_z'] - ($attrArr[6]-1)) - $attrArr[2]) / (real)$attrArr[5];
			}
		
			$params['dataStr'] = implode('^', $tmpDataArr);

			$length = count($tmpDataArr)/4;
			$plotData = array();

			for($j=0; $j<$length; $j++)
			for($i=0; $i<4; $i++)
			{
				$plotData[$j][$i] = $tmpDataArr[$j * 4 + $i];
			}

			$section = array('XY', 'XZ', 'YZ');
			include('create_scatter_plot.php');
		
			// Get cache area
			$sqlStr = "SELECT storage_id, path FROM storage_master"
					. "  WHERE type=3 AND current_use='t'";
			$webCacheRes = DBConnector::query($sqlStr, NULL, 'ARRAY_ASSOC');
			//if (!is_array($webCacheRes))
			//	throw new Exception('Web cache directory not configured');

			foreach($section as $val)
			{
				$tmpFname = 'plot' . $val . '_' . microtime(true) . '.png';

				CreateScatterPlot($plotData, $val,
									$webCacheRes['path'] . $DIR_SEPARATOR . $tmpFname,
									$params['knownTpFlg'],
									$params['missedTpFlg'],
									$params['subTpFlg'],
									$params['fpFlg'],
									$params['pendingFlg']);

				$dstData[$val] = 'storage/' . $webCacheRes['storage_id'] . '/' . $tmpFname;
			}

			$dstdata['dataStr'] = $params['dataStr'];
		}
		//--------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
}

echo json_encode($dstData);
?>
