<?php
	include_once('common.php');
	Auth::checkSession();

	include('statistics/get_cad_list_for_personal_stat.php');	// create $cadList

	//----------------------------------------------------------------------------------------------
	// Settings for Smarty
	//----------------------------------------------------------------------------------------------
	$smarty = new SmartyEx();

	$smarty->assign('cadList',       $cadList);
	$smarty->assign('versionDetail', explode('^', $cadList[0][1]));

	$smarty->display('personal_statistics.tpl');
	//----------------------------------------------------------------------------------------------


