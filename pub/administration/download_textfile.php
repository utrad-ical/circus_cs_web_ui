<?php
include("../common.php");
Auth::checkSession();

//--------------------------------------------------------------------------
// Import $_REQUEST variables
//--------------------------------------------------------------------------
$filename = $_REQUEST['filename'];
//--------------------------------------------------------------------------

//--------------------------------------------------------------------------
// Check privilege
//--------------------------------------------------------------------------
if (!Auth::currentUser()->hasPrivilege(Auth::SERVER_OPERATION)) {
	forbidden();
}

if (preg_match('/^[A-Za-z0-9\_]+\.txt$/', $filename))
{
	header("Content-type: text/plain");
	header("Content-Disposition: attachment; filename=". $filename);
	readfile($LOG_DIR . $DIR_SEPARATOR . $filename);
}
else
{
	forbidden();
}

function forbidden() {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

