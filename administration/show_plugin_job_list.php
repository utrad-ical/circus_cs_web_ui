<?php
	session_cache_limiter('nocache');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include("auto_logout_administration.php");	
	require_once('../class/PersonalInfoScramble.class.php');	

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
	
		//----------------------------------------------------------------------------------------------
		// Create job list
		//----------------------------------------------------------------------------------------------
		$jobList = array();
		
		$sqlStr  = "SELECT * FROM plugin_job_list WHERE ORDER BY registered_at ASC;";
		
		$result = PdoQueryOne($pdo, $sqlStr, null, 'ALL_ASSOC');
		
		if(count($result) > 0)
		{
			foreach($result as $item)
			{
				$pluginType = '';
			
				switch($item['plugin_type'])
				{
					case 1: $pluginType = 'CAD';             break;
					case 2: $pluginType = 'Research';        break;
					case 3: $pluginType = 'Group research';  break;
				}
			
				$patientID    = "";
				$studyID      = "";
				$seriesNumber = "";

				if($item['plugin_type'] == 1)
				{
					$sqlStr = "SELECT *"
							. " FROM plugin_job_list cl, job_series_list cs, study_list st, series_list sr,"
							. " cad_master cm WHERE cl.job_id=?"
							. " AND cl.job_id = cs.job_id" 
							. " AND cm.cad_name = cl.plugin_name" 
							. " AND cm.version = cl.version" 
							. " AND cs.series_id=1"
							. " AND sr.study_instance_uid=cs.study_instance_uid"
							. " AND st.study_instance_uid=sr.study_instance_uid"
							." AND sr.series_instance_uid=cs.series_instance_uid;";
						
					$resDetail = PdoQueryOne($pdo, $sqlStr, $item['job_id'], 'ARRAY_ASSOC');
			
					if($_SESSION['anonymizeFlg'] == 1)
					{
						$patientID = PinfoScramble::encrypt($resDetail['patient_id'], $_SESSION['key']);
					}
					else
					{
						$patientID = $resDetail['patient_id'];
					}
			
					$studyID      = $resDetail['study_id'];
					$seriesNumber = $resDetail['series_number'];
				}

				$jobList[] = array($item['job_id'],
				                   $item['registered_at'],
								   $item['exec_user'],
				                   $item['plugin_name'] . ' v.' . $item['version'],
								   $pluginType,
								   $patientID,
								   $studyID,
								   $seriesNumber,
								   $item['exec_flg']);
			}
		}
		//--------------------------------------------------------------------------------------------------------
	
		//--------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------
		require_once('../smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();

		$smarty->assign('params', $params);
	
		$smarty->assign('userID',   $_SESSION['userID']);
		$smarty->assign('jobList', $jobList);

		$smarty->display('administration/show_plugin_job_list.tpl');
		//--------------------------------------------------------------------------------------------------------	
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
