<?php

include("../common.php");

$params['toTopDir'] = '../';
Auth::checkSession(false);

try
{
	$user = Auth::currentUser();
	$validator = new FormValidator();
	$validator->addRules(array(
		'jobID' => array(
			'type' => 'int',
			'required' => true,
			'min' => 1
		),
		'action' => array(
			'type' => 'string',
			'required' => true
		),
		'options' => array(
			'type' => 'string',
			'required' => false,
			'default' => ''
		)
	));

	if(!$validator->validate($_POST))
		throw new Exception(implode("\n", $validator->errors));
	$req = $validator->output;

	if($params['errorMessage']=="")
	{
		$log = new FeedbackActionLog();
		$log->save(array(
			'FeedbackActionLog' => array(
				'job_id' => $req['jobID'],
				'user_id' => $user->user_id,
				'act_time' => date('Y-m-d H:i:s'),
				'action' => $req['action'],
				'options' => $req['options']
			)
		));
	}
	echo json_encode(array('status' => 'OK'));
}
catch (Exception $e)
{
	echo json_encode(array(
		'action' => 'actionLog',
		'status' => 'SysError',
		'error' => array(
			'message' => $e->getMessage()
		)
	));
}
