<?php
	include_once('common.php');
	Auth::checkSession();
	Auth::purgeUnlessGranted(Auth::LIST_SEARCH);

	//-----------------------------------------------------------------------------------------------------------------
	//
	//-----------------------------------------------------------------------------------------------------------------
	function CheckRegistStatusPersonalFB($stmtPersonalFB, $jobID)
	{
		$stmtPersonalFB->bindParam(1, $jobID);
		$stmtPersonalFB->execute();

		$ret = '-';

		if($stmtPersonalFB->rowCount() == 1)
		{
			if($stmtPersonalFB->fetchColumn() == 1)
			{
				$ret = 'Registered';
			}
			else
			{
				$ret = '<span style="font-weight:bold;color:red;">Incomplete</span>';
			}
		}

		return $ret;
	}
	//-----------------------------------------------------------------------------------------------------------------

	try
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		$userID = $_SESSION['userID'];

		//-----------------------------------------------------------------------------------------------------------------
		// Import $_GET variables and validation
		//-----------------------------------------------------------------------------------------------------------------
		$mode = (isset($_GET['mode']) && ($_GET['mode']=='today')) ? $_GET['mode'] : "";
		$params = array();

		$validator = new FormValidator();

		if($mode != "today")
		{
			$validator->addRules(array(
				"cadDateKind" => array(
					"type" => "str",
					"label" => 'CAD date',
					"default" => 'all'),
				"cadDateFrom" => array(
					"type" => "date",
					"label" => "CAD date"),
				"cadDateTo" => array(
					"type" => "date",
					"label" => "CAD date"),
				"cadTimeTo" => array(
					"type" => "time",
					"label" => "CAD time")
				));
		}

		$validator->addRules(array(
			"filterCadID" => array(
				"type" => "int",
				"min" => '0',
				"label" => "CAD ID"),
			"filterCAD" => array(
				"type" => "cadname",
				"default" => "all",
				"otherwise"=> "all",
				"label" => "CAD Name"),
			"filterVersion" => array(
				"type" => "version",
				"default" => "all",
				"otherwise" => "all",
				"label" => "Version"),
			"filterPtID" => array(
				"type" => "pgregex",
				"label" => "Patient ID"),
			"filterPtName" => array(
				"type" => "pgregex",
				"label" => "Patient name"),
			"filterSex" => array(
				"type" => "select",
				"options" => array('M', 'F', 'all'),
				"default" => "all",
				"otherwise" => "all"),
			"filterAgeMin" => array(
				"type" => "int",
				"min" => "0",
				"label" => "Age"),
			"filterAgeMax" => array(
				"type" => "int",
				"min" => "0",
				"label" => "Age"),
			"filterModality" => array(
				"type" => "select",
				"options" => $modalityList,
				"default" => "all",
				"otherwise" => "all"),
			"srDateKind" => array(
				"type" => "str",
				"label" => 'Series date',
				"default" => 'all'),
			"srDateFrom" => array(
				"type" => "date",
				"label" => "Series date"),
			"srDateTo" => array(
				"type" => "date",
				"label" => "Series date"),
			"srTimeTo" => array(
				"type" => "time",
				"label" => "Series time"),
			"filterTag"=> array(
				"type" => "pgregex",
				"label" => "Tag"),
			//"filterFBUser"=> array(
			//	"type" => "pgregex",
			//	"label" => "Entered by"),
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
				"options" => array("with", "without", "all"),
				"default" => "all",
				"otherwise"  => "all"),
			"filterFN" => array(
				"type" => "select",
				"options" => array("with", "without", "all"),
				"default" => "all",
				"otherwise"  => "all"),
			"orderCol" => array(
				"type" => "select",
				"options" => array("JobID","PatientID","Name","Age","Sex","Series","CAD","CADdate"),
				"default" => "CADdate",
				"otherwise" => "CADdate"),
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

		if($params['mode'] == 'today')
		{
			$params['showing'] = "all";  // for HIMEDIC

			$today = date("Y-m-d");
			$params['cadDateFrom'] = $today;
			$params['cadDateTo']   = $today;
			$params['srDateFrom'] = $today;		// for HIMEDIC
			$params['srDateTo']   = $today;		// for HIMEDIC

			$params['cadDateKind'] = 'today';
			$params['srDateKind'] = 'today';	// for HIMEDIC

			$sqlCondArray[] = "el.executed_at>=? AND el.executed_at<=?";
			$sqlParams[] = $params['cadDateFrom'] . ' 00:00:00';
			$sqlParams[] = $params['cadDateFrom'] . ' 23:59:59';
			$addressParams['mode'] .= 'today';
			$addressParams['cadDateFrom'] = $params['cadDateFrom'];
			$addressParams['cadDateTo']   = $params['cadDateTo'];
			$addressParams['srDateFrom']  = $params['srDateFrom'];
			$addressParams['srDateTo']    = $params['srDateTo'];
		}
		else if($params['cadDateFrom'] != "" && $params['cadDateTo'] != "" && $params['cadDateFrom'] == $params['cadDateTo'])
		{
			if($params['cadDateKind'] != 'all')  $addressParams['cadDateKind'] = $params['cadDateKind'];
			$sqlCondArray[] = "el.executed_at>=? AND el.executed_at<=?";
			$sqlParams[] = $params['cadDateFrom'] . ' 00:00:00';
			$sqlParams[] = $params['cadDateFrom'] . ' 23:59:59';
			$addressParams['cadDateFrom'] = $params['cadDateFrom'];
			$addressParams['cadDateTo'] = $params['cadDateTo'];
		}
		else
		{
			if($params['cadDateKind'] != 'all')  $addressParams['cadDateKind'] = $params['cadDateKind'];

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

		if($params['srDateKind'] != 'all')  $addressParams['srDateKind'] = $params['srDateKind'];

		if($params['srDateFrom'] != "" && $params['srDateTo'] != "" && $params['srDateFrom'] == $params['srDateTo'])
		{
			$sqlCondArray[] = "sr.series_date=?";
			$sqlParams[] = $params['srDateFrom'];
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
			$sqlCondArray[] = "el.job_id=?";
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
			$sqlCondArray[] = "pm.plugin_name=?";
			$sqlParams[] = $params['filterCAD'];
			$addressParams['filterCAD'] = $params['filterCAD'];
		}

		if($params['filterVersion'] != "all")
		{
			$sqlCondArray[] = "pm.version=?";
			$sqlParams[] = $params['filterVersion'];
			$addressParams['filterVersion'] = $params['filterVersion'];
		}

		if($params['filterTag'] != "")
		{
			$sqlCondArray[] = "el.job_id IN (SELECT DISTINCT reference_id FROM tag_list WHERE category=4 AND tag~*?)";
			$sqlParams[] = $params['filterTag'];
			$addressParams['filterTag'] = $params['filterTag'];
		}

		if($params['personalFB'] == "entered" || $params['personalFB'] == "notEntered")
		{
			$params['pageAddress'] .= 'personalFB=' . $params['personalFB'];

			$operator = ($params['personalFB'] == "entered") ? 'IN' : '<> ALL';

			$tmpCond .= " el.job_id " . $operator
					 .  " (SELECT DISTINCT job_id FROM feedback_list"
					 .  " WHERE is_consensual='f' AND status=1 AND entered_by='". $userID ."')";

//			if($params['personalFB'] == "entered")
//			{
//				if($params['filterFBUser'] != "")
//				{
//					if(strncmp($params['filterFBUser'],'PAIR"', 5) == 0)
//					{
//						$tmpStr = substr($params['filterFBUser'], 5);
//						$fbUserArr = array();
//
//						array_push($fbUserArr, strtok($tmpStr,'"'));
//
//						while($tmpStr2 = strtok('"'))
//						{
//							//echo $tmpStr2;
//							if($tmpStr2 != ")")  array_push($fbUserArr, $tmpStr2);
//						}
//
//						if(count($fbUserArr) == 1)
//						{
//							$tmpCond .= ' AND entered_by=?)';
//							$sqlParams[] = $fbUserArr[0];
//						}
//						else if(count($fbUserArr) >= 2)
//						{
//							$tmpCond .= " AND entered_by~*? AND job_id IN"
//									 .  " (SELECT DISTINCT job_id FROM lesion_classification"
//									 .  "  WHERE is_consensual='f' AND interrupted='f'"
//									 .  "  AND entered_by~*?))";
//
//							$sqlParams[] = $fbUserArr[0];
//							$sqlParams[] = $fbUserArr[1];
//						}
//					}
//					else
//					{
//						if($_SESSION[''] == "admin")
//						{
//							$tmpCond .= ')';
//						}
//						else
//						{
//							$tmpCond .= ' AND entered_by~*?)';
//							$sqlParams[] = $params['filterFBUser'];
//						}
//					}
//
//					$params['filterFBUser'] = htmlspecialchars($params['filterFBUser']);
//					$addressParams['filterFBUser'] = $params['filterFBUser'];
//				}
//				else
//				{
//					if($_SESSION['colorSet'] == 'admin')	// admin users check all personal feedback
//					{
//						$tmpCond .= ')';
//					}
//					else
//					{
//						$tmpCond .= ' AND entered_by=?)';
//						$sqlParams[] = $userID;
//					}
//				}
//			}
//			else
//			{
//				$tmpCond .= ")";
//			}
			$sqlCondArray[] = $tmpCond;
		}

		if($params['consensualFB'] == "entered" || $params['consensualFB'] == "notEntered")
		{
			$operator = ($params['consensualFB'] == "entered") ? 'IN' : '<> ALL';

			$tmpCond = "el.job_id " . $operator
					 . " (SELECT job_id FROM feedback_list WHERE is_consensual='t' AND status=1)";

			//if($params['filterTP'] == "all" && $params['filterFN'] == "all")
			//{
				$sqlCondArray[] = $tmpCond;
			//}
			$addressParams['consensualFB'] = $params['consensualFB'];
		}

		if($params['filterTP'] == "with" || $params['filterTP'] == "without")
		{
			$condition = ($params['filterTP'] == "with") ? '>0' : '<=0';

			$tmpCond = " el.job_id IN (SELECT DISTINCT fl.job_id FROM feedback_list fl, candidate_classification cc"
					 . " WHERE cc.fb_id=fl.fb_id AND fl.status=1";

			if($params['consensualFB'] == "entered")
			{
				$tmpCond .= " AND fl.is_consensual='t'";
			}
			else if($params['consensualFB'] == "notEntered")
			{
				$tmpCond .= " AND fl.is_consensual='f'";
			}
			$tmpCond .= " GROUP BY fl.job_id HAVING MAX(cc.evaluation)" . $condition . ")";

			$sqlCondArray[] = $tmpCond;
			$addressParams['filterTP'] = $params['filterTP'];
		}

		if($params['filterFN'] == "with" || $params['filterFN'] == "without")
		{
			$condition = ($params['filterFN'] == "with") ? '>=1' : '=0';

			$tmpCond = "el.job_id IN (SELECT DISTINCT job_id FROM feedback_list fl, fn_count fn"
					 . " WHERE fn.fb_id=fl.fb_id AND fl.status=1";

			if($params['consensualFB'] == "entered")
			{
				$tmpCond .= " AND fl.is_consensual='t'";
			}
			else if($params['consensualFB'] == "notEntered")
			{
				$tmpCond .= " AND fl.is_consensual='f'";
			}
			$tmpCond .= " GROUP BY job_id HAVING MAX(fn_num)" .  $condition . ")";

			$sqlCondArray[] = $tmpCond;
			$addressParams['filterFN'] = $params['filterFN'];
		}

		//var_dump($sqlCondArray);

		if(count($sqlCondArray) > 0)  $sqlCond .= sprintf(" AND %s", implode(' AND ', $sqlCondArray));
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// Retrieve mode of display order (default: ascending order of series number)
		//--------------------------------------------------------------------------------------------------------------
		$orderColStr = "";

		switch($params['orderCol'])
		{
			case "JobID":		$orderColStr = 'el.job_id '       . $params['orderMode'];  break;
			case "PatientID":	$orderColStr = 'pt.patient_id '   . $params['orderMode'];  break;
			case "Name":		$orderColStr = 'pt.patient_name ' . $params['orderMode'];  break;
			case "Age":			$orderColStr = 'st.age '          . $params['orderMode'];  break;
			case "Sex":			$orderColStr = 'pt.sex '          . $params['orderMode'];  break;
			case "Series":
					$orderColStr = 'sr.series_date '.$params['orderMode'].', sr.series_time '.$params['orderMode'];
					break;

			case "CAD":
					$orderColStr = 'pm.plugin_name '.$params['orderMode'].', pm.version '.$params['orderMode'];
					break;

			default:
					$params['orderCol'] = "CADdate";
					$orderColStr = 'el.executed_at '  . $params['orderMode'];
					break;
		}

		$addressParams['orderCol']  = $params['orderCol'];
		$addressParams['orderMode'] = $params['orderMode'];
		$addressParams['showing']   = $params['showing'];
		//--------------------------------------------------------------------------------------------------------------

		$params['pageAddress'] = sprintf('cad_log.php?%s',
		                         implode('&', array_map('UrlKeyValPair', array_keys($addressParams), array_values($addressParams))));
		$_SESSION['listAddress'] = $params['pageAddress'];

		//--------------------------------------------------------------------------------------------------------------
		// Set $data array
		//--------------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT el.job_id, pt.patient_id, pt.patient_name, st.age, pt.sex,"
		        . " sr.series_date, sr.series_time, pm.plugin_name, pm.version,"
				. " el.exec_user, el.executed_at"
				. " FROM patient_list pt, study_list st, series_list sr,"
				. " executed_plugin_list el, executed_series_list es, plugin_master pm"
				. " WHERE pm.type=1 AND el.status=" . Job::JOB_SUCCEEDED
				. " AND pm.plugin_id=el.plugin_id"
				. " AND es.job_id=el.job_id"
				. " AND es.volume_id=0 AND sr.sid=es.series_sid"
				. " AND st.study_instance_uid=sr.study_instance_uid"
				. " AND pt.patient_id=st.patient_id"
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
		$sqlStr  = "SELECT COUNT(DISTINCT entered_by) FROM feedback_list"
	             . " WHERE job_id=? AND is_consensual=? AND status=1";
		$stmtHeads = $pdo->prepare($sqlStr);

		// SQL statement to count the number of TP
		$sqlStr = "SELECT COUNT(*) FROM feedback_list fl, candidate_classification cc"
				. " WHERE fl.job_id=? AND fl.fb_id=cc.fb_id"
				. " AND fl.is_consensual=? AND fl.status=1 AND cc.evaluation>=1";
		$stmtTPCnt = $pdo->prepare($sqlStr);

		// SQL statement to check the status of personal feedback
		$sqlStr = "SELECT status FROM feedback_list"
				. " WHERE job_id=? AND is_consensual='f' AND entered_by=?";
		$stmtPersonalFB = $pdo->prepare($sqlStr);
		$stmtPersonalFB->bindParam(2, $_SESSION['userID']);
		//------------------------------------------------------------------------------------------

		//------------------------------------------------------------------------------------------
		// For cad log
		//------------------------------------------------------------------------------------------
		// SQL statement for count No. of TP
		$sqlStr = "SELECT COUNT(*) FROM feedback_list fl, candidate_classification cc"
				. " WHERE fl.job_id=? AND fl.fb_id=cc.fb_id"
				. " AND fl.is_consensual=? AND fl.status=1 AND cc.evaluation>=1";
		$stmtTP = $pdo->prepare($sqlStr);

		// SQL statement for count No. of FN
		$sqlStr = "SELECT fn.fn_num FROM feedback_list fl, fn_count fn"
				. " WHERE fl.job_id=? AND fl.fb_id=fn.fb_id"
				. " AND fl.is_consensual=? AND fn.fn_num>0 AND fl.status=1";
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
							$result['job_id']);

			$flgArray = array('f', 't');

			if($params['mode'] == 'today')
			{
				if($_SESSION['colorSet'] == "admin")
				{
					$stmtHeads->bindParam(1, $result['job_id']);
					$stmtTPCnt->bindParam(1, $result['job_id']);

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
					$colArr[] = CheckRegistStatusPersonalFB($stmtPersonalFB, $result['job_id']);
				}
			}
			else
			{
				if($_SESSION['colorSet'] == "admin")
				{
					$stmtHeads->bindParam(1, $result['job_id']);
					$stmtTPCnt->bindParam(1, $result['job_id']);

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
					$colArr[] = CheckRegistStatusPersonalFB($stmtPersonalFB, $result['job_id']);
				}

				$tpColStr = "-";
				$fnColStr = "-";

				//if($result['tp_max']>=1)
				{
					$stmtTP->bindValue(1, $result['job_id']);
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

				//if($result['fn_max']>=1)
				{
					$stmtFN->bindValue(1, $result['job_id']);
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

		if($params['filterCAD'] != 'all')
		{
			foreach($modalityCadList['all'][$params['filterCAD']] as $item)
			{
				$versionList[] = $item;
			}
		}

		//var_dump($data);
		//-------------------------------------------------------------------------------------------------------*/

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		$smarty = new SmartyEx();

		$smarty->assign('params',          $params);
		$smarty->assign('data',            $data);
		$smarty->assign('modalityList',    $modalityList);

		$smarty->assign('modalityCadList', $modalityCadList);
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

