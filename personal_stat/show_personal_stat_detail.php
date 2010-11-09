<?php
	session_start();

	include("../common.php");
	require_once('../class/validator.class.php');	
	
	//-----------------------------------------------------------------------------------------------------------------
	// Import $_POST variables and validation
	//-----------------------------------------------------------------------------------------------------------------
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
			'type' => 'int', 
			'min' => '0',
			'default' => '0',
			'errorMes' => "'Size (diameter)' is invalid."),
		"maxSize" => array(
			'type' => 'int', 
			'min' => '0',
			'default' => '100000',
			'errorMes' => "'Size (diameter)' is invalid.")
		));	

	if($validator->validate($_POST))
	{
		$params = $validator->output;
		$params['errorMessage'] = "&nbsp;";
			
		if(isset($params['minSize']) && isset($params['maxSize']) && $params['minSize'] > $params['maxSize'])
		{
			$params['errorMessage'] = "Range of 'Size (diameter)' is invalid."; 
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
			$pdo = new PDO($connStrPDO);
		
			$userList = array();
			$resultTableName = "";
	
			if($params['version'] != "all")
			{
				$stmt = $pdo->prepare("SELECT result_table FROM cad_master WHERE cad_name=? AND version=?");
				$stmt->execute(array($params['cadName'], $params['version']));
				$resultTableName = $stmt->fetchColumn();
			}
			
			$sqlParams = array();
			//--------------------------------------------------------------------------------------------------------------
			// Create user list
			//--------------------------------------------------------------------------------------------------------------
			if($userID == "all")
			{
				$sqlStr = "SELECT DISTINCT lf.entered_by"
						. " FROM lesion_feedback lf, executed_plugin_list el, executed_series_list es, series_list sr"
						. " WHERE lf.exec_id=el.exec_id AND es.exec_id=el.exec_id"
						. " AND el.plugin_name=?";
						
				$sqlParams[] = $params['cadName'];
					 
				if($params['version'] != "all")
				{
					$sqlStr.= " AND el.version=?";
					$sqlParams[] = $params['version'];
				}
			
				$sqlStr .= " AND es.series_id=1"
			    	    .  " AND sr.series_instance_uid = es.series_instance_uid";
			
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
								 
				$sqlStr .= " AND lf.consensual_flg='f' AND lf.interrupt_flg='f'"
				        .  "ORDER BY lf.entered_by ASC";
	
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
				
				$sqlStrCntEval = "SELECT DISTINCT(lf.exec_id) FROM executed_plugin_list el,"
							   . " executed_series_list es, lesion_feedback lf, series_list sr";
	
				$sqlStrCntFN  = "SELECT SUM(fn.false_negative_num) FROM executed_plugin_list el,"
				              . " executed_series_list es, false_negative_count fn, series_list sr";
	
				if($params['version'] != "all")	$sqlStrCntEval .= ', "' . $resultTableName . '" cad';
				
				$sqlStrCntEval .= " WHERE el.plugin_name=?";
				$sqlStrCntFN   .= " WHERE el.plugin_name=?";
				
				$sqlParamCntEval[] = $params['cadName'];
				$sqlParamCntFN[]   = $params['cadName'];
				
				if($params['version'] != "all")
				{
					$sqlStrCntEval .= " AND el.version=?";
					$sqlStrCntFN   .= " AND el.version=?";
				
					$sqlParamCntEval[] = $params['version'];
					$sqlParamCntFN[]   = $params['version'];
				}
				
				$sqlStrCntEval .= " AND es.exec_id=el.exec_id AND es.series_id=1"
				               .  " AND sr.series_instance_uid = es.series_instance_uid";
				$sqlStrCntFN   .= " AND es.exec_id=el.exec_id AND es.series_id=1"
				               .  " AND sr.series_instance_uid = es.series_instance_uid";
	
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
				
				$sqlStrCntEval .= " AND lf.exec_id=el.exec_id";
		
				if($params['version'] != "all")
				{
				  	$sqlStrCntEval .= " AND cad.exec_id=el.exec_id AND cad.sub_id =lf.lesion_id"
						           .  " AND cad.volume_size>=? AND cad.volume_size<=?";
									 
					$sqlParamCntEval[] = $minVolume;
					$sqlParamCntEval[] = $maxVolume;
				}
				
				$sqlStrCntEval .= " AND lf.entered_by=?"
					           .  " AND lf.consensual_flg ='f' AND lf.interrupt_flg='f'";
	
				$sqlStrCntFN .= " AND fn.exec_id=el.exec_id"
					           .  " AND fn.entered_by=?"
				               .  " AND fn.consensual_flg ='f'"
				               .  " AND fn.status>=1";
	
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
					$execID = $execRow[0];
	
					//------------------------------------------------------------------------------------------------
					$sqlParams = array();
					$sqlStr = "SELECT lf.evaluation, COUNT(*) FROM executed_plugin_list el,"
					        . " executed_series_list es, lesion_feedback lf, series_list sr";
					
					if($params['version'] != "all")  $sqlStr .= ', "' . $resultTableName . '" cad';
							
					$sqlStr .= " WHERE el.plugin_name=?";
					$sqlParams[] = $params['cadName'];
						 
					if($params['version'] != "all")
					{
						$sqlStr.= " AND el.version=?";
						$sqlParams[] = $params['version'];
					}
			
					$sqlStr .= " AND es.exec_id=el.exec_id"
							.  " AND es.series_id=1"
							.  " AND sr.series_instance_uid = es.series_instance_uid";
			
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
					
					$sqlStr .= " AND el.exec_id =? AND lf.exec_id=el.exec_id";
					$sqlParams[] = $execID;
					
					if($params['version'] != "all")
					{
						$sqlStr .= " AND cad.exec_id=el.exec_id AND cad.sub_id =lf.lesion_id"
							    .  " AND cad.volume_size>=? AND cad.volume_size<=?";
						$sqlParams[] = $minVolume;
						$sqlParams[] = $maxVolume;
					}
							
					$sqlStr .= " AND lf.entered_by=?"
					        .  " AND lf.consensual_flg ='f' AND lf.interrupt_flg='f'"
					        .  " GROUP BY lf.evaluation;";
					$sqlParams[] = $userList[$k];
					
					$stmtDetail = $pdo->prepare($sqlStr);
					$stmtDetail->execute($sqlParams);
					
					while($result = $stmtDetail->fetch(PDO::FETCH_NUM))
					{
						$evaluation[$result[0]][0] += $result[1];
						$totalNum += $result[1];
					}
			
					if($evaluation["2"][0] > 0)
					{
						$sqlParams = array();
					
						$sqlStr = "SELECT lf.lesion_id FROM executed_plugin_list el,"
						        . " executed_series_list es, lesion_feedback lf, series_list sr";
						        
						if($params['version'] != "all")  $sqlStr .= ', "' . $resultTableName . '" cad';
								
						$sqlStr .= " WHERE el.plugin_name=?";
						$sqlParams[] = $params['cadName'];
							 
						if($params['version'] != "all")
						{
							$sqlStr.= " AND el.version=?";
							$sqlParams[] = $params['version'];
						}
				
						$sqlStr .= " AND es.exec_id=el.exec_id"
								.  " AND es.series_id=1"
								.  " AND sr.series_instance_uid = es.series_instance_uid";
				
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
						
						$sqlStr .= " AND el.exec_id =? AND lf.exec_id=el.exec_id";
						$sqlParams[] = $execID;
						
						if($params['version'] != "all")
						{
							$sqlStr .= " AND cad.exec_id=el.exec_id AND cad.sub_id =lf.lesion_id"
								    .  " AND cad.volume_size>=? AND cad.volume_size<=?";
							$sqlParams[] = $minVolume;
							$sqlParams[] = $maxVolume;
						}
						
						$sqlStr .= " AND lf.entered_by=?"
						        .  " AND lf.consensual_flg ='f' AND lf.interrupt_flg='f'"
						        .  " AND lf.evaluation=2";
						$sqlParams[] = $userList[$k];
						
						//var_dump($sqlStrCntEval);
						//var_dump($sqlParamCntEval);					
							
						$stmtDetail = $pdo->prepare($sqlStr);
						$stmtDetail->execute($sqlParams);
		
						while($result = $stmtDetail->fetch(PDO::FETCH_NUM))
						{
							$sqlStr = "SELECT evaluation FROM lesion_feedback"
							        . " WHERE exec_id=? AND lesion_id=?"
									. " AND consensual_flg='t' AND interrupt_flg='f';";
							
							$stmtEvalDetail = $pdo->prepare($sqlStr);
							$stmtEvalDetail->execute(array($execID, $result[0]));
							
							if($stmtEvalDetail->rowCount() > 0)
							{
								$tmp = $stmtEvalDetail->fetchColumn();
								
								if($tmp > 0)       $detailMissedTP["TP"]++;
								else if($tmp < 0)  $detailMissedTP["FP"]++;
								else if($tmp == 0) $detailMissedTP["pending"]++;
							}
							else  $detailMissedTP["pending"]++;
						}
					}
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
				$dataStr = (isset($_REQUEST['dataStr'])) ? $_REQUEST['dataStr'] : "";
				
				if($evaluation["1"][0]>0 || $evaluation["2"][0]>0 || $evaluation["-1"][0]>0 || $evaluation["0"][0]>0)
				{
					if($dataStr == "")
					{
						$sqlParams = array($minVolume, $maxVolume, $params['cadName'], $params['version'], $userList[0]);
					
						$sqlStr = "SELECT el.exec_id, lf.lesion_id, lf.evaluation, sr.series_date, sr.series_time,"
						        . " ((cast(cad.location_x-ps.crop_org_x as real))/cast(ps.crop_width as real)) AS pos_x,"
						        . " ((cast(cad.location_y-ps.crop_org_y as real))/cast(ps.crop_height as real)) AS pos_y,"
						        . " ((cast(cad.location_z-ps.crop_org_z as real))/cast(ps.crop_depth as real)) AS pos_z"
								. " FROM executed_plugin_list el, executed_series_list es, series_list sr, param_set ps,"
						        . " " . $resultTableName . " cad, lesion_feedback lf"
						        . " WHERE el.exec_id=es.exec_id"
						        . " AND el.exec_id=ps.exec_id"
						        . " AND el.exec_id=cad.exec_id"
						        . " AND el.exec_id=lf.exec_id"
						        . " AND cad.sub_id=lf.lesion_id"
								. " AND cad.volume_size>=?"
							    . " AND cad.volume_size<=?"
						        . " AND es.series_id=1"
						        . " AND es.series_instance_uid=sr.series_instance_uid"
								. " AND el.plugin_name=? AND el.version=?"
						        . " AND lf.consensual_flg='f' AND lf.interrupt_flg='f'"
						        . " AND lf.entered_by=?";
								
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
						
						$sqlStr .= "ORDER BY lf.evaluation ASC, el.exec_id ASC, lf.lesion_id ASC";
					
						$stmt = $pdo->prepare($sqlStr);
						$stmt->execute($sqlParams);
						
						$cnt = $stmt->rowCount();
		
						for($i=0; $i<$cnt; $i++)
						{
							$result = $stmt->fetch(PDO::FETCH_ASSOC);
			
							if($i>0)	$dataStr .= '^';
							$dataStr .= $result['evaluation'] . '^' . $result['pos_x']
							          . '^' . $result['pos_y'] . '^' . $result['pos_z']; 
						
							//echo $result['evaluation'] . ' ' . $result['pos_x'] . ' ';
							//echo $result['pos_y'] . ' ' . $result['pos_z'] . '<br>';
						}
					}
								
					$tmpArr = explode('^', $dataStr);
					$length = count($tmpArr)/4;
					$plotData = array();
					
					for($j=0; $j<$length; $j++)
					for($i=0; $i<4; $i++)
					{
						$plotData[$j][$i] = $tmpArr[$j * 4 + $i];
					}
					
					$knownTpFlg  = (isset($_REQUEST['knownTpFlg']))  ? $_REQUEST['knownTpFlg']  : 1;
					$missedTpFlg = (isset($_REQUEST['missedTpFlg'])) ? $_REQUEST['missedTpFlg'] : 1;
					$fpFlg       = (isset($_REQUEST['fpFlg']))       ? $_REQUEST['fpFlg']       : 1;
					$pendingFlg  = (isset($_REQUEST['pendingFlg']))  ? $_REQUEST['pendingFlg']  : 1;
		
					$section = array('XY', 'XZ', 'YZ');
					include('create_scatter_plot.php');
					
					foreach($section as $val)
					{
						$tmpFname = '../tmp/plot' . $val . '_' . microtime(true) . '.png';
					
						CreateScatterPlot($plotData, $val, $tmpFname,
				                            $knownTpFlg, $missedTpFlg, $fpFlg, $pendingFlg);
			
						$dstData[$val] = 'personal_stat/show_scatter_plot.php?fname=' . $tmpFname;
						//$dstData[$val] = $tmpFname;
					}
				}
				
				$dstdata['dataStr'] = $dataStr;
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

