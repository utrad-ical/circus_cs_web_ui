<?php
	include("../common.php");
	Auth::checkSession();
	Auth::purgeUnlessGranted(Auth::SERVER_SETTINGS);

	$params = array('toTopDir' => "../");

	//-------------------------------------------------------------------------------------------------------------
	// Make one-time ticket
	//-------------------------------------------------------------------------------------------------------------
	$_SESSION['ticket'] = md5(uniqid().mt_rand());
	$params['ticket'] = $_SESSION['ticket'];
	//-------------------------------------------------------------------------------------------------------------

	//--------------------------------------------------------------------------------------------------------------
	// Settings for Smarty
	//--------------------------------------------------------------------------------------------------------------
	$smarty = new SmartyEx();

	$smarty->assign('params', $params);

	$smarty->display('administration/add_plugin.tpl');
	//--------------------------------------------------------------------------------------------------------------


