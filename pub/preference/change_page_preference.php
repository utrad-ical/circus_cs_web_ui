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
		"oldDarkroomFlg" => array(
			"type" => "select",
			"options" => array('t', 'f'),
			"default" => "f",
			"otherwise" => "f"),
		"newDarkroomFlg" => array(
			"type" => "select",
			"options" => array('t', 'f')),
		"oldAnonymizeFlg" => array(
			"type" => "select",
			"options" => array('t', 'f'),
			"default" => "f",
			"otherwise" => "f"),
		"newAnonymizeFlg" => array(
			"type" => "select",
			"options" => array('t', 'f')),
		"oldLatestResults" => array(
			"type" => "select",
			"options" => array('own', 'all', 'none'),
			"default" => 'none',
			"otherwise" => 'none'),
		"newLatestResults" => array(
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
			// Connect to SQL Server
			$pdo = DB::getConnection();

			if($params['oldTodayDisp'] != $params['newTodayDisp']
			   || $params['oldDarkroomFlg'] != $params['newDarkroomFlg']
			   || $params['oldAnonymizeFlg'] != $params['newAnonymizeFlg']
			   || $params['oldLatestResults'] != $params['newLatestResults'])
			{
				$sqlStr = "UPDATE users SET today_disp=?, darkroom_flg=?, anonymize_flg=?, latest_results=?"
						. " WHERE user_id=?";
				$sqlParams = array($params['newTodayDisp'], $params['newDarkroomFlg'],
				                   $params['newAnonymizeFlg'], $params['newLatestResults'],
								   $userID);

				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($sqlParams);

				if($stmt->rowCount() == 1)
				{
					$dstData['message'] = 'Success';
					$_SESSION['todayDisp'] = $params['newTodayDisp'];
					$_SESSION['darmroomFlg'] =($params['newDarkroomFlg'] == 't') ? 1 : 0;
					$_SESSION['anonymizeFlg'] =($params['newAnonymizeFlg'] == 't') ? 1 : 0;
					$_SESSION['latestResults'] = $params['newLatestResults'];
				}
				else
				{
					//$tmp = $stmt->errorInfo();
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
