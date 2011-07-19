<?php
	include("../common.php");
	Auth::checkSession();

	$mode = (isset($_REQUEST['mode']) && ($_SESSION['ticket'] == $_REQUEST['ticket'])) ? $_REQUEST['mode'] : "";
	$serviceName = (isset($_REQUEST['serviceName'])) ? $_REQUEST['serviceName'] : "";
	$ipAddress = (isset($_REQUEST['ipAddress'])) ? $_REQUEST['ipAddress'] : "127.0.0.1";

	if($mode == 'stop')
	{
		win32_stop_service($serviceName, $ipAddress);
	}
	elseif($mode == 'start')
	{
		win32_start_service($serviceName, $ipAddress);
	}

	$dstData = WinServiceControl::getStatus($serviceName, $ipAddress);
	
	echo json_encode($dstData);
?>