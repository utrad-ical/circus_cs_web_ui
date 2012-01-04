<?php
include("../common.php");
Auth::checkSession(false);

$validator = new FormValidator();
$validator->addRules(array(
	"newTodayDisp" => array(
		"type" => "select",
		"options" => array('series', 'cad')
	),
	"newDarkroom" => array(
		"type" => "select",
		"options" => array('t', 'f')
	),
	"newAnonymized" => array(
		"type" => "select",
		"options" => array('t', 'f')
	),
	"newShowMissed" => array(
		"type" => "select",
		"options" => array('own', 'all', 'none')
	)
));

try
{
	if (!$validator->validate($_REQUEST))
		throw new Exception(implode("\n", $validator->errors));
	$params = $validator->output;

	$user = Auth::currentUser();
	$user->save(array('User' => array(
		today_disp => $params['newTodayDisp'],
		darkroom => $params['newDarkroom'],
		anonymized => $params['newAnonymized'],
		show_missed => $params['newShowMissed']
	)));

	$_SESSION['todayDisp']    = $params['newTodayDisp'];
	$_SESSION['darkroomFlg']  = ($params['newDarkroom'] == 't') ? 1 : 0;
	$_SESSION['anonymizeFlg'] = ($params['newAnonymized'] == 't') ? 1 : 0;
	$_SESSION['showMissed']   = $params['newShowMissed'];

	echo json_encode(array(
		'action' => 'changePagePreference',
		'status' => 'OK'
	));
}
catch (Exception $e)
{
	echo json_encode(array(
		'status' => 'SysError',
		'error' => array(
			'message' => 'Critical error while saving the preference.'
		)
	));
}

$pdo = null;

