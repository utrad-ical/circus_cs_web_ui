<?php
	session_start();

	include_once('common.php');
	include_once("auto_logout.php");

	try
	{
		$cadList = array();
		$userID = $_SESSION['userID'];

		// Connect to SQL Server
		$pdo = DB::getConnection();

		//--------------------------------------------------------------------------------------------------------------
		// For page preference
		//--------------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT today_disp, darkroom_flg, anonymize_flg, latest_results FROM users WHERE user_id=?";
		$result = DB::query($sqlStr, $userID, 'ARRAY_NUM');

		$oldTodayDisp = $result[0];
		$oldDarkroomFlg = ($result[1]==true) ? "t" : "f";
		$oldAnonymizeFlg = ($result[2]==true || $_SESSION['anonymizeGroupFlg'] == 1) ? "t" : "f";
		$oldLatestResults = $result[3];
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// For CAD preference
		//--------------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT DISTINCT cad_name FROM cad_master WHERE result_type=1";
		$cadNameArray = DB::query($sqlStr, null, 'ALL_NUM');

		$sqlStr = "SELECT DISTINCT version FROM cad_master WHERE cad_name=? AND result_type=1 ORDER BY version DESC";
		$stmt = $pdo->prepare($sqlStr);

		foreach($cadNameArray as $item)
		{
			$stmt->bindParam(1, $item[0]);
			$stmt->execute();

			$tmpArray = array();

			while($resultVersion = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$tmpArray[] = $resultVersion['version'];
			}
			$cadList[] = array($item[0], implode('^', $tmpArray));
		}
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// Make one-time ticket
		//--------------------------------------------------------------------------------------------------------------
		$_SESSION['ticket'] = md5(uniqid().mt_rand());
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		$smarty = new SmartyEx();

		$smarty->assign('userID',    $userID);

		$smarty->assign('oldTodayDisp',     $oldTodayDisp);
		$smarty->assign('oldDarkroomFlg',   $oldDarkroomFlg);
		$smarty->assign('oldAnonymizeFlg',  $oldAnonymizeFlg);
		$smarty->assign('oldLatestResults', $oldLatestResults);

		$smarty->assign('cadList',   $cadList);
		$smarty->assign('verDetail', explode('^', $cadList[0][1]));
		$smarty->assign('sortStr',   array("Confidence", "Img. No.", "Volume"));
		$smarty->assign('ticket',    $_SESSION['ticket']);

		$smarty->display('user_preference.tpl');
		//----------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;
?>
