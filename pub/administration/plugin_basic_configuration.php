<?php

	session_start();
	include("../common.php");
	include("auto_logout_administration.php");

	$params = array('toTopDir' => "../");

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		$sqlStr = "SELECT DISTINCT cs.modality FROM cad_master cm, cad_series cs"
				. " WHERE cm.cad_name=cs.cad_name AND cm.version=cs.version"
				. " AND cs.series_id=1 ORDER BY cs.modality ASC";

		$params['modalityList'] = PdoQueryOne($pdo, $sqlStr, null, 'ALL_COLUMN');

		//-------------------------------------------------------------------------------------------------------------
		// Make one-time ticket
		//-------------------------------------------------------------------------------------------------------------
		$_SESSION['ticket'] = md5(uniqid().mt_rand());
		$params['ticket'] = htmlspecialchars($_SESSION['ticket'], ENT_QUOTES);
		//-------------------------------------------------------------------------------------------------------------

		//-------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//-------------------------------------------------------------------------------------------------------------
		$smarty = new SmartyEx();

		$smarty->assign('params', $params);

		$smarty->display('administration/plugin_basic_configuration.tpl');
		//-------------------------------------------------------------------------------------------------------------

	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
