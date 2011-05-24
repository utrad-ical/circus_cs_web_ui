<?php
	session_cache_limiter('nocache');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include_once("../auto_logout.php");

	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables
	//------------------------------------------------------------------------------------------------------------------
	$studyUIDArr = array();
	$seriesUIDArr = array();

	$studyUIDArr  = explode('^', $_POST['studyUIDStr']);
	$seriesUIDArr = explode('^', $_POST['seriesUIDStr']);
	$cadName      = $_POST['cadName'];
	$version      = $_POST['version'];

	$seriesNum = count($studyUIDArr);

	$userID = $_SESSION['userID'];

	$dstData = array('message'      => "",
			         'registeredAt' => date("Y-m-d H:i:s"),
			         'executedAt'   => "");
	$sidArr = array();
	//------------------------------------------------------------------------------------------------------------------

	try
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		// Get plugin ID
		$sqlStr = "SELECT plugin_id FROM plugin_master WHERE plugin_name=? AND version=?";
		$pluginID = DBConnector::query($sqlStr, array($cadName, $version), 'SCALAR');
		
		// Get series sid
		$sqlStr = "SELECT sid FROM series_list WHERE series_instance_uid=?";

		foreach($seriesUIDArr as $item)
		{
			$sidArr[] = DBConnector::query($sqlStr, $item, 'SCALAR');
		}

		// Get storage ID of first series
		$sqlStr= "SELECT storage_id FROM series_list WHERE sid=?";
		$storageID =  DBConnector::query($sqlStr, $sidArr[0], 'SCALAR');

		$colArr =array();

		$sqlStr = "SELECT * FROM executed_plugin_list el, executed_series_list es"
				. " WHERE el.plugin_id=? AND el.job_id=es.job_id AND el.status>0"
				. " AND (";

		$colArr[] = $pluginID;

		for($i = 0; $i < count($seriesNum); $i++)
		{
			if($i > 0)  $sqlStr .= " OR ";

			$sqlStr .= "(es.series_id=? AND es.study_sid=?)";

			$colArr[] = $i;
			$colArr[] = $sidArr[$i];
		}
		$sqlStr .= ");";

		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($colArr);

		if($stmt->rowCount() == $seriesNum)
		{
			$result = $stmt->fetch(PDO::FETCH_ASSOC);

			if($status != $PLUGIN_SUCESSED)
			{
				$dstData['message'] = '<b>Already registered by ' . $result['exec_user'] . '!!</b>';
			}
			else
			{
				$dstData['message'] = '<b>Already executed by ' . $result['exec_user'] . '!!</b>';
			}
			$dsaData['executedAt'] = $result['executed_at'];
		}
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
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

			// Get priolity
			$priolity = 1;

			// Get new job ID
			$sqlStr= "SELECT nextval('executed_plugin_list_job_id_seq')";
			$jobID =  DBConnector::query($sqlStr, NULL, 'SCALAR');
			
			// Set new job ID
			$sqlStr = "SELECT setval('executed_plugin_list_job_id_seq', ?, true)";
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindValue(1, $jobID);
			$stmt->execute();

			// Register into "execxuted_plugin_list"
			$sqlStr = "INSERT INTO executed_plugin_list"
					. " (job_id, plugin_id, storage_id, status, exec_user, executed_at)"
					. " VALUES (?, ?, ?, 1, ?, ?)";
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($jobID, $pluginID, $storageID, $userID, $dstData['registeredAt']));

			// Register into "job_queue"
			$sqlStr = "INSERT INTO job_queue"
					. " (job_id, plugin_id, priolity, status, exec_user, registered_at, updated_at)"
					. " VALUES (?, ?, ?, 1, ?, ?, ?)";
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($jobID, $pluginID, $priolity, $userID, $dstData['registeredAt'], $dstData['registeredAt']));

			// Register into executed_series_list and job_queue_series
			for($i=0; $i<$seriesNum; $i++)
			{
				$sqlParams = array($jobID, $i, $sidArr[$i]);
				
				$sqlStr = "INSERT INTO executed_series_list(job_id, series_id, series_sid)"
						. " VALUES (?, ?, ?)";
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($sqlParams);
				
				$sqlStr = "INSERT INTO job_queue_series(job_id, series_id, series_sid)"
						. " VALUES (?, ?, ?)";
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
			//$dstData['message'] = '<b>Fail to register plug-in job</b>';
			$dstData['message'] = $e->getMessage();
		}
	}

	$pdo = null;

	echo json_encode($dstData);
?>
