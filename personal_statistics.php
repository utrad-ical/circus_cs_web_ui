<?php
	session_cache_limiter('none');
	session_start();

	include("common.php");

	//-----------------------------------------------------------------------------------------------------------------
	// Auto logout (session timeout)
	//-----------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'])  header('location: index.php?mode=timeout');
	else	$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	//-----------------------------------------------------------------------------------------------------------------

	try
	{	
		$cadList = array();
		$userList = array();
		$userList[0] = $_SESSION['userID'];
	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		$sqlStr = "SELECT DISTINCT el.plugin_name FROM executed_plugin_list el, cad_master cm"
				. " WHERE el.plugin_name=cm.cad_name AND el.version=cm.version AND cm.result_type=1"
				. " ORDER BY el.plugin_name ASC";
				
		$stmtCad = $pdo->prepare($sqlStr);
		$stmtCad->execute();
		$rowNum = $stmtCad->rowCount();
		$resultCad = $stmtCad->fetchAll(PDO::FETCH_NUM);
		
		if($rowNum > 0)
		{
			foreach($resultCad as $j => $itemCad)
			{
				$cadList[$j][0] = $itemCad[0];
			
				$sqlStr  = "SELECT DISTINCT version FROM executed_plugin_list WHERE plugin_name=?";

				$stmtVersion = $pdo->prepare($sqlStr);
				$stmtVersion->bindValue(1, $itemCad[0]);
				$stmtVersion->execute();

				$resultVersion = $stmtVersion->fetchAll(PDO::FETCH_NUM);
				
				$tmpStr = "";
			
				foreach($resultVersion as $i => $itemVersion)
				{
					if($i > 0) $tmpStr .= '^';
					$tmpStr .= $itemVersion[0];
				}
				
				$cadList[$j][1] = $tmpStr;
			}
		}
	
		if($_SESSION['allStatFlg'])
		{
			$sqlStr = "SELECT DISTINCT entered_by FROM lesion_feedback WHERE consensual_flg='f' AND interrupt_flg='f'"
					. "ORDER BY entered_by ASC";
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_NUM);
		
			foreach($result as $i => $item)
			{
				$userList[$i] = $item[0];
			}
		}	

		//----------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//----------------------------------------------------------------------------------------------------
		//エラーが発生した場合にエラー表示をする設定
		ini_set( 'display_errors', 1 );

		require_once('smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
		
		$smarty->assign('cadList',       $cadList);
		$smarty->assign('versionDetail', explode('^', $cadList[0][1]));
		$smarty->assign('userList',      $userList);
	
		$smarty->display('personal_statistics.tpl');
		//----------------------------------------------------------------------------------------------------		
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
	//--------------------------------------------------------------------------------------------------------
?>

