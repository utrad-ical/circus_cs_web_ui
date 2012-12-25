<?php
/**
 * Script to output a file in DICOM storage or research directory
 * @author Y. Nomura <nomuray-tky@umin.ac.jp>
 */

$id      = $_GET['id'];
$subPath = $_GET['subPath'];

$download = isset($_GET['dl']);

// Load path list (JSON)
$pathList = json_decode(file_get_contents('../config/storage.json'), true);
$fileName = $pathList[$id] . '/' . $subPath;

// Security: deny access to parent directories
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
list($type, $subtype) = explode('/', $mimeType);
finfo_close($finfo);

// turn off output buffering (or large archives may cause out-of-memory error)
if (ob_get_level()) ob_end_clean();

// output the file
$patterns = array(
	'text' => '/^plain|csv|html|css)$/',
	'image' => '/^jpeg|png|gif$/',
	'video' => '/^mp4$/',
	'application' => '/^zip|x-7z-compressed|x-lzh|x-tar|octet-stream$/'
);

if (!isset($patterns[$type]) || !preg_match($patterns[$type], $subtype))
{
	header('HTTP/1.0 403 Forbidden');
	exit;
}

if ($download)
{
	$as = @$_GET['as'];
	if (preg_match('/[\w\.\-\(\)\=\#\@]/', $as))
	{
		header("Content-Disposition: attachment; filename=\"$as\"");
	}
	else
	{
		header("Content-Disposition: attachment");
	}
}

header("Content-Type: {$mimeType}");
header("Cache-Control: max-age=3600");
readfile($fileName);
