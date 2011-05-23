<?php

include("../common.php");
Auth::checkSession(false);

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
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$user_id = $_SESSION['userID'];
	$fb = new Feedback();
	$fb->save(array(
		"Feedback" => array(
			"job_id" => $job_id,
			"is_consensual" => $is_consensual ? 'TRUE' : 'FALSE',
			"status" => 1,
			"entered_by" => $user_id,
			"registered_at" => date('Y-m-d H:i:s')
		),
		"blockFeedback" => $feedback['blockFeedback']
	));
	return true;
}

?>