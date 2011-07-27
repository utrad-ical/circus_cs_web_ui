<?php
require_once('common.php');
Auth::checkSession();

try
{
	$pdo = DBConnector::getConnection();

	$params = array('toTopDir' => "./");
	$data = array();

	// For plug-in block
	$sqlStr = "SELECT plugin_name, version, install_dt FROM plugin_master ORDER BY install_dt DESC";
	$pluginData = DBConnector::query($sqlStr, null, 'ALL_ASSOC');


	//----------------------------------------------------------------------------------------------------
	// Retrieve machine list
	//----------------------------------------------------------------------------------------------------
	$dummy = new ProcessMachine();
	$machines = $dummy->find(array(), array('order'=>array('pm_id')));

	$machineList = array();
	foreach ($machines as $machine)
	{
		$machineList[$machine->pm_id] = array(
			'host_name' => $machine->host_name,
			'ip_address' => $machine->ip_address,
			'os' => $machine->os,
			'architecture' => $machine->architecture,
			'dicom_storage_server' => $machine->dicom_storage_server,
			'plugin_job_manager' => $machine->plugin_job_manager,
			'process_enabled' => $machine->process_enabled
		);
	}
	//----------------------------------------------------------------------------------------------------

	//----------------------------------------------------------------------------------------------------
	// Settings for Smarty
	//----------------------------------------------------------------------------------------------------
	$smarty = new SmartyEx();

	$smarty->assign('params',     $params);
	$smarty->assign('pluginData', $pluginData);
	$smarty->assign('machineList', $machineList);

	$smarty->display('about.tpl');
	//----------------------------------------------------------------------------------------------------
}
catch (PDOException $e)
{
	var_dump($e->getMessage());
}
$pdo = null;

?>
