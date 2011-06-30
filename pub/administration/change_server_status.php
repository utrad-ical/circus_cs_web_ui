<?php
	include("../common.php");
	Auth::checkSession();

	$mode = (isset($_REQUEST['mode']) && ($_SESSION['ticket'] == $_REQUEST['ticket'])) ? $_REQUEST['mode'] : "";
	$serviceName = (isset($_REQUEST['serviceName'])) ? $_REQUEST['serviceName'] : "";

	if($mode == 'stop')
	{
		win32_stop_service($serviceName);
	}
	elseif($mode == 'start')
	{
		win32_start_service($serviceName);
	}

	$hostName = '127.0.0.1';
	$dstData = WinServiceControl::getStatus($serviceName, $hostName);

	echo json_encode($dstData);
?>