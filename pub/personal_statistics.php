<?php
	session_cache_limiter('none');
	session_start();

	include_once('common.php');
	include_once("auto_logout.php");

	include('statistics/get_cad_list_for_personal_stat.php');	// create $cadList

	//----------------------------------------------------------------------------------------------
	// Settings for Smarty
	//----------------------------------------------------------------------------------------------
	$smarty = new SmartyEx();

	$smarty->assign('cadList',       $cadList);
	$smarty->assign('versionDetail', explode('^', $cadList[0][1]));

	$smarty->display('personal_statistics.tpl');
	//----------------------------------------------------------------------------------------------

?>
