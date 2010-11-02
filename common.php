<?php
	
	$CIRCUS_CS_VERSION = "1.0 RC2";

	//-------------------------------------------------------------------------------------------------------
	// Define directories, commands, etc.
	//-------------------------------------------------------------------------------------------------------
	$DIR_SEPARATOR = '\\';
	$DIR_SEPARATOR_WEB = '/';
	
	$BASE_DIR          = "C:\\CIRCUS-CS";
	$APP_DIR           = $BASE_DIR . $DIR_SEPARATOR . "apps";
	$PLUGIN_DIR        = $BASE_DIR . $DIR_SEPARATOR . "plugins";
	$LOG_DIR           = $BASE_DIR . $DIR_SEPARATOR . "logs";
	$SUBDIR_JPEG       = "jpg_img";
	$SUBDIR_CAD_RESULT = "cad_results";
	
	$CONFIG_DICOM_STORAGE = "DICOMStorageServer.conf";
	
	$LOGIN_LOG               = "loginUser_log.txt";
	$LOGIN_ERROR_LOG         = "loginUser_errlog.txt";

	$cmdCreateThumbnail = sprintf("%s%s%s", $APP_DIR, $DIR_SEPARATOR, "createThumbnail.exe");
	$cmdForProcess  = sprintf("%s%s%s", $APP_DIR, $DIR_SEPARATOR, "Wrap_CreateProcess.exe");
	$cmdDcmToVolume = sprintf("%s%s%s", $APP_DIR, $DIR_SEPARATOR, "dcm2volume.exe");
	$cmdDcmToPng = sprintf("%s%s%s", $APP_DIR, $DIR_SEPARATOR, "dcm2png.exe");
	$cmdDcmCompress = sprintf("%s%s%s", $APP_DIR, $DIR_SEPARATOR, "CompressDcmFile.exe");

	$APACHE_BASE = "C:\\apache2";
	$APACHE_DOCUMENT_ROOT = $APACHE_BASE . "\\htdocs";
	$apacheAliasFname = $APACHE_BASE . "\\conf\extra\httpd-aliases.conf";
	//-------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------
	// Definition of windows service
	//-------------------------------------------------------------------------------------------------------
	$APACHE_SERVICE = "Apache2.2";
	$POSTGRESQL_SERVICE = "postgresql-8.4";
	$DICOM_STORAGE_SERVICE = "DICOM Storage Server";
	$CAD_JOB_MANAGER_SERVICE = "CAD Job Manager";
	//-------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------
	// Variables for database access
	//-------------------------------------------------------------------------------------------------------
	$dbName       = "circus_cs";
	$dbAccessUser = "circus";
	$dbAccessPass = "cad";
	
	$connStr = "host=localhost port=5432 dbname=" . $dbName
             . " user=" . $dbAccessUser . " password=" . $dbAccessPass;
	$connStrPDO = "pgsql:host=localhost port=5432 dbname=" . $dbName
                . " user=" . $dbAccessUser . " password=" . $dbAccessPass;
	//-------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------
	// Variables for conversion from DICOM to JPEG
	//-------------------------------------------------------------------------------------------------------
	$JPEG_QUALITY  = 100;
	$DEFAULT_WIDTH = 256;
	
	$RESCALE_RATIO_OF_SERIES_DETAIL = 1.25;
	//-------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------
	// Variables for modality list and CAD log
	//-------------------------------------------------------------------------------------------------------
	$modalityList = array('all', 'CT', 'MR', 'CR', 'DX', 'XA', 'NM', 'PT', 'US', 'RF', 'RG', 'MG', 'OT');

	$PATIENT_LIST_PER_PAGE = 10;
	$CAD_LOG_PER_PAGE = 20;
	$PAGER_DELTA = 3;
	//-------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------
	// Papameters for presentation of executed result of CAD software
	//-------------------------------------------------------------------------------------------------------
	$RESULT_COL_NUM = 3;
	//-------------------------------------------------------------------------------------------------------
	
	//-------------------------------------------------------------------------------------------------------
	// Count DICOM files in the selected directory (for Win)
	//-------------------------------------------------------------------------------------------------------
	function GetFileNumberOfDicomInPath($path)
	{
		$flist = scandir($path);
		$imgCnt = 0;
	
		for($i = 0; $i<count($flist); $i++)
		{
			if(preg_match('/\\.dcm$/i', $flist[$i]))  $imgCnt++;
		}
		
		return $imgCnt;
	}
	//-------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------
	// Retrieve DICOM file list in the selected path
	//-------------------------------------------------------------------------------------------------------
	function GetDicomFileListInPath($path)
	{
		$tmpFlist = scandir($path);
		
		$flist = array();
		
		for($i=0; $i<count($tmpFlist); $i++)
		{
			if(preg_match('/\\.dcm$/i', $tmpFlist[$i]))  $flist[] = $tmpFlist[$i];
		}
		
		return $flist;
	}
	//-------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------
	// Function for calculation age
	//-------------------------------------------------------------------------------------------------------
	function CalcAge($birthDate, $baseDate)
	{
		$birthDate = str_replace('-', '', $birthDate);
		$baseDate  = str_replace('-', '', $baseDate);
	
		if(!checkdate(substr($birthDate,4,2), substr($birthDate,6,2), substr($birthDate,0,4)))	return -1;
		if(!checkdate(substr($baseDate,4,2),  substr($baseDate,6,2),  substr($baseDate,0,4)))	return -1;
	
		if($baseDate < $birthDate)	return -1;
		else						return (int)(($baseDate - $birthDate) / 10000);
	}
	//-------------------------------------------------------------------------------------------------------
	
	//-------------------------------------------------------------------------------------------------------
	// Function for URL key and val pair
	//-------------------------------------------------------------------------------------------------------
	function UrlKeyValPair($key, $val)
	{
		return $key . "=" . urlencode($val);
	}
	//-------------------------------------------------------------------------------------------------------
	
?>