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

	$dirs = preg_split('{[/\\\\]}', $subPath);
	if (array_search('..', $dirs) !== false)
	{
		header('HTTP/1.0 403 Forbidden');
		exit;
	}

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
		// text based files
		case "text/plain":  // .txt
		case "text/csv":    // .csv
		case "text/html":   // .html
		case "text/css":    // .css
			header("Content-type: {$mimeType}");
			readfile($fileName);
			break;

		// images
		case "image/jpeg":  // .jpg .jpeg
		case "image/png":   // .png
		case "image/gif":   // .gif
			header("Content-type: {$mimeType}"); // set mime type
			header("Cache-Control: max-age=3600");
			readfile($fileName);
			break;

		// archives (download)
		case "application/zip":              // .zip
		case "application/x-7z-compressed":  // .7z
		case "application/x-lzh":            // .lha .lzh
		case "application/x-tar":            // .tar .tgz
		case "application/octet-stream":

			$pathInfo = pathinfo($fileName); // get path info
			$fileSize = filesize($fileName);

			@apache_setenv('no-gzip', 1);
			@ini_set('zlib.output_compression', 0);
			header("Content-type: application/force-download");
			header('Content-Type: application/octet-stream');
			header('Content-Length: ' .  $fileSize);

			set_time_limit(300);

			$chunkSize = 1 * (1024 * 1024); // how many bytes per chunk

			if ($fileSize > $chunkSize)
			{
				$fp = fopen($fileName, 'rb');
				$buffer = '';
				while (!feof($fp))
				{
					$buffer = fread($fp, $chunkSize);
					echo $buffer;
					ob_flush();
					flush();
				}
				fclose($fp);
			}
			else
			{
				readfile($fileName);
			}
			break;

		// forbidden
		default:
			header('HTTP/1.0 403 Forbidden');
			break;
	}
	finfo_close($finfo);

