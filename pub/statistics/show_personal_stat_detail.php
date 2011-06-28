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
			"otherwise"=> "all",
			"errorMes" => "'CAD' is invalid."),
		"version" => array(
			"type" => "version",
			"otherwise" => "all",
			"errorMes" => "'Version' is invalid."),
		"minSize" => array(
			'type' => 'numeric',
			'min' => '0.0',
			'default' => '0.0',
			'errorMes' => "'Size' is invalid."),
		"maxSize" => array(
			'type' => 'numeric',
			'min' => '0.0',
			'default' => '100000',
			'errorMes' => "'Size' is invalid."),
		"dataStr" => array(
			"type" => "string",
			"regex" => "/^[-\d\s\^]+$/",
			"errorMes" => "[ERROR] Input data (dataStr) is invalid."),
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

	$evaluation["1"][0]  = 0;		$evaluation["1"][1]  = 'known TP';
	$evaluation["2"][0]  = 0;		$evaluation["2"][1]  = 'missed TP';
	$evaluation["-1"][0] = 0;		$evaluation["-1"][1] = 'FP';
	$evaluation["0"][0]  = 0;		$evaluation["0"][1]  = 'pending';

	$minVolume = 4.0 / 3.0 * pi() * pow($params['minSize']/2.0, 3);
	$maxVolume = 4.0 / 3.0 * pi() * pow($params['maxSize']/2.0, 3);

	$dstData = array('errorMessage' => $params['errorMessage'],
					 'caseNum'      => 0,
					 'tblHtml'      => "",
	                 'dataStr'      => "");

	if($params['errorMessage'] == "&nbsp;")
	{
		try
		{
			// Connect to SQL Server
			$pdo = DBConnector::getConnection();

			$userList = array();
			$resultTableName = "";

			if($params['version'] != "all")
			{
				$sqlStr = "SELECT cm.result_table FROM plugin_master pm, plugin_cad_master cm"
						. " WHERE cm.plugin_id=pm.plugin_id AND pm.plugin_name=? AND pm.version=?";
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute(array($params['cadName'], $params['version']));
				$resultTableName = $stmt->fetchColumn();
			}

			$sqlParams = array();
			//--------------------------------------------------------------------------------------------------------------
			// Create user list
			//--------------------------------------------------------------------------------------------------------------
			if($userID == "all")
			{
				$sqlStr = "SELECT DISTINCT fl.entered_by"
						. " FROM executed_plugin_list el, feedback_list fl, plugin_master pm,"
						. " executed_series_list es, series_list sr"
						. " WHERE lf.job_id=el.job_id"
						. " AND pm.job_id=el.job_id"
						. " AND es.job_id=el.job_id"
						. " AND pm.plugin_name=?";

				$sqlParams[] = $params['cadName'];

				if($params['version'] != "all")
				{
					$sqlStr.= " AND pm.version=?";
					$sqlParams[] = $params['version'];
				}

				$sqlStr .= " AND es.volume_id=0 AND sr.sid = es.series_sid";

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

				$sqlStr .= " AND fl.is_consensual='f' AND fl.status=1"
						.  "ORDER BY fl.entered_by ASC";

				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($sqlParams);
				$userList = $stmt->fetchAll(PDO::FETCH_COLUMN);
			}
			else $userList[] = $userID;

			$userNum = count($userList);
			//--------------------------------------------------------------------------------------------------------------

			//--------------------------------------------------------------------------------------------------------------
			// Create tblHtml
			//--------------------------------------------------------------------------------------------------------------
			for($k=0; $k<$userNum; $k++)
			{
				$evaluation["1"][0]   = 0;
				$evaluation["2"][0]   = 0;
				$evaluation["0"][0]   = 0;
				$evaluation["-1"][0]  = 0;

				$detailMissedTP["TP"]      = 0;
				$detailMissedTP["FP"]      = 0;
				$detailMissedTP["pending"] = 0;

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
								   .  " AND (cad.sub_id =cc.candidate_id OR cc.candidate_id=0)"
								   .  " AND cad.volume_size>=? AND cad.volume_size<=?";

					$sqlParamCntEval[] = $minVolume;
					$sqlParamCntEval[] = $maxVolume;
				}

				$sqlStrCntEval .= " AND fl.entered_by=?"
					           .  " AND fl.is_consensual ='f' AND fl.status=1";

				$sqlStrCntFN .= " AND fl.entered_by=?"
				             .  " AND fl.is_consensual ='f' AND fl.status=1";

				$sqlParamCntEval[] = $userList[$k];
				$sqlParamCntFN[]   = $userList[$k];

				$stmt = $pdo->prepare($sqlStrCntFN);
				$stmt->execute($sqlParamCntFN);

				$fnNum = $stmt->fetchColumn();
				if($fnNum == "" || $fnNum < 0)  $fnNum = 0;
				$totalNum += $fnNum;

				$stmt = $pdo->prepare($sqlStrCntEval);
				$stmt->execute($sqlParamCntEval);

				//var_dump($sqlStrCntEval);
				//var_dump($sqlParamCntEval);

				$dstData['caseNum'] = $stmt->rowCount();

				while($execRow = $stmt->fetch(PDO::FETCH_NUM))
				{
					$jobID = $execRow[0];

					//------------------------------------------------------------------------------------------------
					$sqlParams = array();
					$sqlStr = "SELECT cc.evaluation, COUNT(*)"
							. " FROM executed_plugin_list el, executed_series_list es,"
							. " plugin_master pm, feedback_list fl, candidate_classification cc,"
							. " series_list sr";

					if($params['version'] != "all")  $sqlStr .= ', "' . $resultTableName . '" cad';

					$sqlStr .= " WHERE el.job_id =?"
							.  " AND fl.job_id=el.job_id AND cc.fb_id=fl.fb_id"
							.  " AND es.job_id=el.job_id AND es.volume_id=0"
							.  " AND sr.sid = es.series_sid"
							.  " AND pm.plugin_id=el.plugin_id"
							.  " AND pm.plugin_name=?";

					$sqlParams[] = $jobID;
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

					if($params['version'] != "all")
					{
						$sqlStr .= " AND cad.job_id=el.job_id AND cad.sub_id =cc.candidate_id"
							    .  " AND cad.volume_size>=? AND cad.volume_size<=?";
						$sqlParams[] = $minVolume;
						$sqlParams[] = $maxVolume;
					}

					$sqlStr .= " AND fl.entered_by=?"
					        .  " AND fl.is_consensual ='f' AND fl.status=1"
					        .  " GROUP BY cc.evaluation;";
					$sqlParams[] = $userList[$k];

					$stmtDetail = $pdo->prepare($sqlStr);
					$stmtDetail->execute($sqlParams);

					while($result = $stmtDetail->fetch(PDO::FETCH_NUM))
					{
						$evaluation[$result[0]][0] += $result[1];
						$totalNum += $result[1];
					}

				//	if($evaluation["2"][0] > 0)
				//	{
				//		$sqlParams = array();
				//
				//						$sqlStr = "SELECT lf.lesion_id FROM executed_plugin_list el,"
				//		        . " executed_series_list es, lesion_classification lf, series_list sr";
//
//						if($params['version'] != "all")  $sqlStr .= ', "' . $resultTableName . '" cad';
//
//						$sqlStr .= " WHERE el.plugin_name=?";
//						$sqlParams[] = $params['cadName'];
//
//						if($params['version'] != "all")
//						{
//							$sqlStr.= " AND el.version=?";
//							$sqlParams[] = $params['version'];
//						}
//
//						$sqlStr .= " AND es.job_id=el.job_id"
//								.  " AND es.volume_id=0"
//								.  " AND sr.series_instance_uid = es.series_instance_uid";
//
//						if($params['dateFrom'] != "")
//						{
//							$sqlStr .= " AND sr.series_date>=?";
//							$sqlParams[] = $params['dateFrom'];
//						}
//						if($params['dateTo'] != "")
//						{
//							$sqlStr .= " AND sr.series_date<=?";
//							$sqlParams[] = $params['dateTo'];
//						}
//
//						$sqlStr .= " AND el.job_id =? AND lf.job_id=el.job_id";
//						$sqlParams[] = $jobID;
//
//						if($params['version'] != "all")
//						{
//							$sqlStr .= " AND cad.job_id=el.job_id AND cad.sub_id =lf.lesion_id"
//								    .  " AND cad.volume_size>=? AND cad.volume_size<=?";
//							$sqlParams[] = $minVolume;
//							$sqlParams[] = $maxVolume;
//						}
//
//						$sqlStr .= " AND lf.entered_by=?"
//						        .  " AND lf.is_consensual ='f' AND lf.interrupted='f'"
//						        .  " AND lf.evaluation=2";
//						$sqlParams[] = $userList[$k];
//
//						//var_dump($sqlStrCntEval);
//						//var_dump($sqlParamCntEval);
//
//						$stmtDetail = $pdo->prepare($sqlStr);
//						$stmtDetail->execute($sqlParams);
//
//						while($result = $stmtDetail->fetch(PDO::FETCH_NUM))
//						{
//							$sqlStr = "SELECT evaluation FROM lesion_classification"
//									. " WHERE job_id=? AND lesion_id=?"
//									. " AND is_consensual='t' AND interrupted='f';";
//
//							$stmtEvalDetail = $pdo->prepare($sqlStr);
//							$stmtEvalDetail->execute(array($jobID, $result[0]));
//
//							if($stmtEvalDetail->rowCount() > 0)
//							{
//								$tmp = $stmtEvalDetail->fetchColumn();
//
//								if($tmp > 0)       $detailMissedTP["TP"]++;
//								else if($tmp < 0)  $detailMissedTP["FP"]++;
//								else if($tmp == 0) $detailMissedTP["pending"]++;
//							}
//							else  $detailMissedTP["pending"]++;
//						}
//					}
					//------------------------------------------------------------------------------------------------
				} // end while

				$dstData['tblHtml'] = '<tr';

				if($k%2==1) $dstData['tblHtml'] .= ' class="column"';

				$dstData['tblHtml'] .= '>'
									. '<td>' . $userList[$k] . '</td>'
									. '<td>' . $dstData['caseNum'] . '</td>';

				foreach($evaluation as $key)
				{
					$dstData['tblHtml'] .= '<td>' . $key[0] . '</td>';
				}

				$dstData['tblHtml'] .= '<td>' . $fnNum . '</td>'
				                    .  '<td>' . $totalNum . '</td>';

				if($evaluation["2"][0] > 0)
				{
					$dstData['tblHtml'] .= '<td align=center>' . $detailMissedTP["TP"] . '</td>'
										.  '<td align=center>' . $detailMissedTP["FP"] . '</td>'
										.  '<td align=center>' . $detailMissedTP["pending"] . '</td>';
				}
				else
				{
					$dstData['tblHtml'] .= '<td align=center>0</td>'
										.  '<td align=center>0</td>'
										.  '<td align=center>0</td>';
				}
				$dstData['tblHtml'] .= '</tr>';

			} // end for(k)
			//--------------------------------------------------------------------------------------------------------------

			//--------------------------------------------------------------------------------------------------------------
			// Create scatter plot
			//--------------------------------------------------------------------------------------------------------------
			if($userNum == 1 && $params['version'] != "All")
			{
				if($evaluation["1"][0]>0 || $evaluation["2"][0]>0 || $evaluation["-1"][0]>0 || $evaluation["0"][0]>0)
				{
					if($params['dataStr'] == "")
					{
						$sqlParams = array($minVolume, $maxVolume, $params['cadName'], $params['version'], $userList[0]);

						$sqlStr = "SELECT el.job_id, cc.evaluation, cad.location_x, cad.location_y, cad.location_z"
								. " FROM executed_plugin_list el, executed_series_list es,"
								. " feedback_list fl, candidate_classification cc, plugin_master pm,"
								. " series_list sr, " . $resultTableName . " cad"
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
								. " AND pm.plugin_name=? AND pm.version=?"
								. " AND fl.is_consensual='f' AND fl.status=1"
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
								. "MAX(case when key='slice_offset' then value else null end)"
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
							$tmpDataArr[] = (real)(($result['location_z'] - $attrArr[6]) - $attrArr[2]) / (real)$attrArr[5];
						}
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
											$params['fpFlg'],
											$params['pendingFlg']);

						$dstData[$val] = 'storage/' . $webCacheRes['storage_id'] . '/' . $tmpFname;
					}
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
