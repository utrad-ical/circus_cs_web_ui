<?php
	//session_cache_limiter('nocache');
	session_start();

	include_once("common.php");
	include("auto_logout.php");
	require_once('class/PersonalInfoScramble.class.php');
	require_once('class/validator.class.php');

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		//-------------------------------------------------------------------------------------------------------------
		// Import $_GET variables and validation
		//-------------------------------------------------------------------------------------------------------------
		$params = array();

		PgValidator::$conn = $pdo;
		$validator = new FormValidator();
		$validator->registerValidator('pgregex', 'PgRegexValidator');
	
		$validator->addRules(array(
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
				"oterwise" => "all"),
			"orderCol" => array(
				"type" => "select",
				"options" => array('Name', 'Sex', 'Birth date', 'Patient ID'),
				"default"=> 'Patient ID',
				"oterwise" => 'Patient ID'),
			"orderMode" => array(
				"type" => "select",
				"options" => array('DESC', 'ASC'),
				"default" => 'ASC',
				"oterwise" => 'ASC'),
			"showing" => array(
				"type" => "select",
				"options" => array('10', '25', '50', 'all'),
				"default" => '10',
				"oterwise" => '10')
			));
		
		if($validator->validate($_GET))
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
			$params = $validator->output;
			$params['errorMessage'] = implode('<br/>', $validator->errors);
		}
		//--------------------------------------------------------------------------------------------------------------
		
		$data = array();

		if($params['errorMessage'] == "&nbsp;")
		{
			//----------------------------------------------------------------------------------------------------------
			// Create WHERE statement of SQL
			//----------------------------------------------------------------------------------------------------------
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
			$sqlStr = "SELECT COUNT(*) FROM patient_list" . $sqlCond;
			$params['totalNum']     = PdoQueryOne($pdo, $sqlStr, $sqlParams, 'SCALAR');
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

			$sqlStr = "SELECT tag FROM tag_list WHERE category=1 AND reference_id=?";
			$stmtTag = $pdo->prepare($sqlStr);
			
			while ($result = $stmt->fetch(PDO::FETCH_NUM))
			{
				$stmtTag->bindValue(1, $result[0]);
				$stmtTag->execute();
				$tagStr = implode(',', $stmtTag->fetchAll(PDO::FETCH_COLUMN));

				$encryptedPtID = PinfoScramble::encrypt($result[1], $_SESSION['key']);
	
				if($_SESSION['anonymizeFlg'] == 1)
				{
					$data[] = array($result[0], $encryptedPtID, 
					                PinfoScramble::scramblePtName(),
					                $result[3],
					                PinfoScramble::scrambleBirthDate(),
					                $encryptedPtID,
					                $tagStr);
				}
				else
				{
					$data[] = array($result[0],$result[1],$result[2],$result[3],$result[4],$encryptedPtID,$tagStr);
				}
			}
			//var_dump($data);
			//---------------------------------------------------------------------------------------------------------

			//---------------------------------------------------------------------------------------------------------
			// Generate one-time ticket to delete selected patient(s)
			//---------------------------------------------------------------------------------------------------------
			if($_SESSION['dataDeleteFlg'])
			{
				$_SESSION['ticket'] = md5(uniqid().mt_rand());
				$params['ticket'] = $_SESSION['ticket'];
			}
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
