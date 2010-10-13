<?php
	session_start();
	
	include("common.php");
	
	//-----------------------------------------------------------------------------------------------------------------
	// Auto logout (session timeout)
	//-----------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'])  header('location: index.php?mode=timeout');
	else	$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	//-----------------------------------------------------------------------------------------------------------------

	$data = array();

	try
	{	
		$cadList = array();
		$userID = $_SESSION['userID'];

		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
		
		//--------------------------------------------------------------------------------------------------------------
		// For page preference
		//--------------------------------------------------------------------------------------------------------------
		$stmt = $pdo->prepare("SELECT today_disp, darkroom_flg, latest_results FROM users WHERE user_id=?");
		$stmt->bindParam(1, $userID);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		$oldTodayDisp = $result[0];
		$oldDarkroomFlg = ($result[1]==true) ? "t" : "f";
		$oldLatestResults = $result[2];
		//--------------------------------------------------------------------------------------------------------------
		
		//--------------------------------------------------------------------------------------------------------------
		// For CAD preference
		//--------------------------------------------------------------------------------------------------------------
		$stmt = $pdo->prepare("SELECT DISTINCT cad_name FROM cad_master WHERE result_type=1");
		$stmt->execute();
			
		$sqlStr = "SELECT DISTINCT version FROM cad_master WHERE cad_name=? AND result_type=1";

		$stmtVersion = $pdo->prepare($sqlStr);

		while($result = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			$stmtVersion->bindParam(1, $result['cad_name']);	
			$stmtVersion->execute();
				 
			$tmpStr = "";
			$cnt = 0;
				
			while($resultVersion = $stmtVersion->fetch(PDO::FETCH_ASSOC))
			{
				if($cnt > 0) $tmpStr .= '^';
				$tmpStr .= $resultVersion['version'];
				$cnt++;
			}
			
			array_push($cadList, array($result['cad_name'], $tmpStr));
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
		require_once('./smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
			
		$smarty->assign('userID',    $userID);
		
		$smarty->assign('oldTodayDisp',      $oldTodayDisp);
		$smarty->assign('oldDarkroomFlg',   $oldDarkroomFlg);
		$smarty->assign('oldLatestResults', $oldLatestResults);
		
		$smarty->assign('cadList',   $cadList);
		$smarty->assign('verDetail', explode('^', $cadList[0][1]));
		$smarty->assign('sortStr',   array("Confidence", "Img. No.", "Volume"));
		$smarty->assign('ticket',    htmlspecialchars($_SESSION['ticket'], ENT_QUOTES));
		
		$smarty->display('user_preference.tpl');
		//----------------------------------------------------------------------------------------------------
	}	
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;
?>
