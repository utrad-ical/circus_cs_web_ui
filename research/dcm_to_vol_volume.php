<?php
	session_cache_limiter('nocache');
	session_start();

	include("../common.php");
	require_once('../class/validator.class.php');
	
	$errorFlg = 0;
	
	if($_SESSION['researchShowFlg'] == 0 && $_SESSION['researchExecFlg'] == 0)
	{
		$errorFlg = 1;
	}
	
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

	if($validator->validate($_POST))
	{
		$params = $validator->output;
		//$params['message'] = "";
	}
	else
	{
		$params = $validator->output;
		//$params['message'] = implode('<br/>', $validator->errors);
		$errorFlg = 1;
	}
	
	$params['toTopDir'] = "../";
	//-----------------------------------------------------------------------------------------------------------------	

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		if(!$errorFlg)
		{
			$sqlStr = "SELECT st.patient_id, sm.path" 
					. " FROM study_list st, series_list sr, storage_master sm " 
					. " WHERE sr.series_instance_uid=? AND sr.study_instance_uid=?" 
					. " AND sr.study_instance_uid=st.study_instance_uid" 
					. " AND sr.storage_id=sm.storage_id;";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($params['seriesInstanceUID'], $params['studyInstanceUID']));

			if($stmt->rowCount() != 1)
			{
				$errorFlg = 1;
			}
			else
			{
				// Prevent timeout error
				set_time_limit(0);

				$result = $stmt->fetch(PDO::FETCH_NUM);

				$seriesDir = $result[1] . $DIR_SEPARATOR . $result[0]
				           . $DIR_SEPARATOR . $params['studyInstanceUID']
				           . $DIR_SEPARATOR .  $params['seriesInstanceUID'];

				$baseName = $seriesDir . $DIR_SEPARATOR . $params['seriesInstanceUID'];
				$dstFileName = $baseName . ".zip";
		
				if(!is_file($dstFileName))
				{
					//--------------------------------------------------------------------------------------------------
					// Convert DICOM files to Volume-One data
					//--------------------------------------------------------------------------------------------------
					$cmdStr = $cmdForProcess . ' "' . $cmdDcmToVolume . ' ' . $seriesDir
							. ' ' . $params['seriesInstanceUID'] . '"';
					shell_exec($cmdStr);
					//--------------------------------------------------------------------------------------------------
	
					//--------------------------------------------------------------------------------------------------
					// create a zip archive
					//--------------------------------------------------------------------------------------------------
					$zip = new ZipArchive();
			
					if ($zip->open($dstFileName, ZIPARCHIVE::CREATE)!==TRUE)
					{
						$errorFlg = 1;
					}
					else
					{
						if($zip->addFile($baseName . ".vol", "/" .  $params['seriesInstanceUID'] . ".vol") !== TRUE
						    || $zip->addFile($baseName . ".txt", "/" .  $params['seriesInstanceUID'] . ".txt") !== TRUE)
						{
							$errorFlg = 1;
						}
					}
					$zip->close();
	
					if($errorFlg == 1 && is_file($dstFileName))  unlink($dstFileName);
					if(is_file($baseName . ".vol"))  unlink($baseName . ".vol");
					if(is_file($baseName . ".txt"))  unlink($baseName . ".txt");
				}
				//------------------------------------------------------------------------------------------------------
			}
		}
		echo $errorFlg;
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
