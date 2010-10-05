<?

	header('Content-type: application/zip');
	header('"Content-Disposition: attachment; filename="' . basename($_REQUEST['dlFileNameWeb']) . '"');
	header("Content-Transfer-Encoding: binary");
	readfile($_REQUEST['dlFileName']);

?>