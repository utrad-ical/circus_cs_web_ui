<?php
	include_once('common.php');
	Auth::checkSession();
	Auth::purgeUnlessGranted(Auth::LIST_SEARCH);

	try
	{

		$params = array('mode'         => "",
					    'filterSex'    => "all",
					    'personalFB'   => "all",
					    'consensualFB' => "all",
					    'filterTP'     => "all",
					    'filterFN'     => "all",
					    'showing'      => 10);

		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		include('set_cad_panel_params.php');
		$versionList = array("all");

		//----------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//----------------------------------------------------------------------------------------------------
		$smarty = new SmartyEx();

		$smarty->assign('params', $params);

		$smarty->assign('modalityList',    $modalityList);
		//$smarty->assign('modalityMenuVal', $modalityMenuVal);

		$smarty->assign('modalityCadList', $modalityCadList);
		$smarty->assign('cadList',         $cadList);
		$smarty->assign('versionList',     $versionList);

		$smarty->assign('modalityCadList', $modalityCadList);

		$smarty->display('search.tpl');
		//----------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
    	var_dump($e->getMessage());
	}
	$pdo = null;
