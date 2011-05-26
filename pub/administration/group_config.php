<?php
require("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(AUTH::SERVER_SETTINGS);

$params = array('toTopDir' => "../");

try
{
	//--------------------------------------------------------------------------
	// Import $_REQUEST variables
	//--------------------------------------------------------------------------
	$validator = new FormValidator();
	$validator->addRules(array(
		'mode' => array(
			'type' => 'select',
			'options' => array('delete', 'set')
		),
		'target' => array( 'type' => 'string' ),
		'newname' => array(
			'type' => 'string',
			'regex' => '/^[_A-Za-z][_A-Za-z0-9]*$/',
			'errorMes' => 'Invalid group ID. Use only alphabets and numerals.'
		),
		'priv' => array(
			'type' => 'array',
			'childrenRule' => array('type' => 'string', 'regex' => '/^[A-Za-z]+$/')
		),
		'colorSet' => array(
			'type' => 'select',
			'options' => array ('admin', 'user', 'guest')
		),
		'ticket' => array ( 'type' => 'string' )
	));

	if ($validator->validate($_POST)) {
		$req = $validator->output;
	} else {
		throw new Exception(implode(" ", $validator->errors));
	}

	// Connect to SQL Server
	$pdo = DBConnector::getConnection();

	//--------------------------------------------------------------------------
	// Add / Update / Delete group
	//--------------------------------------------------------------------------
	$sqlStr = "";
	$sqlParams = array();

	if ($req['mode'] && $req['ticket'] != $_SESSION['ticket'])
		throw new Exception('Invalid page transition detected. Try again.');

	if($req['mode'] == 'delete')
	{
		$target = $req['target'];
		if ($target == 'admin')
			throw new Exception("You can not delete 'admin' group.");
		$group = new Group($target);
		if (!$group->group_id)
			throw new Exception("The group '$target'does not exist.");
		$users = $group->User;
		if (count($users) > 0)
			throw new Exception("You can not delete group '$target' because a user belongs to it.");
		DBConnector::query(
			'DELETE FROM groups WHERE group_id = ?',
			array($target)
		);
		$message = "Deleted group '$target'.";
	}
	else if ($req['mode'] == 'set')
	{
		if ($req['target'] == 'admin')
			throw new Exception("You can not modify 'admin' group.");
		if ($req['target']) {
			$group = new Group($req['target']);
		} else {
			$group = new Group();
		}
		if (!$req['newname'])
			throw new Exception("Specify the new group name.");
		if ($req['target'] != $req['newname'])
		{
			$dummy = new Group($req['newname']);
			if ($dummy->group_id)
				throw new Exception("That group name already exists.");
		}

		$set = array(
			'Group' => array(
				'color_set' => $req['colorSet'],
				'group_id' => $req['newname']
			)
		);
		$group->save($set);
		$group->updatePrivilege($req['priv']);
		$message = "Group '" . $req['newname'] . "' updated.";
	}
}
catch (Exception $e)
{
	if ($e instanceof PDOException)
		$message = 'Database Error.';
	else
		$message = $e->getMessage();
}

//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// Make one-time ticket
//------------------------------------------------------------------------------
$_SESSION['ticket'] = md5(uniqid().mt_rand());
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// Retrieve group lists
//------------------------------------------------------------------------------
$dummy = new Group();
$groups = $dummy->find();
$privs = Auth::getPrivilegeTypes();

$groupList = array();
foreach ($groups as $group)
{
	$groupList[$group->group_id] = array(
		'group_id' => $group->group_id,
		'privs' => $group->listPrivilege(),
		'color_set' => $group->color_set
	);
}
// Sort by privilege count
uasort($groupList, function ($a, $b) {
	return count($b['privs']) - count($a['privs']); });


//------------------------------------------------------------------------------
// Settings for Smarty
//------------------------------------------------------------------------------
$smarty = new SmartyEx();

$smarty->assign(array(
	'message' => $message,
	'params' => $params,
	'groupList' => $groupList,
	'ticket' => $_SESSION['ticket'],
	'privs' => $privs
));
$smarty->display('administration/group_config.tpl');
//------------------------------------------------------------------------------


?>
