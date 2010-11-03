<?php
	session_cache_limiter('none');
	session_start();

	include_once('common.php');
	include_once("auto_logout.php");

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
				
		$resultCad = PdoQueryOne($pdo, $sqlStr, null, 'ALL_COLUMN');
				
		if(count($resultCad) > 0)
		{
			foreach($resultCad as $key => $item)
			{
				$cadList[$key][0] = $item;
			
				$sqlStr  = "SELECT DISTINCT version FROM executed_plugin_list WHERE plugin_name=?";
				$resultVersion = PdoQueryOne($pdo, $sqlStr, $item, 'ALL_COLUMN');

				$cadList[$key][1] = implode('^', $resultVersion);
			}
		}
	
		if($_SESSION['allStatFlg'])
		{
			$sqlStr = "SELECT DISTINCT entered_by FROM lesion_feedback WHERE consensual_flg='f' AND interrupt_flg='f'"
					. "ORDER BY entered_by ASC";
			$userList = PdoQueryOne($pdo, $sqlStr, null, 'ALL_COLUMN');
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

