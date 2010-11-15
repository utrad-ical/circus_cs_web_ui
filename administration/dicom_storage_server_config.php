<?php

	session_start();
	
	include("../common.php");
	include("auto_logout_administration.php");

	if($_SESSION['serverSettingsFlg']==1)
	{
		$params = array('toTopDir' => "../",
						'message'  => "&nbsp;");
		$confFname = $APP_DIR . $DIR_SEPARATOR . $CONFIG_DICOM_STORAGE;

		//--------------------------------------------------------------------------------------------------------------
		// Import $_REQUEST variables 
		//--------------------------------------------------------------------------------------------------------------
		$mode = (isset($_REQUEST['mode']) && ($_SESSION['ticket'] == $_REQUEST['ticket'])) ? $_REQUEST['mode'] : "";
		$newAeTitle      = (isset($_REQUEST['newAeTitle']))      ? $_REQUEST['newAeTitle']      : "";
		$newPortNumber   = (isset($_REQUEST['newPortNumber']))   ? $_REQUEST['newPortNumber']   : "";
		$newLogFname     = (isset($_REQUEST['newLogFname']))     ? $_REQUEST['newLogFname']     : "";
		$newErrLogFname  = (isset($_REQUEST['newErrLogFname']))  ? $_REQUEST['newErrLogFname']  : "";
		$newThumbnailFlg = (isset($_REQUEST['newThumbnailFlg'])) ? $_REQUEST['newThumbnailFlg'] : "";
		$newCompressFlg  = (isset($_REQUEST['newCompressFlg']))  ? $_REQUEST['newCompressFlg']  : "";
		
		$newDbConnectAddress = (isset($_REQUEST['newDbConnectAddress'])) ? $_REQUEST['newDbConnectAddress'] : "";
		$newDbConnectPort    = (isset($_REQUEST['newDbConnectPort']))    ? $_REQUEST['newDbConnectPort']    : "";
		$newDbName           = (isset($_REQUEST['newDbName']))           ? $_REQUEST['newDbName']           : "";
		$newDbUserName       = (isset($_REQUEST['newDbUserName']))       ? $_REQUEST['newDbUserName']       : "";
		$newDbPassword       = (isset($_REQUEST['newDbPassword']))       ? $_REQUEST['newDbPassword']       : "";
		//--------------------------------------------------------------------------------------------------------------

		$restartFlg = 0;

		if($mode == "update")
		{
			// Update 
			$fp = fopen($confFname, "w");
		
			if($fp != NULL)
			{
				fprintf($fp, "%s\r\n", $newAeTitle);
				fprintf($fp, "%s\r\n", $newPortNumber);
				fprintf($fp, "%s\r\n", $newLogFname);
				fprintf($fp, "%s\r\n", $newErrLogFname);
				fprintf($fp, "%s\r\n", $newThumbnailFlg);
				fprintf($fp, "%s\r\n", $newCompressFlg);
				
				fprintf($fp, "%s\r\n", $newDbConnectAddress);
				fprintf($fp, "%s\r\n", $newDbConnectPort);
				fprintf($fp, "%s\r\n", $newDbName);
				fprintf($fp, "%s\r\n", $newDbUserName);
				fprintf($fp, "%s\r\n", $newDbPassword);
				
			}
			else
			{
				$params['message'] = '<span style="color:#ff0000;">Fail to open file: ' . $confFname . '</span>';
			}
				
			fclose($fp);
				
			if($params['message'] == "&nbsp;")
			{
				$params['message'] = '<span style="color:#0000ff;">'
					  			   . 'Configuration file was successfully updated. Please restart DICOM storage server!!'
					               . '</span>';
				$restartFlg = 1;
			}
		}
		else if($mode == "restartSv")
		{
			win32_stop_service($DICOM_STORAGE_SERVICE);
			win32_start_service($DICOM_STORAGE_SERVICE);
	
			$status = win32_query_service_status($DICOM_STORAGE_SERVICE);
			
			if($status != FALSE)
			{
				if($status['CurrentState'] == WIN32_SERVICE_RUNNING
		   			|| $status['CurrentState'] == WIN32_SERVICE_START_PENDING)
				{
					$params['message'] = '<span style="color:#0000ff">DICOM storage server is restarted.</span>';
				}
			}
		}
		
		//----------------------------------------------------------------------------------------------------
		// Load configration file
		//----------------------------------------------------------------------------------------------------
		$configData = array();
		
		$fp = fopen($confFname, "r");
		
		if($fp != NULL)
		{
			$configData['aeTitle']          = rtrim(fgets($fp), "\r\n");
			$configData['portNumber']       = rtrim(fgets($fp), "\r\n");
			$configData['logFname']         = rtrim(fgets($fp), "\r\n");
			$configData['errLogFname']      = rtrim(fgets($fp), "\r\n");
			$configData['thumbnailFlg']     = rtrim(fgets($fp), "\r\n");
			$configData['compressFlg']      = rtrim(fgets($fp), "\r\n");
			$configData['dbConnectAddress'] = rtrim(fgets($fp), "\r\n");
			$configData['dbConnectPort']    = rtrim(fgets($fp), "\r\n");
			$configData['dbName']           = rtrim(fgets($fp), "\r\n");
			$configData['dbUserName']       = rtrim(fgets($fp), "\r\n");
			$configData['dbPassword']       = rtrim(fgets($fp), "\r\n");
		}
		fclose($fp);
		//----------------------------------------------------------------------------------------------------

		//----------------------------------------------------------------------------------------------------
		// Make one-time ticket
		//----------------------------------------------------------------------------------------------------
		$_SESSION['ticket'] = md5(uniqid().mt_rand());
		//----------------------------------------------------------------------------------------------------

		//----------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//----------------------------------------------------------------------------------------------------
		require_once('../smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();	

		$smarty->assign('params',     $params);
		$smarty->assign('configData', $configData);
		$smarty->assign('restartFlg', $restartFlg);
		
		$smarty->assign('ticket',     rawurlencode($_SESSION['ticket']));

		$smarty->display('administration/dicom_storage_server_config.tpl');
		//----------------------------------------------------------------------------------------------------
	
	} // end if($_SESSION['serverSettingFlg'])

?>

</center>
</body>
</html>
