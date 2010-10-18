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
	// Import $_REQUEST variables and set $param array	
	//------------------------------------------------------------------------------------------------------------------
	$param = array('srcPage'             => (isset($_REQUEST['srcPage'])) ? $_REQUEST['srcPage'] : "",
	               'filterPtID'          => (isset($_REQUEST['filterPtID'])) ? $_REQUEST['filterPtID'] : "",
				   'filterPtName'        => (isset($_REQUEST['filterPtName'])) ? $_REQUEST['filterPtName'] : "",
				   'filterSex'           => (isset($_REQUEST['filterSex'])) ? $_REQUEST['filterSex'] : "all",
				   'filterAgeMin'        => (isset($_REQUEST['filterAgeMin'])) ? $_REQUEST['filterAgeMin'] : "",
				   'filterAgeMax'        => (isset($_REQUEST['filterAgeMax'])) ? $_REQUEST['filterAgeMax'] : "",
				   'filterCadID'         => (isset($_REQUEST['filterCadID'])) ? $_REQUEST['filterCadID'] : "",
				   'filterModality'      => (isset($_REQUEST['filterModality'])) ? $_REQUEST['filterModality'] : "all",
				   'filterCAD'           => (isset($_REQUEST['filterCAD'])) ? $_REQUEST['filterCAD'] : "all",
				   'filterVersion'       => (isset($_REQUEST['filterVersion'])) ? $_REQUEST['filterVersion'] : "all",
				   'filterTag'           => (isset($_REQUEST['filterTag'])) ? $_REQUEST['filterTag'] : "",
				   'srDateFrom'          => (isset($_REQUEST['srDateFrom'])) ? $_REQUEST['srDateFrom'] : "",
				   'srDateTo'            => (isset($_REQUEST['srDateTo'])) ? $_REQUEST['srDateTo'] : "",
				   //'srTimeFrom'          => (isset($_REQUEST['srTimeFrom'])) ? $_REQUEST['srTimeFrom'] : "00:00:00",
				   'srTimeTo'            => (isset($_REQUEST['stTimeTo'])) ? $_REQUEST['stTimeTo'] : "",
				   'cadDateFrom'         => (isset($_REQUEST['cadDateFrom'])) ? $_REQUEST['cadDateFrom'] : "",
				   'cadDateTo'           => (isset($_REQUEST['cadDateTo'])) ? $_REQUEST['cadDateTo'] : "",
				   //'cadTimeFrom'         => (isset($_REQUEST['cadTimeFrom'])) ? $_REQUEST['cadTimeFrom'] : "00:00:00",
				   'cadTimeTo'           => (isset($_REQUEST['cadTimeTo'])) ? $_REQUEST['cadTimeTo'] : "",
				   'personalFB'          => (isset($_REQUEST['personalFB'])) ? $_REQUEST['personalFB'] : "all",
				   'consensualFB'        => (isset($_REQUEST['consensualFB'])) ? $_REQUEST['consensualFB'] : "all",
				   'filterFBUser'        => (isset($_REQUEST['filterFBUser'])) ? $_REQUEST['filterFBUser'] : "",
				   'filterTP'            => (isset($_REQUEST['filterTP'])) ? $_REQUEST['filterTP'] : "all",
				   'filterFN'            => (isset($_REQUEST['filterFN'])) ? $_REQUEST['filterFN'] : "all",
				   'mode'                => (isset($_REQUEST['mode'])) ? $_REQUEST['mode'] : "",
				   'orderCol'            => (isset($_REQUEST['orderCol'])) ? $_REQUEST['orderCol'] : "Study date",
				   'orderMode'           => ($_REQUEST['orderMode'] === "ASC") ? "ASC" : "DESC",
				   'totalNum'            => (isset($_REQUEST['totalNum']))  ? $_REQUEST['totalNum'] : 0,
				   'pageNum'             => (isset($_REQUEST['pageNum']))   ? $_REQUEST['pageNum']  : 1,
				   'showing'             => (isset($_REQUEST['showing'])) ? $_REQUEST['showing'] : 10,
				   'startNum'            => 1,
				   'endNum'              => 10,
				   'maxPageNum'          => 1,
				   'pageAddress'         => 'cad_log.php?');

	if($param['filterSex'] != "M" && $param['filterSex'] != "F")  $param['filterSex'] = "all";
	if($param['showing'] != "all" && $param['showing'] < 10)  $param['showing'] = 10;
	
	$userID = $_SESSION['userID'];
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Retrieve mode of display order (Default: ascending order of series number)
	//------------------------------------------------------------------------------------------------------------------
	$orderColStr = "";
	
	switch($param['orderCol'])
	{
		case "Patient ID":  $orderColStr = 'pt.patient_id '   . $param['orderMode'];  break;	
		case "Name":        $orderColStr = 'pt.patient_name ' . $param['orderMode'];  break;	
		case "Age":         $orderColStr = 'st.age '          . $param['orderMode'];  break;
		case "Sex":         $orderColStr = 'pt.sex '          . $param['orderMode'];  break;
		case "Series":      $orderColStr = 'sr.series_date '.$param['orderMode'].', sr.series_time '.$param['orderMode']; break;
		case "CAD":         $orderColStr = 'el.plugin_name '.$param['orderMode'].', el.version '.$param['orderMode'];        break;
		default:
					$param['orderCol'] = "CAD date";
					$orderColStr = 'el.executed_at '  . $param['orderMode'];
					break;
	}
	//------------------------------------------------------------------------------------------------------------------

	$colParam = array( array('colName' => 'Patient ID',  'align' => 'al-l'),
		               array('colName' => 'Name',        'align' => 'al-l'),
		               array('colName' => 'Age',         'align' => ''),
		               array('colName' => 'Sex',         'align' => ''),
		               array('colName' => 'Date',        'align' => ''),
		               array('colName' => 'Time',        'align' => ''),
		               array('colName' => 'CAD',         'align' => 'al-l'),
		               array('colName' => 'CAD date',    'align' => ''),
		               array('colName' => 'Result',      'align' => ''),
		               array('colName' => 'Feedback',    'align' => ''));

	$data = array();

	try
	{
		$PinfoScramble = new PinfoScramble();

		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
		
		//--------------------------------------------------------------------------------------------------------
		// Create SQL queries 
		//--------------------------------------------------------------------------------------------------------
		$optionNum = 0;
		$condArr = array();		
		
		$sqlCond = " FROM patient_list pt JOIN (study_list st JOIN series_list sr"
			       . " ON (st.study_instance_uid = sr.study_instance_uid)) ON (pt.patient_id=st.patient_id)"
			       . " JOIN (executed_series_list es JOIN executed_plugin_list el"
			       . " ON (es.exec_id=el.exec_id AND es.series_id=1 AND el.plugin_type=1))"
			       . " ON (sr.series_instance_uid = es.series_instance_uid)"
			       . " LEFT JOIN lesion_feedback lf ON (es.exec_id=lf.exec_id AND lf.interrupt_flg='f')"
			       . " LEFT JOIN false_negative_count fn ON (es.exec_id = fn.exec_id AND fn.status>=1)";

		if($param['mode'] == 'today')
		{
			$param['showing'] = "all";  // for HIMEDIC
		
			$today = date("Y-m-d");
			$param['cadDateFrom'] = $today;
			$param['cadDateTo']   = $today;
			
			$sqlCond .= " WHERE el.executed_at>=? AND el.executed_at<=?";
			array_push($condArr, $param['cadDateFrom'] . ' 00:00:00');
			array_push($condArr, $param['cadDateFrom'] . ' 23:59:59');
			$param['pageAddress'] .= 'mode=today&cadDateFrom=' . $param['cadDateFrom'] . '&cadDateTo=' . $param['cadDateTo'];
			$optionNum++;		
		}
		else if($param['cadDateFrom'] != "" && $param['cadDateTo'] != "" && $param['cadDateFrom'] == $param['cadDateTo'])
		{
			if(0<$optionNum) 
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}
			else	$sqlCond .= " WHERE";	
		
			$sqlCond .= " el.executed_at>=? AND el.executed_at<=?";
			array_push($condArr, $param['cadDateFrom'] . ' 00:00:00');
			array_push($condArr, $param['cadDateFrom'] . ' 23:59:59');
			$param['pageAddress'] .= 'cadDateFrom=' . $param['cadDateFrom'] . '&cadDateTo=' . $param['cadDateTo'];
			$optionNum++;
		}
		else
		{
			if($param['cadDateFrom'] != "")
			{
				if(0<$optionNum) 
				{
					$sqlCond .= " AND";
					$param['pageAddress'] .= "&";
				}
				else	$sqlCond .= " WHERE";	
						
				$sqlCond .= " ?<=el.executed_at";
				array_push($condArr, $param['cadDateFrom'].' 00:00:00');
				$param['pageAddress'] .= 'cadDateFrom=' . $param['cadDateFrom'];
				$optionNum++;
			}
		
			if($param['cadDateTo'] != "")
			{
				if(0<$optionNum) 
				{
					$sqlCond .= " AND";
					$param['pageAddress'] .= "&";
				}
				else	$sqlCond .= " WHERE";
		
				if($param['cadTimeTo'] != "")
				{
					$sqlCond .= " el.executed_at<=?";
					array_push($condArr, $param['cadDateTo'] . ' ' . $param['cadTimeTo']);
					$param['pageAddress'] .= 'cadDateTo=' . $param['cadDateTo'] . '&cadTimeTo=' . $param['cadTimeTo'];
				}
				else
				{
					$sqlCond .= " el.executed_at<=?";
					array_push($condArr, $param['cadDateTo'] . ' 23:59:59');
					$param['pageAddress'] .= 'cadDateTo=' . $param['cadDateTo'];
				}
				$optionNum++;
			}
		}

	
		if($param['srDateFrom'] != "" && $param['srDateTo'] != "" && $param['srDateFrom'] == $param['srDateTo'])
		{
			if(0<$optionNum) 
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}
			else	$sqlCond .= " WHERE";
		
			$sqlCond .= " sr.series_date=?";
			array_push($condArr, $param['srDateFrom']);
			$param['pageAddress'] .= 'srDateFrom=' . $param['srDateFrom'] . '&srDateTo=' . $param['srDateTo'];
			$optionNum++;
		}
		else
		{
			if($param['srDateFrom'] != "")
			{
				if(0<$optionNum) 
				{
					$sqlCond .= " AND";
					$param['pageAddress'] .= "&";
				}
				else	$sqlCond .= " WHERE";

				$sqlCond .= " ?<=sr.series_date";
				array_push($condArr, $param['srDateFrom']);
				$param['pageAddress'] .= 'srDateFrom=' . $param['srDateFrom'];
				$optionNum++;
			}
		
			if($param['srDateTo'] != "")
			{
				if(0<$optionNum) 
				{
					$sqlCond .= " AND";
					$param['pageAddress'] .= "&";
				}
				else	$sqlCond .= " WHERE";
		
				if($param['srTimeTo'] != "")
				{
					$sqlCond .= " (sr.series_date<? OR (sr.series_date=? AND sr.series_date<=?))";
					array_push($condArr, $param['srDateTo']);
					array_push($condArr, $param['srDateTo']);
					array_push($condArr, $param['srTimeTo']);
					$param['pageAddress'] .= 'srDateTo=' . $param['srDateTo'] . '&srTimeTo=' . $param['srTimeTo'];
				}
				else
				{
					$sqlCond .= " sr.series_date<=?";
					array_push($condArr, $param['srDateTo']);
					$param['pageAddress'] .= 'srDateTo=' . $param['srDateTo'];
				}
				$optionNum++;
			}
		}

		if($param['filterCadID'] != "")
		{
			if(0<$optionNum) 
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}
			else	$sqlCond .= " WHERE";
			
			// Search by regular expression
			$sqlCond .= " el.exec_id=?";
			array_push($condArr, $param['filterCadID']);
			$param['pageAddress'] .= 'filterCadID=' . $param['filterCadID'];
			$optionNum++;
		}
		
		if($param['filterPtID'] != "")
		{
			$patientID = $param['filterPtID'];
			if($_SESSION['anonymizeFlg'] == 1)  $patientID = $PinfoScramble->Decrypt($param['filterPtID'], $_SESSION['key']);		

			if(0<$optionNum) 
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}
			else	$sqlCond .= " WHERE";
			
			// Search by regular expression
			$sqlCond .= " pt.patient_id~*?";
			array_push($condArr, $patientID);
			$param['pageAddress'] .= 'filterPtID=' . $param['filterPtID'];
			$optionNum++;
		}

		if($param['filterPtName'] != "")
		{
			if(0<$optionNum) 
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}
			else	$sqlCond .= " WHERE";
			
			// Search by regular expression (test, case-insensitive)
			$sqlCond .= " pt.patient_name~*?";
			array_push($condArr, $param['filterPtName']);
			$param['pageAddress'] .= 'filterPtName=' . $param['filterPtName'];
			$optionNum++;
		}
		
		if($param['filterSex'] == "M" || $param['filterSex'] == "F")
		{
			if(0<$optionNum) 
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}
			else	$sqlCond .= " WHERE";
			
			$sqlCond .= " pt.sex=?";
			array_push($condArr, $param['filterSex']);
			$param['pageAddress'] .= 'filterSex=' . $param['filterSex'];
			$optionNum++;
		}
		
		if($param['filterAgeMin'] != "" && $param['filterAgeMax'] != "" && $param['filterAgeMin'] == $param['filterAgeMax'])
		{
			if(0<$optionNum) 
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}
			else	$sqlCond .= " WHERE";
				
			$sqlCond .= " st.age=?";
			array_push($condArr, $param['filterAgeMin']);
			$param['pageAddress'] .= 'filterAgeMin=' . $param['filterAgeMin'] . '&filterAgeMax=' . $param['filterAgeMax'];
			$optionNum++;
		}
		else
		{
			if($param['filterAgeMin'] != "")
			{
				if(0<$optionNum) 
				{
					$sqlCond .= " AND";
					$param['pageAddress'] .= "&";
				}
				else	$sqlCond .= " WHERE";
				
				$sqlCond .= " ?<=st.age";
				array_push($condArr, $param['filterAgeMin']);
				$param['pageAddress'] .= 'filterAgeMin=' . $param['filterAgeMin'];
				$optionNum++;
			}
		
			if($param['filterAgeMax'] != "")
			{
				if(0<$optionNum) 
				{
					$sqlCond .= " AND";
					$param['pageAddress'] .= "&";
				}
				else	$sqlCond .= " WHERE";
				
				$sqlCond .= " st.age<=?";
				array_push($condArr, $param['filterAgeMax']);
				$param['pageAddress'] .= 'filterAgeMax=' . $param['filterAgeMax'];
				$optionNum++;
			}
		}				

		if($param['filterModality'] != "" && $param['filterModality'] != "all")
		{
			if(0<$optionNum) 
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}
			else	$sqlCond .= " WHERE";
			
			$sqlCond .= " sr.modality=?";
			array_push($condArr, $param['filterModality']);
			$param['pageAddress'] .= 'filterModality=' . $param['filterModality'];
			$optionNum++;
		}		
			
		
		if($param['filterCAD'] != "all")
		{
			if(0<$optionNum) 
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}
			else	$sqlCond .= " WHERE";
		
		 	$sqlCond .= " el.plugin_name=?";
			array_push($condArr, $param['filterCAD']);
			$param['pageAddress'] .= 'filterCAD=' . $param['filterCAD'];
			$optionNum++;
		}				
	
		if($param['filterVersion'] != "all")
		{
			if(0<$optionNum) 
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}
			else	$sqlCond .= " WHERE";
		
		 	$sqlCond .= " el.version=?";
			array_push($condArr, $param['filterVersion']);
			$param['pageAddress'] .= 'filterVersion=' . $param['filterVersion'];
			$optionNum++;
		}
		
		if($param['filterTag'] != "")
		{		
			if(0<$optionNum) 
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}
			else	$sqlCond .= " WHERE";
			
		 	$sqlCond .= " el.exec_id IN (SELECT DISTINCT exec_id FROM executed_plugin_tag WHERE tag~*?)";
			array_push($condArr, $param['filterTag']);
			$param['pageAddress'] .= 'filterTag=' . $param['filterTag'];
			$optionNum++;
		}
	
		if($param['personalFB'] == "entered" || $param['personalFB'] == "notEntered")
		{
			if(0<$optionNum) 
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}
			else	$sqlCond .= " WHERE";

			$param['pageAddress'] .= 'personalFB=' . $param['personalFB'];
			
			$operator = ($param['personalFB'] == "entered") ? '=' : '<>';
		
			$sqlCond .= " el.exec_id " . $operator . " ANY"
					 .  " (SELECT DISTINCT exec_id FROM lesion_feedback WHERE consensual_flg='f'"
					 .  " AND interrupt_flg='f'";

			if($param['personalFB'] == "entered")
			{
				if($param['filterFBUser'] != "")
				{
					//if(strncmp($param['filterFBUser'],'PAIR(', 5) == 0)
					if(strncmp($param['filterFBUser'],'PAIR"', 5) == 0)
					{
						//$tmpStr = trim(strtok(substr($param['filterFBUser'], 5),")"));
						//$fbUserArr = explode(',', $tmpStr);

						$tmpStr = substr($param['filterFBUser'], 5);
						$fbUserArr = array();
						//echo $tmpStr;
						
						array_push($fbUserArr, strtok($tmpStr,'"'));
						
						echo $fbUserArr[0];

						while($tmpStr2 = strtok('"'))
						{
							echo $tmpStr2;
							if($tmpStr2 != ")")  array_push($fbUserArr, $tmpStr2);
						}
						
						if(count($fbUserArr) == 1)
						{
							$sqlCond .= ' AND entered_by=?)';
							array_push($condArr, $fbUserArr[0]);
						}
						else if(count($fbUserArr) >= 2)
						{
							$sqlCond .= " AND entered_by~*? AND exec_id IN"
									 .  " (SELECT DISTINCT exec_id FROM lesion_feedback"
									 .  "  WHERE consensual_flg='f' AND interrupt_flg='f'"
									 .  "  AND entered_by~*?))";
									 
							array_push($condArr, $fbUserArr[0]);
							array_push($condArr, $fbUserArr[1]);
						}
					}
					else
					{
						$sqlCond .= ' AND entered_by~*?)';
						array_push($condArr, $param['filterFBUser']);
					}
			
					$param['filterFBUser'] = htmlspecialchars($param['filterFBUser']);
					$param['pageAddress'] .= 'filterFBUser=' . $param['filterFBUser'];
				}
				else
				{
					$sqlCond .= ' AND entered_by=?)';
					array_push($condArr, $userID);
				}
			}	
			$optionNum++;
		}	
	
		if($param['consensualFB'] == "entered" || $param['consensualFB'] == "notEntered")
		{
			if(0<$optionNum) 
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}
			else	$sqlCond .= " WHERE";
			
			$operator = ($param['consensualFB'] == "entered") ? '=' : '<>';
		
			$sqlCond .= " el.exec_id " . $operator . "ANY"
					 .  " (SELECT exec_id FROM lesion_feedback WHERE consensual_flg='t' AND interrupt_flg='f')";
			$param['pageAddress'] .= 'consensualFB=' . $param['consensualFB'];
			$optionNum++;
		}		
	
		if($param['filterTP'] == "with" || $param['filterTP'] == "without")
		{
			if(0<$optionNum) 
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}
			else	$sqlCond .= " WHERE";
			
			$operator = ($param['filterTP'] == "with") ? '>=' : '<';
		
			$sqlCond .= " el.exec_id IN (SELECT DISTINCT exec_id FROM lesion_feedback WHERE interrupt_flg='f'";
			if($param['consensualFB'] == "entered")          $sqlCond .= " AND consensual_flg='t'";
			else if($param['consensualFB'] == "notEntered")  $sqlCond .= " AND consensual_flg='f'";
			$sqlCond .= " GROUP BY exec_id HAVING MAX(evaluation)" . $operator . "1)";

			$param['pageAddress'] .= 'filterTP=' . $param['filterTP'];
			$optionNum++;
		}
				
		if($param['filterFN'] == "with" || $param['filterFN'] == "without") 
		{
			if(0<$optionNum) 
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}
			else	$sqlCond .= " WHERE";
			
			$condition = ($param['filterFN'] == "with") ? '>=1' : '=0';
			
			$sqlCond .= " el.exec_id IN (SELECT DISTINCT exec_id FROM false_negative_count WHERE status>0";
			if($param['consensualFB'] == "entered")          $sqlCond .= " AND consensual_flg='t'";
			else if($param['consensualFB'] == "notEntered")  $sqlCond .= " AND consensual_flg='f'";
			$sqlCond .= " GROUP BY exec_id HAVING MAX(false_negative_num)" .  $condition . ")";

			$param['pageAddress'] .= 'filterFN=' . $param['filterFN'];
			$optionNum++;
		}
		
		$sqlCond .= " GROUP BY el.exec_id, pt.patient_id, pt.patient_name, st.age, pt.sex,"
				 .  " sr.series_date, sr.series_time, el.plugin_name, el.version, el.executed_at,"
				 .  " es.study_instance_uid, es.series_instance_uid";

		if(0<$optionNum)  $param['pageAddress'] .= "&";
		$param['pageAddress'] .= 'orderCol=' . $param['orderCol'] . '&orderMode=' .  $param['orderMode']
		                      .  '&showing=' . $param['showing'];
							  
		$_SESSION['listAddress'] = $param['pageAddress'];
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// count total number
		//--------------------------------------------------------------------------------------------------------------
		$stmt = $pdo->prepare("SELECT COUNT(*) " . $sqlCond);
		$stmt->execute($condArr);
		
		//$param['totalNum'] = $stmt->fetchColumn();
		$param['totalNum'] = $stmt->rowCount();
		$param['maxPageNum'] = ($param['showing'] == "all") ? 1 : ceil($param['totalNum'] / $param['showing']);
		$param['startPageNum'] = max($param['pageNum'] - $PAGER_DELTA, 1);
		$param['endPageNum']   = min($param['pageNum'] + $PAGER_DELTA, $param['maxPageNum']);		
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
		
		if($param['showing'] != "all")
		{
			$sqlStr .= " LIMIT ? OFFSET ?";
			array_push($condArr, $param['showing']);
			array_push($condArr, $param['showing'] * ($param['pageNum']-1));
		}
		
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($condArr);

		//var_dump($stmt);
		//var_dump($condArr);
		
		$rowNum = $stmt->rowCount();
		$param['startNum'] = ($rowNum == 0) ? 0 : $param['showing'] * ($param['pageNum']-1) + 1;
		$param['endNum']   = ($rowNum == 0) ? 0 : $param['startNum'] + $rowNum - 1;			
		

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
				$patientID   = $PinfoScramble->Encrypt($result['patient_id'], $_SESSION['key']);	// Patient ID
				$patientName = $PinfoScramble->ScramblePtName();
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
							(($param['mode'] == 'today') ? substr($result['executed_at'], 11) : $result['executed_at']),
							$result['plugin_name'],
							$result['version'],
							$result['study_instance_uid'],
							$result['series_instance_uid']);
	
			$flgArray = array('f', 't');
	
			if($param['mode'] == 'today')
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
						
						if($numTP > 0)	array_push($colArr, ('<span style="color:#0000ff; font-weight:bold;">'.$numFeedback.'</span>'));
						else			array_push($colArr, $numFeedback);
					}
				}
				else if($_SESSION['colorSet']=="user" && $_SESSION['personalFBFlg'])
				{
					$stmtPersonalFB->bindParam(1, $result['exec_id']);
					$stmtPersonalFB->execute();
						
					array_push($colArr, ($stmtPersonalFB->fetchColumn() > 0) ? 'Registered' : '-');
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
					
					if($numTP > 0)	array_push($colArr, ('<span style="color:#0000ff; font-weight:bold;">'.$numFeedback.'</span>'));
					else			array_push($colArr, $numFeedback);
				}
				else if($_SESSION['colorSet']=="user" && $_SESSION['personalFBFlg'])
				{
					$stmtPersonalFB->bindParam(1, $result['exec_id']);
					$stmtPersonalFB->execute();
						
					array_push($colArr, ($stmtPersonalFB->fetchColumn() > 0) ? 'Registered' : '-');
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
			
				array_push($colArr, $tpColStr);
				array_push($colArr, $fnColStr);
			}
			
			array_push($data, $colArr);

		}// end while
		
		// set parameter of CAD, version menu
		include('set_cad_panel_param.php');
		$versionList = array("all");
		
		if($param['filterCAD'] != 'all' && $param['filterVersion'] != 'all')
		{
		
			for($i=0; $i<$cadNum; $i++)
			{
				if($param['filterCAD'] == $cadList[$i][0])
				{
					$tmpArr = explode('^', $cadList[$i][1]);
					
					foreach($tmpArr as $item)
					{
						array_push($versionList, $item);
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
		
		$smarty->assign('param', $param);
		$smarty->assign('colParam', $colParam);
		$smarty->assign('data', $data);
		
		$smarty->assign('modalityList', $modalityList);
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


