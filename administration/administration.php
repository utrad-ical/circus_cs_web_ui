<?php

	session_start();
	include("../common.php");

	//-----------------------------------------------------------------------------------------------------------------
	// Auto logout
	//-----------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'] || $_SESSION['superUserFlg'] == 0)
	{
		header('location: ../index.php?mode=timeout');
	}
	else
	{
		$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	}
	//-----------------------------------------------------------------------------------------------------------------

	include("server_status_private.php");

	try
	{	
		$param = array('toTopDir' => "../");	
		$cadList = array();

		$userID = $_SESSION['userID'];

		$adminModeFlg = $mode = (isset($_REQUEST['adminModeFlg'])) ? $_REQUEST['adminModeFlg'] : 0;
		if($adminModeFlg == 1) $_SESSION['adminModeFlg'] = 1;

		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
		
		//-------------------------------------------------------------------------------------------------------------
		// Check server status
		//-------------------------------------------------------------------------------------------------------------
		$storageSvStatus  = ShowWindowsServiceStatus($DICOM_STORAGE_SERVICE);
		$jobManagerStatus = ShowWindowsServiceStatus($CAD_JOB_MANAGER_SERVICE);
		//-------------------------------------------------------------------------------------------------------------
		
		//-------------------------------------------------------------------------------------------------------------
		// Make one-time ticket
		//-------------------------------------------------------------------------------------------------------------
		$_SESSION['ticket'] = md5(uniqid().mt_rand());
		$param['ticket'] = htmlspecialchars($_SESSION['ticket'], ENT_QUOTES);
		//-------------------------------------------------------------------------------------------------------------
		
		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		require_once('../smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
			
		$smarty->assign('param',            $param);
		$smarty->assign('storageSvStatus',  $storageSvStatus);
		$smarty->assign('jobManagerStatus', $jobManagerStatus);
		
		$smarty->display('administration/administration.tpl');
		//--------------------------------------------------------------------------------------------------------------
	}	
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;
?>
