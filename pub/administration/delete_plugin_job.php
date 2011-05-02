<?php
	session_cache_limiter('nocache');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include("auto_logout_administration.php");

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
		}
		catch (PDOException $e)
		{
			$pdo->rollBack();
			$dstData['message'] .= '[ERROR] Fail to delete plug-in job (jobID:' . $dstData['jobID'] . ')';
			//$dstData['message'] = $e->getMessage();
		}
	}

	if($dstData['message'] == "")
	{
		//--------------------------------------------------------------------------------------------------------------
		// Create job list
		//--------------------------------------------------------------------------------------------------------------
		include('get_job_queue_list.php');
		//--------------------------------------------------------------------------------------------------------------

		$dstData['jobListHtml'] = "";

		foreach($jobList as $item)
		{
			$dstData['jobListHtml'] .= '<tr>'
									.  '<td>' . $item[0] . '</td>'
									.  '<td>' . $item[1] . '</td>'
									.  '<td>' . $item[2] . '</td>'
									.  '<td>' . $item[3] . '</td>'
									.  '<td>' . $item[4] . '</td>'
									.  '<td>' . $item[5] . '</td>'
									.  '<td>' . $item[6] . '</td>'
									.  '<td>' . $item[7] . '</td>';
									//.  '<td>'
									//. '<input type="button" class="form-btn" value="detail"'
									//.  ' onClick="ShowJobDetail(' . $item[0] . ');" />'
									//.  '</td>';
			if($item[8] == 3)
			{
				$dstData['jobListHtml'] .= '<td>Processing</td>';
			}
			else if($_SESSION['serverOperationFlg'] == 1 || $_SESSION['serverSettingsFlg'] == 1 || $userID == $item[2])
			{
				$dstData['jobListHtml'] .= '<td>'
									    .  '<input type="button" class="form-btn" value="delete"'
										.  ' onClick="DeleteJob(' . $item[0] . ');">'
										.  '</td>';
			}
			else
			{
				$dstData['jobListHtml'] .= '<td>&nbsp;</td>';
			}
			$dstData['jobListHtml'] .= '</tr>';
		}
	}
	echo json_encode($dstData);

	$pdo = null;
?>
