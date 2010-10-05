<?php
	session_start();

	include("../common.php");

	//-----------------------------------------------------------------------------------------------------------------
	// Auto logout
	//-----------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'] || $_SESSION['superUserFlg'] == 0)
	{
		header('location: ../index.php?mode=timeout');
	}
	else
	{
		$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	}
	//-----------------------------------------------------------------------------------------------------------------

	include("server_status_private.php");

	$mode = (isset($_REQUEST['mode']) && ($_SESSION['ticket'] == $_REQUEST['ticket'])) ? $_REQUEST['mode'] : "";
	$serviceName = (isset($_REQUEST['serviceName'])) ? $_REQUEST['serviceName'] : "";

	if($mode == 'stop')			win32_stop_service($serviceName);
	elseif($mode == 'start')	win32_start_service($serviceName);
	
	$dstData  = ShowWindowsServiceStatus($serviceName);

	echo json_encode($dstData);

?>