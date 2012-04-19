<?php

$CIRCUS_CS_VERSION = "2.3";

//------------------------------------------------------------------------------
// Define directories, commands, etc.
//------------------------------------------------------------------------------
$DIR_SEPARATOR = DIRECTORY_SEPARATOR;
$DIR_SEPARATOR_WEB = '/';

$BASE_DIR          = "C:\\CIRCUS-CS";
$APP_DIR           = $BASE_DIR . $DIR_SEPARATOR . "apps";
$PLUGIN_DIR        = $BASE_DIR . $DIR_SEPARATOR . "plugins";
$LOG_DIR           = $BASE_DIR . $DIR_SEPARATOR . "logs";
$WEB_UI_ROOT       = $BASE_DIR . $DIR_SEPARATOR . "web_ui";
$WEB_UI_LIBDIR     = $WEB_UI_ROOT . $DIR_SEPARATOR . "app" . $DIR_SEPARATOR . "lib";
$SUBDIR_CAD_RESULT = "cad_results";

$CONFIG_DICOM_STORAGE = "DICOMStorageServer.ini";

$LOGIN_LOG               = "loginUser_log.txt";
$LOGIN_ERROR_LOG         = "loginUser_errlog.txt";

$cmdCreateThumbnail = sprintf("%s%s%s", $APP_DIR, $DIR_SEPARATOR, "createThumbnail.exe");
$cmdForProcess  = sprintf("%s%s%s", $APP_DIR, $DIR_SEPARATOR, "Wrap_CreateProcess.exe");
$cmdDcmToVolume = sprintf("%s%s%s", $APP_DIR, $DIR_SEPARATOR, "dcm2volume.exe");
$cmdDcmToPng = sprintf("%s%s%s", $APP_DIR, $DIR_SEPARATOR, "dcm2png.exe");
$cmdDcmCompress = sprintf("%s%s%s", $APP_DIR, $DIR_SEPARATOR, "CompressDcmFile.exe");
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// Include path definition and enable class autoloading
//------------------------------------------------------------------------------
set_include_path(get_include_path() . PATH_SEPARATOR . $WEB_UI_LIBDIR);

spl_autoload_register(function($class) {
	global $WEB_UI_LIBDIR, $DIR_SEPARATOR;

	// First use include path with no absolute path specified!
	@include_once("$class.class.php");
	if (class_exists($class)) return;

	$includes = array(
		array('feedbacklistener/', '/FeedbackListener$/'),
		array('displaypresenter/', '/DisplayPresenter$/'),
		array('models/', ''),
		array('api/', ''),
	);
	foreach($includes as $item)
	{
		$path = $item[0];
		$pattern = $item[1];
		if ($pattern && !preg_match($pattern, $class)) continue;
		$file = "$WEB_UI_LIBDIR$DIR_SEPARATOR$path$class.class.php";
		if (file_exists($file))
		{
			include_once($file);
			return;
		}
	}
});

//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// Definition of windows service
//------------------------------------------------------------------------------
$DICOM_STORAGE_SERVICE = "DICOM Storage Server";
$PLUGIN_JOB_MANAGER_SERVICE = "Plug-in Job Manager";
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// Variables for session time limit
//------------------------------------------------------------------------------
$SESSION_TIME_LIMIT = 3600;
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// Variables for modality list and CAD log
//------------------------------------------------------------------------------
$modalityList = array('all', 'CT', 'MR', 'CR', 'DX', 'XA', 'NM', 'PT', 'US', 'RF', 'RG', 'MG', 'OT');

$PATIENT_LIST_PER_PAGE = 10;
$CAD_LOG_PER_PAGE = 20;
$PAGER_DELTA = 3;
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// Reserved user ID / name by CIRCUS CS
//------------------------------------------------------------------------------
$DEFAULT_CAD_PREF_USER = 'preference_default';
$RESERVED_USER_LIST = array($DEFAULT_CAD_PREF_USER, 'server_service');
//------------------------------------------------------------------------------

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
	global $DIR_SEPARATOR;

	if(is_dir($dir))
	{
		$objects = scandir($dir);

		foreach ($objects as $object)
		{
			if($object != "." && $object != "..")
			{
				$fname = $dir . $DIR_SEPARATOR . $object;
				if(filetype($fname) == "dir")		DeleteDirRecursively($fname);
				else								unlink($fname);
			}
		}
		reset($objects);
		rmdir($dir);
	}
	return TRUE;
}

/**
 * Recursively copy a directory that is not empty.
 * @param string $srcDir The path of source directory.
 * @param string $dstDir The path of destination directory
 */
function CopyDirRecursively($srcDir, $dstDir)
{
	global $DIR_SEPARATOR;

	if(is_dir($srcDir))
	{
		if(!is_dir($dstDir))  mkdir($dstDir);

		$objects = scandir($srcDir);

		foreach( $objects as $file )
		{
			if( $file == "." || $file == ".." )  continue;

			if( is_dir($srcDir.$DIR_SEPARATOR.$file) )
			{
				CopyDirRecursively($srcDir.$DIR_SEPARATOR.$file, $dstDir.$DIR_SEPARATOR.$file);
			}
			else
			{
				copy($srcDir.$DIR_SEPARATOR.$file, $dstDir.$DIR_SEPARATOR.$file);
			}
		}
	}
	return TRUE;
}

/**
 * Finds the web root directory as relative path from the current directory.
 * @return string The relative path of the web root, like '../../' or '';
 * @throws Exception
 */
function relativeTopDir()
{
	// Find web root directory (where home.php exists) as a relative path
	do
	{
		$rp = str_repeat('../', $step);
		if (file_exists($rp . 'home.php'))
		break;
	} while ($step++ < 10);
	if ($step >= 10)
		throw new Exception('Web root cannot be resolved');
	return $rp;
}

