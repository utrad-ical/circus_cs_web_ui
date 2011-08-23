<?php
include("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(Auth::SERVER_OPERATION);

$params = array('toTopDir' => "../");
$message = '';


try
{
	$pdo = DBConnector::getConnection();

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
	));

	if ($_POST['mode'] == 'set')
	{
		$validator->addRules(array(
			'user_id' => array(
				'label' => 'user ID',
				'type' => 'string',
				'regex' => '/^[_A-Za-z][\-_A-Za-z0-9]*$/',
				'errorMes' => 'Invalid user ID. Use only alphabets and numerals.'
			),
			'user_name' => array(
				'label' => 'user name',
				'required' => true,
				'type' => 'string',
				'label' => 'User name',
				'maxLength' => 50,
			),
			'enabled' => array(
				'type' => 'select',
				'options' => array('true', 'false'),
				'default' => 'false'
			),
			'passcode' => array(
				'label' => 'password',
				'type' => 'string',
				'minLength' => 3,
			),
			'groups' => array(
				'type' => 'array',
				'minLength' => 1,
				childrenRules => array('type' => 'string')
			),
			'today_disp' => array(
				'label' => 'today display',
				'required' => true,
				'type' => 'select',
				'options' => array('series', 'cad')
			),
			'darkroom' => array(
				'label' => 'darkroom mode',
				'required' => true,
				'type' => 'select',
				'options' => array('true', 'false')
			),
			'anonymized' => array(
				'required' => true,
				'type' => 'select',
				'options' => array('true', 'false')
			),
			'show_missed' => array(
				'label' => 'missed lesions display',
				'required' => true,
				'type' => 'select',
				'options' => array('own', 'all', 'none')
			),
			'ticket' => array('required' => true, 'type' => 'string')
		));
	}

	if ($_POST['mode'] == 'delete')
	{
		$validator->addRules(array(
			'user_id' => array(
				'label' => 'user ID',
				'type' => 'string',
				'regex' => '/^[_A-Za-z][\-_A-Za-z0-9]*$/',
				'errorMes' => 'Invalid user ID. Use only alphabets and numerals.'
			),
			'ticket' => array('required' => true, 'type' => 'string')
		));
	}

	if ($validator->validate($_POST)) {
		$req = $validator->output;
	} else {
		throw new Exception(implode(" ", $validator->errors));
	}

	//--------------------------------------------------------------------------
	// Add / Update / Delete user
	//--------------------------------------------------------------------------
	if ($req['mode'])
	{
		if ($req['ticket'] != $_SESSION['ticket'])
		{
			throw new Exception('Invalid page transition detected. Try again.');
		}
		if ($req['target'] == $DEFAULT_CAD_PREF_USER)
			throw new Exception('This user ID is reserved for CIRCUS CS system.');
	}

	$currentUser = Auth::currentUser();

	if ($req['mode'] == 'set')
	{
		$param = array();
		if ($req['target'])
		{
			// update existing user
			$user = new User($req['target']);
			$update_mode = true;
			if ($user->user_id == $currentUser->user_id)
				$update_mine = true;
			if (!$user)
				throw new Exception('The target user does not exist.');
			if ($req['passcode'])
			{
				$param['passcode'] = md5($req['passcode']);
			}
		}
		else
		{
			// create new user
			$user = new User();
			$tmp = new User($req['user_id']);
			if ($tmp->user_id)
				throw new Exception("The user with ID '{$req['user_id']}' already exists.");
			if (!$req['passcode'])
				throw new Exception('Password must be specified.');
			$param['passcode'] = md5($req['passcode']);
		}

		$groups = array();
		$group_ids = array();
		foreach ($req['groups'] as $group_id)
		{
			$group = new Group($group_id);
			if (!$group->group_id)
				throw new Exception('Invalid group ID.');
			if ($group->hasPrivilege(Auth::SERVER_SETTINGS))
				$admin_new = true;
			$groups[] = $group;
			$group_ids[] = $group->group_id;
		}

		if (!$currentUser->hasPrivilege(Auth::SERVER_SETTINGS))
		{
			// Secrity Rule:
			// User with only 'serverOperation' privilege can not edit user
			// with 'serverOperation' or 'serverSettings' privilege.
			if ($update_mode && $user->hasPrivilege(Auth::SERVER_OPERATION))
				throw new Exception("You do not have sufficient privilege to modify user '$req[target]'.");
			foreach ($groups as $group)
				if ($group->hasPrivilege(Auth::SERVER_OPERATION))
					throw new Exception(
						"You do not have sufficient privilege to add this user to '$group->group_id' group.");
		}

		if ($update_mine)
		{
			if (!$admin_new)
				throw new Exception('You cannot revoke serverSettings privilege from yourself.');
			if ($req['enabled'] != 'true')
				throw new Exception('You cannot disable yourself.');
			if ($req['user_id'] != $currentUser->user_id)
				throw new Exception('You cannot change the user ID of yourself.');
		}

		$fields = array(
			'user_id', 'user_name', 'enabled', 'today_disp', 'darkroom', 'anonymized', 'show_missed'
		);
		foreach ($fields as $col)
			$param[$col] = $req[$col];

		$pdo->beginTransaction();
		$transaction_started = true;
		$user->save(array('User' => $param));
		$user->updateGroups($group_ids);
		$pdo->commit();

		$message = "User '$req[user_id]' was successfully updated.";
	}
	else if ($req['mode'] == 'delete')
	{
		$user = new User($req['target']);
		if (!$user->user_id)
			throw new Exception('The specified user does not exist.');
		if ($currentUser->user_id == $user->user_id)
			throw new Exception('You cannot delete your user ID.');

		if (!$currentUser->hasPrivilege(Auth::SERVER_SETTINGS))
		{
			if ($user->hasPrivilege(Auth::SERVER_OPERATION))
				throw new Exception("You do not have sufficient privilege to delete user '$req[target]'.");
		}

		try
		{
			User::delete($user->user_id);
		} catch (PDOException $e) {
			if (preg_match('/^23/', $e->getCode())) // Constraint error
				throw new Exception("The user '$req[target]' could not be deleted. " .
					'Perhaps this user already has given feedback or done other activities.');
			else
				throw $e;
		}
		$message = "User '$user->user_id' was deleted.";
	}
}
catch (Exception $e)
{
	if ($e instanceof PDOException)
	{
		$message = 'Database Error.' . $e->getMessage() . $e->getTraceAsString();
		if ($pdo && $transaction_started) $pdo->rollBack();
	}
	else $message = $e->getMessage();
}

//------------------------------------------------------------------------------
// Make one-time ticket
//------------------------------------------------------------------------------
$ticket =md5(uniqid().mt_rand());
$_SESSION['ticket'] = $ticket;


//------------------------------------------------------------------------------
// Retrieve user list and group list
//------------------------------------------------------------------------------
$dum = new User();
$userList = array();
$users = $dum->find(array(), array('order' => array('enabled DESC', 'user_id ASC')));
foreach ($users as $user) {
	if ($user->user_name == $RESERVED_USER_NAME)
		continue;
	$item = $user->getData() ?: array();
	$item['groups'] = array();
	foreach ($user->Group as $group)
	{
		$item['groups'][] = $group->group_id;
	}
	$userList[$user->user_id] = $item;
}

$dum = new Group();
$groupList = $dum->find(array());


//------------------------------------------------------------------------------
// Settings for Smarty
//------------------------------------------------------------------------------
$smarty = new SmartyEx();
$smarty->assign(array(
	'user' => Auth::currentUser(),
	'message' => $message,
	'ticket' => $ticket,
	'params' => $params,
	'userList' => $userList,
	'groupList' => $groupList
));
$smarty->display('administration/user_config.tpl');


?>