<?php
	session_cache_limiter('nocache');
	session_start();

	include_once('common.php');
	include_once("auto_logout.php");
	require_once('class/PersonalInfoScramble.class.php');
	
	//------------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables and set $params array	
	//------------------------------------------------------------------------------------------------------------------
	$params = array('mode'           => (isset($_REQUEST['mode'])) ? $_REQUEST['mode'] : "",
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
				   
	if($params['filterSex'] != "M" && $params['filterSex'] != "F")  $params['filterSex'] = "all";
	if($params['showing'] != "all" && $params['showing'] < 10)  $params['showing'] = 10;	
	//------------------------------------------------------------------------------------------------------------------

	$data = array();

	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
		
		//--------------------------------------------------------------------------------------------------------------
		// Create WHERE statement of SQL
		//--------------------------------------------------------------------------------------------------------------
		$sqlCondArray = array();
		$sqlParams = array();
		$sqlCond = "";
		$pageAddressParams = array();
		
		$sqlCond = " WHERE ";

		if($params['mode'] == 'patient')
		{
			$patientID = PinfoScramble::decrypt($params['encryptedPtID'], $_SESSION['key']);
			$params['filterPtID'] = ($_SESSION['anonymizeFlg'] == 1) ? $params['encryptedPtID'] : $patientID;
			
			$sqlCondArray[] = "pt.patient_id=?";
			$sqlParams[]    = $patientID;
			$pageAddressParams['mode'] = 'patient';
			$pageAddressParams['encryptedPtID'] = $params['encryptedPtID'];
			
			$stmt = $pdo->prepare("SELECT pt.patient_name, pt.sex FROM patient_list pt WHERE patient_id=?");
			$stmt->bindParam(1, $patientID);
			$stmt->execute();
			
			$result = $stmt->fetch(PDO::FETCH_NUM);
			$params['filterPtName'] = $result[0];
			$params['filterSex'] = $result[1];
			
			if($params['filterSex'] != "M" && $params['filterSex'] != "F")  $params['filterSex'] = "all";
		}
		else
		{
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
				// Search by regular expression 
				$sqlCondArray[] = "pt.patient_name~*?";
				$sqlParams[] = $params['filterPtName'];
				$pageAddressParams['filterPtName'] = $params['filterPtName'];
			}
		
			if($params['filterSex'] == "M" || $params['filterSex'] == "F")
			{
				$sqlCondArray[] = "pt.sex=?";
				$sqlParams[] = $params['filterSex'];
				$params['pageAddress'] .= 'filterSex=' . $params['filterSex'];
			}
		}
		
		if($params['stDateFrom'] != "" && $params['stDateTo'] != "" && $params['stDateFrom'] == $params['stDateTo'])
		{
			$sqlCondArray[] = "st.study_date=?";
			$sqlParams[] = $params['stDateFrom'];
			$pageAddressParams['stDateFrom'] = $params['stDateFrom'];
			$pageAddressParams['stDateTo'] = $params['stDateTo'];
		}
		else
		{
			if($params['stDateFrom'] != "")
			{
	 			$sqlCondArray[] = "?<=st.study_date";
				$sqlParams[] = $params['stDateFrom'];
				$pageAddressParams['stDateFrom'] = $params['stDateFrom'];
			}
	
			if($params['stDateTo'] != "")
			{
				if($params['stTimeTo'] != "")
				{
					$sqlCondArray[] = "(st.study_date<? OR (st.study_date=? AND st.study_time<=?))";
					$sqlParams[] = $params['stDateTo'];
					$sqlParams[] = $params['stDateTo'];
					$sqlParams[] = $params['stTimeTo'];
					$pageAddressParams['stDateTo'] = $params['stDateTo'];
					$pageAddressParams['stTimeTo'] = $params['stTimeTo'];
				}
				else
				{
					$sqlCondArray[] = "st.study_date<=?";
					$sqlParams[] = $params['stDateTo'];
					$pageAddressParams['stDateTo'] = $params['stDateTo'];
				}
			}
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
				$sqlCondArray[] .= " ?<=st.age";
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
			$sqlCondArray[] = "st.modality=?";
			$sqlParams[] = $params['filterModality'];
			$params['pageAddress'] .= 'filterModality=' . $params['filterModality'];
		}
		
		$sqlCondArray[] = "pt.patient_id=st.patient_id";
		$sqlCond = sprintf(" WHERE %s", implode(' AND ', $sqlCondArray));					  
		//--------------------------------------------------------------------------------------------------------------
		
		//--------------------------------------------------------------------------------------------------------------
		// Retrieve sort column and order (Default: ascending order of patient ID)
		//--------------------------------------------------------------------------------------------------------------
		$orderColStr = "";
	
		switch($params['orderCol'])
		{
			case "Patient ID":		$orderColStr = 'pt.patient_id '   . $params['orderMode'];  break;
			case "Name":			$orderColStr = 'pt.patient_name ' . $params['orderMode'];  break;
			case "Age":				$orderColStr = 'st.age '          . $params['orderMode'];  break;
			case "Sex":				$orderColStr = 'pt.sex '          . $params['orderMode'];  break;
			case "Modality":		$orderColStr = 'st.modality '     . $params['orderMode'];  break;
			case "Study ID":		$orderColStr = 'st.study_id" '    . $params['orderMode'];  break;
			default:	
				$orderColStr = 'st.study_date ' . $params['orderMode'] . ', st.study_time ' . $params['orderMode'];
				$params['orderCol']    = 'Study date';
				break;
		}

		$pageAddressParams['orderCol']  = $paramss['orderCol'];
		$pageAddressParams['orderMode'] = $paramss['orderMode'];
		$pageAddressParams['showing']   = $paramss['showing'];
		//--------------------------------------------------------------------------------------------------------------

		$params['pageAddress'] = implode('&', array_map(UrlKeyValPair, array_keys($pageAddressParams), array_values($pageAddressParams)));

		//--------------------------------------------------------------------------------------------------------------
		// count total number
		//--------------------------------------------------------------------------------------------------------------
		$stmt = $pdo->prepare("SELECT COUNT(*) FROM patient_list pt, study_list st " . $sqlCond);
		$stmt->execute($sqlParams);		
		
		$params['totalNum'] = $stmt->fetchColumn();
		$params['maxPageNum'] = ($params['showing'] == "all") ? 1 : ceil($params['totalNum'] / $params['showing']);
		$params['startPageNum'] = max($params['pageNum'] - $PAGER_DELTA, 1);
		$params['endPageNum']   = min($params['pageNum'] + $PAGER_DELTA, $params['maxPageNum']);		
		//--------------------------------------------------------------------------------------------------------------
		
		//--------------------------------------------------------------------------------------------------------------
		// Set $data array
		//--------------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT * FROM patient_list pt, study_list st" . $sqlCond . " ORDER BY " . $orderColStr;
				
		if($params['showing'] != "all")
		{
			$sqlStr .= " LIMIT ? OFFSET ?";
			$sqlParams[] = $params['showing'];
			$sqlParams[] = $params['showing'] * ($params['pageNum']-1);
		}

		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($sqlParams);
		
		$rowNum = $stmt->rowCount();
		$params['startNum'] = ($rowNum == 0) ? 0 : $params['showing'] * ($params['pageNum']-1) + 1;
		$params['endNum']   = ($rowNum == 0) ? 0 : $params['startNum'] + $rowNum - 1;	
		
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			$patientID = $result['patient_id'];
			$patientName = $result['patient_name'];
			
			if($_SESSION['anonymizeFlg'])
			{
				$patientID   = htmlspecialchars(PinfoScramble::encrypt($patientID, $_SESSION['key']), ENT_QUOTES);
				$patientName = PinfoScramble::scramblePtName();
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
			
		$smarty->assign('params', $params);
		$smarty->assign('data',   $data);
		
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