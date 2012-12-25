<?php

/**
 * Attach some files to plugin result directory.
 */

require_once('../common.php');
Auth::checkSession();

header('text/plain');

// check that current user has access to job directory
$job_id = $_REQUEST['jobID'];
$cad_result = new CadResult($job_id);
$user = Auth::currentUser();
if (!$cad_result->checkCadResultAvailability($user->Group))
{
	echo "Authentication Error.";
	exit();
}

$dest = $cad_result->pathOfCadResult() . '/attachment/';
if (!file_exists($dest))
{
	mkdir($dest);
} else {
	if (!is_dir($dest))
	{
		echo "Error: attachment is not a directory";
		exit();
	}
}

if (is_uploaded_file($_FILES['upfile']['tmp_name'])) {
	if (move_uploaded_file($_FILES['upfile']['tmp_name'], $dest . $_FILES["upfile"]["name"])) {
		echo "OK";
	} else {
		echo "Internal Server Error.";
	}
} else {
	echo "File not uploaded. ";
}