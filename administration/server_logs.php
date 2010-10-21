<?php

	session_start();
	
	include("../common.php");
	include("auto_logout_administration.php");

	//------------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//------------------------------------------------------------------------------------------------------------------
	$mode = (isset($_REQUEST['mode'])) ? $_REQUEST['mode'] : "";
	$filename = $_REQUEST['filename'];
	//------------------------------------------------------------------------------------------------------------------

	if($mode == "clear")
	{
		unlink($LOG_DIR.$DIR_SEPARATOR.$filename);
		touch($LOG_DIR.$DIR_SEPARATOR.$filename);
	}
	
	$params = array('toTopDir' => "../");

	$flist = scandir($LOG_DIR);
	$numFiles = count($flist);
	
	$fileData = array();
	$cnt = 0; 

	for($i=0; $i<$numFiles; $i++)
	{
		if($flist[$i] != "." && $flist[$i] != "..") 
		{
			$fileData[$cnt][0] = $flist[$i];
			$fileData[$cnt][1] = date("Y-m-d H:i:s", filemtime($LOG_DIR.$DIR_SEPARATOR.$flist[$i]));
			$fileData[$cnt][2] = number_format(filesize($LOG_DIR.$DIR_SEPARATOR.$flist[$i]));
			$cnt++;
		}
	}

	//------------------------------------------------------------------------------------------------------------------
	// Settings for Smarty
	//------------------------------------------------------------------------------------------------------------------
	require_once('../smarty/SmartyEx.class.php');
	$smarty = new SmartyEx();
		
	$smarty->assign('params',   $params);
	$smarty->assign('fileData', $fileData);

	$smarty->display('administration/server_logs.tpl');
	//------------------------------------------------------------------------------------------------------------------	
	
?>

