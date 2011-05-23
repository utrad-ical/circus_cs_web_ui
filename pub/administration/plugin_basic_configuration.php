<?php
	include("../common.php");
	Auth::checkSession();
	Auth::purgeUnlessGranted(Auth::SERVER_OPERATION);

	$params = array('toTopDir' => "../");

	try
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		$sqlStr = "SELECT DISTINCT cs.modality FROM plugin_cad_master cm, plugin_cad_series cs"
				. " WHERE cm.plugin_id=cs.plugin_id AND cs.series_id=0 ORDER BY cs.modality ASC";

		$params['modalityList'] = DBConnector::query($sqlStr, null, 'ALL_COLUMN');

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
