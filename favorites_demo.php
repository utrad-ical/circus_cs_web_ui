<?php
	session_cache_limiter('nocache');
	session_start();

	include_once("common.php");
	include("auto_logout.php");
	
	$data = array();

	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		//-------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//-------------------------------------------------------------------------------------------------------------
		require_once('smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
		
		$smarty->display('favorites_demo.tpl');
		//-------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
