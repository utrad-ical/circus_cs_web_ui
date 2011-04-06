<?php
	session_start();

	include("../common.php");

	//--------------------------------------------------------------------------------------------------------
	// Import $_POST variables
	//--------------------------------------------------------------------------------------------------------
	$mode = (isset($_REQUEST['mode']) && ($_SESSION['ticket'] == $_REQUEST['ticket'])) ? $_REQUEST['mode'] : "";

	$params = array();
	$validator = new FormValidator();

	$validator->addRules(array(
		"oldTodayDisp" => array(
			"type" => "select",
			"options" => array('series', 'cad'),
			"default" => "series",
			"otherwise" => "series"),
		"newTodayDisp" => array(
			"type" => "select",
			"options" => array('series', 'cad')),
		"oldDarkroom" => array(
			"type" => "select",
			"options" => array('t', 'f'),
			"default" => "f",
			"otherwise" => "f"),
		"newDarkroom" => array(
			"type" => "select",
			"options" => array('t', 'f')),
		"oldAnonymized" => array(
			"type" => "select",
			"options" => array('t', 'f'),
			"default" => "f",
			"otherwise" => "f"),
		"newAnonymized" => array(
			"type" => "select",
			"options" => array('t', 'f')),
		"oldShowMissed" => array(
			"type" => "select",
			"options" => array('own', 'all', 'none'),
			"default" => 'none',
			"otherwise" => 'none'),
		"newShowMissed" => array(
			"type" => "select",
			"options" => array('own', 'all', 'none'))
		));


	if($validator->validate($_POST))
	{
		$params = $validator->output;
		$params['message'] = "";
	}
	else
	{
		$params = $validator->output;
		$params['message'] = implode(' ', $validator->errors);
	}

	$userID = $_SESSION['userID'];
	//--------------------------------------------------------------------------------------------------------

	try
	{
		$dstData = array('messaage'  => $params['message'],
						 'todayList' => ($params['newTodayDisp'] == 'cad') ? 'cad_log' : 'series_list');

		if($params['message'] == "")
		{
			$params['message'] = "No change";

			// Connect to SQL Server
			$pdo = DBConnector::getConnection();

			if($params['oldTodayDisp'] != $params['newTodayDisp']
			   || $params['oldDarkroomFlg'] != $params['newDarkroomFlg']
			   || $params['oldAnonymizeFlg'] != $params['newAnonymizeFlg']
			   || $params['oldShowMissed'] != $params['newShowMissed'])
			{
				$sqlStr = "UPDATE users SET today_disp=?, darkroom=?, anonymized=?, show_missed=?"
						. " WHERE user_id=?";
				$sqlParams = array($params['newTodayDisp'], $params['newDarkroom'],
				                   $params['newAnonymized'], $params['newShowMissed'],
								   $userID);

				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($sqlParams);

				if($stmt->errorCode() == '00000')
				{
					$dstData['message'] = 'Success';
					$_SESSION['todayDisp'] = $params['newTodayDisp'];
					$_SESSION['darmroom'] =($params['newDarkroom'] == 't') ? 1 : 0;
					$_SESSION['anonymized'] =($params['newAnonymized'] == 't') ? 1 : 0;
					$_SESSION['showMissed'] = $params['newShowMissed'];
				}
				else
				{
					//$tmp = $stmt->errorInfo();
					//echo $tmp[2];
					$dstData['message'] = "Fail to change page preference.";
				}
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
