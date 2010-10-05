<?php

	include("../common.php");

	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//--------------------------------------------------------------------------------------------------------
	$filename = $_REQUEST['filename'];
	//--------------------------------------------------------------------------------------------------------

	header("Content-type: text/plain");
	header("Content-Disposition: attachment; filename=". $filename);
	readfile($LOG_DIR . $DIR_SEPARATOR . $filename);

?>
