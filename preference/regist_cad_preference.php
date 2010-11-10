<?php
	session_start();

	include("../common.php");
	require_once('../class/validator.class.php');
	
	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables and validation
	//------------------------------------------------------------------------------------------------------------------
	$params = array();
	$validator = new FormValidator();

	$mode = (isset($_POST['mode'])) ? $_POST['mode'] : "";
	
	$validator->addRules(array(
		"cadName" => array(
			"type" => "cadname",
			"required" => true,
			"errorMes" => "[ERROR] 'CAD name' is invalid."),
		"version" => array(
			"type" => "version",
			"required" => true,
			"errorMes" => "[ERROR] 'Version' is invalid."),	
		"sortKey" => array(
			"type" => "select",
			"options" => array("0", "1", "2"),
			'oterwise' => "0"),
		"sortOrder" => array(
			"type" => "select",
			"options" => array("t", "f"),
			'oterwise' => "t"),
		"maxDispNum" => array(
			"type" => "string",
			"regex" => "/^(all|[\d]+)$/i"),
		"confidenceTh" => array(
			"type" => "numeric",
			"min" => 0),
		"dispConfidenceFlg" => array(
			"type" => "select",
			"options" => array("t", "f")),
		"dispCandidateTagFlg" => array(
			"type" => "select",
			"options" => array("t", "f"),
			'oterwise' => "f"),
		"preferenceFlg" => array(
			"type" => "select",
			"options" => array("1", "0"),
			'oterwise' => "0")
		));

	if($validator->validate($_POST))
	{
		$params = $validator->output;
		$params['errorMessage'] = "";
		if(preg_match('/^all/i', $params['maxDispNum']))  $params['maxDispNum'] = 0;
	}
	else
	{
		$params = $validator->output;
		$params['errorMessage'] = implode('<br/>', $validator->errors);
	}	
		
	$userID = $_SESSION['userID'];

	$dstData = array('preferenceFlg' => $params['preferenceFlg'],
			         'message'       => $params['errorMessage'],
					 'newMaxDispNum' => $params['maxDispNum']);

	//--------------------------------------------------------------------------------------------------------
	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		//----------------------------------------------------------------------------------------------------
		// regist or delete prefence
		//----------------------------------------------------------------------------------------------------
		$sqlParams = array();
		
		$sqlParams[] = $userID;
		$sqlParams[] = $params['cadName'];
		$sqlParams[] = $params['version'];
		
		if($mode == 'delete')
		{
			$sqlStr = "DELETE FROM cad_preference WHERE user_id=? AND cad_name=? AND version=?";
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);			
		
			if($stmt->rowCount() == 1)
			{
				$dstData['message'] = 'Succeeded!';
				$dstData['preferenceFlg'] = ($mode == 'delete') ? 0 : 1;
			}
			else
			{
				$dstData['message'] = 'Fail to delete the preference.';
			}
		}
		if($mode == 'update')	// restore default settings
		{
			$sqlStr = "DELETE FROM cad_preference WHERE user_id=? AND cad_name=? AND version=?";
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);			

			$sqlParams[] = $params['sortKey'];
			$sqlParams[] = $params['sortOrder'];
			$sqlParams[] = $params['maxDispNum'];
			$sqlParams[] = $params['confidenceTh'];		
			$sqlParams[] = $params['dispConfidenceFlg'];
			$sqlParams[] = $params['dispCandidateTagFlg'];
			
			$sqlStr = "INSERT INTO cad_preference(user_id, cad_name, version,"
					. " default_sort_key, default_sort_order, max_disp_num,"
					. " confidence_threshold, disp_confidence_flg, disp_candidate_tag_flg)"
					. " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);

			if($stmt->rowCount() == 1)
			{
				$dstData['message'] = 'Succeeded!';
				$dstData['preferenceFlg'] = ($mode == 'delete') ? 0 : 1;
			}
			else
			{
				$dstData['message'] = 'Fail to save the preference.';
			}
		}
		
		echo json_encode($dstData);
		
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;	
?>


