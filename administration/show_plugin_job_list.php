<?php
	session_cache_limiter('nocache');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include("auto_logout_administration.php");	
	require_once('../class/PersonalInfoScramble.class.php');

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
	
		//--------------------------------------------------------------------------------------------------------
		// Create job list
		//--------------------------------------------------------------------------------------------------------
		include('make_job_list.php');
		//--------------------------------------------------------------------------------------------------------
	
		//--------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------
		require_once('../smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();

		$smarty->assign('params', $params);
	
		$smarty->assign('userID',   $_SESSION['userID']);
		$smarty->assign('jobList', $jobList);

		$smarty->display('administration/show_plugin_job_list.tpl');
		//--------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
