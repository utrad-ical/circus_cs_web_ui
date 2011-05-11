<?php

	$CIRCUS_CS_VERSION = "2.0 alpha";

	//-------------------------------------------------------------------------------------------------------
	// Define directories, commands, etc.
	//-------------------------------------------------------------------------------------------------------
	$DIR_SEPARATOR = '\\';
	$DIR_SEPARATOR_WEB = '/';

	$BASE_DIR          = "C:\\CIRCUS-CS";
	$APP_DIR           = $BASE_DIR . $DIR_SEPARATOR . "apps";
	$PLUGIN_DIR        = $BASE_DIR . $DIR_SEPARATOR . "plugins";
	$LOG_DIR           = $BASE_DIR . $DIR_SEPARATOR . "logs";
	$WEB_UI_ROOT       = $BASE_DIR . $DIR_SEPARATOR . "web_ui";
	$WEB_UI_LIBDIR     = $WEB_UI_ROOT . $DIR_SEPARATOR . "app" . $DIR_SEPARATOR . "lib";
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
	//-------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------
	// Include path definition and enable class autoloading
	//-------------------------------------------------------------------------------------------------------
	set_include_path(get_include_path() . PATH_SEPARATOR . $WEB_UI_LIBDIR);
	function __autoLoad($class)
	{
		if (!class_exists($class))
		{
			require_once($class . ".class.php");
		}
	}
	//-------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------
	// Definition of windows service
	//-------------------------------------------------------------------------------------------------------
	$APACHE_SERVICE = "Apache2.2";
	$POSTGRESQL_SERVICE = "postgresql-9.0";
	//$POSTGRESQL_SERVICE = "postgresql-8.4";			// for HIMEDIC
	$DICOM_STORAGE_SERVICE = "DICOM Storage Server";
	$CAD_JOB_MANAGER_SERVICE = "CAD Job Manager";
	//-------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------
	// Variables for session time limit
	//-------------------------------------------------------------------------------------------------------
	$SESSION_TIME_LIMIT = 3600;
	$SESSION_TIME_LIMIT_ADMIN_PAGES = 1800;
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
	// Status of plug-in execution
	//-------------------------------------------------------------------------------------------------------
	$PLUGIN_FAILED        = -1;
	$PLUGIN_NOT_ALLOCATED =  1;
	$PLUGIN_ALLOCATED     =  2;
	$PLUGIN_PROCESSING    =  3;
	$PLUGIN_SUCESSED      =  4;
	//-------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------
	// Papameters for presentation of executed result of CAD software
	//-------------------------------------------------------------------------------------------------------
	$RESULT_COL_NUM = 3;
	//-------------------------------------------------------------------------------------------------------

	//-------------------------------------------------------------------------------------------------------
	// Default user ID for Plug-in preference
	//-------------------------------------------------------------------------------------------------------
	$DEFAOULT_CAD_PREF_USER = 'preference_default';
	//-------------------------------------------------------------------------------------------------------

	/**
	 * Count DICOM files in the selected directory.
	 */
	function GetFileNumberOfDicomInPath($path)
	{
		$flist = scandir($path);
		$imgCnt = 0;

		for($i = 0; $i < count($flist); $i++)
		{
			if(preg_match('/\\.dcm$/i', $flist[$i]))  $imgCnt++;
		}

		return $imgCnt;
	}

	/**
	 * Retrieve DICOM file list in the selected path.
	 */
	function GetDicomFileListInPath($path)
	{
		$tmpFlist = scandir($path);

		$flist = array();

		for($i=0; $i < count($tmpFlist); $i++)
		{
			if(preg_match('/\\.dcm$/i', $tmpFlist[$i]))  $flist[] = $tmpFlist[$i];
		}

		return $flist;
	}

	/**
	 * Utility function to calculate age.
	 * @param string $birthDate The date of birth in 'YYYY-MM-DD' or 'YYYYMMDD'
	 * format (hyphens are optinal).
	 * @param string $baseDate The date at which we calculate age
	 * (typically today).
	 * @return string The calculated age. Return -1 if invalid date is passed.
	 */
	function CalcAge($birthDate, $baseDate)
	{
		$birthDate = str_replace('-', '', $birthDate);
		$baseDate  = str_replace('-', '', $baseDate);

		if(!checkdate(substr($birthDate,4,2), substr($birthDate,6,2), substr($birthDate,0,4)))	return -1;
		if(!checkdate(substr($baseDate,4,2),  substr($baseDate,6,2),  substr($baseDate,0,4)))	return -1;

		if($baseDate < $birthDate)	return -1;
		else						return (int)(($baseDate - $birthDate) / 10000);
	}

	/**
	 * Utility function to encode key/value pair into urlencoded format.
	 */
	function UrlKeyValPair($key, $val)
	{
		return $key . "=" . urlencode($val);
	}

	/**
	 * Recursively delete a directory that is not empty.
	 * @param string $dir The path to the directory to delete.
	 */
	function DeleteDirRecursively($dir)
	{
		if(is_dir($dir))
		{
			$objects = scandir($dir);

			foreach ($objects as $object)
			{
				if($object != "." && $object != "..")
				{
					$fname = $dir . "/" . $object;
					if(filetype($fname) == "dir")		DeleteDirRecursively($fname);
					else								unlink($fname);
				}
			}
			reset($objects);
			rmdir($dir);
		}
		return TRUE;
	}

?>
