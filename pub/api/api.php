<?php
include("../common.php");
require_once('../../app/lib/api/ApiException.class.php');
// manually issue require() to load exception subclasses

error_reporting(E_CORE_ERROR); // turn off error reporting
set_error_handler('api_error_handler', E_WARNING | E_ERROR);

try
{
	header('Content-Type: application/json');
	$api_request = json_decode($_POST['request'], true);
	if (is_null($api_request))
	{
		$api_request = json_decode(file_get_contents("php://input"), true);
	}
	if (is_null($api_request) && $_POST['action'])
	$api_request = $_POST;
	if (is_null($api_request))
		throw new ApiOperationException("Request format is invalid.");

	$action = $api_request['action'];

	// Autoload action class.
	if (!preg_match('/^[A-Za-z_]+$/', $action))
		throw new ApiOperationException("Requested action is not defined.");
	$class = ucfirst($action) . "Action";
	if (!file_exists("../../app/lib/api/$class.class.php"))
		throw new ApiOperationException("Requested action is not defined.");

	$api = new $class();
	$res = $api->doAction($api_request);
	$result = array(
		'action' => $action,
		'status' => 'OK',
	);
	if (!is_null($res))
		$result['result'] = $res;
	echo json_encode($result);
}
catch (Exception $e)
{
	if ($e instanceof ApiAuthException)
		$error_type = 'AuthError';
	else if ($e instanceof ApiOperationException)
		$error_type = 'OperationError';
	else
		$error_type = 'SystemError';

	$message = $e->getMessage();
	// Hide exception details thrown from PDO (security)
	if ($e instanceof PDOException)
		$message = 'Internal database error.';
	output_error($error_type, $message);
}

function output_error($error_type, $message)
{
	global $action;
	$result = array(
		'status' => $error_type,
		'error' => array('message' => $message)
	);
	if ($action) $result['action'] = $action;
	echo json_encode($result);
	exit();
}

function api_error_handler($errno, $errmsg, $filename, $linenum, $vars)
{
	// detect the use of error supression operator ('@')
	if (error_reporting() === 0) return;
	output_error('SystemError', "$errmsg in $filename line $linenum");
}