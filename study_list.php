<?php
	session_cache_limiter('nocache');
	session_start();

	include("common.php");
	
	//------------------------------------------------------------------------------------------------------------------
	// Auto logout (session timeout)
	//------------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'])  header('location: index.php?mode=timeout');
	else	$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables and set $param array	
	//------------------------------------------------------------------------------------------------------------------
	$param = array('mode'           => (isset($_REQUEST['mode'])) ? $_REQUEST['mode'] : "",
	               'filterPtID'     => (isset($_REQUEST['filterPtID'])) ? $_REQUEST['filterPtID'] : "",
				   'filterPtName'   => (isset($_REQUEST['filterPtName'])) ? $_REQUEST['filterPtName'] : "",
				   'filterSex'      => (isset($_REQUEST['filterSex'])) ? $_REQUEST['filterSex'] : "all",
				   'filterAgeMin'   => (isset($_REQUEST['filterAgeMin'])) ? $_REQUEST['filterAgeMin'] : "",
				   'filterAgeMax'   => (isset($_REQUEST['filterAgeMax'])) ? $_REQUEST['filterAgeMax'] : "",
				   'filterModality' => (isset($_REQUEST['filterModality'])) ? $_REQUEST['filterModality'] : "all",
				   'stDateFrom'     => (isset($_REQUEST['stDateFrom'])) ? $_REQUEST['stDateFrom'] : "",
				   'stDateTo'       => (isset($_REQUEST['stDateTo'])) ? $_REQUEST['stDateTo'] : "",
				   //'stTimeFrom'     => (isset($_REQUEST['stTimeFrom'])) ? $_REQUEST['stTimeFrom'] : "00:00:00",
				   'stTimeTo'       => (isset($_REQUEST['stTimeTo'])) ? $_REQUEST['stTimeTo'] : "",
				   'encryptedPtID'  => (isset($_REQUEST['encryptedPtID']))   ? $_REQUEST['encryptedPtID'] : "",
				   'orderCol'       => (isset($_REQUEST['orderCol'])) ? $_REQUEST['orderCol'] : "Study date",
				   'orderMode'      => ($_REQUEST['orderMode'] === "ASC") ? "ASC" : "DESC",
				   'totalNum'       => (isset($_REQUEST['totalNum']))  ? $_REQUEST['totalNum'] : 0,
				   'pageNum'        => (isset($_REQUEST['pageNum']))   ? $_REQUEST['pageNum']  : 1,
				   'showing'        => (isset($_REQUEST['showing'])) ? $_REQUEST['showing'] : 10,
				   'startNum'       => 1,
				   'endNum'         => 10,
				   'maxPageNum'     => 1,
				   'pageAddress'    => 'study_list.php?');
				   
	if($param['filterSex'] != "M" && $param['filterSex'] != "F")  $param['filterSex'] = "all";
	if($param['showing'] != "all" && $param['showing'] < 10)  $param['showing'] = 10;	
	//------------------------------------------------------------------------------------------------------------------

	$orderColStr = "";
	//------------------------------------------------------------------------------------------------------------------
	// Retrieve sort column and order (Default: descending order of study date/time)
	//------------------------------------------------------------------------------------------------------------------
	switch($param['orderCol'])
	{
		case "Patient ID":		$orderColStr = 'pt.patient_id '   . $param['orderMode'];  break;
		case "Name":			$orderColStr = 'pt.patient_name ' . $param['orderMode'];  break;
		case "Age":				$orderColStr = 'st.age '          . $param['orderMode'];  break;
		case "Sex":				$orderColStr = 'pt.sex '          . $param['orderMode'];  break;
		case "Modality":		$orderColStr = 'st.modality '     . $param['orderMode'];  break;
		case "Study ID":		$orderColStr = 'st.study_id" '    . $param['orderMode'];  break;
		default:	
			$orderColStr = 'st.study_date ' . $param['orderMode'] . ', st.study_time ' . $param['orderMode'];
			$param['orderCol']    = 'Study date';
			break;
	}
	//------------------------------------------------------------------------------------------------------------------

	$colParam = array( array('colName' => 'Patient ID',    'align' => 'al-l'),
		               array('colName' => 'Name',          'align' => 'al-l'),
		               array('colName' => 'Age',           'align' => 'al-r'),
		               array('colName' => 'Sex',           'align' => ''),
		               array('colName' => 'Study ID',      'align' => 'al-r'),
		               array('colName' => 'Study date',    'align' => ''),
		               array('colName' => 'Study time',    'align' => ''),
		               array('colName' => 'Modality',      'align' => ''),
		               array('colName' => 'Accession No.', 'align' => ''),
		               array('colName' => 'Detail',        'align' => ''));

	$data = array();

	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
		
		//--------------------------------------------------------------------------------------------------------------
		// Create WHERE statement of SQL
		//--------------------------------------------------------------------------------------------------------------
		$optionNum = 0;
		$condArr = array();
		
		$sqlCond = " WHERE";

		if($param['mode'] == 'patient')
		{
			$patientID = PinfoDecrypter($param['encryptedPtID'], $_SESSION['key']);
			$param['filterPtID'] = ($_SESSION['anonymizeFlg'] == 1) ? $param['encryptedPtID'] : $patientID;
			
			$sqlCond .= " pt.patient_id=?";
			array_push($condArr, $patientID);
			$param['pageAddress'] .= 'mode=patient&encryptedPtID=' . $param['encryptedPtID'];
			$optionNum++;
			
			$stmt = $pdo->prepare("SELECT pt.patient_name, pt.sex FROM patient_list pt WHERE patient_id=?");
			$stmt->bindParam(1, $patientID);
			$stmt->execute();
			
			$result = $stmt->fetch(PDO::FETCH_NUM);
			$param['filterPtName'] = $result[0];
			$param['filterSex'] = $result[1];
			
			if($param['filterSex'] != "M" && $param['filterSex'] != "F")  $param['filterSex'] = "all";
		}
		else
		{
			if($param['filterPtID'] != "")
			{
				$patientID = $param['filterPtID'];
				if($_SESSION['anonymizeFlg'] == 1)  $patientID = PinfoDecrypter($param['filterPtID'], $_SESSION['key']);

				if(0<$optionNum)
				{
					$sqlCond .= " AND";
					$param['pageAddress'] .= "&";
				}
	
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

				// Search by regular expression 
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
			
				$sqlCond .= " pt.sex=?";
				array_push($condArr, $param['filterSex']);
				$param['pageAddress'] .= 'filterSex=' . $param['filterSex'];
				$optionNum++;
			}
		}
		
		if($param['stDateFrom'] != "" && $param['stDateTo'] != "" && $param['stDateFrom'] == $param['stDateTo'])
		{
			if(0<$optionNum)
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}
			
			$sqlCond .= " st.study_date=?";
			array_push($condArr, $param['stDateFrom']);
			$param['pageAddress'] .= 'stDateFrom=' . $param['stDateFrom'] . '&stDateTo=' . $param['stDateTo'];
			$optionNum++;
		}
		else
		{
			if($param['stDateFrom'] != "")
			{
				if(0<$optionNum)
				{
					$sqlCond .= " AND";
					$param['pageAddress'] .= "&";
				}
	 			$sqlCond .= " ?<=st.study_date";
				array_push($condArr, $param['stDateFrom']);
				$param['pageAddress'] .= 'stDateFrom=' . $param['stDateFrom'];
				$optionNum++;
			}
	
			if($param['stDateTo'] != "")
			{
				if(0<$optionNum)
				{
					$sqlCond .= " AND";
					$param['pageAddress'] .= "&";
				}
		
				if($param['stTimeTo'] != "")
				{
					$sqlCond .= " (st.study_date<? OR (st.study_date=? AND st.study_time<=?))";
					array_push($condArr, $param['stDateTo']);
					array_push($condArr, $param['stDateTo']);
					array_push($condArr, $param['stTimeTo']);
					$param['pageAddress'] .= 'stDateTo=' . $param['stDateTo'] . '&stTimeTo=' . $param['stTimeTo'];
				}
				else
				{
					$sqlCond .= " st.study_date<=?";
					array_push($condArr, $param['stDateTo']);
					$param['pageAddress'] .= 'stDateTo=' . $param['stDateTo'];
				}
				$optionNum++;
			}
		}
	
		if($param['filterAgeMin'] != "" && $param['filterAgeMax'] != "" && $param['filterAgeMin'] == $param['filterAgeMax'])
		{
			if(0<$optionNum)
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}		
		
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
			
			$sqlCond .= " st.modality=?";
			array_push($condArr, $param['filterModality']);
			$param['pageAddress'] .= 'filterModality=' . $param['filterModality'];
			$optionNum++;
		}
		
		if(0<$optionNum)
		{
			$sqlCond .= " AND";
			$param['pageAddress'] .= "&";
		}
		$sqlCond .= " pt.patient_id=st.patient_id";
		$param['pageAddress'] .= 'orderCol=' . $param['orderCol'] . '&orderMode=' .  $param['orderMode']
		                      .  '&showing=' . $param['showing'];
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// count total number
		//--------------------------------------------------------------------------------------------------------------
		$stmt = $pdo->prepare("SELECT COUNT(*) FROM patient_list pt, study_list st " . $sqlCond);
		$stmt->execute($condArr);		
		
		$param['totalNum'] = $stmt->fetchColumn();
		$param['maxPageNum'] = ($param['showing'] == "all") ? 1 : ceil($param['totalNum'] / $param['showing']);
		$param['startPageNum'] = max($param['pageNum'] - $PAGER_DELTA, 1);
		$param['endPageNum']   = min($param['pageNum'] + $PAGER_DELTA, $param['maxPageNum']);		
		//--------------------------------------------------------------------------------------------------------------
		
		//--------------------------------------------------------------------------------------------------------------
		// Set $data array
		//--------------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT * FROM patient_list pt, study_list st"
				. $sqlCond . " ORDER BY " . $orderColStr;
				
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
		
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			$patientID = $result['patient_id'];
			$patientName = $result['patient_name'];
			
			if($_SESSION['anonymizeFlg'])
			{
				$patientID   = htmlspecialchars(PinfoEncrypter($patientID, $_SESSION['key']), ENT_QUOTES);
				$patientName = ScramblePatientName();
			}

			array_push($data, array($result['study_instance_uid'],
			                        $patientID,
									$patientName,
									$result['age'],
									$result['sex'],
									$result['study_id'],
									$result['study_date'],
									$result['study_time'],
									$result['modality'],
									$result['accession_number']));
		}
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		require_once('smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
			
		$smarty->assign('param',    $param);
		$smarty->assign('colParam', $colParam);
		$smarty->assign('data',     $data);
		
		$smarty->assign('modalityList', $modalityList);
		
		$smarty->display('study_list.tpl');
		//--------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;	
?>