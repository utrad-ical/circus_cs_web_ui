<?php

require_once("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(Auth::SERVER_SETTINGS);

// Getting/setting the rulesets is done by Web API.
// See SeriesRulesetAction class.

display();

function display()
{
	global $keys;
	$smarty = new SmartyEx();

	$plugin_list = Plugin::select(array('type' => 1));
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
		'keys' => SeriesFilter::availableKeys()
	));
	$smarty->display('administration/series_ruleset_config.tpl');
}
