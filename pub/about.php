<?php
	session_start();

	include_once('common.php');
	include_once("auto_logout.php");

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		$params = array('toTopDir' => "./");
		$data = array();

		// For plug-in block
		$sqlStr = "SELECT plugin_name, version, install_dt FROM plugin_master ORDER BY install_dt DESC";
		$pluginData = PdoQueryOne($pdo, $sqlStr, null, 'ALL_ASSOC');

		//----------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//----------------------------------------------------------------------------------------------------
		$smarty = new SmartyEx();

		$smarty->assign('params',     $params);
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
