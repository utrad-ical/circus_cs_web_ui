<?php
	//session_cache_limiter('nocache');
	session_start();

	include_once("common.php");
	include("auto_logout.php");
	require_once('class/PersonalInfoScramble.class.php');
	require_once('class/FormValidator.class.php');	
	
	//-----------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables and set $params array (with validation)
	//-----------------------------------------------------------------------------------------------------------------
	$params = array('filterPtID'   => "",
				    'filterPtName' => "",
				    'filterSex'    => "all",
				    'orderCol'     => "Patient ID",
				    'orderMode'    => 'ASC',
				    'totalNum'     => 0,
				    'pageNum'      => 1,
				    'showing'      => "all",
				    'startNum'     => 1,
				    'endNum'       => 10,
				    'maxPageNum'   => 1);
					
	
	if(isset($_GET['filterPtID']))
	{
		//if(validation条件を付加)
		//{
			$params['filterPtID'] = $_GET['filterPtID'];
		//}
		//else
		//{
		//	// エラー対策
		//}
	}
	
	if(isset($_GET['filterPtName']))
	{
		//if(validation条件を付加)
		//{
			$params['filterPtName'] = $_GET['filterPtName'];
		//}
		//else
		//{
		//	// エラー対策
		//}
	}
	
	if(isset($_GET['filterSex']) && FormValidator::validateSex($_GET['filterSex']))
	{	
		$params['filterSex'] = $_GET['filterSex'];
	}
	
	if(isset($_GET['orderCol']))
	{
		$params['orderCol'] = $_GET['orderCol'];
	}
	
	if($_GET['orderMode'] === 'DESC')
	{
		$params['orderMode'] = 'DESC';
	}
	
	if(isset($_GET['totalNum']) && ctype_digit($_GET['totalNum']))
	{
		$params['totalNum'] = ($_GET['totalNum'] >= 0) ? $_GET['totalNum'] : 0;
	}
	
	if(isset($_GET['pageNum']) && ctype_digit($_GET['pageNum']))
	{
		$params['pageNum'] = ($_GET['pageNum'] > 0) ? $_GET['pageNum'] : 1;
	}

	if(isset($_GET['showing']) && ctype_digit($_GET['showing']))
	{
		$params['showing'] = ($_GET['showing'] >= 10) ? $_GET['showing'] : 10;
	}
	//-----------------------------------------------------------------------------------------------------------------
	
	$data = array();
	
	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

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
		//--------------------------------------------------------------------------------------------------------------
		
		//--------------------------------------------------------------------------------------------------------------
		// Retrieve sort column and order (Default: ascending order of patient ID)
		//--------------------------------------------------------------------------------------------------------------
		$orderColStr = "";
		switch($params['orderCol'])
		{
			case "Name":		$orderColStr = 'patient_name ' . $params['orderMode'];  break;
			case "Sex":			$orderColStr = 'sex '          . $params['orderMode'];  break;
			case "Birth date":	$orderColStr = 'birth_date '   . $params['orderMode'];  break;
			default:			$orderColStr = 'patient_id '   . $params['orderMode'];  break;
		}

		$addressParams['orderCol']  = $params['orderCol'];
		$addressParams['orderMode'] = $params['orderMode'];
		$addressParams['showing']   = $params['showing'];
		//--------------------------------------------------------------------------------------------------------------

		$params['pageAddress'] = sprintf('patient_list.php?%s', implode('&', array_map(UrlKeyValPair, array_keys($addressParams), array_values($addressParams))));

		//--------------------------------------------------------------------------------------------------------------
		// count total number
		//--------------------------------------------------------------------------------------------------------------
		$stmt = $pdo->prepare("SELECT COUNT(*) FROM patient_list" . $sqlCond);
		$stmt->execute($sqlParams);

		$params['totalNum']     = $stmt->fetchColumn();
		$params['maxPageNum']   = ($params['showing'] == "all") ? 1 : ceil($params['totalNum'] / $params['showing']);
		$params['startPageNum'] = max($params['pageNum'] - $PAGER_DELTA, 1);
		$params['endPageNum']   = min($params['pageNum'] + $PAGER_DELTA, $params['maxPageNum']);		
		//-------------------------------------------------------------------------------------------------------------

		//-------------------------------------------------------------------------------------------------------------
		// Set $data array
		//-------------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT patient_id, patient_name, sex, birth_date FROM patient_list"
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
		//var_dump($condArr);
		
		$rowNum = $stmt->rowCount();
		$params['startNum'] = ($rowNum == 0) ? 0 : $params['showing'] * ($params['pageNum']-1) + 1;
		$params['endNum']   = ($rowNum == 0) ? 0 : $params['startNum'] + $rowNum - 1;	
		
		while ($result = $stmt->fetch(PDO::FETCH_NUM))
		{
			$encryptedPtID = PinfoScramble::encrypt($result[0], $_SESSION['key']);

			if($_SESSION['anonymizeFlg'] == 1)
			{
				array_push($data, array($encryptedPtID,
				                        PinfoScramble::scramblePtName(),
			                            $result[2],
				                        PinfoScramble::scrambleBirthDate(),
			                            htmlspecialchars($encryptedPtID, ENT_QUOTES)));
			}
			else
			{
				array_push($data, array($result[0], $result[1], $result[2], $result[3],
				                        htmlspecialchars($encryptedPtID, ENT_QUOTES)));
			}
		}
		//var_dump($data);
		//-------------------------------------------------------------------------------------------------------------
		
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
