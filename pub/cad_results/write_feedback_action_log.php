<?php

	session_cache_limiter('none');
	session_start();

	include("../common.php");

	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables and validation
	//------------------------------------------------------------------------------------------------------------------
	$params = array();
	$validator = new FormValidator();

	$validator->addRules(array(
		"execID" => array(
			"type" => "int",
			"required" => true,
			"min" => 1,
			"errorMes" => "[ERROR] CAD ID is invalid."),
		"action" => array(
			"type" => "select",
			"required" => true,
			"options" => array("open", "register", "classify", "select")),
		"options" => array(
			"type" => "string",
			"required" => true)
		));

	if($validator->validate($_POST))
	{
		$params = $validator->output;
		$params['errorMessage'] = "";
	}
	else
	{
		$params = $validator->output;
		$params['errorMessage'] = implode('<br/>', $validator->errors);
	}

	$params['toTopDir'] = '../';
	$userID = $_SESSION['userID'];
	//------------------------------------------------------------------------------------------------------------------

	try
	{
		if($params['errorMessage']=="")
		{
			// Connect to SQL Server
			$pdo = new PDO($connStrPDO);

			$sqlStr = "INSERT INTO feedback_action_log (exec_id, user_id, act_time, action, options) VALUES (?,?,?,?,?)";
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindValue(1, $params['execID']);
			$stmt->bindValue(2, $userID);
			$stmt->bindValue(3, date('Y-m-d H:i:s'));
			$stmt->bindValue(4, $params['action']);
			$stmt->bindValue(5, $params['options']);
			$stmt->execute();

			//$tmp = $stmt->errorInfo();
			//echo $tmp[2];
		}
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;

?>