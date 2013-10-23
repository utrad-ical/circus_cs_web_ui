<?php
include_once('common.php');
Auth::checkSession();

try
{
	$plugins = array();
	$list = Plugin::select(array('type' => Plugin::CAD_PLUGIN, 'exec_enabled' => true));
	foreach ($list as $plugin)
	{
		$plugins[$plugin->plugin_name][] = $plugin->version;
	}
	foreach ($plugins as &$p) usort($p, 'version_compare');

	//--------------------------------------------------------------------------
	// Make one-time ticket
	//--------------------------------------------------------------------------
	$_SESSION['ticket'] = md5(uniqid().mt_rand());
	//--------------------------------------------------------------------------

	$smarty = new SmartyEx();

	$user = Auth::currentUser()->getData();
	unset($user['passcode']);
	$user['darkroom'] = $user['darkroom'] ? 't' : 'f';
	$user['anonymized'] = $user['anonymized'] ? 't' : 'f';
	$smarty->assign('user', $user);

	$smarty->assign('plugins', $plugins);

	$smarty->assign('ticket',    $_SESSION['ticket']);

	$smarty->display('user_preference.tpl');
	//--------------------------------------------------------------------------
}
catch (PDOException $e)
{
	var_dump($e->getMessage());
}
