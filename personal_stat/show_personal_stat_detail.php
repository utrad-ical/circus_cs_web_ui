<?php
	session_start();

	include("../common.php");

	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//--------------------------------------------------------------------------------------------------------
	$dateFrom = (isset($_REQUEST['dateFrom'])) ? $_REQUEST['dateFrom'] : "";
	$dateTo   = (isset($_REQUEST['dateTo']))   ? $_REQUEST['dateTo']   : "";
	$cadName  = (isset($_REQUEST['cadName']))  ? $_REQUEST['cadName']  : "";
	$version  = (isset($_REQUEST['version']))  ? $_REQUEST['version']  : "";
	
	$minSize   = (isset($_REQUEST['minSize']) && $_REQUEST['minSize'] != "") ? $_REQUEST['minSize'] : 0;
	$maxSize   = (isset($_REQUEST['maxSize']) && $_REQUEST['maxSize'] != "") ? $_REQUEST['maxSize'] : 10000.0;
	
	if($_SESSION['allStatFlg'])		$userID = $_REQUEST['evalUser'];
	else							$userID = $_SESSION['userID'];
	//--------------------------------------------------------------------------------------------------------

	$evaluation["1"][0]  = 0;		$evaluation["1"][1]  = 'known TP';
	$evaluation["2"][0]  = 0;		$evaluation["2"][1]  = 'missed TP';
	$evaluation["0"][0]  = 0;		$evaluation["0"][1]  = 'FP';
	$evaluation["-1"][0] = 0;		$evaluation["-1"][1] = 'pending';

	$minVolume = 4.0 / 3.0 * pi() * pow($minSize/2.0, 3);
	$maxVolume = 4.0 / 3.0 * pi() * pow($maxSize/2.0, 3);

	$dstData = array('caseNum' => 0,
					 'tblHtml' => "",
	                 'dataStr' => "");
	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
	
		$userList = array();
	
		$resultTableName = "";

		if($version != "all")
		{
			$stmt = $pdo->prepare("SELECT result_table FROM cad_master WHERE cad_name=? AND version=?");
			$stmt->execute(array($cadName, $version));
			$resultTableName = $stmt->fetchColumn();
		}
		
		$sqlParam = array();

		//--------------------------------------------------------------------------------------------------------------
		// Create user list
		//--------------------------------------------------------------------------------------------------------------
		if($userID == "all")
		{
			$sqlStr = "SELECT DISTINCT lf.entered_by"
					. " FROM lesion_feedback lf, executed_plugin_list el, executed_series_list es, series_list sr"
					. " WHERE lf.exec_id=el.exec_id AND es.exec_id=el.exec_id"
					. " AND el.plugin_name=?";
					
			array_push($sqlParam, $cadName);
				 
			if($version != "all")
			{
				$sqlStr.= " AND el.version=?";
				array_push($sqlParam, $version);
			}
		
			$sqlStr .= " AND es.series_id=1"
		    	    .  " AND sr.series_instance_uid = es.series_instance_uid";
		
			if($dateFrom != "")
			{
				$sqlStr .= " AND sr.series_date>=?";
				array_push($sqlParam, $dateFrom);
			}	
			if($dateTo != "")
			{
				$sqlStr .= " AND sr.series_date<=?";
				array_push($sqlParam, $dateTo);
			}
							 
			$sqlStr .= " AND lf.consensual_flg='f' AND lf.interrupt_flg='f'"
			        .  "ORDER BY lf.entered_by ASC";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute();

			while($result = $stmt->fetch(PDO::FETCH_NUM))
			{
				array_push($userList, $result[0]);
			}
		}
		else $userList[0] = $userID;

		$userNum = count($userList);
		//--------------------------------------------------------------------------------------------------------------
		
		//--------------------------------------------------------------------------------------------------------------
		// Create tblHtml
		//--------------------------------------------------------------------------------------------------------------
		for($k=0; $k<$userNum; $k++)
		{
			$evaluation["1"][0]  = 0;
			$evaluation["2"][0]  = 0;
			$evaluation["0"][0]  = 0;
			$evaluation["-1"][0] = 0;
	
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

			if($version != "all")	$sqlStrCntEval .= ', "' . $resultTableName . '" cad';
			
			$sqlStrCntEval .= " WHERE el.plugin_name=?";
			$sqlStrCntFN   .= " WHERE el.plugin_name=?";
			
			array_push($sqlParamCntEval, $cadName);
			array_push($sqlParamCntFN,   $cadName);
			
			if($version != "all")
			{
				$sqlStrCntEval .= " AND el.version=?";
				$sqlStrCntFN   .= " AND el.version=?";
			
				array_push($sqlParamCntEval, $version);
				array_push($sqlParamCntFN,   $version);
			}
			
			$sqlStrCntEval .= " AND es.exec_id=el.exec_id AND es.series_id=1"
			               .  " AND sr.series_instance_uid = es.series_instance_uid";
			$sqlStrCntFN   .= " AND es.exec_id=el.exec_id AND es.series_id=1"
			               .  " AND sr.series_instance_uid = es.series_instance_uid";

			if($dateFrom != "")
			{
				$sqlStrCntEval .= " AND sr.series_date>=?";
				$sqlStrCntFN   .= " AND sr.series_date>=?";
			
				array_push($sqlParamCntEval, $dateFrom);
				array_push($sqlParamCntFN,   $dateFrom);
			}
				
			if($dateTo != "")
			{
				$sqlStrCntEval .= " AND sr.series_date<=?";
				$sqlStrCntFN   .= " AND sr.series_date<=?";
			
				array_push($sqlParamCntEval, $dateTo);
				array_push($sqlParamCntFN, $dateTo);
			}
			
			$sqlStrCntEval .= " AND lf.exec_id=el.exec_id";
	
			if($version != "all")
			{
			  	$sqlStrCntEval .= " AND cad.exec_id=el.exec_id AND cad.sub_id =lf.lesion_id"
					             .  " AND cad.volume_size>=? AND cad.volume_size<=?";
								 
				array_push($sqlParamCntEval, $minVolume);
				array_push($sqlParamCntEval, $maxVolume);
			}
			
			$sqlStrCntEval .= " AND lf.entered_by=?"
				             .  " AND lf.consensual_flg ='f' AND lf.interrupt_flg='f'";

			$sqlStrCntFN .= " AND fn.exec_id=el.exec_id"
				           .  " AND fn.entered_by=?"
			               .  " AND fn.consensual_flg ='f'"
			               .  " AND fn.status>=1";

			array_push($sqlParamCntEval, $userList[$k]);
			array_push($sqlParamCntFN,   $userList[$k]);

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
				$sqlParam = array();
				$sqlStr = "SELECT lf.evaluation, COUNT(*) FROM executed_plugin_list el,"
				        . " executed_series_list es, lesion_feedback lf, series_list sr";
				
				if($version != "all")  $sqlStr .= ', "' . $resultTableName . '" cad';
						
				$sqlStr .= " WHERE el.plugin_name=?";
				array_push($sqlParam, $cadName);
					 
				if($version != "all")
				{
					$sqlStr.= " AND el.version=?";
					array_push($sqlParam, $version);
				}
		
				$sqlStr .= " AND es.exec_id=el.exec_id"
						.  " AND es.series_id=1"
						.  " AND sr.series_instance_uid = es.series_instance_uid";
		
				if($dateFrom != "")
				{
					$sqlStr .= " AND sr.series_date>=?";
					array_push($sqlParam, $dateFrom);
				}
				
				if($dateTo != "")
				{
					$sqlStr .= " AND sr.series_date<=?";
					array_push($sqlParam, $dateTo);
				}
				
				$sqlStr .= " AND el.exec_id =? AND lf.exec_id=el.exec_id";
				array_push($sqlParam, $execID);
				
				if($version != "all")
				{
					$sqlStr .= " AND cad.exec_id=el.exec_id AND cad.sub_id =lf.lesion_id"
						    .  " AND cad.volume_size>=? AND cad.volume_size<=?";
					array_push($sqlParam, $minVolume);
					array_push($sqlParam, $maxVolume);
				}
						
				$sqlStr .= " AND lf.entered_by=?"
				        .  " AND lf.consensual_flg ='f' AND lf.interrupt_flg='f'"
				        .  " GROUP BY lf.evaluation;";
				array_push($sqlParam, $userList[$k]);
				
				$stmtDetail = $pdo->prepare($sqlStr);
				$stmtDetail->execute($sqlParam);
				
				while($result = $stmtDetail->fetch(PDO::FETCH_NUM))
				{
					$evaluation[$result[0]][0] += $result[1];
					$totalNum += $result[1];
				}
		
				if($evaluation["2"][0] > 0)
				{
					$sqlParam = array();
				
					$sqlStr = "SELECT lf.lesion_id FROM executed_plugin_list el,"
					        . " executed_series_list es, lesion_feedback lf, series_list sr";
					        
					if($version != "all")  $sqlStr .= ', "' . $resultTableName . '" cad';
							
					$sqlStr .= " WHERE el.plugin_name=?";
					array_push($sqlParam, $cadName);
						 
					if($version != "all")
					{
						$sqlStr.= " AND el.version=?";
						array_push($sqlParam, $version);
					}
			
					$sqlStr .= " AND es.exec_id=el.exec_id"
							.  " AND es.series_id=1"
							.  " AND sr.series_instance_uid = es.series_instance_uid";
			
					if($dateFrom != "")
					{
						$sqlStr .= " AND sr.series_date>=?";
						array_push($sqlParam, $dateFrom);
					}	
					if($dateTo != "")
					{
						$sqlStr .= " AND sr.series_date<=?";
						array_push($sqlParam, $dateTo);
					}
					
					$sqlStr .= " AND el.exec_id =? AND lf.exec_id=el.exec_id";
					array_push($sqlParam, $execID);
					
					if($version != "all")
					{
						$sqlStr .= " AND cad.exec_id=el.exec_id AND cad.sub_id =lf.lesion_id"
							    .  " AND cad.volume_size>=? AND cad.volume_size<=?";
						array_push($sqlParam, $minVolume);
						array_push($sqlParam, $maxVolume);
					}
					
					$sqlStr .= " AND lf.entered_by=?"
					        .  " AND lf.consensual_flg ='f' AND lf.interrupt_flg='f'"
					        .  " AND lf.evaluation=2";
					array_push($sqlParam, $userList[$k]);
					
					//var_dump($sqlStrCntEval);
					//var_dump($sqlParamCntEval);					
						
					$stmtDetail = $pdo->prepare($sqlStr);
					$stmtDetail->execute($sqlParam);
	
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
							
							if($tmp > 0)         $detailMissedTP["TP"]++;
							else if($tmp ==  0)  $detailMissedTP["FP"]++;
							else if($tmp == -1)  $detailMissedTP["pending"]++;
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
		if($userNum == 1 && $version != "All")
		{
			$dataStr = (isset($_REQUEST['dataStr'])) ? $_REQUEST['dataStr'] : "";
			
			if($evaluation["1"][0]>0 || $evaluation["2"][0]>0 || $evaluation["0"][0]>0 || $evaluation["-1"][0]>0)
			{
				if($dataStr == "")
				{
					$sqlParam = array($minVolume, $maxVolume, $cadName, $version, $userList[0]);
				
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
							
					if($dateFrom != "")
					{
						$sqlStr .= " AND sr.series_date>=?";
						array_push($sqlParam, $dateFrom);
					}
		
					if($dateTo != "")
					{
						$sqlStr .= " AND sr.series_date<=?";
						array_push($sqlParam, $dateTo);
					}
					
					$sqlStr .= "ORDER BY lf.evaluation ASC, el.exec_id ASC, lf.lesion_id ASC";
				
					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute($sqlParam);
					
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

		echo json_encode($dstData);
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;	

?>

