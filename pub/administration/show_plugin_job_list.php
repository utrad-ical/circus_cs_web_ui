<?php
	session_cache_limiter('nocache');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include("auto_logout_administration.php");

	try
	{
		// Connect to SQL Server
		$pdo = DB::getConnection();

		//--------------------------------------------------------------------------------------------------------
		// Create job list
		//--------------------------------------------------------------------------------------------------------
		include('make_job_list.php');
		//--------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------
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
