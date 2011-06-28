<?php
	$params = array('toTopDir' => "../");
	include_once("../common.php");
	Auth::checkSession();
	Auth::purgeUnlessGranted(Auth::RESEARCH_EXEC);

	try
	{
		$pluginList = array();

		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		$sqlStr = "SELECT pm.plugin_name, pm.version, rm.research_type, rm.target_plugin_name, rm.target_version_min,"
				. " rm.target_version_max, rm.time_limit, rm.result_table"
				. " FROM plugin_master pm, plugin_research_master rm"
		        . " WHERE pm.plugin_id=rm.plugin_id AND pm.type=2 AND pm.exec_enabled='t'"
				. " ORDER BY rm.label_order ASC";

		$stmtCad = $pdo->prepare($sqlStr);
		$stmtCad->execute();
		$pluginNum = $stmtCad->rowCount();
		$resultPlugin = $stmtCad->fetchAll(PDO::FETCH_NUM);

		$pluginMenuVal = array();
		$cadList = array();
		$versionList = array();

		for($i=0; $i<$pluginNum; $i++)
		{
			if($resultPlugin[$i][2] == 1)
			{
				$sqlStr = "SELECT DISTINCT pm.plugin_name, pm.version"
						. " FROM executed_plugin_list el, plugin_master pm"
	    		        . " WHERE pm.plugin_id=el.plugin_id"
	    		        . " AND pm.plugin_name=?"
	    		        . " AND pm.version>=? AND pm.version<=?";
				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindParam(1, $resultPlugin[$i][3]);
				$stmt->bindParam(2, $resultPlugin[$i][4]);
				$stmt->bindParam(3, $resultPlugin[$i][5]);
			}
			else if($resultPlugin[$i][2] == 2)
			{
				$sqlStr = "SELECT DISTINCT pm.plugin_name, pm.version"
						. " FROM executed_plugin_list el, feedback_list fl,"
						. " plugin_master pm, plugin_cad_master cm"
						. " WHERE pm.plugin_id=el.plugin_id"
						. " AND cm.plugin_id=pm.plugin_id"
						. " AND cm.result_type=1"
						. " AND el.job_id=fl.job_id"
						. " AND fl.is_consensual='t'"
						. " AND fl.status=1"
						. " ORDER BY pm.plugin_name ASC, pm.version ASC";
				$stmt = $pdo->prepare($sqlStr);
			}

			$tmpStr = "";
			$prevCadName = "";

			$stmt->execute();

			while($result = $stmt->fetch(PDO::FETCH_NUM))
			{
				if($result[0] != $prevCadName)
				{
					if($prevCadName != "")  $tmpStr .= '/';
					$tmpStr .= $result[0];
					$prevCadName = $result[0];
				}
				$tmpStr .= '^' . $result[1];
			}
			$pluginMenuVal[] = $tmpStr;
		}

		$cadMenuStr = explode('/', $pluginMenuVal[0]);
		$cadNum = count($cadMenuStr);

		for($j=0; $j<$cadNum; $j++)
		{
			$tmpStr = explode('^', $cadMenuStr[$j]);

			$cadList[$j][0] =  $tmpStr[0]; // CAD name
			$cadList[$j][1] =  substr($cadMenuStr[$j], strlen($tmpStr[0])+1); // version str

			if($j==0)
			{
				for($i = 1; $i < count($tmpStr); $i++)
				{
					$versionList[$i-1] = $tmpStr[$i];
				}
			}
		}

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		$smarty = new SmartyEx();

		$smarty->assign('params',        $params);
		$smarty->assign('pluginList',    $resultPlugin);
		$smarty->assign('pluginMenuVal', $pluginMenuVal);
		$smarty->assign('cadList',       $cadList);
		$smarty->assign('versionList',   $versionList);

		$smarty->display('research/research_job.tpl');
		//--------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
