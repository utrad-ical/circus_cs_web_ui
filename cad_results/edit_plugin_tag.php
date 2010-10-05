<?php
	session_cache_limiter('none');
	session_start();

	include("../common.php");

	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//--------------------------------------------------------------------------------------------------------	
	$param = array('toTopDir'   => '../',
	               'message'    => '',
	               'execID'     => $_REQUEST['execID'],
	               'pluginType' => (isset($_REQUEST['pluginType'])) ? $_REQUEST['pluginType'] : 1);
	//--------------------------------------------------------------------------------------------------------	

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
	
		$sqlStr = "SELECT tag_id, tag FROM executed_plugin_tag WHERE exec_id=? ORDER BY tag_id ASC";
		
		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindValue(1, $param['execID']);
		$stmt->execute();

		$tagArray = $stmt->fetchAll(PDO::FETCH_NUM);

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		require_once('../smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
		
		$smarty->assign('param',    $param);
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
