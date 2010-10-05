<?php
	session_start();
	
	include("common.php");

	$data = array();

	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
		
		$param = array('toTopDir' => "./");

		// For plug-in block
		$stmt = $pdo->prepare("SELECT plugin_name, version, install_dt FROM plugin_master ORDER BY install_dt DESC");
		$stmt->execute();
		$pluginData = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		//----------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//----------------------------------------------------------------------------------------------------
		require_once('smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
		

		$smarty->assign('param',      $param);
		$smarty->assign('pluginData', $pluginData);
		
		$smarty->display('about.tpl');
		//----------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;

?>
	