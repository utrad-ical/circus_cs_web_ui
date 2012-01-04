<?php
	require_once("../common.php");
	Auth::checkSession();

	if ($_REQUEST['open'] == 1) {
		$_SESSION['adminModeFlg'] = 1;
		exit;
	}

	//--------------------------------------------------------------------------
	// Group privilege check
	//--------------------------------------------------------------------------
	Auth::purgeUnlessGranted(Auth::SERVER_OPERATION);

	//--------------------------------------------------------------------------
	// Generate one-time ticket
	//--------------------------------------------------------------------------
	$_SESSION['ticket'] = md5(uniqid().mt_rand());
	$params['ticket'] = $_SESSION['ticket'];
	//--------------------------------------------------------------------------

	//--------------------------------------------------------------------------
	// Settings for Smarty
	//--------------------------------------------------------------------------
	$smarty = new SmartyEx();

	$smarty->assign('adminModeFlg', $_SESSION['adminModeFlg'] ? 1 : 0);

	$smarty->display('administration/administration.tpl');
	//--------------------------------------------------------------------------

