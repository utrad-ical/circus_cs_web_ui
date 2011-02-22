<?php
	session_cache_limiter('nocache');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include_once("auto_logout_research_exec.php");

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
		if($params['errorMessage'] == "")
		{
			$dstData['registeredAt'] = date("Y-m-d H:i:s");

			$studyUIDArr = array();
			$seriesUIDArr = array();

			$pluginNameTmp = $params['pluginName'];
			$pluginName    = substr($pluginNameTmp, 0, strpos($pluginNameTmp, " v."));
			$version       = substr($pluginNameTmp, strrpos($pluginNameTmp, " v.")+3);

			$cadIDArr = explode('^', $params['checkedCadIdStr']);
			$cadNum   = count($cadIDArr);

			$userID = $_SESSION['userID'];

			// Connect to SQL Server
			$pdo = new PDO($connStrPDO);

			$sqlStr = "SELECT sub.id FROM"
					. " (SELECT pj.job_id as id, count(jcad.exec_id) as cad_num"
					. " FROM plugin_job_list pj, job_cad_list jcad"
					. " WHERE pj.job_id=jcad.job_id AND pj.plugin_name=? AND pj.version=? GROUP BY id) as sub"
					. " WHERE sub.cad_num=? ORDER BY sub.id";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($pluginName, $version, $cadNum));

			if($stmt->rowCount() > 0)
			{
				while($result = $stmt->fetchColumn())
				{
					$colArr =array();

					$sqlStr = "SELECT * FROM plugin_job_list pjob, job_cad_list jcad"
							. " WHERE pjob.job_id=? AND pjob.job_id=jcad.job_id"
							. " AND (";

					$colArr[] = $result;

					for($i=0; $i<$cadNum; $i++)
					{
						if($i > 0)  $sqlStr .= " OR ";

						$sqlStr .= "(jcad.exec_id=?)";
						$colArr[] = $cadIDArr[$i];
					}
					$sqlStr .= ")";

					$stmtSub = $pdo->prepare($sqlStr);
					$stmtSub->execute($colArr);

					if($stmtSub->rowCount() == $cadNum)
					{
						$resultSub = $stmtSub->fetch(PDO::FETCH_ASSOC);

						$dstData['message'] = '<b>Already registerd by ' . $resultSub['exec_user'] . ' !!</b>';
					    $dstData['registeredAt'] = $resultSub['registered_at'];
						break;
					}
				}
			}

			if($dstData['message'] == "")
			{
				$sqlStr = "SELECT sub.id FROM"
						. " (SELECT el.exec_id as id, count(ec.exec_id) as cad_num"
						. " FROM executed_plugin_list el, executed_cad_list ec"
						. " WHERE el.exec_id=ec.exec_id AND el.plugin_name=? AND el.version=? GROUP BY id) as sub"
						. " WHERE sub.cad_num=? ORDER BY sub.id";

				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute(array($pluginName, $version, $cadNum));

				if($stmt->rowCount() > 0)
				{
					while($result = $stmt->fetchColumn())
					{
						$colArr =array();

						$sqlStr = "SELECT * FROM executed_plugin_list el, executed_cad_list ecad"
								. " WHERE el.exec_id=? AND el.exec_id=ecad.exec_id"
								. " AND (";

						$colArr[] = $result;

						for($i=0; $i<$cadNum; $i++)
						{
							if($i > 0)  $sqlStr .= " OR ";

							$sqlStr .= "(ecad.cad_exec_id=?)";
							$colArr[] = $cadIDArr[$i];
						}
						$sqlStr .= ");";

						$stmtSub = $pdo->prepare($sqlStr);
						$stmtSub->execute($colArr);

						//echo $sqlStr;

						if($stmtSub->rowCount() == $cadNum)
						{
							$resultSub = $stmt->fetch(PDO::FETCH_ASSOC);

							$dstData['message']    = '<b>Already executed by ' . $resultSub['exec_user'] . $tmp . '!!</b>';
							$dsaData['executedAt'] = $resultSub['executed_at'];
							break;
						}
					}
				}
			}


			if($dstData['message'] == "")
			{
				$sqlStr = 'INSERT INTO plugin_job_list (exec_user, plugin_name, version, plugin_type, registered_at)'
				        . ' VALUES (?,?,?,2,?)';
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute(array($userID, $pluginName, $version, $dstData['registeredAt']));

				if($stmt->rowCount() == 1)
				{
					$sqlStr = "SELECT job_id FROM plugin_job_list WHERE plugin_name=? AND version=? AND registered_at=?";

					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute(array($pluginName, $version, $dstData['registeredAt']));
					$jobID = $stmt->fetchColumn();

					$colArr = array();
					$sqlStr = "INSERT INTO job_cad_list (job_id, exec_id) VALUES ";

					for($i=0; $i<$cadNum; $i++)
					{
						if($i > 0) $sqlStr .= ",";
						$sqlStr .= "(?,?)";
						$colArr[] = $jobID;
						$colArr[] = $cadIDArr[$i];
					}

					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute($colArr);

					if($stmt->rowCount() != $cadNum)
					{
						$dstData['message'] = '<b>Fail to register in plug-in job list!!</b>';
						$dstData['registeredAt'] = "";

						$sqlStr = "DELETE FROM job_cad_list WHERE job_id=?; DELETE FROM plugin_job_list WHERE job_id=?;";
						$stmt = $pdo->prepare($sqlStr);
						$stmt->execute(array($jobID, $jobID));
					}
					else
					{
						$dstData['message'] = '<b>Successfully registered in plug-in job list!!</b>';
					}
				}
				else
				{
					//$tmp = $stmt->errorInfo();
					//$dstData['message'] = $tmp[2];
					$dstData['message'] = '<b>Fail to register in plug-in job list!!</b>';
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
