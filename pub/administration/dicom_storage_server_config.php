<?php
include("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(AUTH::SERVER_SETTINGS);

$params = array('message'  => "&nbsp;");
$configFileName = $CONF_DIR . $DIR_SEPARATOR . $CONFIG_DICOM_STORAGE;

//------------------------------------------------------------------------------
// Import $_REQUEST variables
//------------------------------------------------------------------------------
$mode = (isset($_REQUEST['mode']) && ($_SESSION['ticket'] == $_REQUEST['ticket'])) ? $_REQUEST['mode'] : "";
$newAeTitle              = (isset($_REQUEST['newAeTitle'])) ? $_REQUEST['newAeTitle']      : "";
$newPort                 = (isset($_REQUEST['newPort']))   ? $_REQUEST['newPort']   : "";
$newThumbnailFlg         = (isset($_REQUEST['newThumbnailFlg'])) ? $_REQUEST['newThumbnailFlg'] : "";
$newThumbnailSize        = (isset($_REQUEST['newThumbnailSize'])) ? $_REQUEST['newThumbnailSize']  : "";
$newCompressDicomFile    = (isset($_REQUEST['newCompressDicomFile']))  ? $_REQUEST['newCompressDicomFile']  : "";
$newOverwriteDicomFile   = (isset($_REQUEST['newOverwriteDicomFile'])) ? $_REQUEST['newOverwriteDicomFile']  : "";
$newOverwritePatientName = (isset($_REQUEST['newOverwritePatientName'])) ? $_REQUEST['newOverwritePatientName']  : "";
//------------------------------------------------------------------------------

$restartFlg = 0;

if($mode == "update")  // Update
{
	$fp = fopen($configFileName, 'r');

	if($fp == FALSE)
	{
		$params['message'] = 'Failed to open file: ' . $configFileName;
	}
	else
	{
		$dstStr = "";

		$isOverwriteDicomFileFlg = 0;

		while(!feof($fp))
		{
			$tmpArr = explode("=", fgets($fp));

			if(count($tmpArr) == 2)
			{
				switch(trim($tmpArr[0]))
				{
					case 'aeTitle':
						$tmpArr[1] = sprintf("%s\r\n", $newAeTitle);
						break;

					case 'port':
						$tmpArr[1] = sprintf("%s\r\n", $newPort);
						break;

					case 'thumbnailFlg':
						$tmpArr[1] = sprintf("%s\r\n", $newThumbnailFlg);
						break;

					case 'defaultThumbnailSize':
						$tmpArr[1] = sprintf("%s\r\n", $newThumbnailSize);
						break;

					case 'compressFlg':
					case 'compressDicomFile':
						$tmpArr[0] = 'compressDicomFile';
						$tmpArr[1] = sprintf("%s\r\n", $newCompressDicomFile);
						break;

					case 'overwriteDicomFile':
						$tmpArr[1] = sprintf("%s\r\n", $newOverwriteDicomFile);
						$isOverwriteDicomFileFlg = 1;
						break;

					case 'overwritePtNameFlg':
					case 'overwritePatientName':
						$tmpArr[0] = 'overwritePatientName';
						$tmpArr[1] = sprintf("%s\r\n", $newOverwritePatientName);
						break;
				}

				$dstStr .= trim($tmpArr[0]) . "=" . $tmpArr[1];
			}
			else
			{
				$dstStr .= $tmpArr[0];
			}
		}
		fclose($fp);

		// Corresponding to update 3.4-6 to 3.7
		if(!$isOverwriteDicomFileFlg)
		{
			$dstStr .= sprintf("\r\n\r\n");
			$dstStr .= sprintf("; Flag for overwrite DICOM file (1: allow to overwrite)\r\n");
			$dstStr .= sprintf("overwriteDicomFile=%s\r\n", $newOverwriteDicomFile);
		}

		file_put_contents($configFileName, $dstStr);

		$params['message'] = 'Configuration file was successfully updated. Please restart DICOM storage server!!';
		$restartFlg = 1;
	}
}
else if($mode == "restartSv")
{
	WinServiceControl::stopService($DICOM_STORAGE_SERVICE);
	WinServiceControl::startService($DICOM_STORAGE_SERVICE);

	$status = WinServiceControl::getStatus($DICOM_STORAGE_SERVICE);

	if($status != FALSE && $status['val'] == 1)
	{
		$params['message'] = 'DICOM storage server is restarted.';
	}
}

// Load configration file
$configData = parse_ini_file($configFileName);

//------------------------------------------------------------------------------
// Corresponding to update 3.4-6 to 3.7
//------------------------------------------------------------------------------
$isOverwriteDicomFileFlg = 0;

foreach($configData as $key => $value)
{
	switch($key)
	{
		case 'compressFlg':
			unset($configData['compressFlg']);
			$configData['compressDicomFile'] = $value;
			break;

		case 'overwritePtNameFlg':
			unset($configData['overwritePtNameFlg']);
			$configData['overwritePatientName'] = $value;
			break;

		case 'overwritePtNameFlg':
			$isOverwriteDicomFileFlg = 1;
			break;
	}
}

// Add 'overwriteDicomFile' (default: true)
if(!$isOverwriteDicomFileFlg)
{
	$configData['overwriteDicomFile'] = 1;
}
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// Make one-time ticket
//------------------------------------------------------------------------------
$_SESSION['ticket'] = md5(uniqid().mt_rand());
$configData['ticket'] = $_SESSION['ticket'];
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// Settings for Smarty
//------------------------------------------------------------------------------
$smarty = new SmartyEx();

$smarty->assign('params',     $params);
$smarty->assign('configData', $configData);
$smarty->assign('restartFlg', $restartFlg);

$smarty->display('administration/dicom_storage_server_config.tpl');
//------------------------------------------------------------------------------

