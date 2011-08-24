<?php

require_once("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(Auth::SERVER_SETTINGS);

$keys = array(
	array(
		'label' => 'manufacturer',
		'value' => 'manufacturer'
	),
	array(
		'label' => 'study instance UID',
		'value' => 'study_uid'
	),
	array(
		'label' => 'modality',
		'value' => 'modality'
	),
	array(
		'label' => 'series description',
		'value' => 'series_description'
	),
	array(
		'label' => 'number of images',
		'value' => 'image_number'
	)
);

$req = $_REQUEST;

switch($req['mode'])
{
	case 'get_rulesets':
		get_rulesets($req['plugin_id']);
		break;
	case 'set_rulesets':
		break;
	default:
		display();
}

function get_rulesets($plugin_id)
{
	$dum = new PluginCadSeries();
	$entries = $dum->find(
		array('plugin_id' => $plugin_id),
		array('order' => array('volume_id'))
	);
	global $req;
	$items = array();
	foreach ($entries as $item)
		$items[$item->volume_id] = json_decode($item->ruleset);
	json_result($items);
}

function json_result($result, $status = 'OK')
{
	global $req;
	$out = array(
		'action' => $req['mode'],
		'status' => $status == 'OK' ? 'OK' : 'Error',
		'result' => $result
	);
	print json_encode($out);
}

function display()
{
	global $keys;
	$params = array('toTopDir' => "../");
	$smarty = new SmartyEx();

	$dum = new Plugin();
	$plugin_list = $dum->find(array('type' => 1));
	$plugins = array();
	foreach ($plugin_list as $item)
	{
		$plugins[] = array(
			'id' => $item->plugin_id,
			'name' => $item->fullName()
		);
	}

	$smarty->assign(array(
		'plugins' => $plugins,
		'params' => $params,
		'keys' => $keys
	));
	$smarty->display('administration/series_ruleset_config.tpl');
}


?>