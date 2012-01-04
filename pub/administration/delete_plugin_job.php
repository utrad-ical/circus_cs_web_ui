<?php
include_once("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(Auth::SERVER_OPERATION);

//-----------------------------------------------------------------------------------------------------------------
// Import $_POST variables and validation
//-----------------------------------------------------------------------------------------------------------------
$dstData = array();
$validator = new FormValidator();

$validator->addRules(array(
	"jobID" => array(
		"type" => "int",
		"min" => 1),
	));

if($validator->validate($_POST))
{
	$dstData = $validator->output;
	$dstData['message'] = "";
}
else
{
	$dstData = $validator->output;
	$dstData['message'] = implode('<br/>', $validator->errors);
}

$userID = $_SESSION['userID'];
//-----------------------------------------------------------------------------------------------------------------

if($dstData['message'] == "")
{
	try
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		// Begin transaction
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->beginTransaction();	// Begin transaction

		//----------------------------------------------------------------------------------------------------
		// Delete the selected plug-in job (not processed)
		//----------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT status FROM executed_plugin_list where job_id=?";
		$status = DBConnector::query($sqlStr, $dstData['jobID'], 'SCALAR');

		if(0 <= $status && $status < 3)
		{
			$sqlStr = "DELETE FROM executed_plugin_list WHERE job_id=?";
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindValue(1, $dstData['jobID']);
			$stmt->execute();
		}

		// Commit transaction
		$pdo->commit();

		$dstData['message'] = 'Success to delete plug-in job (jobID:' . $dstData['jobID'] . ')';
	}
	catch (PDOException $e)
	{
		$pdo->rollBack();
		$dstData['message'] = '[ERROR] Fail to delete plug-in job (jobID:' . $dstData['jobID'] . ')';
		//$dstData['message'] = $e->getMessage();
	}
	$pdo = null;
}

echo json_encode($dstData);
?>
