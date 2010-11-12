<?php

	session_cache_limiter('nocache');
	session_start();

	include("../common.php");
	require_once('../class/validator.class.php');
	
	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables and validation
	//------------------------------------------------------------------------------------------------------------------
	$params = array();
	$validator = new FormValidator();
	
	$validator->addRules(array(
		"studyInstanceUID" => array(
			"type" => "uid",
			"required" => true,
			"errorMes" => "[ERROR] Parameter of URL (studyInstanceUID) is invalid."),
		"seriesInstanceUID" => array(
			"type" => "uid",
			"required" => true,
			"errorMes" => "[ERROR] Parameter of URL (seriesInstanceUID) is invalid.")
		));				

	if($validator->validate($_GET))
	{
		$params = $validator->output;
		$params['message'] = "";
	}
	else
	{
		$params = $validator->output;
		$params['message'] = implode('<br/>', $validator->errors);
	}
	
	$params['fileName'] = '';
	//-----------------------------------------------------------------------------------------------------------------		

	try
	{
		if($params['message'] == "")
		{
			// Connect to SQL Server
			$pdo = new PDO($connStrPDO);

			$sqlStr = "SELECT st.patient_id, sm.apache_alias" 
					. " FROM study_list st, series_list sr, storage_master sm " 
					. " WHERE sr.series_instance_uid=? AND sr.study_instance_uid=?" 
					. " AND sr.study_instance_uid=st.study_instance_uid" 
					. " AND sr.storage_id=sm.storage_id;";
			
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($params['seriesInstanceUID'], $params['studyInstanceUID']));
		
			if($stmt->rowCount() != 1)
			{
				$params['message'] = "[Error] DICOM series is unspecified!!";
			}		
			else
			{
				$result = $stmt->fetch(PDO::FETCH_NUM);

				$webPathOfseriesDir = $result[1] . $result[0]
				                    . $DIR_SEPARATOR_WEB . $params['studyInstanceUID']
		    		                . $DIR_SEPARATOR_WEB . $params['seriesInstanceUID'];
						
				$params['fileName'] = "../" . $webPathOfseriesDir . $DIR_SEPARATOR_WEB
								    . $params['seriesInstanceUID'] . ".zip";
			}
		}

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		require_once('../smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
		
		$smarty->assign('params', $params);

		$smarty->display('research/download_volume.tpl');
		//--------------------------------------------------------------------------------------------------------------
		
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
