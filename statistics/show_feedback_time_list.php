<?php
	session_start();

	include("../common.php");
	require_once('../class/validator.class.php');
	
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
		"evalUser" => array(
			"type" => "string")
		));	

	if($validator->validate($_POST))
	{
		$params = $validator->output;
		$params['errorMessage'] = "&nbsp;";
	}
	else
	{
		$params = $validator->output;
		$params['errorMessage'] = implode('<br/>', $validator->errors);
	}

	if($_SESSION['allStatFlg'])		$userID = $params['evalUser'];
	else							$userID = $_SESSION['userID'];
	//--------------------------------------------------------------------------------------------------------

	$dstData = array('errorMessage' => $params['errorMessage'],
					 'tblHtml'      => "");

	if($params['errorMessage'] == "&nbsp;")
	{
		try
		{	
			// Connect to SQL Server
			$pdo = new PDO($connStrPDO);
		
			//--------------------------------------------------------------------------------------------------------------
			// Create tblHtml
			//--------------------------------------------------------------------------------------------------------------
			$sqlParams = array();
				
			$sqlStr = "SELECT DISTINCT(fa.exec_id) FROM executed_plugin_list el,"
					. " executed_series_list es, feedback_action_log fa, series_list sr"
					. " WHERE el.plugin_name=?";
				
			$sqlParams[] = $params['cadName'];
			
			if($params['version'] != "all")
			{
				$sqlStr .= " AND el.version=?";
				$sqlParams[] = $params['version'];
			}
			
			$sqlStr .= " AND es.exec_id=el.exec_id AND fa.exec_id=el.exec_id AND es.series_id=1"
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
				
			$sqlStr .= " AND fa.user_id=? ORDER BY fa.exec_id ASC";
			$sqlParams[] = $userID;
				
			$execIdList = PdoQueryOne($pdo, $sqlStr, $sqlParams, 'ALL_COLUMN');

			for($j = 0; $j < count($execIdList); $j++)
			{
				$sqlStr = "SELECT st.patient_id, sr.series_date, sr.series_time,"
						. " el.plugin_name, el.version, el.executed_at,"
						. " fa.action, fa.options, fa.act_time"
						. " FROM executed_plugin_list el, executed_series_list es,"
						. " feedback_action_log fa, study_list st, series_list sr"
						. " WHERE el.exec_id=? AND es.exec_id=el.exec_id"
						. " AND fa.exec_id=el.exec_id AND es.series_id=1"
						. " AND sr.series_instance_uid = es.series_instance_uid"
						. " AND st.study_instance_uid = es.study_instance_uid"
						. " ORDER BY fa.sid ASC";

				$results = PdoQueryOne($pdo, $sqlStr, $execIdList[$j], 'ALL_NUM');

				$startFlg = 0;
				$fnInputFlg = 0;
				$transFlg = 0;
				$totalStartTime = "";
				$totalEndTime = "";
				$fnStartTime = "";
				$fnEndTime = "";
				$transStartTime = "";
				$transEndTime = "";
				$fnTime = 0;
				$transTime = 0;
				$tmpStr = "";

				for($i = 0; $i < count($results); $i++)
				{
					if($i==0)
					{
						$tmpStr = '<tr';
						if($j%2==1) $tmpStr .= ' class="column"';

						$tmpStr .= '>'
						        .  '<td>' . $execIdList[$j] . '</td>'
								.  '<td>' . $results[$i][0] . '</td>'
								.  '<td>' . $results[$i][1] . '</td>'
								.  '<td>' . $results[$i][2] . '</td>'
								.  '<td>' . $results[$i][3] . ' v.' . $results[$i][4] . '</td>'
								.  '<td>' . $results[$i][5] . '</td>';
					}
						
					if($startFlg == 0)
					{
						if($results[$i][6] == 'open' && $results[$i][7] == 'CAD result')
						{
							$totalStartTime = $results[$i][8];
						}
					}
					
					if($startFlg == 0
					   && ($results[$i][6] != 'open' || ($results[$i][6] == 'open' &&  $results[$i][7] == 'FN input')))
					{
						$startFlg=1;
					}
					
					if($fnInputFlg == 0 && $results[$i][6] == 'open' &&  $results[$i][7] == 'FN input')
					{
						$fnInputFlg = 1;
						$fnStartTime = $results[$i][8];
					}
					
					if($fnInputFlg == 1 && $results[$i][6] == 'save' &&  $results[$i][7] == 'FN input')
					{
						$fnInputFlg = 0;
						$fnEndTime = $results[$i][8];
						
						$fnTime += (strtotime($fnEndTime)-strtotime($fnStartTime));
					}

					if($startFlg == 1 &&  $results[$i][6] == 'save')
					{
						$transFlg = 1;
						$transStartTime = $results[$i][8];
					}

					if($transFlg == 1 &&  $results[$i][6] == 'open')
					{
						$transFlg = 0;
						$transEndTime = $results[$i][8];
						
						$transTime += (strtotime($transEndTime)-strtotime($transStartTime));
					}

					
					if($results[$i][6] == 'register')
					{
						$totalEndTime = $results[$i][8];
					}
				
					if($i == count($results)-1)
					{
						if($totalStartTime != "" && $totalEndTime != "")
						{
							//------------------------------------------------------------------------------------------
							// For TP and FN column
							//------------------------------------------------------------------------------------------
							$sqlStr = "SELECT COUNT(*) FROM lesion_feedback WHERE exec_id=? AND entered_by=?"
									. " AND consensual_flg='f' AND interrupt_flg='f' AND lesion_id>0";
							$dispCandNum = PdoQueryOne($pdo, $sqlStr, array($execIdList[$j], $userID), 'SCALAR');

							$sqlStr = "SELECT false_negative_num FROM false_negative_count"
									. " WHERE exec_id=? AND entered_by=? AND consensual_flg='f' AND status=2";
							$enterFnNum = PdoQueryOne($pdo, $sqlStr, array($execIdList[$j], $userID), 'SCALAR');
							
							// SQL statement for count No. of TP
							$sqlStr  = "SELECT COUNT(*) FROM lesion_feedback WHERE exec_id=? AND consensual_flg=?"
							         . " AND interrupt_flg='f' AND evaluation>=1";
										
							$stmtTP = $pdo->prepare($sqlStr);
					
							// SQL statement for count No. of FN
							$sqlStr  = "SELECT false_negative_num FROM false_negative_count WHERE exec_id=?"
								     . " AND consensual_flg=? AND false_negative_num>0 AND status=2";
					
							$stmtFN = $pdo->prepare($sqlStr);

							$tpColStr = "-";
							$fnColStr = "-";

							$stmtTP->bindValue(1, $execIdList[$j]);
							$stmtTP->bindValue(2, 't', PDO::PARAM_BOOL);
							$stmtTP->execute();
					
							if($stmtTP->fetchColumn() > 0)	$tpColStr = '<span style="font-weight:bold;">+</span>';
							else
							{
								$stmtTP->bindValue(2, 'f', PDO::PARAM_BOOL);
								$stmtTP->execute();
								if($stmtTP->fetchColumn() > 0) $tpColStr = '<span style="font-weight:bold;">!</span>';
							}
	
							$stmtFN->bindValue(1, $execIdList[$j]);
							$stmtFN->bindValue(2, 't', PDO::PARAM_BOOL);
							$stmtFN->execute();	
							
							if($stmtFN->fetchColumn() > 0)  $fnColStr = '<span style="font-weight:bold;">+</span>';
							else
							{
								$stmtFN->bindValue(2, 'f', PDO::PARAM_BOOL);
								$stmtFN->execute();	
								if($stmtFN->fetchColumn() > 0)  $fnColStr = '<span style="font-weight:bold;">!</span>';
							}
							//------------------------------------------------------------------------------------------
						
							$tmpStr .= '<td>' . (strtotime($totalEndTime)-strtotime($totalStartTime)-$fnTime-$transTime) . '</td>'
									.  '<td>' . $fnTime . '</td>'
									.  '<td>' . $dispCandNum . '</td>'
									.  '<td>' . $enterFnNum . '</td>'
								//	.  '<td>' . $tpColStr . '</td>'
								//	.  '<td>' . $fnColStr . '</td>'
									.  '</tr>';

							//$tmpStr .= '<td>' . $totalStartTime  . ' - ' . $totalEndTime . '='
							//        . (strtotime($totalEndTime)-strtotime($totalStartTime)) . '</td></tr>';
						}
						else $tmpStr = "";
							
						$dstData['tblHtml'] .= $tmpStr;
					}
				} // end for: $i
			} // end for: $j
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
