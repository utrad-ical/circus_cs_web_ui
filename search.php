<?php
	session_cache_limiter('none');
	session_start();

	include ('common.php');
	include("auto_logout.php");
	
	//-----------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables and set $params array
	//-----------------------------------------------------------------------------------------------------------------
	$params = array('mode'         => "",
				    'filterSex'    => "all",
				    'personalFB'   => "all",
				    'consensualFB' => "all",
				    'filterTP'     => "all",
				    'filterFN'     => "all",
				    'showing'      => 10);
	//-----------------------------------------------------------------------------------------------------------------

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		include('set_cad_panel_params.php');
		$versionList = array("all");

		//----------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//----------------------------------------------------------------------------------------------------
		require_once('smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();

		$smarty->assign('params', $params);

		$smarty->assign('modalityList',    $modalityList);
		$smarty->assign('modalityMenuVal', $modalityMenuVal);	
		$smarty->assign('cadList',         $cadList);
		$smarty->assign('versionList',     $versionList);		

		$smarty->display('search.tpl');
		//----------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
    	var_dump($e->getMessage());
	}
	$pdo = null;
?>