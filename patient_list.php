<?php
	//session_cache_limiter('nocache');
	session_start();

	include_once("common.php");
	include("auto_logout.php");
	require_once('class/PersonalInfoScramble.class.php');
	require_once('class/validator.class.php');
	
	//-----------------------------------------------------------------------------------------------------------------
	// Import $_GET variables (set $request array)
	//-----------------------------------------------------------------------------------------------------------------
	$request = array('filterPtID'   => (isset($_GET['filterPtID'])) ? $_GET['filterPtID'] : "",
				     'filterPtName' => (isset($_GET['filterPtName'])) ? $_GET['filterPtName'] : "",
				     'filterSex'    => (isset($_GET['filterSex'])) ? $_GET['filterSex'] : "all",
				     'orderCol'     => (isset($_GET['orderCol'])) ? $_GET['orderCol'] : "Patient ID",
				     'orderMode'    => (isset($_GET['orderMode']) && $_GET['orderMode'] === 'DESC') ? 'DESC' : 'ASC',
				     'showing'      => (isset($_GET['showing'])) ? $_GET['showing'] : 10);

	$params = array();
	//-----------------------------------------------------------------------------------------------------------------
				
	//-----------------------------------------------------------------------------------------------------------------
	// Validation
	//-----------------------------------------------------------------------------------------------------------------
	$validator = new FormValidator();

	$validator->addRules(array(
		"filterPtID" => array(
			"type" => "regexp",
			"errorMes" => "'Patient ID' is invalid."),
		"filterPtName" => array(
			"type" => "regexp",
			"errorMes" => "'Patient name' is invalid."),
		"filterSex" => array(
			"type" => "adjselect",
			"options" => array('M', 'F', 'all'),
			"default" => "all",
			"adjVal" => "all"),
		"orderCol" => array(
			"type" => "adjselect",
			"options" => array('Name', 'Sex', 'Birth date', 'Patient ID'),
			"default"=> 'Patient ID',
			"adjVal" => 'Patient ID'),
		"orderMode" => array(
			"type" => "adjselect",
			"options" => array('DESC', 'ASC'),
			"default" => 'ASC',
			"adjVal" => 'ASC'),
		"showing" => array(
			"type" => "adjselect",
			"options" => array('10', '25', '50', 'all'),
			"default" => '10',
			"adjVal" => '10')
		));
	
	if($validator->validate($request))
	{
		$params = $validator->output;
		$params['errorMessage'] = "&nbsp;";
		
		$params['pageNum']  = (ctype_digit($_GET['pageNum']) && $_GET['pageNum'] > 0) ? $_GET['pageNum'] : 1;
		$params['startNum'] = 0;
		$params['endNum'] = 0;
		$params['totalNum'] = 0;
		$params['maxPageNum'] = 1;
	}
	else
	{
		$params = $request;
		$params['errorMessage'] = implode('<br/>', $validator->errors);
	}
	//-----------------------------------------------------------------------------------------------------------------
	
	$data = array();
	
	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		if($params['errorMessage'] == "&nbsp;")
		{
			//-------------------------------------------------------------------------------------------------------------
			// Create WHERE statement of SQL
			//-------------------------------------------------------------------------------------------------------------
			$sqlCondArray = array();
			$sqlParams = array();
			$sqlCond = "";
			$addressParams = array();
		
			if($params['filterPtID'] != "")
			{
				// Search by regular expression (test, case-insensitive)
				$sqlCondArray[] = "patient_id ~* ?";
				$sqlParams[] = $params['filterPtID'];
				$addressParams['filterPtID'] = $params['filterPtID'];
			}	
		
			if($params['filterPtName'] != "")
			{
				// Search by regular expression (test, case-insensitive)
				$sqlCondArray[] = "patient_name ~* ?";
				$sqlParams[] = $params['filterPtName'];
				$addressParams['filterPtName'] = $params['filterPtName'];
			}
	
			if($params['filterSex'] == "M" || $params['filterSex'] == "F")
			{
				$sqlCondArray[] = "sex = ?";
				$sqlParams[] = $params['filterSex'];
				$addressParams['filterSex'] = $params['filterSex'];
			}
			
			if(count($sqlParams) > 0)  $sqlCond = sprintf(" WHERE %s", implode(' AND ', $sqlCondArray));
			//----------------------------------------------------------------------------------------------------------
			
			//----------------------------------------------------------------------------------------------------------
			// Retrieve sort column and order (Default: ascending order of patient ID)
			//----------------------------------------------------------------------------------------------------------
			$orderColStr = "";
			switch($params['orderCol'])
			{
				case "Name":		$orderColStr = 'patient_name ' . $params['orderMode'];  break;
				case "Sex":			$orderColStr = 'sex '          . $params['orderMode'];  break;
				case "Birth date":	$orderColStr = 'birth_date '   . $params['orderMode'];  break;
				default:			
					$orderColStr = 'patient_id ' . $params['orderMode']; 
					$params['orderCol'] = "Patient ID";
					break;
			}
	
			$addressParams['orderCol']  = $params['orderCol'];
			$addressParams['orderMode'] = $params['orderMode'];
			$addressParams['showing']   = $params['showing'];
			//----------------------------------------------------------------------------------------------------------
	
			$params['pageAddress'] = sprintf('patient_list.php?%s',
									 implode('&', array_map(UrlKeyValPair, array_keys($addressParams), array_values($addressParams))));
	
			//----------------------------------------------------------------------------------------------------------
			// count total number
			//----------------------------------------------------------------------------------------------------------
			$stmt = $pdo->prepare("SELECT COUNT(*) FROM patient_list" . $sqlCond);
			$stmt->execute($sqlParams);
	
			$params['totalNum']     = $stmt->fetchColumn();
			$params['maxPageNum']   = ($params['showing'] == "all") ? 1 : ceil($params['totalNum'] / $params['showing']);
			$params['startPageNum'] = max($params['pageNum'] - $PAGER_DELTA, 1);
			$params['endPageNum']   = min($params['pageNum'] + $PAGER_DELTA, $params['maxPageNum']);		
			//---------------------------------------------------------------------------------------------------------
	
			//---------------------------------------------------------------------------------------------------------
			// Set $data array
			//---------------------------------------------------------------------------------------------------------
			$sqlStr = "SELECT sid, patient_id, patient_name, sex, birth_date FROM patient_list"
					. $sqlCond . " ORDER BY " . $orderColStr;
					
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
			
			while ($result = $stmt->fetch(PDO::FETCH_NUM))
			{
				$encryptedPtID = PinfoScramble::encrypt($result[1], $_SESSION['key']);
	
				if($_SESSION['anonymizeFlg'] == 1)
				{
					$data[] = array($result[0], $encryptedPtID, 
					                PinfoScramble::scramblePtName(),
				                    $result[3],
					                PinfoScramble::scrambleBirthDate(),
				                    $encryptedPtID);
				}
				else
				{
					$data[] = array($result[0], $result[1], $result[2], $result[3], $result[4],$encryptedPtID);
				}
			}
			//var_dump($data);
			//---------------------------------------------------------------------------------------------------------
		}
		
		//-------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//-------------------------------------------------------------------------------------------------------------
		require_once('smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
		
		$smarty->assign('params', $params);
		$smarty->assign('data',   $data);
	
		$smarty->display('patient_list.tpl');
		//-------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
