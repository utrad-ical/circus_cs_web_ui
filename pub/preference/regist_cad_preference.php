<?php
	session_start();

	include("../common.php");

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
			"options" => array("confidence", "location_z", "volume_size"),
			'oterwise' => "confidence"),
		"sortOrder" => array(
			"type" => "select",
			"options" => array("ASC", "DESC"),
			'oterwise' => "DESC"),
		"maxDispNum" => array(
			"type" => "string",
			"regex" => "/^(all|[\d]+)$/i"),
		"confidenceTh" => array(
			"type" => "numeric",
			"min" => 0),
		"dispConfidenceFlg" => array(
			"type" => "select",
			"options" => array("1", "0"),
			'otherwise' => "1"),
		"dispCandidateTagFlg" => array(
			"type" => "select",
			"options" => array("1", "0"),
			'oterwise' => "0"),
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
		$pdo = DBConnector::getConnection();

		//----------------------------------------------------------------------------------------------------
		// regist or delete prefence
		//----------------------------------------------------------------------------------------------------
		$sqlParams = array();

		$sqlParams[] = $userID;
		$sqlParams[] = $params['cadName'];
		$sqlParams[] = $params['version'];

		if($mode == 'delete')
		{
			$sqlStr = "DELETE FROM plugin_user_preference WHERE user_id=? AND plugin_name=? AND version=?";
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);

			if($stmt->errorCode() == '00000')
			{
				$dstData['message'] = 'Succeeded!';
				$dstData['preferenceFlg'] = 0;
			}
			else
			{
				$dstData['message'] = 'Fail to delete the preference.';
			}
		}
		if($mode == 'update')	// restore default settings
		{
			$sqlStr = "DELETE FROM plugin_user_preference WHERE user_id=? AND plugin_name=? AND version=?";
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);

			$keyStr = array('sortKey', 'sortOrder', 'maxDispNum', 'confidenceTh',
							'dispConfidenceFlg', 'dispCandidateTagFlg');

			$sqlStr = "INSERT INTO plugin_user_preference(user_id, plugin_name, version, key, value)"
					. " VALUES (?,?,?,?,?)";

			for($i = 0; $i < count($keyStr); $i++)
			{
				if($i > 0)
				{
					$sqlStr .= ",(?,?,?,?,?)";
					$sqlParams[] = $userID;
					$sqlParams[] = $params['cadName'];
					$sqlParams[] = $params['version'];
				}

				$sqlParams[] = $keyStr[$i];
				$sqlParams[] = $params[$keyStr[$i]];
			}

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);

			if($stmt->errorCode() == '00000')
			{
				$dstData['message'] = 'Succeeded!';
				$dstData['preferenceFlg'] = 1;
			}
			else
			{
				//$dstData['message'] = 'Fail to save the preference.';
				$errorMessage = $stmt->errorInfo();
				$dstData['message'] .= $errorMessage[2];

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
