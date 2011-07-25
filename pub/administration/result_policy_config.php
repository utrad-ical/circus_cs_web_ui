<?php

require_once('../common.php');
$params['toTopDir'] = '../';
Auth::checkSession();
Auth::purgeUnlessGranted(Auth::SERVER_SETTINGS);

$message = '';

$fields = array(
	'policy_name',
	'allow_result_reference',
	'allow_personal_fb',
	'allow_consensual_fb',
	'time_to_freeze_personal_fb',
	'max_personal_fb',
	'min_personal_fb_to_make_consensus',
	'automatic_consensus'
);

try {
	//--------------------------------------------------------------------------
	// Import $_REQUEST variables
	//--------------------------------------------------------------------------
	$validator = new FormValidator();
	$validator->addRules(array(
		'ticket' => array('type' => 'string'),
		'mode' => array(
			'type' => 'select',
			'options' => array('set')
		),
		'target' => array('type' => 'int'),
		'policy_name' => array(
			'type' => 'string',
			'regex' => '/^[_A-Za-z][_A-Za-z0-9]*$/',
			'errorMes' => 'Invalid policy name. Use only alphabets and numerals.'
		),
		'allow_result_reference' => array('type' => 'array'),
		'allow_personal_fb' => array('type' => 'array'),
		'allow_consensual_fb' => array('type' => 'array'),
		'time_to_freeze_personal_fb' => array('type' => 'int', 'min' => 0),
		'max_personal_fb' => array('type' => 'int', 'min' => 0),
		'min_personal_fb_to_make_consensus' => array('type' => 'int', 'min' => 0),
		'automatic_consensus' => array(
			'type' => 'int',
			'min' => 1,
			'max' => 1,
			'default' => 0
		)
	));

	if ($validator->validate($_POST))
	{
		$req = $validator->output;
		$req['allow_result_reference'] = implode(',', $req['allow_result_reference']);
		$req['allow_personal_fb'] = implode(',', $req['allow_personal_fb']);
		$req['allow_consensual_fb'] = implode(',', $req['allow_consensual_fb']);
	}
	else
		throw new Exception(implode(' ', $validator->errors));

	if ($req['mode'] && $req['ticket'] != $_SESSION['ticket'])
		throw new Exception('Invalid page transition detected. Try again.');

	if ($req['mode'] == 'set')
	{
		if ($req['target']) {
			$pol = new PluginResultPolicy($req['target']);
		}
		else
		{
			$pol = new PluginResultPolicy();
		}
		$data = array('PluginResultPolicy' => array());
		foreach ($fields as $column)
		{
			$data['PluginResultPolicy'][$column] = $req[$column];
		}
		$pol->save($data);
		$message = 'Policy "' . $pol->policy_name . '" updated.';
	}
}
catch (Exception $e)
{
	$message = $e->getMessage();
}

//------------------------------------------------------------------------------
// Make one-time ticket
//------------------------------------------------------------------------------
$_SESSION['ticket'] = md5(uniqid().mt_rand());
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// Retrieve policy lists
//------------------------------------------------------------------------------
$dum = new PluginResultPolicy();
$pols = $dum->find(array()); // fetch all policies
$policyList = array();
foreach ($pols as $pol)
{
	$item = array();
	$pol_id = $pol->policy_id;
	$item['policy_id'] = $pol_id;
	foreach ($fields as $column) $item[$column] = $pol->$column;
	$policyList[$pol_id] = $item;
}

$dum = new Group();
$gps = $dum->find(array(), array('order' => array('group_id')));
$groups = array();
foreach ($gps as $grp)
	$groups[] = $grp->group_id;

$smarty = new SmartyEx();
$smarty->assign(array(
	'message' => $message,
	'params' => $params,
	'ticket' => $_SESSION['ticket'],
	'groups' => $groups,
	'policyList' => $policyList
));
$smarty->display('administration/result_policy_config.tpl');
exit();

function parseUserList($input)
{
	$tokens = preg_split('/\\,\\s*/', $input);
	$users = array();
	$groups = array();
	$username_rgx = '[a-zA-Z_][a-zA-Z_0-9]*';
	foreach ($tokens as $token)
	{
		$token = trim($token);
		if (preg_match("/^($username_rgx)$/", $token, $match))
			$users[] = $match[1];
		if (preg_match("/^\[\s*($username_rgx)\s*\]", $token, $match))
			$groups[] = $match[1];
	}
	return array ('users' => $users, 'groups' => $groups);
}

?>