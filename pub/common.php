<?php

call_user_func(function() {
	$tmp = json_decode(file_get_contents(dirname(__DIR__) . '/version.json'), true);
	$GLOBALS['CIRCUS_CS_VERSION'] = $tmp['CIRCUS_CS_VERSION'];
	$GLOBALS['CIRCUS_REVISION'] = $tmp['REVISION'];
});

//------------------------------------------------------------------------------
// Define directories, commands, etc.
//------------------------------------------------------------------------------
$DIR_SEPARATOR = DIRECTORY_SEPARATOR;
$DIR_SEPARATOR_WEB = '/';

$BASE_DIR          = dirname(dirname(__DIR__));
$APP_DIR           = "$BASE_DIR/apps";
$CONF_DIR          = "$BASE_DIR/config";
$PLUGIN_DIR        = "$BASE_DIR/plugins";
$LOG_DIR           = "$BASE_DIR/logs";
$WEB_UI_ROOT       = "$BASE_DIR/web_ui";
$WEB_UI_LIBDIR     = "$WEB_UI_ROOT/app/lib";
$SUBDIR_CAD_RESULT = "cad_results";

$CONFIG_DICOM_STORAGE = "DICOMStorageServer.ini";

$LOGIN_LOG               = "loginUser_log.txt";
$LOGIN_ERROR_LOG         = "loginUser_errlog.txt";

$cmdCreateThumbnail = "$APP_DIR/createThumbnail.exe";
$cmdForProcess  = "$APP_DIR/Wrap_CreateProcess.exe";
$cmdDcmToVolume = "$APP_DIR/dcm2volume.exe";
$cmdDcmToPng = "$APP_DIR/dcm2png.exe";
$cmdDcmCompress = "$APP_DIR/CompressDcmFile.exe";
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// Include path definition and enable class autoloading
//------------------------------------------------------------------------------
set_include_path(get_include_path() . PATH_SEPARATOR . $WEB_UI_LIBDIR);

spl_autoload_register(function($class) {
	global $WEB_UI_LIBDIR;

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
		$file = "$WEB_UI_LIBDIR/$path$class.class.php";
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
// Variables for modality list and CAD log
//------------------------------------------------------------------------------
$modalityList = array('all', 'CT', 'MR', 'CR', 'DX', 'XA',
	'NM', 'PT', 'US', 'RF', 'RG', 'MG', 'OT');

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
 * Utility function to encode key/value pair into urlencoded format.
 * @deprecated
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
				$fname = "$dir/$object";
				if(filetype($fname) == "dir")
					DeleteDirRecursively($fname);
				else
					unlink($fname);
			}
		}
		reset($objects);
		rmdir($dir);
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
	$step = 0;
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

