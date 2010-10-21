<?php
	session_cache_limiter('nocache');
	session_start();

	include("common.php");
	require_once('class/PersonalInfoScramble.class.php');	

	//------------------------------------------------------------------------------------------------------------------
	// Auto logout (session timeout)
	//------------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'])  header('location: index.php?mode=timeout');
	else	$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables and set $params array	
	//------------------------------------------------------------------------------------------------------------------
	$params = array('srcPage'        => (isset($_REQUEST['srcPage'])) ? $_REQUEST['srcPage'] : "",
	                'filterPtID'     => (isset($_REQUEST['filterPtID'])) ? $_REQUEST['filterPtID'] : "",
				    'filterPtName'   => (isset($_REQUEST['filterPtName'])) ? $_REQUEST['filterPtName'] : "",
				    'filterSex'      => (isset($_REQUEST['filterSex'])) ? $_REQUEST['filterSex'] : "all",
				    'filterAgeMin'   => (isset($_REQUEST['filterAgeMin'])) ? $_REQUEST['filterAgeMin'] : "",
				    'filterAgeMax'   => (isset($_REQUEST['filterAgeMax'])) ? $_REQUEST['filterAgeMax'] : "",
				    'filterCadID'    => (isset($_REQUEST['filterCadID'])) ? $_REQUEST['filterCadID'] : "",
				    'filterModality' => (isset($_REQUEST['filterModality'])) ? $_REQUEST['filterModality'] : "all",
				    'filterCAD'      => (isset($_REQUEST['filterCAD'])) ? $_REQUEST['filterCAD'] : "all",
				    'filterVersion'  => (isset($_REQUEST['filterVersion'])) ? $_REQUEST['filterVersion'] : "all",
				    'filterTag'      => (isset($_REQUEST['filterTag'])) ? $_REQUEST['filterTag'] : "",
				    'srDateFrom'     => (isset($_REQUEST['srDateFrom'])) ? $_REQUEST['srDateFrom'] : "",
				    'srDateTo'       => (isset($_REQUEST['srDateTo'])) ? $_REQUEST['srDateTo'] : "",
				    //'srTimeFrom'     => (isset($_REQUEST['srTimeFrom'])) ? $_REQUEST['srTimeFrom'] : "00:00:00",
				    'srTimeTo'       => (isset($_REQUEST['stTimeTo'])) ? $_REQUEST['stTimeTo'] : "",
				    'cadDateFrom'    => (isset($_REQUEST['cadDateFrom'])) ? $_REQUEST['cadDateFrom'] : "",
				    'cadDateTo'      => (isset($_REQUEST['cadDateTo'])) ? $_REQUEST['cadDateTo'] : "",
				    //'cadTimeFrom'    => (isset($_REQUEST['cadTimeFrom'])) ? $_REQUEST['cadTimeFrom'] : "00:00:00",
				    'cadTimeTo'      => (isset($_REQUEST['cadTimeTo'])) ? $_REQUEST['cadTimeTo'] : "",
				    'personalFB'     => (isset($_REQUEST['personalFB'])) ? $_REQUEST['personalFB'] : "all",
				    'consensualFB'   => (isset($_REQUEST['consensualFB'])) ? $_REQUEST['consensualFB'] : "all",
				    'filterFBUser'   => (isset($_REQUEST['filterFBUser'])) ? $_REQUEST['filterFBUser'] : "",
				    'filterTP'       => (isset($_REQUEST['filterTP'])) ? $_REQUEST['filterTP'] : "all",
				    'filterFN'       => (isset($_REQUEST['filterFN'])) ? $_REQUEST['filterFN'] : "all",
				    'mode'           => (isset($_REQUEST['mode'])) ? $_REQUEST['mode'] : "",
				    'orderCol'       => (isset($_REQUEST['orderCol'])) ? $_REQUEST['orderCol'] : "Study date",
				    'orderMode'      => ($_REQUEST['orderMode'] === "ASC") ? "ASC" : "DESC",
				    'totalNum'       => (isset($_REQUEST['totalNum']))  ? $_REQUEST['totalNum'] : 0,
				    'pageNum'        => (isset($_REQUEST['pageNum']))   ? $_REQUEST['pageNum']  : 1,
				    'showing'        => (isset($_REQUEST['showing'])) ? $_REQUEST['showing'] : 10,
				    'startNum'       => 1,
				    'endNum'         => 10,
				    'maxPageNum'     => 1,
				    'pageAddress'    => 'cad_log.php?');

	if($params['filterSex'] != "M" && $params['filterSex'] != "F")  $params['filterSex'] = "all";
	if($params['showing'] != "all" && $params['showing'] < 10)  $params['showing'] = 10;
	
	$userID = $_SESSION['userID'];
	//------------------------------------------------------------------------------------------------------------------

	$data = array();

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
		
		//--------------------------------------------------------------------------------------------------------
		// Create SQL queries 
		//--------------------------------------------------------------------------------------------------------
		$sqlCondArray = array();
		$sqlParams = array();
		$pageAddressParams = array();	
		
		$sqlCond = " FROM patient_list pt JOIN (study_list st JOIN series_list sr"
			       . " ON (st.study_instance_uid = sr.study_instance_uid)) ON (pt.patient_id=st.patient_id)"
			       . " JOIN (executed_series_list es JOIN executed_plugin_list el"
			       . " ON (es.exec_id=el.exec_id AND es.series_id=1 AND el.plugin_type=1))"
			       . " ON (sr.series_instance_uid = es.series_instance_uid)"
			       . " LEFT JOIN lesion_feedback lf ON (es.exec_id=lf.exec_id AND lf.interrupt_flg='f')"
			       . " LEFT JOIN false_negative_count fn ON (es.exec_id = fn.exec_id AND fn.status>=1)";

		if($params['mode'] == 'today')
		{
			$params['showing'] = "all";  // for HIMEDIC
		
			$today = date("Y-m-d");
			$params['cadDateFrom'] = $today;
			$params['cadDateTo']   = $today;
			
			$sqlCondArray[] = "el.executed_at>=? AND el.executed_at<=?";
			$sqlParams[] = $params['cadDateFrom'] . ' 00:00:00';
			$sqlParams[] = $params['cadDateFrom'] . ' 23:59:59';
			$pageAddressParams['mode'] .= 'today';
			$pageAddressParams['cadDateFrom'] = $params['cadDateFrom'];
			$pageAddressParams['cadDateTo'] = $params['cadDateTo'];
		}
		else if($params['cadDateFrom'] != "" && $params['cadDateTo'] != "" && $params['cadDateFrom'] == $params['cadDateTo'])
		{
			$sqlCondArray[] = "el.executed_at>=? AND el.executed_at<=?";
			$sqlParams[] = $params['cadDateFrom'] . ' 00:00:00';
			$sqlParams[] = $params['cadDateFrom'] . ' 23:59:59';
			$pageAddressParams['cadDateFrom'] = $params['cadDateFrom'];
			$pageAddressParams['cadDateTo'] = $params['cadDateTo'];
		}
		else
		{
			if($params['cadDateFrom'] != "")
			{
				$sqlCondArray[] = "?<=el.executed_at";
				$sqlParams[] = $params['cadDateFrom'].' 00:00:00';
				$pageAddressParams['cadDateFrom'] = $params['cadDateFrom'];
			}
		
			if($params['cadDateTo'] != "")
			{
				$sqlCondArray[] = "el.executed_at<=?";
				$pageAddressParams['cadDateTo'] = $params['cadDateTo'];

				if($params['cadTimeTo'] != "")
				{
					$sqlParams[] = $params['cadDateTo'] . ' ' . $params['cadTimeTo'];
					$pageAddressParams['cadTimeTo'] = $params['cadTimeTo'];
				}
				else
				{
					$sqlParams[] = $params['cadDateTo'] . ' 23:59:59';
				}
			}
		}

	
		if($params['srDateFrom'] != "" && $params['srDateTo'] != "" && $params['srDateFrom'] == $params['srDateTo'])
		{
			$sqlCondArray[] = "sr.series_date=?";
			
			$pageAddressParams['srDateFrom'] = $params['srDateFrom'];
			$pageAddressParams['srDateTo'] = $params['srDateTo'];
		}
		else
		{
			if($params['srDateFrom'] != "")
			{
				$sqlCondArray[] = "?<=sr.series_date";
				$sqlParams[] = $params['srDateFrom'];
				$pageAddressParams['srDateFrom'] = $params['srDateFrom'];
			}
		
			if($params['srDateTo'] != "")
			{
				$sqlParams[] = $params['srDateTo'];
				$pageAddressParams['srDateTo'] = $params['srDateTo'];
		
				if($params['srTimeTo'] != "")
				{
					$sqlCondArray[] = "(sr.series_date<? OR (sr.series_date=? AND sr.series_date<=?))";
					$sqlParams[] = $params['srDateTo'];
					$sqlParams[] = $params['srTimeTo'];
					$pageAddressParams['srTimeTo'] = $params['srTimeTo'];
				}
				else
				{
					$sqlCondArray[] = "sr.series_date<=?";
				}
			}
		}

		if($params['filterCadID'] != "")
		{
			// Search by regular expression
			$sqlCondArray[] = "el.exec_id=?";
			$sqlParams[] = $params['filterCadID'];
			$pageAddressParams['filterCadID'] = $params['filterCadID'];
		}
		
		if($params['filterPtID'] != "")
		{
			$patientID = $params['filterPtID'];
			if($_SESSION['anonymizeFlg'] == 1)  $patientID = PinfoScramble::decrypt($params['filterPtID'], $_SESSION['key']);		

			// Search by regular expression
			$sqlCondArray[] = "pt.patient_id~*?";
			$sqlParams[] = $patientID;
			$pageAddressParams['filterPtID'] = $params['filterPtID'];
		}

		if($params['filterPtName'] != "")
		{
			// Search by regular expression (test, case-insensitive)
			$sqlCondArray[] = "pt.patient_name~*?";
			$sqlParams[] = $params['filterPtName'];
			$pageAddressParams['filterPtName'] = $params['filterPtName'];
		}
		
		if($params['filterSex'] == "M" || $params['filterSex'] == "F")
		{
			$sqlCondArray[] = "pt.sex=?";
			$sqlParams[] = $params['filterSex'];
			$pageAddressParams['filterSex'] = $params['filterSex'];
		}
		
		if($params['filterAgeMin'] != "" && $params['filterAgeMax'] != "" && $params['filterAgeMin'] == $params['filterAgeMax'])
		{
			$sqlCondArray[] = "st.age=?";
			$sqlParams[] = $params['filterAgeMin'];
			$pageAddressParams['filterAgeMin'] = $params['filterAgeMin'];
			$pageAddressParams['filterAgeMax'] = $params['filterAgeMax'];
		}
		else
		{
			if($params['filterAgeMin'] != "")
			{
				$sqlCondArray[] = "?<=st.age";
				$sqlParams[] = $params['filterAgeMin'];
				$pageAddressParams['filterAgeMin'] = $params['filterAgeMin'];
			}
		
			if($params['filterAgeMax'] != "")
			{
				$sqlCondArray[] = "st.age<=?";
				$sqlParams[] = $params['filterAgeMax'];
				$pageAddressParams['filterAgeMax'] = $params['filterAgeMax'];
			}
		}				

		if($params['filterModality'] != "" && $params['filterModality'] != "all")
		{
			$sqlCondArray[] = "sr.modality=?";
			$sqlParams[] = $params['filterModality'];
			$pageAddressParams['filterModality'] = $params['filterModality'];
		}		
			
		
		if($params['filterCAD'] != "all")
		{
			$sqlCondArray[] = "el.plugin_name=?";
			$sqlParams[] = $params['filterCAD'];
			$pageAddressParams['filterCAD'] = $params['filterCAD'];
		}				
	
		if($params['filterVersion'] != "all")
		{
			$sqlCondArray[] = "el.version=?";
			$sqlParams[] = $params['filterVersion'];
			$pageAddressParams['filterVersion'] = $params['filterVersion'];
		}
		
		if($params['filterTag'] != "")
		{		
			$sqlCondArray[] = "el.exec_id IN (SELECT DISTINCT exec_id FROM executed_plugin_tag WHERE tag~*?)";
			$sqlParams[] = $params['filterTag'];
			$pageAddressParams['filterTag'] = $params['filterTag'];
		}
	
		if($params['personalFB'] == "entered" || $params['personalFB'] == "notEntered")
		{
			$params['pageAddress'] .= 'personalFB=' . $params['personalFB'];
			
			$operator = ($params['personalFB'] == "entered") ? '=' : '<>';
		
			$tmpCond .= " el.exec_id " . $operator . " ANY"
					 .  " (SELECT DISTINCT exec_id FROM lesion_feedback WHERE consensual_flg='f'"
					 .  " AND interrupt_flg='f'";
		
			if($params['personalFB'] == "entered")
			{
				if($params['filterFBUser'] != "")
				{
					if(strncmp($params['filterFBUser'],'PAIR"', 5) == 0)
					{
						$tmpStr = substr($params['filterFBUser'], 5);
						$fbUserArr = array();

						array_push($fbUserArr, strtok($tmpStr,'"'));
		
						while($tmpStr2 = strtok('"'))
						{
							echo $tmpStr2;
							if($tmpStr2 != ")")  array_push($fbUserArr, $tmpStr2);
						}
						
						if(count($fbUserArr) == 1)
						{
							$tmpCond .= ' AND entered_by=?)';
							$sqlParams[] = $fbUserArr[0];
						}
						else if(count($fbUserArr) >= 2)
						{
							$tmpCond .= " AND entered_by~*? AND exec_id IN"
									 .  " (SELECT DISTINCT exec_id FROM lesion_feedback"
									 .  "  WHERE consensual_flg='f' AND interrupt_flg='f'"
									 .  "  AND entered_by~*?))";
									 
							$sqlParams[] = $fbUserArr[0];
							$sqlParams[] = $fbUserArr[1];
						}
					}
					else
					{
						$tmpCond .= ' AND entered_by~*?)';
						$sqlParams[] = $params['filterFBUser'];
					}
			
					$params['filterFBUser'] = htmlspecialchars($params['filterFBUser']);
					$pageAddressParams['filterFBUser'] = $params['filterFBUser'];
				}
				else
				{
					$tmpCond .= ' AND entered_by=?)';
					array_push($condArr, $userID);
				}
			}
			else
			{
				$tmpCond .= ")";
			} 
			$sqlCondArray[] = $tmpCond;
		}	
	
		if($params['consensualFB'] == "entered" || $params['consensualFB'] == "notEntered")
		{
			$operator = ($params['consensualFB'] == "entered") ? '=' : '<>';
		
			$tmpCond = "el.exec_id " . $operator . " ANY"
					 . " (SELECT exec_id FROM lesion_feedback WHERE consensual_flg='t' AND interrupt_flg='f')";

			$sqlCondArray[] = $tmpCond;
			$pageAddressParams['consensualFB'] = $params['consensualFB'];
		}		
	
		if($params['filterTP'] == "with" || $params['filterTP'] == "without")
		{
			$operator = ($params['filterTP'] == "with") ? '>=' : '<';
		
			$tmpCond .= " el.exec_id IN (SELECT DISTINCT exec_id FROM lesion_feedback WHERE interrupt_flg='f'";
	
			if($params['consensualFB'] == "entered")
			{
				$tmpCond .= " AND consensual_flg='t'";
			}
			else if($params['consensualFB'] == "notEntered")
			{
				$tmpCond .= " AND consensual_flg='f'";
			}
			$tmpCond .= " GROUP BY exec_id HAVING MAX(evaluation)" . $tmpCond . "1)";

			$sqlCondArray[] = $tmpCond;
			$pageAddressParams['filterTP'] = $params['filterTP'];
		}
				
		if($params['filterFN'] == "with" || $params['filterFN'] == "without") 
		{
			$condition = ($params['filterFN'] == "with") ? '>=1' : '=0';
			
			$tmpCond = "el.exec_id IN (SELECT DISTINCT exec_id FROM false_negative_count WHERE status>0";
			
			if($params['consensualFB'] == "entered")
			{
				$tmpCond .= " AND consensual_flg='t'";
			}
			else if($params['consensualFB'] == "notEntered")
			{
				$tmpCond .= " AND consensual_flg='f'";
			}
			$tmpCond .= " GROUP BY exec_id HAVING MAX(false_negative_num)" .  $condition . ")";
			
			$sqlCondArray[] = $tmpCond;
			$pageAddressParams['filterFN'] = $params['filterFN'];
		}
		
		if(count($sqlParams) > 0)  $sqlCond .= sprintf(" WHERE %s", implode(' AND ', $sqlCondArray));		
		
		$sqlCond .= " GROUP BY el.exec_id, pt.patient_id, pt.patient_name, st.age, pt.sex,"
				 .  " sr.series_date, sr.series_time, el.plugin_name, el.version, el.executed_at,"
				 .  " es.study_instance_uid, es.series_instance_uid";
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// Retrieve mode of display order (Default: ascending order of series number)
		//--------------------------------------------------------------------------------------------------------------
		$orderColStr = "";
		
		switch($params['orderCol'])
		{
			case "Patient ID":  $orderColStr = 'pt.patient_id '   . $params['orderMode'];  break;	
			case "Name":        $orderColStr = 'pt.patient_name ' . $params['orderMode'];  break;	
			case "Age":         $orderColStr = 'st.age '          . $params['orderMode'];  break;
			case "Sex":         $orderColStr = 'pt.sex '          . $params['orderMode'];  break;
			case "Series":      $orderColStr = 'sr.series_date '.$params['orderMode'].', sr.series_time '.$params['orderMode']; break;
			case "CAD":         $orderColStr = 'el.plugin_name '.$params['orderMode'].', el.version '.$params['orderMode'];        break;
			default:
					$params['orderCol'] = "CAD date";
					$orderColStr = 'el.executed_at '  . $params['orderMode'];
					break;
		}
		
		$pageAddressParams['orderCol']  = $params['orderCol'];
		$pageAddressParams['orderMode'] = $params['orderMode'];
		$pageAddressParams['showing']   = $params['showing'];		
		//--------------------------------------------------------------------------------------------------------------
			
		$params['pageAddress'] = implode('&', array_map(UrlKeyValPair, array_keys($pageAddressParams), array_values($pageAddressParams)));
		$_SESSION['listAddress'] = $params['pageAddress'];

		//--------------------------------------------------------------------------------------------------------------
		// count total number
		//--------------------------------------------------------------------------------------------------------------
		$stmt = $pdo->prepare("SELECT COUNT(*) " . $sqlCond);
		$stmt->execute($sqlParams);
		
		$params['totalNum'] = $stmt->fetchColumn();
		//$params['totalNum'] = $stmt->rowCount();
		$params['maxPageNum'] = ($params['showing'] == "all") ? 1 : ceil($params['totalNum'] / $params['showing']);
		$params['startPageNum'] = max($params['pageNum'] - $PAGER_DELTA, 1);
		$params['endPageNum']   = min($params['pageNum'] + $PAGER_DELTA, $params['maxPageNum']);		
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// Set $data array
		//--------------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT el.exec_id, pt.patient_id, pt.patient_name, st.age, pt.sex,"
		        . " sr.series_date, sr.series_time, el.plugin_name, el.version, el.executed_at,"
		        . " es.study_instance_uid, es.series_instance_uid,"
		        . " MAX(lf.evaluation) as tp_max,"
		        . " MAX(fn.false_negative_num) as fn_max"
				. $sqlCond . " ORDER BY " . $orderColStr;
				
		//echo $sqlStr;
		
		if($params['showing'] != "all")
		{
			$sqlStr .= " LIMIT ? OFFSET ?";
			$sqlParams[] = $params['showing'];
			$sqlParams[] = $params['showing'] * ($params['pageNum']-1);
		}
		
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($sqlParams);

		//var_dump($stmt);
		//var_dump($condArr);
		
		$rowNum = $stmt->rowCount();
		$params['startNum'] = ($rowNum == 0) ? 0 : $params['showing'] * ($params['pageNum']-1) + 1;
		$params['endNum']   = ($rowNum == 0) ? 0 : $params['startNum'] + $rowNum - 1;			
		

		//------------------------------------------------------------------------------------------
		// For today's CAD 
		//------------------------------------------------------------------------------------------
		// SQL statement to count entered heads of personal feedback		
		$sqlStr  = "SELECT COUNT(DISTINCT entered_by) FROM lesion_feedback"
	             . " WHERE exec_id=? AND consensual_flg=? AND interrupt_flg='f'";
		$stmtHeads = $pdo->prepare($sqlStr);

		// SQL statement to count the number of TP
		$sqlStr = "SELECT COUNT(*) FROM lesion_feedback WHERE exec_id=?"
		        . " AND consensual_flg=? AND evaluation>=1 AND interrupt_flg='f'";
		$stmtTPCnt = $pdo->prepare($sqlStr);

		// SQL statement to count the number of personal feedback
		$sqlStr  = "SELECT COUNT(*) FROM lesion_feedback WHERE exec_id=? AND consensual_flg='f'"
		         . " AND interrupt_flg='f' AND entered_by=?";
		$stmtPersonalFB = $pdo->prepare($sqlStr);
		$stmtPersonalFB->bindParam(2, $_SESSION['userID']);

		// SQL statement to count the number of personal feedback
		//$sqlStr  = "SELECT COUNT(*) FROM false_negative_count WHERE exec_id=? AND consensual_flg='f'"
		//         . " AND status=2 AND entered_by=?";
		//$stmtPersonalFN = $pdo->prepare($sqlStr);
		//$stmtPersonalFN->bindParam(2, $_SESSION['userID']);
		//------------------------------------------------------------------------------------------
		
		//------------------------------------------------------------------------------------------
		// For cad log
		//------------------------------------------------------------------------------------------
		// SQL statement for count No. of TP
		$sqlStr  = "SELECT COUNT(*) FROM lesion_feedback WHERE exec_id=? AND consensual_flg=?"
		         . " AND interrupt_flg='f' AND evaluation>=1";
					
		$stmtTP = $pdo->prepare($sqlStr);

		// SQL statement for count No. of FN
		$sqlStr  = "SELECT false_negative_num FROM false_negative_count WHERE exec_id=?"
			     . " AND consensual_flg=? AND false_negative_num>0 AND status>=1";

		$stmtFN = $pdo->prepare($sqlStr);
		//------------------------------------------------------------------------------------------
		
		while($result = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			if($_SESSION['anonymizeFlg'] == 1)
			{
				$patientID   = PinfoScramble::encrypt($result['patient_id'], $_SESSION['key']);	// Patient ID
				$patientName = PinfoScramble::scramblePtName();
			}
			else
			{
				$patientID   = $result['patient_id'];
				$patientName = $result['patient_name'];
			}
			
			$colArr = array($patientID,
							$patientName,
							$result['age'],
							$result['sex'],
							$result['series_date'],
							$result['series_time'],
							($result['plugin_name'].' v.'.$result['version']),
							(($params['mode'] == 'today') ? substr($result['executed_at'], 11) : $result['executed_at']),
							$result['plugin_name'],
							$result['version'],
							$result['study_instance_uid'],
							$result['series_instance_uid']);
	
			$flgArray = array('f', 't');
	
			if($params['mode'] == 'today')
			{
				if($_SESSION['colorSet'] == "admin")
				{	
					$stmtHeads->bindParam(1, $result['exec_id']);
					$stmtTPCnt->bindParam(1, $result['exec_id']);
					
					for($i=0; $i<2; $i++)
					{
						$stmtHeads->bindParam(2, $flgArray[$i]);
						$stmtHeads->execute();
						$numFeedback = $stmtHeads->fetchColumn();
						
						$numTP = 0;
						
						if($numFeedback > 0)
						{
							$stmtTPCnt->bindParam(2, $flgArray[$i]);
							$stmtTPCnt->execute();
							
							$numTP = $stmtTPCnt->fetchColumn();
						}
						
						if($numTP > 0)	$colArr[] = '<span style="color:#0000ff; font-weight:bold;">'.$numFeedback.'</span>';
						else			$colArr[] = $numFeedback;
					}
				}
				else if($_SESSION['colorSet']=="user" && $_SESSION['personalFBFlg'])
				{
					$stmtPersonalFB->bindParam(1, $result['exec_id']);
					$stmtPersonalFB->execute();
						
					$colArr[] = ($stmtPersonalFB->fetchColumn() > 0) ? 'Registered' : '-';
				}				
			}
			else
			{
				if($_SESSION['colorSet'] == "admin")
				{
					$stmtHeads->bindParam(1, $result['exec_id']);
					$stmtTPCnt->bindParam(1, $result['exec_id']);
					
					$stmtHeads->bindParam(2, $flgArray[0]);
					$stmtHeads->execute();
					$numFeedback = $stmtHeads->fetchColumn();
						
					$numTP = 0;
						
					if($numFeedback > 0)
					{
						$stmtTPCnt->bindParam(2, $flgArray[0]);
						$stmtTPCnt->execute();
							
						$numTP = $stmtTPCnt->fetchColumn();
					}
					
					if($numTP > 0)	$colArr[] = '<span style="color:#0000ff; font-weight:bold;">'.$numFeedback.'</span>';
					else			$colArr[] = $numFeedback;
				}
				else if($_SESSION['colorSet']=="user" && $_SESSION['personalFBFlg'])
				{
					$stmtPersonalFB->bindParam(1, $result['exec_id']);
					$stmtPersonalFB->execute();
						
					$colArr[] = ($stmtPersonalFB->fetchColumn() > 0) ? 'Registered' : '-';
				}							

				$tpColStr = "-";
				$fnColStr = "-";

				if($result['tp_max']>=1)
				{
					$stmtTP->bindValue(1, $result['exec_id']);
					$stmtTP->bindValue(2, 'f', PDO::PARAM_BOOL);
					$stmtTP->execute();
					
					if($stmtTP->fetchColumn() > 0)	$tpColStr = '<span style="font-weight:bold;">+</span>';
					else
					{
						$stmtTP->bindValue(2, 'f', PDO::PARAM_BOOL);
						$stmtTP->execute();
						if($stmtTP->fetchColumn() > 0) $tpColStr = '<span style="font-weight:bold;">!</span>';
					}
				}
	
				if($result['fn_max']>=1)
				{
					$stmtFN->bindValue(1, $result['exec_id']);
					$stmtFN->bindValue(2, 't', PDO::PARAM_BOOL);
					$stmtFN->execute();	
				
					if($stmtFN->fetchColumn() > 0)  $fnColStr = '<span style="font-weight:bold;">+</span>';
					else
					{
						$stmtFN->bindValue(2, 'f', PDO::PARAM_BOOL);
						$stmtFN->execute();	
						if($stmtFN->fetchColumn() > 0)  $fnColStr = '<span style="font-weight:bold;">!</span>';
					}
				}
			
				$colArr[] = $tpColStr;
				$colArr[] = $fnColStr;
			}
			
			$data[] = $colArr;

		}// end while
		
		// set paramseter of CAD, version menu
		include('set_cad_panel_params.php');
		$versionList = array("all");
		
		if($params['filterCAD'] != 'all' && $params['filterVersion'] != 'all')
		{
		
			for($i=0; $i<$cadNum; $i++)
			{
				if($params['filterCAD'] == $cadList[$i][0])
				{
					$tmpArr = explode('^', $cadList[$i][1]);
					
					foreach($tmpArr as $item)
					{
						$versionList[] = $item;
					}		
					break;
				}
			}
		}

		//var_dump($data);
		//-------------------------------------------------------------------------------------------------------*/
	
		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		require_once('smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
		
		$smarty->assign('params', $params);
		$smarty->assign('data',   $data);
		
		$smarty->assign('modalityList',    $modalityList);
		$smarty->assign('modalityMenuVal', $modalityMenuVal);	
		$smarty->assign('cadList',         $cadList);
		$smarty->assign('versionList',     $versionList);		

		$smarty->display('cad_log.tpl');
		//--------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;	
?>


