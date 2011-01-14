<?php
	session_cache_limiter('none');
	session_start();

	$params = array('toTopDir' => "../");
		
	include_once('../common.php');
	include_once("../auto_logout.php");

	include('get_cad_list_for_personal_stat.php');	// create $cadList

	//----------------------------------------------------------------------------------------------
	// Settings for Smarty
	//----------------------------------------------------------------------------------------------
	require_once('../smarty/SmartyEx.class.php');
	$smarty = new SmartyEx();
	
	$smarty->assign('params',        $params);
	$smarty->assign('cadList',       $cadList);
	$smarty->assign('versionDetail', explode('^', $cadList[0][1]));
	
	$smarty->display('time_for_feedback_entry.tpl');
	//----------------------------------------------------------------------------------------------
?>
