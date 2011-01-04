<?php
	session_cache_limiter('nocache');
	session_start();

	include_once('common.php');
	include_once("auto_logout.php");
	require_once('class/PersonalInfoScramble.class.php');	
	require_once('class/validator.class.php');	

	//-----------------------------------------------------------------------------------------------------------------
	//
	//-----------------------------------------------------------------------------------------------------------------
	function CheckRegistStatusPersonalFB($stmtPersonalFB, $stmtPersonalFN, $execID)
	{
		$registStatus = array('cand' => 0,
							  'FN'   => 0);  // 0：未入力、1：途中、2：入力済

		$stmtPersonalFB->bindParam(1, $execID);
		$stmtPersonalFB->execute();

		if($stmtPersonalFB->rowCount() > 0)
		{
			$registStatus['cand'] = 2;
	
			while($resultPersonalFB = $stmtPersonalFB->fetch(PDO::FETCH_ASSOC))
			{
				if($resultPersonalFB['evaluation'] == -99 || $resultPersonalFB['interrupt_flg'])
				{
					$registStatus['cand'] = 1;
					break;
				}		
			}
		}
	
		$stmtPersonalFN->bindParam(1, $execID);
		$stmtPersonalFN->execute();
		
		if($stmtPersonalFN->rowCount() == 1)
		{
			if($stmtPersonalFN->fetchColumn() == 2)  $registStatus['FN'] = 2;
			else									 $registStatus['FN'] = 1;
		}
	
		//echo '(' . $registStatus['cand'] . ' ' . $registStatus['FN'] . ')';
	
		if($registStatus['cand'] == 0 && $registStatus['FN'] == 0)
		{
			$ret = '-';
		}
		else if($registStatus['cand'] == 2 && $registStatus['FN'] == 2)
		{
			$ret = 'Registered';
		}
		else
		{
			$ret = '<span style="font-weight:bold;color:red;">Incomplete</span>';
		}
		return $ret;
	}
	//-----------------------------------------------------------------------------------------------------------------

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
	
		$userID = $_SESSION['userID'];
	
		//-----------------------------------------------------------------------------------------------------------------
		// Import $_GET variables and validation
		//-----------------------------------------------------------------------------------------------------------------
		$mode = (isset($_GET['mode']) && ($_GET['mode']=='today')) ? $_GET['mode'] : "";	
		$params = array();
	
		PgValidator::$conn = $pdo;
		$validator = new FormValidator();
		$validator->registerValidator('pgregex', 'PgRegexValidator');		
	
		if($mode != "today")
		{
			$validator->addRules(array(
				"cadDateFrom" => array(
					"type" => "date",
					"errorMes" => "'CAD date' is invalid."),
				"cadDateTo" => array(
					"type" => "date",
					"errorMes" => "'CAD date' is invalid."),
				"cadTimeTo" => array(
					"type" => "time",
					"errorMes" => "'CAD time' is invalid.")
				));	
		}
	
		$validator->addRules(array(
			"filterCadID" => array(
				"type" => "int", 
				"min" => '0',
				"errorMes" => "'CAD ID' is invalid."),
			"filterCAD" => array(
				"type" => "cadname",
				"default" => "all",
				"otherwise"=> "all",
				"errorMes" => "'CAD' is invalid."),
			"filterVersion" => array(
				"type" => "version",
				"default" => "all",
				"otherwise" => "all",
				"errorMes" => "'Version' is invalid."),
			"filterPtID" => array(
				"type" => "pgregex",
				"errorMes" => "'Patient ID' is invalid."),
			"filterPtName" => array(
				"type" => "pgregex",
				"errorMes" => "'Patient name' is invalid."),
			"filterSex" => array(
				"type" => "select",
				"options" => array('M', 'F', 'all'),
				"default" => "all",
				"otherwise" => "all"),
			"filterAgeMin" => array(
				"type" => "int", 
				"min" => "0",
				"errorMes" => "'Age' is invalid."),
			"filterAgeMax" => array(
				"type" => "int", 
				"min" => "0",
				"errorMes" => "'Age' is invalid."),
			"filterModality" => array(
				"type" => "select", 
				"options" => $modalityList,
				"default" => "all",
				"otherwise" => "all"),
			"srDateFrom" => array(
				"type" => "date",
				"errorMes" => "'Series date' is invalid."),
			"srDateTo" => array(
				"type" => "date",
				"errorMes" => "'Series date' is invalid."),
			"srTimeTo" => array(
				"type" => "time",
				"errorMes" => "'Series time' is invalid."),
			"filterTag"=> array(
				"type" => "pgregex",
				"errorMes" => "'Tag' is invalid."),
			"filterFBUser"=> array(
				"type" => "pgregex",
				"errorMes" => "'Series description' is invalid."),
			"personalFB" => array(
				"type" => "select",
				"options" => array("entered", "notEntered", "all"),
				"default" => "all",
				"otherwise"  => "all"),
			"consensualFB" => array(
				"type" => "select",
				"options" => array("entered", "notEntered", "all"),
				"default" => "all",
				"otherwise"  => "all"),
			"filterTP" => array(
				"type" => "select",
				"options" => array("with", "withour", "all"),
				"default" => "all",
				"otherwise"  => "all"),
			"filterFN" => array(
				"type" => "select",
				"options" => array("with", "without", "all"),
				"default" => "all",
				"otherwise"  => "all"),
			"orderCol" => array(
				"type" => "select",
				"options" => array("Patient ID","Name","Age","Sex","Series","CAD","CAD date"),
				"default" => "CAD date",
				"otherwise" => "CAD date"),
			"orderMode" => array(
				"type" => "select",
				"options" => array('DESC', 'ASC'),
				"default" => "DESC",
				"otherwise"  => "DESC"),
			"showing" => array(
				"type" => "select",
				"options" => array("10", "25", "50", "all"),
				"default" => "10",
				"otherwise" => "10")
			));
		
		if($validator->validate($_GET))
		{
			$params = $validator->output;
			$params['errorMessage'] = "&nbsp;";
			
			$params['pageNum']  = (isset($_GET['pageNum']) && ctype_digit($_GET['pageNum'])) ? $_GET['pageNum'] : 1;
			$params['startNum'] = 0;
			$params['endNum'] = 0;
			$params['totalNum'] = 0;
			$params['maxPageNum'] = 1;
			
			if(isset($params['filterAgeMin']) && isset($params['filterAgeMax'])
			   && $params['filterAgeMin'] > $params['filterAgeMax'])
			{
				//$params['errorMessage'] = "Range of 'Age' is invalid."; 
				$tmp = $params['filterAgeMin'];
				$params['filterAgeMin'] = $params['filterAgeMax'];
				$params['filterAgeMax'] = $tmp;
			}
		}
		else
		{
			$params = $validator->output;
			$params['errorMessage'] = implode('<br/>', $validator->errors);
		}
		$params['mode'] = $mode;
		//-----------------------------------------------------------------------------------------------------------------
	
		$data = array();

		//--------------------------------------------------------------------------------------------------------
		// Create SQL queries 
		//--------------------------------------------------------------------------------------------------------
		$sqlCondArray = array();
		$sqlParams = array();
		$addressParams = array();	
		
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
			$addressParams['mode'] .= 'today';
			$addressParams['cadDateFrom'] = $params['cadDateFrom'];
			$addressParams['cadDateTo'] = $params['cadDateTo'];
		}
		else if($params['cadDateFrom'] != "" && $params['cadDateTo'] != "" && $params['cadDateFrom'] == $params['cadDateTo'])
		{
			$sqlCondArray[] = "el.executed_at>=? AND el.executed_at<=?";
			$sqlParams[] = $params['cadDateFrom'] . ' 00:00:00';
			$sqlParams[] = $params['cadDateFrom'] . ' 23:59:59';
			$addressParams['cadDateFrom'] = $params['cadDateFrom'];
			$addressParams['cadDateTo'] = $params['cadDateTo'];
		}
		else
		{
			if($params['cadDateFrom'] != "")
			{
				$sqlCondArray[] = "?<=el.executed_at";
				$sqlParams[] = $params['cadDateFrom'].' 00:00:00';
				$addressParams['cadDateFrom'] = $params['cadDateFrom'];
			}
		
			if($params['cadDateTo'] != "")
			{
				$sqlCondArray[] = "el.executed_at<=?";
				$addressParams['cadDateTo'] = $params['cadDateTo'];

				if($params['cadTimeTo'] != "")
				{
					$sqlParams[] = $params['cadDateTo'] . ' ' . $params['cadTimeTo'];
					$addressParams['cadTimeTo'] = $params['cadTimeTo'];
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
			
			$addressParams['srDateFrom'] = $params['srDateFrom'];
			$addressParams['srDateTo'] = $params['srDateTo'];
		}
		else
		{
			if($params['srDateFrom'] != "")
			{
				$sqlCondArray[] = "?<=sr.series_date";
				$sqlParams[] = $params['srDateFrom'];
				$addressParams['srDateFrom'] = $params['srDateFrom'];
			}
		
			if($params['srDateTo'] != "")
			{
				$sqlParams[] = $params['srDateTo'];
				$addressParams['srDateTo'] = $params['srDateTo'];
		
				if($params['srTimeTo'] != "")
				{
					$sqlCondArray[] = "(sr.series_date<? OR (sr.series_date=? AND sr.series_date<=?))";
					$sqlParams[] = $params['srDateTo'];
					$sqlParams[] = $params['srTimeTo'];
					$addressParams['srTimeTo'] = $params['srTimeTo'];
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
			$addressParams['filterCadID'] = $params['filterCadID'];
		}
		
		if($params['filterPtID'] != "")
		{
			$patientID = $params['filterPtID'];
			if($_SESSION['anonymizeFlg'] == 1)  $patientID = PinfoScramble::decrypt($params['filterPtID'], $_SESSION['key']);		

			// Search by regular expression
			$sqlCondArray[] = "pt.patient_id~*?";
			$sqlParams[] = $patientID;
			$addressParams['filterPtID'] = $params['filterPtID'];
		}

		if($params['filterPtName'] != "")
		{
			// Search by regular expression (test, case-insensitive)
			$sqlCondArray[] = "pt.patient_name~*?";
			$sqlParams[] = $params['filterPtName'];
			$addressParams['filterPtName'] = $params['filterPtName'];
		}
		
		if($params['filterSex'] == "M" || $params['filterSex'] == "F")
		{
			$sqlCondArray[] = "pt.sex=?";
			$sqlParams[] = $params['filterSex'];
			$addressParams['filterSex'] = $params['filterSex'];
		}
		
		if($params['filterAgeMin'] != "" && $params['filterAgeMax'] != "" && $params['filterAgeMin'] == $params['filterAgeMax'])
		{
			$sqlCondArray[] = "st.age=?";
			$sqlParams[] = $params['filterAgeMin'];
			$addressParams['filterAgeMin'] = $params['filterAgeMin'];
			$addressParams['filterAgeMax'] = $params['filterAgeMax'];
		}
		else
		{
			if($params['filterAgeMin'] != "")
			{
				$sqlCondArray[] = "?<=st.age";
				$sqlParams[] = $params['filterAgeMin'];
				$addressParams['filterAgeMin'] = $params['filterAgeMin'];
			}
		
			if($params['filterAgeMax'] != "")
			{
				$sqlCondArray[] = "st.age<=?";
				$sqlParams[] = $params['filterAgeMax'];
				$addressParams['filterAgeMax'] = $params['filterAgeMax'];
			}
		}				

		if($params['filterModality'] != "" && $params['filterModality'] != "all")
		{
			$sqlCondArray[] = "sr.modality=?";
			$sqlParams[] = $params['filterModality'];
			$addressParams['filterModality'] = $params['filterModality'];
		}		
			
		
		if($params['filterCAD'] != "all")
		{
			$sqlCondArray[] = "el.plugin_name=?";
			$sqlParams[] = $params['filterCAD'];
			$addressParams['filterCAD'] = $params['filterCAD'];
		}				
	
		if($params['filterVersion'] != "all")
		{
			$sqlCondArray[] = "el.version=?";
			$sqlParams[] = $params['filterVersion'];
			$addressParams['filterVersion'] = $params['filterVersion'];
		}
		
		if($params['filterTag'] != "")
		{		
			$sqlCondArray[] = "el.exec_id IN (SELECT DISTINCT reference_id FROM tag_list WHERE category=4 AND tag~*?)";
			$sqlParams[] = $params['filterTag'];
			$addressParams['filterTag'] = $params['filterTag'];
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
							//echo $tmpStr2;
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
						if($_SESSION[''] == "admin")
						{
							$tmpCond .= ')';
						}
						else
						{
							$tmpCond .= ' AND entered_by~*?)';
							$sqlParams[] = $params['filterFBUser'];
						}
					}
			
					$params['filterFBUser'] = htmlspecialchars($params['filterFBUser']);
					$addressParams['filterFBUser'] = $params['filterFBUser'];
				}
				else	//　entered by 欄に何も入力されていない場合
				{
					if($_SESSION['colorSet'] == 'admin')	// 管理者は全ユーザのpersonal feedbackをcheck
					{
						$tmpCond .= ')';
					}
					else									
					{
						$tmpCond .= ' AND entered_by=?)';
						$sqlParams[] = $userID;
					}
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

			//if($params['filterTP'] == "all" && $params['filterFN'] == "all")
			//{
				$sqlCondArray[] = $tmpCond;
			//}
			$addressParams['consensualFB'] = $params['consensualFB'];
		}		
	
		if($params['filterTP'] == "with" || $params['filterTP'] == "without")
		{
			$condition = ($params['filterTP'] == "with") ? '>0' : '<=0';
		
			$tmpCond = " el.exec_id IN (SELECT DISTINCT exec_id FROM lesion_feedback WHERE interrupt_flg='f'";
	
			if($params['consensualFB'] == "entered")
			{
				$tmpCond .= " AND consensual_flg='t'";
			}
			else if($params['consensualFB'] == "notEntered")
			{
				$tmpCond .= " AND consensual_flg='f'";
			}
			$tmpCond .= " GROUP BY exec_id HAVING MAX(evaluation)" . $condition . ")";

			$sqlCondArray[] = $tmpCond;
			$addressParams['filterTP'] = $params['filterTP'];
		}
				
		if($params['filterFN'] == "with" || $params['filterFN'] == "without") 
		{
			$condition = ($params['filterFN'] == "with") ? '>=1' : '=0';
			
			$tmpCond = "el.exec_id IN (SELECT DISTINCT exec_id FROM false_negative_count WHERE status=2";
			
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
			$addressParams['filterFN'] = $params['filterFN'];
		}
		
		//var_dump($sqlCondArray);
		
		if(count($sqlCondArray) > 0)  $sqlCond .= sprintf(" WHERE %s", implode(' AND ', $sqlCondArray));		
		
		$sqlCond .= " GROUP BY el.exec_id, pt.patient_id, pt.patient_name, st.age, pt.sex,"
				 .  " sr.series_date, sr.series_time, el.plugin_name, el.version,"
				 .  " el.exec_user, el.executed_at, es.study_instance_uid, es.series_instance_uid";
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
			case "Series":      
					$orderColStr = 'sr.series_date '.$params['orderMode'].', sr.series_time '.$params['orderMode'];
					break;
	
			case "CAD":         
					$orderColStr = 'el.plugin_name '.$params['orderMode'].', el.version '.$params['orderMode'];
					break;
			
			default:
					$params['orderCol'] = "CAD date";
					$orderColStr = 'el.executed_at '  . $params['orderMode'];
					break;
		}
		
		$addressParams['orderCol']  = $params['orderCol'];
		$addressParams['orderMode'] = $params['orderMode'];
		$addressParams['showing']   = $params['showing'];		
		//--------------------------------------------------------------------------------------------------------------
			
		$params['pageAddress'] = sprintf('cad_log.php?%s',
		                         implode('&', array_map(UrlKeyValPair, array_keys($addressParams), array_values($addressParams))));
		$_SESSION['listAddress'] = $params['pageAddress'];

		//--------------------------------------------------------------------------------------------------------------
		// Set $data array
		//--------------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT el.exec_id, pt.patient_id, pt.patient_name, st.age, pt.sex,"
		        . " sr.series_date, sr.series_time, el.plugin_name, el.version,"
				. " el.exec_user, el.executed_at,"
		        . " es.study_instance_uid, es.series_instance_uid,"
		        . " MAX(lf.evaluation) as tp_max,"
		        . " MAX(fn.false_negative_num) as fn_max"
				. $sqlCond;
				
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($sqlParams);
		
		// count total number
		$params['totalNum'] = $stmt->rowCount();
		$params['maxPageNum'] = ($params['showing'] == "all") ? 1 : ceil($params['totalNum'] / $params['showing']);
		$params['startPageNum'] = max($params['pageNum'] - $PAGER_DELTA, 1);
		$params['endPageNum']   = min($params['pageNum'] + $PAGER_DELTA, $params['maxPageNum']);				
				
		$sqlStr .= " ORDER BY " . $orderColStr;
				
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
		//var_dump($sqlParams);
		
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
		$sqlStr = "SELECT evaluation, interrupt_flg FROM lesion_feedback WHERE exec_id=?"
				. " AND consensual_flg='f' AND entered_by=? ORDER BY lesion_id ASC";
		$stmtPersonalFB = $pdo->prepare($sqlStr);
		$stmtPersonalFB->bindParam(2, $_SESSION['userID']);

		// SQL statement to count the number of personal feedback
		$sqlStr  = "SELECT status FROM false_negative_count WHERE exec_id=? AND consensual_flg='f'"
		         . " AND entered_by=?";
		$stmtPersonalFN = $pdo->prepare($sqlStr);
		$stmtPersonalFN->bindParam(2, $_SESSION['userID']);
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
			     . " AND consensual_flg=? AND false_negative_num>0 AND status=2";

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
							$result['exec_user'],
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
					$colArr[] = CheckRegistStatusPersonalFB($stmtPersonalFB, $stmtPersonalFN, $result['exec_id']);	
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
					$colArr[] = CheckRegistStatusPersonalFB($stmtPersonalFB, $stmtPersonalFN, $result['exec_id']);	
				}							

				$tpColStr = "-";
				$fnColStr = "-";

				if($result['tp_max']>=1)
				{
					$stmtTP->bindValue(1, $result['exec_id']);
					$stmtTP->bindValue(2, 't', PDO::PARAM_BOOL);
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


