<?php
	session_cache_limiter('nocache');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include("auto_logout_administration.php");

	try
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		//--------------------------------------------------------------------------------------------------------
		// Create job list
		//--------------------------------------------------------------------------------------------------------
		include('get_job_queue_list.php');
		//--------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------
		$smarty = new SmartyEx();

		$smarty->assign('params', $params);

		$smarty->assign('userID',  $_SESSION['userID']);
		$smarty->assign('jobList', $jobList);

		$smarty->display('administration/show_job_queue.tpl');
		//--------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
