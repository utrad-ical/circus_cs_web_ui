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

$ext = $cad_result->Plugin->presentation()->extensionByName('CadFileManagerExtension');
if (!$ext)
{
	echo 'This plug-in does not enable file downloads.';
	exit();
}
if (!$ext->checkUploadableGroups(Auth::currentUser()->Group))
{
	echo 'You do not have acess to file upload function.';
	exit();
}

$options = $ext->getParameter();

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

$file = $_FILES['upfile'];
$pat = $options['uploadFilesMatch'];
if (strlen($pat) > 0 && !preg_match($pat, $file['name']))
{
	echo "This file type cannot be uploaded.";
	exit();
}

$dest_file_path = $dest . $file["name"];

if (file_exists($dest_file_path) && !$options['overwrite'])
{
	echo "You can not overwrite existing files.";
	exit();
}

if (move_uploaded_file($file['tmp_name'], $dest_file_path)) {
	echo "OK";
} else {
	echo "Internal Server Error.";
}
