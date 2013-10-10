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
		)
	));

	if ($validator->validate($_POST)) {
		$req = $validator->output;
	} else {
		throw new Exception(implode(" ", $validator->errors));
	}

	if ($req['mode'] == 'set') {
		ServerParam::setVal('top_message', $req['top_message']);
		ServerParam::setVal('top_message_style', $req['top_message_style']);
		$message = 'Saved.';
	}

	$initial = array(
		'top_message' => ServerParam::getVal('top_message'),
		'top_message_style' => ServerParam::getVal('top_message_style') ?: 'plain'
	);

	$smarty = new SmartyEx();
	$smarty->assign(array(
		'initial' => $initial,
		'message' => $message
	));
	$smarty->display('administration/website_config.tpl');
} catch (Exception $e) {
	var_dump($e->getMessage());
}