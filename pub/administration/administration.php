<?php
	require_once("../common.php");
	$params = array('toTopDir' => "../");
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
	// Settings for Smarty
	//--------------------------------------------------------------------------
	$smarty = new SmartyEx();

	$smarty->assign('params', $params);
	$smarty->assign('adminModeFlg', $_SESSION['adminModeFlg'] ? 1 : 0);
	$smarty->assign('storageServerName', $DICOM_STORAGE_SERVICE);
	$smarty->assign('managerServerName', $PLUGIN_JOB_MANAGER_SERVICE);

	$smarty->display('administration/administration.tpl');
	//--------------------------------------------------------------------------
?>
