<?php
require_once('common.php');
Auth::checkSession();

try
{
	$plugins = Plugin::select(array(), array('order' => array('install_dt DESC')));
	$machines = ProcessMachine::select(array(), array('order'=>array('pm_id')));

	//--------------------------------------------------------------------------
	// Settings for Smarty
	//--------------------------------------------------------------------------
	$smarty = new SmartyEx();

	$smarty->assign('plugins', $plugins);
	$smarty->assign('machines', $machines);

	$smarty->display('about.tpl');
	//--------------------------------------------------------------------------
}
catch (PDOException $e)
{
	var_dump($e->getMessage());
}

