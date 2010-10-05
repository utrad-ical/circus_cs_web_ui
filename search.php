<?php
	session_cache_limiter('none');
	session_start();

	include ('common.php');

	//-----------------------------------------------------------------------------------------------------------------
	// Auto logout (session timeout)
	//-----------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'])  header('location: index.php?mode=timeout');
	else	$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	//-----------------------------------------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables and set $param array
	//-----------------------------------------------------------------------------------------------------------------
	$param = array('mode'         => "",
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

		include('set_cad_panel_param.php');
		$versionList = array("all");

		//----------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//----------------------------------------------------------------------------------------------------
		require_once('smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();

		$smarty->assign('param', $param);

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