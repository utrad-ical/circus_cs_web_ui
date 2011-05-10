<?php

session_cache_limiter('none');
session_start();

include("../common.php");

//------------------------------------------------------------------------------
// Import $_POST variables and validation
//------------------------------------------------------------------------------

$validator = new FormValidator();
$validator->addRules(array(
	"jobID" => array(
		"type" => "int",
		"required" => true,
		"min" => 1,
		"errorMes" => "[ERROR] CAD Job ID is invalid."
	),
	"feedbackMode" => array(
		"type" => "select",
		"required" => true,
		"options" => array("personal", "consensual"),
		"errorMes" => "[ERROR] 'Feedback mode' is invalid."
	),
	"feedback" => array(
		"type" => "json",
		"required" => true,
		"errorMes" => "[ERROR] Feedback data is invalid."
	)
));

if($validator->validate($_POST))
{
	$params = $validator->output;
	$params['errorMessage'] = "";
}
else
{
	$params = $validator->output;
	$params['errorMessage'] = implode('<br/>', $validator->errors);
}

$dstData = array();
$is_consensual = $params['feedbackMode'] == 'consensual';
try {
	if (registerFeedback($params['jobID'], $params['feedback'], $is_consensual) === true)
	{
		echo json_encode(array('message' => 'Success!'));
	} else {
		echo json_encode(array('message' => 'Failed!'));
	}
} catch (Exception $e) {
	echo json_encode(array('mesasge' => $e->getMessage()));
}
exit;


//------------------------------------------------------------------------------

function registerFeedback($job_id, $feedback, $is_consensual)
{
	$pdo = DBConnector::getConnection();
	$user_id = $_SESSION['userID'];
	$fb = new Feedback();
	return $fb->saveFeedback($job_id, $feedback['blockFeedback'], null, $user_id, false);
}

?>