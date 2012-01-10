<?php
include("../common.php");
require_once('../../app/lib/api/ApiException.class.php');
// manually issue require() to load exception subclasses

try
{
	$api_request = json_decode($_POST['request'], true);
	if (is_null($api_request))
	{
		$api_request = json_decode(file_get_contents("php://input"), true);
	}
	if (is_null($api_request) && $_POST['action'])
	$api_request = $_POST;
	if (is_null($api_request))
		throw new ApiOperationException("Request format is invalid.");

	$exec = new ApiExec();
	$res = $exec->doAction($api_request);
	$result = array(
		'action' => $exec->action,
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
	$result = array(
		'status' => $error_type,
		'error' => array('message' => $e->getMessage())
	);

	// Hide exception details thrown from PDO (security)
	if ($e instanceof PDOException)
		$result['error'] = 'Internal Database Error.';

	if ($exec && $exec->action)
		$result['action'] = $exec->action;
	echo json_encode($result);
}
