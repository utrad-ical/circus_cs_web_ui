<?php
include("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(AUTH::SERVER_SETTINGS);


$params = array('toTopDir' => "../",
				'message'  => "&nbsp;");
$confFname = $APP_DIR . $DIR_SEPARATOR . $CONFIG_DICOM_STORAGE;

//--------------------------------------------------------------------------------------------------------------
// Import $_REQUEST variables
//--------------------------------------------------------------------------------------------------------------
$mode = (isset($_REQUEST['mode']) && ($_SESSION['ticket'] == $_REQUEST['ticket'])) ? $_REQUEST['mode'] : "";
$newAeTitle       = (isset($_REQUEST['newAeTitle'])) ? $_REQUEST['newAeTitle']      : "";
$newPort          = (isset($_REQUEST['newPort']))   ? $_REQUEST['newPort']   : "";
$newThumbnailFlg  = (isset($_REQUEST['newThumbnailFlg'])) ? $_REQUEST['newThumbnailFlg'] : "";
$newCompressFlg   = (isset($_REQUEST['newCompressFlg']))  ? $_REQUEST['newCompressFlg']  : "";
$newThumbnailSize = (isset($_REQUEST['newThumbnailSize'])) ? $_REQUEST['newThumbnailSize']  : "";
//--------------------------------------------------------------------------------------------------------------

$restartFlg = 0;

if($mode == "update")  // Update
{
	$fp = fopen($confFname, 'r');
		
	if($fp == FALSE)
	{
		$params['message'] = 'Fail to open file: ' . $confFname;
	}
	else
	{
		$dstStr = "";
		
		while(!feof($fp))
		{
			$tmpArr = explode("=", fgets($fp));
			
			if(count($tmpArr) == 2)
			{
				switch(trim($tmpArr[0]))
				{
					case 'aeTitle':  
						$tmpArr[1] = sprintf(" %s\r\n", $newAeTitle);
						break;
						
					case 'port':
						$tmpArr[1] = sprintf(" %s\r\n", $newPort);
						break;
					
					case 'thumbnailFlg':
						$tmpArr[1] = sprintf(" %s\r\n", $newThumbnailFlg);
						break;
						
					case 'compressFlg':
						$tmpArr[1] = sprintf(" %s\r\n", $newCompressFlg);
						break;
						
					case 'defaultThumbnailSize':
						$tmpArr[1] = sprintf(" %s\r\n", $newThumbnailSize);
						break;
				}	

				$dstStr .= $tmpArr[0] . "=" . $tmpArr[1];
			}
			else
			{
				$dstStr .= $tmpArr[0];
			}
		}
		fclose($fp);
		
		file_put_contents($confFname, $dstStr);

		$params['message'] = 'Configuration file was successfully updated. Please restart DICOM storage server!!';
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
			$params['message'] = 'DICOM storage server is restarted.';
		}
	}
}

// Load configration file
$configData = parse_ini_file($confFname);

//----------------------------------------------------------------------------------------------------
// Make one-time ticket
//----------------------------------------------------------------------------------------------------
$_SESSION['ticket'] = md5(uniqid().mt_rand());
$configData['ticket'] = $_SESSION['ticket'];
//----------------------------------------------------------------------------------------------------

//----------------------------------------------------------------------------------------------------
// Settings for Smarty
//----------------------------------------------------------------------------------------------------
$smarty = new SmartyEx();

$smarty->assign('params',     $params);
$smarty->assign('configData', $configData);
$smarty->assign('restartFlg', $restartFlg);

$smarty->display('administration/dicom_storage_server_config.tpl');
//----------------------------------------------------------------------------------------------------

?>
