<?php

	session_start();
	include("../common.php");
	include("auto_logout_administration.php");
	include("server_status_private.php");

	$params = array('toTopDir' => "../");
	$cadList = array();

	$userID = $_SESSION['userID'];

	$adminModeFlg = $mode = (isset($_REQUEST['adminModeFlg'])) ? $_REQUEST['adminModeFlg'] : 0;
	if($adminModeFlg == 1) $_SESSION['adminModeFlg'] = 1;

	// Connect to SQL Server
	$pdo = DBConnector::getConnection();

	//-------------------------------------------------------------------------------------------------------------
	// Check server status
	//-------------------------------------------------------------------------------------------------------------
	$storageSvStatus  = ShowWindowsServiceStatus($DICOM_STORAGE_SERVICE);
	$jobManagerStatus = ShowWindowsServiceStatus($PLUGIN_JOB_MANAGER_SERVICE);
	//-------------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------------
	// Generate one-time ticket
	//-------------------------------------------------------------------------------------------------------------
	$_SESSION['ticket'] = md5(uniqid().mt_rand());
	$params['ticket'] = $_SESSION['ticket'];
	//-------------------------------------------------------------------------------------------------------------

	//--------------------------------------------------------------------------------------------------------------
	// Settings for Smarty
	//--------------------------------------------------------------------------------------------------------------
	$smarty = new SmartyEx();

	$smarty->assign('params',           $params);
	$smarty->assign('storageSvStatus',  $storageSvStatus);
	$smarty->assign('jobManagerStatus', $jobManagerStatus);

	$smarty->display('administration/administration.tpl');
	//--------------------------------------------------------------------------------------------------------------

?>
