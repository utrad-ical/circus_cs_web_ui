<?php
	session_cache_limiter('none');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include_once("../auto_logout.php");	

	try
	{
		//--------------------------------------------------------------------------------------------------------------
		// Import $_REQUEST variables 
		//--------------------------------------------------------------------------------------------------------------	
		$params['message'] = '';
		$params['execID'] = $_REQUEST['execID'];
		$params['pluginType'] = (isset($_REQUEST['pluginType'])) ? $_REQUEST['pluginType'] : 1;
		//--------------------------------------------------------------------------------------------------------------	


		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
	
		$sqlStr = "SELECT tag_id, tag FROM executed_plugin_tag WHERE exec_id=? ORDER BY tag_id ASC";
		
		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindValue(1, $params['execID']);
		$stmt->execute();

		$tagArray = $stmt->fetchAll(PDO::FETCH_NUM);

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		require_once('../smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
		
		$smarty->assign('params',   $params);
		$smarty->assign('tagArray', $tagArray);
		
		$smarty->display('cad_results/edit_plugin_tag.tpl');
		//--------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;

?>
