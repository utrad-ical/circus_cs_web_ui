<?php
include_once('../common.php');
Auth::checkSession();

try {
	$plugins = array();
	$list = Plugin::select(array('type' => Plugin::CAD_PLUGIN, 'exec_enabled' => true));
	foreach ($list as $plugin)
	{
		$plugins[$plugin->plugin_name][] = $plugin->version;
	}
	foreach ($plugins as &$p) usort($p, 'version_compare');

	$smarty = new SmartyEx();
	$smarty->assign('plugins', $plugins);
	$smarty->display('analysis/export_feedback.tpl');
} catch(Exception $e) {
	critical_error($e);
}

