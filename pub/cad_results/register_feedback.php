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
	$job_id = $params['jobID'];

	$currentUser = Auth::currentUser();

	$cadResult = new CadResult($job_id);
	if (!$cadResult->job_id)
		throw new Exception('No CAD result found for the specified Job ID.');
	$reason = 'You can not enter the feedback for unknown reason.';
	if ($is_consensual)
		$av = $cadResult->feedbackAvailability('consensual', $currentUser, $reason);
	else
		$av = $cadResult->feedbackAvailability('personal', $currentUser, $reason);
	if ($av != 'normal')
		throw new Exception($reason);

	if (registerFeedback($cadResult, $params['feedback'], $params['temporary'], $is_consensual) === true)
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

function registerFeedback(CadResult $cadResult, $feedback, $temporary, $is_consensual)
{
	$job_id = $cadResult->job_id;
	$pdo = DBConnector::getConnection();
	$user = Auth::currentUser();
	$user_id = $user->user_id;

	if (strlen($user_id) == 0)
		throw new Exception('Session not established.');

	// Delete existing feedback set (temporary or not)
	$cond = array(
		'is_consensual' => $is_consensual ? 'TRUE' : 'FALSE',
		'job_id' => $job_id
	);
	if (!$is_consensual)
		$cond['entered_by'] = $user_id; // only delete my personal feedback
	$fbs = Feedback::select($cond);
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

	// Automatic consensus: If 'Automatic consensual' is enabled
	// by plugin result policy settings, save the same data
	// as consensual feedback.
	$policy = $cadResult->PluginResultPolicy;
	if (!$is_consensual && $policy->automatic_consensus)
	{
		// Use another instance of CadResult to avoid cacheing
		$cadResult2 = new CadResult($job_id);
		// Actually not the exact copy, since use of buildInitilalConsensualFeedback
		// may modify the feedback content (like 'missed TP' => 'TP')
		$pfb = $cadResult2->queryFeedback('personal');
		$cfb = $cadResult2->buildInitialConsensualFeedback($pfb);
		if (!registerFeedback($cadResult2, $cfb, false, true))
			return false;
	}

	return true;
}
