<?php
/**
 * Script to output a file in DICOM storage or research directory
 * @author Y. Nomura <nomuray-tky@umin.ac.jp>
 */

	$id      = $_GET['id'];
	$subPath = $_GET['subPath'];

	// Load path list (JSON)
	$pathList = json_decode(file_get_contents('../config/storage.json'), TRUE);

	$fileName = $pathList[$id] . '/' . $subPath;

	if (!file_exists($fileName))
	{
		header('HTTP/1.0 404 Not Found');
		exit;
	}

	// get mime type
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$mimeType = finfo_file($finfo, $fileName);

	// output the file
	switch($mimeType)
	{
		case "text/plain":  // .txt
		case "text/csv":    // .csv
		case "text/html":   // .html
		case "text/css":    // .css

		// images
		case "image/jpeg":  // .jpg .jpeg
		case "image/png":   // .png
		case "image/gif":   // .gif

			header("Content-type: {$mimeType}"); // set mime type
			readfile($fileName);
			break;

		// archives
		case "application/zip":              // .zip
		case "application/x-7z-compressed":  // .7z
		case "application/x-lzh":            // .lha .lzh
		case "application/x-tar":            // .tar .tgz

			$pathInfo = pathinfo($fileName);			// get path info
			header("Content-type: {$mimeType}");		// set mime type
			header("Content-Disposition: attachment");
			header("Cache-Control: Private");
			readfile($fileName);
			break;

		// forbidden
		default:
			header('HTTP/1.0 403 Forbidden');
			break;
	}
	finfo_close($finfo);
?>
