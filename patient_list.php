<?php
	session_cache_limiter('nocache');
	session_start();

	include_once("common.php");
	require_once('class/PersonalInfoScramble.class.php');	
	
	//-----------------------------------------------------------------------------------------------------------------
	// Auto logout (session timeout)
	//-----------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'])  header('location: index.php?mode=timeout');
	else	$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	//-----------------------------------------------------------------------------------------------------------------
	
	//-----------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables and set $param array
	//-----------------------------------------------------------------------------------------------------------------
	$param = array('filterPtID'   => (isset($_REQUEST['filterPtID'])) ? $_REQUEST['filterPtID'] : "",
				   'filterPtName' => (isset($_REQUEST['filterPtName'])) ? $_REQUEST['filtePtName'] : "",
				   'filterSex'    => (isset($_REQUEST['filterSex'])) ? $_REQUEST['filterSex'] : "all",
				   'orderCol'     => (isset($_REQUEST['orderCol'])) ? $_REQUEST['orderCol'] : "Patient ID",
				   'orderMode'    => ($_REQUEST['orderMode'] === 'DESC') ? 'DESC' : 'ASC',
				   'totalNum'     => (isset($_REQUEST['totalNum'])) ? $_REQUEST['totalNum'] : 0,
				   'pageNum'      => (isset($_REQUEST['pageNum'])) ? $_REQUEST['pageNum'] : 1,
				   'showing'      => (isset($_REQUEST['showing'])) ? $_REQUEST['showing'] : 1,
				   'startNum'     => 1,
				   'endNum'       => 10,
				   'maxPageNum'   => 1,
				   'pageAddress'  => 'patient_list.php?');
		   
	if($param['filterSex'] != "M" && $param['filterSex'] != "F")  $param['filterSex'] = "all";
	if($param['showing'] != "all" && $param['showing'] < 10)  $param['showing'] = 1;	

	//-----------------------------------------------------------------------------------------------------------------
	
	//-----------------------------------------------------------------------------------------------------------------
	// Retrieve sort column and order (Default: ascending order of patient ID)
	//-----------------------------------------------------------------------------------------------------------------
	$orderColStr = "";
	switch($param['orderCol'])
	{
		case "Name":		$orderColStr = 'patient_name ' . $param['orderMode'];  break;
		case "Sex":			$orderColStr = 'sex '          . $param['orderMode'];  break;
		case "Birth date":	$orderColStr = 'birth_date '   . $param['orderMode'];  break;
		default:			$orderColStr = 'patient_id '   . $param['orderMode'];  break;
	}
	//-----------------------------------------------------------------------------------------------------------------

	$colParam = array( array('colName' => 'Patient ID', 'align' => 'al-l'),
		               array('colName' => 'Name',       'align' => 'al-l'),
		               array('colName' => 'Sex',        'align' => ''),
		               array('colName' => 'Birth date', 'align' => ''),
		               array('colName' => 'Detail',     'align' => ''));

	$data = array();
	
	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		//-------------------------------------------------------------------------------------------------------------
		// Create WHERE statement of SQL
		//-------------------------------------------------------------------------------------------------------------
		$optionNum = 0;
		$condArr = array();
		
		$sqlCond = " WHERE ";
	
		if($param['filterPtID'] != "")
		{
			// Search by regular expression (test, case-insensitive)
			$sqlCond .= " patient_id ~* ?";
			array_push($condArr, $param['filterPtID']);
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
			
			// Search by regular expression (test, case-insensitive)
			$sqlCond .= " patient_name ~* ?";
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
			
			$sqlCond .= " sex = ?";
			array_push($condArr, $param['filterSex']);
			$param['pageAddress'] .= 'filterSex=' . $param['filterSex'];
			$optionNum++;
			
		}
		
		if(0<$optionNum) $param['pageAddress'] .= "&";
		$param['pageAddress'] .= 'orderCol=' . $param['orderCol'] . '&orderMode=' .  $param['orderMode']
		                      .  '&showing=' . $param['showing'];

		if($optionNum == 0)  $sqlCond = "";
		//-------------------------------------------------------------------------------------------------------------

		//-------------------------------------------------------------------------------------------------------------
		// count total number
		//-------------------------------------------------------------------------------------------------------------
		$stmt = $pdo->prepare("SELECT COUNT(*) FROM patient_list" . $sqlCond);
		$stmt->execute($condArr);

		$param['totalNum']     = $stmt->fetchColumn();
		$param['maxPageNum']   = ($param['showing'] == "all") ? 1 : ceil($param['totalNum'] / $param['showing']);
		$param['startPageNum'] = max($param['pageNum'] - $PAGER_DELTA, 1);
		$param['endPageNum']   = min($param['pageNum'] + $PAGER_DELTA, $param['maxPageNum']);		
		//-------------------------------------------------------------------------------------------------------------

		//-------------------------------------------------------------------------------------------------------------
		// Set $data array
		//-------------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT patient_id, patient_name, sex, birth_date FROM patient_list"
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
		
		$smarty->assign('param',    $param);
		$smarty->assign('colParam', $colParam);
		$smarty->assign('data',     $data);
	
		$smarty->display('patient_list.tpl');
		//-------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
