<?php
	session_start();
	include("../common.php");
	
	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables
	//--------------------------------------------------------------------------------------------------------
	$mode = (isset($_REQUEST['mode']) && ($_SESSION['ticket'] == $_REQUEST['ticket'])) ? $_REQUEST['mode'] : "";
	$oldTodayDisp     = (isset($_REQUEST['oldTodayDisp'])) ? $_REQUEST['oldTodayDisp'] : "series";
	$newTodayDisp     = (isset($_REQUEST['newTodayDisp'])) ? $_REQUEST['newTodayDisp'] : "";
	$oldDarkroomFlg   = (isset($_REQUEST['oldDarkroomFlg'])) ? $_REQUEST['oldDarkroomFlg'] : "f";
	$newDarkroomFlg   = (isset($_REQUEST['newDarkroomFlg'])) ? $_REQUEST['newDarkroomFlg'] : "";
	$oldAnonymizeFlg  = (isset($_REQUEST['oldAnonymizeFlg'])) ? $_REQUEST['oldAnonymizeFlg'] : "f";
	$newAnonymizeFlg  = (isset($_REQUEST['newAnonymizeFlg'])) ? $_REQUEST['newAnonymizeFlg'] : "";
	$newLatestResults = (isset($_REQUEST['newLatestResults'])) ? $_REQUEST['newLatestResults'] : "";
	$userID = $_SESSION['userID'];
	//--------------------------------------------------------------------------------------------------------

	$message  = "";

	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
		
		$dstData = array('messaage'  => '',
						 'todayList' => ($newTodayDisp == 'cad') ? 'cad_log' : 'series_list');

		if($oldTodayDisp != $newTodayDisp || $oldDarkroomFlg != $newDarkroomFlg || $oldAnonymizeFlg != $newAnonymizeFlg
		   || $oldLatestResults != $newLatestResults)
		{
			$stmt = $pdo->prepare("UPDATE users SET today_disp=?, darkroom_flg=?, anonymize_flg=?, latest_results=? WHERE user_id=?");
			$stmt->execute(array($newTodayDisp, $newDarkroomFlg, $newAnonymizeFlg, $newLatestResults, $userID));

			if($stmt->rowCount() == 1)
			{
				$dstData['message'] = 'Success';
				
				'Page preference was successfully changed.';
				$oldTodayDisp = $newTodayDisp;
				$oldDarkroomFlg = $newDarkroomFlg;
				$_SESSION['todayDisp'] = $newTodayDisp;
				$_SESSION['darmroomFlg'] =($newDarkroomFlg == 't') ? 1 : 0;
				$_SESSION['anonymizeFlg'] =($newAnonymizeFlg == 't') ? 1 : 0;
				$_SESSION['latestResults'] = $newLatestResults;
			}
			else
			{
				$tmp = $stmt->errorInfo();
				$dstData['message'] = $tmp[2];
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