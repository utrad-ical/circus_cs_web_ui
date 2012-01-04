<?php
include("../common.php");
Auth::checkSession(false);

//------------------------------------------------------------------------------
// Import $_POST variables
//------------------------------------------------------------------------------

$params = array();
$validator = new FormValidator();

$validator->addRules(array(
	'darkroom' => array(
		'type' => 'select',
		'options' => array('t', 'f'),
		'required' => true
	)
));

try {
	if ($validator->validate($_POST))
		$req = $validator->output;
	else
		throw new Exception(implode("\n", $validator->errors));

	$user = Auth::currentUser();
	if (!$user)
		throw new Exception('User not logged in');

	$pdo = DBConnector::getConnection();
	$result = DBConnector::query(
		'UPDATE users SET darkroom=? WHERE user_id=?',
		array($req['darkroom'], $user->user_id),
		'SCALAR'
	);

	echo json_encode(array(
		'action' => 'preference',
		'status' => 'OK',
		'result' => array('darkroom' => $req['darkroom'])
	));
}
catch (Exception $e)
{
	echo json_encode(array(
		'action' => 'preference',
		'status' => 'SystemError',
		'message' => $e->getMessage()
	));
}

