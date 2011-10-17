<?php
	include("../common.php");
	Auth::checkSession();
	Auth::purgeUnlessGranted(Auth::SERVER_OPERATION);

	$params = array('toTopDir' => "../");

	try
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

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
		$smarty->display('administration/plugin_display_order.tpl');
		//-------------------------------------------------------------------------------------------------------------

	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
