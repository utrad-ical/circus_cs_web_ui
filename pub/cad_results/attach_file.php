<?php

/**
 * Attach files to plugin result directory.
 */

require_once('../common.php');
Auth::checkSession();

header('text/plain');

// The maximum file size is defined in php.ini file.
// Note that the size of the uploaded file is limited by both
// 'upload_max_filesize' (default: 2M as of PHP 5.3) and
// 'post_max_size' (default: 8M) ini variables.
// If the file size exceeds either of the two limits, the $_FILES superglobal
// will be empty.
$job_id = $_REQUEST['jobID'];
if (empty($job_id) || !is_uploaded_file($_FILES['upfile']['tmp_name']))
{
	$max = ini_get('upload_max_filesize');
	echo "File size was too large (max=$max), or malformed request. " .
		"Consult the administrator if you need to upload larger files.";
	exit;
}

// Check that current user has access to job directory.
$cad_result = new CadResult($job_id);
$user = Auth::currentUser();
if (!$cad_result->checkCadResultAvailability($user->Group))
{
	echo "Authentication failed. You do not have access to this CAD result.";
	exit();
}

$dest = $cad_result->pathOfCadResult() . '/attachment/';
if (!file_exists($dest))
{
	mkdir($dest);
} else {
	if (!is_dir($dest))
	{
		echo "Error: 'attachment' is not a directory";
		exit();
	}
}

if (move_uploaded_file($_FILES['upfile']['tmp_name'], $dest . $_FILES["upfile"]["name"])) {
	echo "OK";
} else {
	echo "Internal Server Error.";
}
