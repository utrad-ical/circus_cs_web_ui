<?php

include("../common.php");
Auth::checkSession(false);

//------------------------------------------------------------------------------
// Import $_POST variables and validation
//------------------------------------------------------------------------------

$validator = new FormValidator();
$validator->addRules(array(
	'jobID' => array(
		'type' => 'int',
		'required' => true,
		'min' => 1,
	),
	'temporary' => array(
		'type' => 'select',
		'required' => false,
		'options' => array(1, 0),
		'default' => 0
	),
	'feedbackMode' => array(
		'type' => 'select',
		'required' => true,
		'options' => array('personal', 'consensual')
	),
	'feedback' => array(
		'type' => 'json',
		'required' => true
	)
));

try {
	if($validator->validate($_POST))
		$params = $validator->output;
	else
		throw new Exception(implode("\n", $validator->errors));
	$is_consensual = $params['feedbackMode'] == 'consensual';

	$dstData = array();

	if (registerFeedback($params['jobID'], $params['feedback'], $params['temporary'], $is_consensual) === true)
		echo json_encode(array('status' => 'OK'));
	else
		throw new Exception('Failed!');
} catch (Exception $e) {
	echo json_encode(array(
		'action' => 'registerFeedback',
		'status' => 'SysError',
		'error' => array(
			'message' => $e->getMessage()
		)
	));
}
exit;


//------------------------------------------------------------------------------

function registerFeedback($job_id, $feedback, $temporary, $is_consensual)
{
	$pdo = DBConnector::getConnection();
	$user_id = Auth::currentUser()->user_id;
	if (strlen($user_id) == 0)
		throw new Exception('Session not established.');

	// Delete existing feedback set (temporary or not)
	$dummy = new Feedback();
	$cond = array('is_consensual' => $is_consensual ? 'TRUE' : 'FALSE');
	if (!$is_consensual)
		$cond['entered_by'] = $user_id; // only delete my personal feedback
	$fbs = $dummy->find($cond);
	foreach ($fbs as $delete_fb)
		Feedback::delete($delete_fb->fb_id);

	// Save the new feedback data
	$fb = new Feedback();
	$fb->save(array(
		'Feedback' => array(
			'job_id' => $job_id,
			'is_consensual' => $is_consensual ? 'TRUE' : 'FALSE',
			'status' => $temporary ? Feedback::TEMPORARY : Feedback::REGISTERED,
			'entered_by' => $user_id,
			'registered_at' => date('Y-m-d H:i:s')
		),
		'blockFeedback' => $feedback['blockFeedback'],
		'additionalFeedback' => $feedback['additionalFeedback']
	));
	return true;
}

?>