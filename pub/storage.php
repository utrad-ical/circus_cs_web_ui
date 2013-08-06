<?php
/**
 * Output files in plugin result or web cache directories.
 * @author Y. Nomura <nomuray-tky@umin.ac.jp>
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */

$id = $_GET['id'];
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

	if (isset($_SESSION['authenticated_jobs']))
	{
		$jobs = explode(';', $_SESSION['authenticated_jobs']);
		if (array_search($job_id, $jobs) === false)
			error(403, 'You do not have access to this job. Reload the result page.');
	} elseif (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
		// Try basic authentication
		require_once('common.php');
		$user = Auth::checkAuth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
		if (!$user)
			error(403, 'Basic authentication failed.');
		$job = new CadResult($job_id);
		if (!isset($job->job_id))
			error(403, 'No such CAD job.');
		if (!$job->checkCadResultAvailability($user->Group))
			error(403, 'You do not have access to this job.');
	} else {
		error(404, 'You do not have access to this job. Reload the result page.');
	}
}

if (!is_file($fileName) || !is_readable($fileName)) {
	error(404);
}

$path_parts = pathinfo($fileName);
$file_ext   = $path_parts['extension'];

// get mime type
$mime_types = array(
	'txt' => 'text/plain',
	'csv' => 'text/csv',
	'html' => 'text/html',
	'htm' => 'text/html',
	'css' => 'text/css',
	'jpeg' => 'image/jpeg',
	'jpg' => 'image/jpeg',
	'png' => 'image/png',
	'gif' => 'image/gif',
	'mp4' => 'video/mp4',
	'm4v' => 'video/mp4',
	'zip' => 'application/zip'
);
$mime_type = $mime_types[$file_ext] ?: 'application/octet-stream';
$file_size = filesize($fileName);

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

header("Content-Type: {$mime_type}");
header("Cache-Control: max-age=3600");
header('Accept-Ranges: bytes');
header_remove('Pragma');

// turn off output buffering (or large archives may cause out-of-memory error)
if (ob_get_level()) ob_end_clean();
if(isset($_SERVER['HTTP_RANGE']))
{
	list($size_unit, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
	if ($size_unit != 'bytes') error(416);
	list($range) = explode(',', $range, 2); // ignore multiple ranges
	list($seek_start, $seek_end) = explode('-', $range, 2);
	$seek_end   = (empty($seek_end)) ? ($file_size - 1) : min(abs(intval($seek_end)),($file_size - 1));
	$seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)),0);
}
else
{
	$seek_start = 0;
	$seek_end = $file_size - 1;
}

//Only send partial content header if downloading a piece of the file (IE workaround)
if ($seek_start > 0 || $seek_end < ($file_size - 1))
{
	header('HTTP/1.1 206 Partial Content');
	header("Content-Range: bytes $seek_start-$seek_end/$file_size");
	header('Content-Length: ' . ($seek_end - $seek_start + 1));
}
else
{
	header("Content-Length: $file_size");
}

set_time_limit(0);
$file = fopen($fileName,"rb");
fseek($file, $seek_start);

while(!feof($file))
{
	print(@fread($file, 1024*8));
	if (connection_status() != 0)
	{
		@fclose($file);
		exit;
	}
}
fclose($file);

function error($status = 403, $message = '')
{
	switch ($status)
	{
		case 404:
			header('HTTP/1.0 404 Not Found');
			break;
		case 416:
			header('HTTP/1.1 416 Requested Range Not Satisfiable');
			break;
		case 403:
		default:
			header('HTTP/1.0 403 Forbidden');
			break;
	}
	if (strlen($message) > 0) print $message;
	exit();
}