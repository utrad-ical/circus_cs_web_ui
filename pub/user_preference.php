<?php
	include_once('common.php');
	Auth::checkSession();

	try
	{
		$cadList = array();
		$userID = $_SESSION['userID'];

		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		//--------------------------------------------------------------------------------------------------------------
		// For page preference
		//--------------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT today_disp, darkroom, anonymized, show_missed FROM users WHERE user_id=?";
		$result = DBConnector::query($sqlStr, $userID, 'ARRAY_NUM');

		$oldTodayDisp  = $result[0];
		$oldDarkroom   = ($result[1]==true) ? "t" : "f";
		$oldAnonymized = ($result[2]==true || $_SESSION['anonymizeGroupFlg'] == 1) ? "t" : "f";
		$oldShowMissed = $result[3];
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// For CAD preference
		//--------------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT DISTINCT pm.plugin_name FROM plugin_master pm, plugin_cad_master cm"
				. " WHERE cm.plugin_id=pm.plugin_id AND result_type=1";
		$cadNameArray = DBConnector::query($sqlStr, null, 'ALL_NUM');

		$sqlStr = "SELECT DISTINCT version FROM plugin_master WHERE plugin_name=? ORDER BY version DESC";
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

		$smarty->assign('oldTodayDisp',  $oldTodayDisp);
		$smarty->assign('oldDarkroom',   $oldDarkroom);
		$smarty->assign('oldAnonymized', $oldAnonymized);
		$smarty->assign('oldShowMissed', $oldShowMissed);

		$smarty->assign('cadList',   $cadList);
		$smarty->assign('verDetail', explode('^', $cadList[0][1]));
		$smarty->assign('sortArr',   array(array("confidence", "Confidence"),
		                                   array("location_z", "Img. No."),
		                                   array("volume_size", "Volume")));
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
