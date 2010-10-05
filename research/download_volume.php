<?php

	session_cache_limiter('nocache');
	session_start();

	include("../common.php");

	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//--------------------------------------------------------------------------------------------------------	
	$studyInstanceUID  = $_REQUEST['studyInstanceUID'];
	$seriesInstanceUID = $_REQUEST['seriesInstanceUID'];
	//--------------------------------------------------------------------------------------------------------	

	try
	{
		$param = array('message'    => '',
		               'dlFileName' => '');
	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		$sqlStr = "SELECT st.patient_id, sm.apache_alias" 
				. " FROM study_list st, series_list sr, storage_master sm " 
				. " WHERE sr.series_instance_uid=? AND sr.study_instance_uid=?" 
				. " AND sr.study_instance_uid=st.study_instance_uid" 
				. " AND sr.storage_id=sm.storage_id;";
			
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute(array($seriesInstanceUID, $studyInstanceUID));
		
		if($stmt->rowCount() != 1)
		{
			$param['message'] = "[Error] DICOM series is unspecified!!";
		}		
		else
		{
			$result = $stmt->fetch(PDO::FETCH_NUM);

			$patientID = $result[0];
			$webPath   = $result[1];

			$webPathOfseriesDir = $webPath . $patientID
			                    . $DIR_SEPARATOR_WEB . $studyInstanceUID
	    		                . $DIR_SEPARATOR_WEB . $seriesInstanceUID;
						
			$param['fileName'] = "../" . $webPathOfseriesDir . $DIR_SEPARATOR_WEB . $seriesInstanceUID . ".zip";
		}

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		require_once('../smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
		
		$smarty->assign('param', $param);

		$smarty->display('research/download_volume.tpl');
		//--------------------------------------------------------------------------------------------------------------
		
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
