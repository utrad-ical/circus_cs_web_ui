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
			'regex' => '/^[_A-Za-z0-9][\-_A-Za-z0-9]*$/',
			'errorMes' => 'Invalid group ID. Use only alphabets and numerals.'
		),
		'priv' => array(
			'type' => 'array',
			'childrenRule' => array('type' => 'string', 'regex' => '/^[A-Za-z]+$/')
		),
		'ticket' => array ( 'type' => 'string' )
	));

	if ($validator->validate($_POST)) {
		$req = $validator->output;
	} else {
		throw new Exception(implode(" ", $validator->errors));
	}

	//--------------------------------------------------------------------------
	// Add / Update / Delete group
	//--------------------------------------------------------------------------

	if ($req['mode'] && $req['ticket'] != $_SESSION['ticket'])
		throw new Exception('Invalid page transition detected. Try again.');

	if($req['mode'] == 'delete')
	{
		$target = $req['target'];
		$group = new Group($target);
		if (!$group->group_id)
			throw new Exception("The group '$target'does not exist.");
		$users = $group->User;
		if (count($users) > 0)
			throw new Exception("You can not delete group '$target' because a user belongs to it.");
		Group::delete($target);
		$message = "Deleted group '$target'.";
	}
	else if ($req['mode'] == 'set')
	{
		if ($req['target']) {
			$group = new Group($req['target']);
			if (!$group)
				throw new Exception('The target group does not exist anymore.');
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

		$user = Auth::currentUser();
		foreach ($user->Group as $my_group)
		{
			if ($req['target'] == $my_group->group_id)
				$editing_my_group = true;
			if ($my_group->hasPrivilege(Auth::SERVER_SETTINGS))
				$my_admin_groups[] = $my_group;
		}
		if ($editing_my_group && count($my_admin_groups) == 1 &&
			array_search(Auth::SERVER_SETTINGS, $req['priv']) === false)
			throw new Exception('You cannot revoke serverSettings privilege ' .
				'from this group because you belong to this group.');

		$pdo = DBConnector::getConnection();
		$pdo->beginTransaction();
		$transaction_started = true;
		$set = array(
			'Group' => array(
				'group_id' => $req['newname']
			)
		);
		$group->save($set);
		$group->updatePrivilege($req['priv']);
		$pdo->commit();

		$message = "Group '" . $req['newname'] . "' updated.";
	}
}
catch (Exception $e)
{
	if ($e instanceof PDOException)
	{
		$message = 'Database Error.';
		if ($pdo && $transaction_started)
			$pdo->rollBack();
	}
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
$groups = Group::find();
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

