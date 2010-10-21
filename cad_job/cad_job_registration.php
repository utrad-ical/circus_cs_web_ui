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
	//------------------------------------------------------------------------------------------------------------------

	try
	{
		$dstData = array('message'      => "",
				         'registeredAt' => date("Y-m-d H:i:s"),
						 'executedAt'   => "");	

		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
		
		$colArr =array();
		
		$sqlStr = "SELECT * FROM plugin_job_list pjob, job_series_list js"
					. " WHERE pjob.plugin_name=? AND pjob.version=?"
					. " AND pjob.job_id=js.job_id"
					. " AND (";
					
		array_push($colArr, $cadName);
		array_push($colArr, $version);

		for($i=0; $i<$seriesNum; $i++)
		{
			if($i > 0)  $sqlStr .= " OR ";

			$sqlStr .= "(js.series_id=? AND js.study_instance_UID=? AND js.series_instance_UID=?)";

			array_push($colArr, $i+1);
			array_push($colArr, $studyUIDArr[$i]);
			array_push($colArr, $seriesUIDArr[$i]);
		}
		$sqlStr .= ")";
				
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($colArr);		
				
		if($stmt->rowCount() == $seriesNum)
		{
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
	
			$dstData['message'] = '<b>Already registerd by ' . $result['exec_user'] . ' !!</b>';
		    $dstData['registeredAt'] = $result['registered_at'];
		}
		else
		{
			$colArr =array();
		
			$sqlStr = "SELECT * FROM executed_plugin_list el, executed_series_list es"
					. " WHERE el.plugin_name=? AND el.version=? AND el.exec_id=es.exec_id"
					. " AND (";
		
			array_push($colArr, $cadName);
			array_push($colArr, $version);
			
			for($i=0; $i<count($seriesNum); $i++)
			{
				if($i > 0)  $sqlStr .= " OR ";
					
				$sqlStr .= "(es.series_id=? AND es.study_instance_UID=? AND es.series_instance_UID=?)";

				array_push($colArr, $i+1);
				array_push($colArr, $studyUIDArr[$i]);
				array_push($colArr, $seriesUIDArr[$i]);		
			}
			$sqlStr .= ");";
			
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($colArr);
	
			if($stmt->rowCount() == $seriesNum)
			{
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
		
				$dstData['message']    = '<b>Already executed by ' . $result['exec_user'] . '!!</b>';
				$dsaData['executedAt'] = $result['executed_at'];
			}
			else
			{
				$sqlStr = 'INSERT INTO plugin_job_list (exec_user, plugin_name, version, plugin_type, registered_at)'
				        . ' VALUES (?,?,?,1,?)';
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute(array($userID, $cadName, $version, $dstData['registeredAt']));
		
				if($stmt->rowCount() == 1)
				{
					$sqlStr = "SELECT job_id FROM plugin_job_list WHERE plugin_name=? AND version=? AND registered_at=?";

					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute(array($cadName, $version, $dstData['registeredAt']));
					$jobID = $stmt->fetchColumn();

					$colArr = array();
					$sqlStr = "INSERT INTO job_series_list (job_id, series_id, study_instance_uid, series_instance_uid) VALUES ";
							
					for($i=0; $i<$seriesNum; $i++)
					{
						if($i > 0) $sqlStr .= ",";
						$sqlStr .= "(?,?,?,?)";
						array_push($colArr, $jobID);
						array_push($colArr, $i+1);
						array_push($colArr, $studyUIDArr[$i]);
						array_push($colArr, $seriesUIDArr[$i]);
					}

					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute($colArr);

					if($stmt->rowCount() != $seriesNum)
					{
						$dstData['message'] = '<b>Fail to register in CAD job list!!</b>';
						$dstData['registeredAt'] = "";

						$sqlStr = "DELETE FROM job_series_list WHERE job_id=?; DELETE FROM plugin_job_list WHERE job_id=?;";
						$stmt = $pdo->prepare($sqlStr);
						$stmt->execute(array($jobID, $jobID));
					}
					else
					{
						$dstData['message'] = 'Successfully registered in CAD job list!!';
					}
				}
				else
				{
					$tmp = $stmt->errorInfo();
					$dstData['message'] = $tmp[2];
					//$dstData['message'] = '<b>Fail to registered in CAD job list!!</b>';
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

?>
