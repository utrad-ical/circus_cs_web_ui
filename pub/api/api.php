<?php
include("../common.php");

$api_action = "";
$api_status = "";
$api_result = array();
$api_errmsg = array();

$api_request = json_decode($_POST['request'], true);
if (is_null($api_request))
{
	$api_request = json_decode(file_get_contents("php://input"), true);
}

if (is_null($api_request))
{
	$ret_array = array(
		"status" => "OperationError",
		"error"  => array("message" => "Request format is invalid.")
	);
	echo json_encode($ret_array);
	exit;
}
else
{
	try {
		$api = new ApiExecuter();
		$api_action = $api_request['action'];
		switch ($api_action) {
			case "login":
				include("api_login.php");
				$api_result = api_login($api_request);
			break;
			
			case "countImages":
				include("api_count_images.php");
				$api_result = count_images($api_request);
			break;
			
			case "queryJob":
				include("api_query_job.php");
				$api_result = query_job($api_request);
			break;
			
			case "executePlugin":
				include("api_execute_plugin.php");
				$api_result = execute_plugin($api_request);
			break;
			
			default:
				echo json_encode(
					array(
						"action" => $api_action,
						"status" => "OperationError",
						"error"  => array("message" => "Request action is invalid.")
					)
				);
				exit;
			break;
		}
	}
	catch (ApiException $e)
	{
		echo json_encode(
			array(
				"action" => $api_action,
				"status" => $e->getStatus(),
				"error"  => array("message" => $e->getErrmsg())
			)
		);
		exit;
	}
	
	echo json_encode(
		array(
			"action" => $api_action,
			"status" => "OK",
			"result" => $api_result
		)
	);
}
?>
