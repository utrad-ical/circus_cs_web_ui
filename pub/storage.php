<?php
/**
 * Output files in plugin result or web cache directories.
 * @author Y. Nomura <nomuray-tky@umin.ac.jp>
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */

$id      = $_GET['id'];
$subPath = $_GET['subPath'];

$download = isset($_GET['dl']);

// Fetch storage area data.
// To reduce DB connections, data are cached in text file as JSON format.
$cacheFile = '../cache/storage.json';
if (!file_exists($cacheFile))
{
	require_once('common.php');
	$list = Storage::select();
	$tmp = array();
	foreach ($list as $item) {
		$tmp[$item->storage_id] = array('type' => $item->type, 'path' => $item->path);
	}
	file_put_contents($cacheFile, json_encode($tmp));
}
$storage_list = json_decode(file_get_contents($cacheFile), true);
if (!is_array($storage_list[$id]))
{
	error(404);
}

$storage = $storage_list[$id];
$is_cad_result = $storage['type'] == 2; // Storage::PLUGIN_RESULT
$storage_path = $storage['path'];
$fileName = $storage_path . '/' . $subPath;


// Security: deny access to parent directories
$dirs = preg_split('{[/\\\\]}', $subPath);
if (array_search('..', $dirs) !== false)
{
	error(403);
}

// Security: check session to see the current user has access to this job
if ($is_cad_result)
{
	$job_id = $dirs[0];
	session_start();
	$jobs = explode(';', $_SESSION['authenticated_jobs']);
	if (array_search($job_id, $jobs) === false)
		error(403, 'You do not have access to this job. Reload the result page.');
}

if (!is_file($fileName) || !is_readable($fileName)) {
	error(404);
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
	'text' => '/^plain|csv|html|css$/',
	'image' => '/^jpeg|png|gif$/',
	'video' => '/^mp4$/',
	'application' => '/^zip|x-7z-compressed|x-lzh|x-tar|octet-stream$/'
);

if (!isset($patterns[$type]) || !preg_match($patterns[$type], $subtype))
	error(403);

if ($download)
{
	$as = @$_GET['as'];
	if (preg_match('/^[\w\.\-\(\)\=\#\@]+$/', $as))
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

function error($status = 403, $message = '')
{
	switch ($status)
	{
		case 404:
			header('HTTP/1.0 404 Not Found');
			break;
		case 403:
		default:
			header('HTTP/1.0 403 Forbidden');
			break;
	}
	if (strlen($message) > 0) print $message;
	exit();
}