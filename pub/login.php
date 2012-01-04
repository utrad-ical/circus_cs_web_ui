<?php

include("common.php");
$toTopDir = './';

try
{
	$onetime = $_GET['onetime'];
	$jump = $_GET['jump'];

	// Get valid onetime pass
	$sqlStr = 'SELECT *'
			. ' FROM user_onetime'
			. ' WHERE onetime_pass = ?'
			. ' AND registered_at > ?';

	$result = DBConnector::query(
		$sqlStr,
		array($onetime, date("Y-m-d H:i:s", time()-60)),
		'ARRAY_ASSOC'
	);

	// User authentication
	$user = new User($result['user_id']);
	if(!$user || !$user->enabled) {
		throw new Exception('Authentication failed.');
	}

	// Delete used onetime password
	$sqlStr = "DELETE FROM user_onetime"
			. " WHERE onetime_pass = ? ";
	DBConnector::query(
		$sqlStr,
		array($onetime)
	);

	session_start();
	Auth::createSession($user);

	// Redirect
	header('location: ' . $toTopDir . $jump);
}
catch (Exception $e)
{
	$smarty = new SmartyEx();
	$smarty->assign('message', $e->getMessage());
	$smarty->display('critical_error.tpl');
	exit;
}
