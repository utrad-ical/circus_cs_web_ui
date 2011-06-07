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
	),
	"feedbackMode" => array(
		"type" => "select",
		"required" => true,
		"options" => array("personal", "consensual"),
	),
	"feedback" => array(
		"type" => "json",
		"required" => true,
	)
));

try {
	if($validator->validate($_POST))
		$params = $validator->output;
	else
		throw new Exception(implode("\n", $validator->errors));
	$is_consensual = $params['feedbackMode'] == 'consensual';

	$dstData = array();

	if (registerFeedback($params['jobID'], $params['feedback'], $is_consensual) === true)
		echo json_encode(array('status' => 'OK'));
	else
		throw new Exception('Failed!');
} catch (Exception $e) {
	echo json_encode(array(
		'action' => 'registerFeedback',
		'status' => 'SysError',
		'error' => array(
			'mesasge' => $e->getMessage()
		)
	));
}
exit;


//------------------------------------------------------------------------------

function registerFeedback($job_id, $feedback, $is_consensual)
{
	$pdo = DBConnector::getConnection();
	$user_id = Auth::currentUser()->user_id;
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