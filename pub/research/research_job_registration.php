<?php
include_once("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(Auth::RESEARCH_EXEC);

//-----------------------------------------------------------------------------------------------------------------
// Import $_POST variables and validation
//-----------------------------------------------------------------------------------------------------------------
$params = array();
$validator = new FormValidator();

$validator->addRules(array(
	"pluginName" => array(
		"type" => "string",
		"regex" => "/^[\w\s-_\.]+$/",
		"errorMes" => "'Plugin name' is invalid."),
	"checkedCadIdStr" => array(
		"type" => "string",
		"regex" => "/^[\d\^]+$/",
		"errorMes" => "'Plugin name' is invalid."),
	));

if($validator->validate($_POST))
{
	$params = $validator->output;
	$params['errorMessage'] = "";
}
else
{
	$params = $validator->output;
	$params['errorMessage'] = implode('<br/>', $validator->errors);
}
//------------------------------------------------------------------------------------------------------------------

$dstData = array('message'      => $params['errorMessage'],
		         'registeredAt' => "",
				 'executedAt'   => "");

try
{
	if($dstData['errorMessage'] == "")
	{
		$dstData['registeredAt'] = date("Y-m-d H:i:s");

		$studyUIDArr = array();
		$seriesUIDArr = array();

		$pluginNameTmp = $params['pluginName'];
		$pluginName    = substr($pluginNameTmp, 0, strpos($pluginNameTmp, " v."));
		$version       = substr($pluginNameTmp, strrpos($pluginNameTmp, " v.")+3);

		$cadIdArr = explode('^', $params['checkedCadIdStr']);
		$cadNum   = count($cadIdArr);

		sort($cadIdArr, SORT_NUMERIC);

		$userID = $_SESSION['userID'];

		$pdo = DBConnector::getConnection();

		$sqlStr = "SELECT plugin_id FROM plugin_master WHERE plugin_name=? AND version=?";
		$pluginID = DBConnector::query($sqlStr, array($pluginName, $version), 'SCALAR');

		$sqlStr = "SELECT job_id FROM executed_plugin_list"
				. " WHERE plugin_id=? AND status>?";
		$jobIdArr = DBConnector::query($sqlStr, array($pluginID, $PLUGIN_FAILED), 'ALL_COLUMN');

		foreach($jobIdArr as $jobID)
		{
			$sqlStr = "SELECT target_job_id FROM executed_research_targets"
					. " WHERE research_job_id=?"
					. " ORDER BY target_job_id ASC";
			$targetIdArr = DBConnector::query($sqlStr, array($jobID), 'ALL_COLUMN');

			if(count($targetIdArr) == $cadNum)
			{
				$cnt = 0;

				for($i=0; $i<$cadNum; $i++)
				{
					if($targetIdArr[$i] == $cadIdArr[$i]) $cnt++;
				}

				if($cnt == $cadNum)
				{
					$sqlStr = "SELECT status, executed_at, exec_user FROM executed_plugin_list WHERE job_id=?";
					$result = DBConnector::query($sqlStr, array($jobID), 'ARRAY_NUM');

					if($result[0] == $PLUGIN_SUCESSED)
					{
						$dstData['message'] = '<b>Already executed by ' . $result[2] . ' !!</b>';
					    $dstData['executeddAt'] = $result[1];
					}
					else
					{
						$dstData['message'] = '<b>Already registered by ' . $result[2] . ' !!</b>';
					    $dstData['registeredAt'] = $result[1];
					}
				    break;
				}
			}
		}

		if($dstData['message'] == "")
		{
			try
			{
				//---------------------------------------------------------------------------------------------------------
				// Begin transaction
				//---------------------------------------------------------------------------------------------------------
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$pdo->beginTransaction();
				//---------------------------------------------------------------------------------------------------------

				// Get current storage ID for plugin result
				$sqlStr = "SELECT storage_id FROM storage_master WHERE type=2 AND current_use='t'";
				$storageID =  DBConnector::query($sqlStr, NULL, 'SCALAR');

				// Get new job ID
				$sqlStr= "SELECT nextval('executed_plugin_list_job_id_seq')";
				$newJobID =  DBConnector::query($sqlStr, NULL, 'SCALAR');

				// Get policy ID
				//$sqlStr = "SELECT policy_id FROM plugin_result_policy"
				//		. " WHERE policy_name = ?";
				//$policyID = DBConnector::query($sqlStr, array($resultPolicy), 'SCALAR');
				$policyID = 1;
				$priority = 1;

				// Register into "execxuted_plugin_list"
				$sqlStr = "INSERT INTO executed_plugin_list"
						. " (job_id, plugin_id, storage_id, policy_id, status, exec_user,"
						. " registered_at, started_at, executed_at)"
						. " VALUES (?, ?, ?, ?, 1, ?, ?, ?, ?)";
				$sqlParams = array($newJobID,
								$pluginID,
								$storageID,
								$policyID,
								$userID,
								$dstData['registeredAt'],
								$dstData['registeredAt'],
								$dstData['registeredAt']);
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($sqlParams);

				// Register into "job_queue"
				$sqlStr = "INSERT INTO job_queue"
						. " (job_id, plugin_id, priority, status, exec_user, registered_at, updated_at)"
						. " VALUES (?, ?, ?, 1, ?, ?, ?)";
				$sqlParams = array($newJobID,
								$pluginID,
								$priority,
								$userID,
								$dstData['registeredAt'],
								$dstData['registeredAt']);
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($sqlParams);

				// Register into executed_research_targets and job_queue_research_targets
				for($i=0; $i<$cadNum; $i++)
				{
					$sqlParams = array($newJobID, $cadIdArr[$i]);

					$sqlStr = "INSERT INTO executed_research_targets (research_job_id, target_job_id)"
							. " VALUES (?, ?)";
					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute($sqlParams);

					// Match plug-in cad series
					$sqlStr = "INSERT INTO job_queue_research_targets (research_job_id, target_job_id)"
							. " VALUES (?, ?)";
					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute($sqlParams);
				}
				//---------------------------------------------------------------------------------------------------------
				// Commit transaction
				//---------------------------------------------------------------------------------------------------------
				$pdo->commit();
				//---------------------------------------------------------------------------------------------------------

				$dstData['message'] = 'Successfully registered plug-in job';
			}
			catch (PDOException $e)
			{
				$pdo->rollBack();

				$dstData['message'] = '<b>Fail to register plug-in job!!</b>';
				$dstData['message'] = var_dump($e->getMessage());
				$dstData['registeredAt'] = "";
			}
		}
	}
	echo json_encode($dstData);
}
catch (PDOException $e)
{
	var_dump($e->getMessage());
}

$pdo = null;


