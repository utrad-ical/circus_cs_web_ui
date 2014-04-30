<?php
include_once("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(Auth::SERVER_OPERATION);

$dstData = array('message' => "");

$hostName = '127.0.0.1';
$svStat = WinServiceControl::getStatus($PLUGIN_JOB_MANAGER_SERVICE, $hostName);

if($svStat['str'] != 'Stopped')
{
	$dstData['message'] = "[ERROR] Failed to reset plug-in job queue (" . $PLUGIN_JOB_MANAGER_SERVICE . ' is not stopped)';
}

if($dstData['message'] == "")
{
	try
	{
		$nowDateTime = date("Y-m-d H:i:s");

		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		// Begin transaction
		$pdo->beginTransaction();	// Begin transaction

		$sqlStr = "UPDATE job_queue SET status=1,"
				. " pm_id=NULL, registered_at= :dt, updated_at= :dt"
				. " WHERE status<=3";
		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindValue(':dt', $nowDateTime);
		$stmt->execute();

		$sqlStr = "UPDATE executed_plugin_list SET status=1, executed_at=?"
				. " WHERE status>=1 AND status<=3";
		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindValue(1, $nowDateTime);
		$stmt->execute();

		// Commit transaction
		$pdo->commit();

		$dstData['message'] = 'Success to reset plug-in job queue. Please restart' . $PLUGIN_JOB_MANAGER_SERVICE;

	}
	catch (PDOException $e)
	{
		$pdo->rollBack();
		$dstData['message'] = '[ERROR] Failed to reset plug-in job queue';
		//$dstData['message'] = $e->getMessage();
	}
}

echo json_encode($dstData);

