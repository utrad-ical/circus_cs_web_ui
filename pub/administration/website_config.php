<?php

require_once('../common.php');
Auth::checkSession();
Auth::purgeUnlessGranted(Auth::SERVER_SETTINGS);

try {
	$validator = new FormValidator();
	$validator->addRules(array(
		'mode' => array(
			'type' => 'select',
			'options' => array('set')
		),
		'top_message' => array('type' => 'string', 'default' => ''),
		'top_message_style' => array(
			'type' => 'select',
			'options' => array('plain', 'html', 'default' => 'plain')
		),
		'keep_session' => array('type' => 'bool', 'default' => false),
		'session_time_limit' => array(
			'type' => 'int',
			'min' => 1,
			'max' => 60 * 24 * 100
		)
	));

	if ($validator->validate($_POST)) {
		$req = $validator->output;
	} else {
		$req = array();
		$message = implode(" ", $validator->errors);
	}

	if ($req['mode'] == 'set') {
		ServerParam::setVal('top_message', $req['top_message']);
		ServerParam::setVal('top_message_style', $req['top_message_style']);
		ServerParam::setVal('session_time_limit', $req['session_time_limit']);
		ServerParam::setBoolVal('keep_session', $req['keep_session']);
		$message = 'Saved.';
	}

	$initial = array(
		'top_message' => ServerParam::getVal('top_message'),
		'top_message_style' => ServerParam::getVal('top_message_style') ?: 'plain',
		'session_time_limit' => intval(ServerParam::getVal('session_time_limit')),
		'keep_session' => ServerParam::getBoolVal('keep_session') ? 1 : 0
	);
	if ($initial['session_time_limit'] <= 0) {
		$initial['session_time_limit'] = Auth::DEFAULT_SESSION_TIME_LIMIT / 60;
	}

	$smarty = new SmartyEx();
	$smarty->assign(array(
		'initial' => $initial,
		'message' => $message
	));
	$smarty->display('administration/website_config.tpl');
} catch (Exception $e) {
	var_dump($e->getMessage());
}