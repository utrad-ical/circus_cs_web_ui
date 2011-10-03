<?php
include("../common.php");

$api_request = json_decode($_POST['request'], true);
if (is_null($api_request))
{
	$api_request = json_decode(file_get_contents("php://input"), true);
}
if (is_null($api_request) && $_POST['action'])
	$api_request = $_POST;

if (is_null($api_request))
{
	$res = new ApiResponse();
	$res->setError(NULL, ApiResponse::STATUS_ERR_OPE, "Request format is invalid.");
	echo $res->getJson();
	exit;
}
else
{
	try {
		$res = ApiExec::doAction($api_request);
		echo $res->getJson();
	}
	catch (Exception $e)
	{
		$action = $api_request['action'];
		$res = new ApiResponse();
		$res->setError($action, ApiResponse::STATUS_ERR_SYS, "Internal system error.");
		echo $res->getJson();
	}
}
?>
