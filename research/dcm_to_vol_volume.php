<?php
	session_cache_limiter('nocache');
	session_start();

	include("../common.php");
	
	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables 
	//------------------------------------------------------------------------------------------------------------------
	$studyInstanceUID  = $_POST['studyInstanceUID'];
	$seriesInstanceUID = $_POST['seriesInstanceUID'];
	//------------------------------------------------------------------------------------------------------------------

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		$errorFlg = 0;

		$sqlStr = "SELECT st.patient_id, sm.path" 
				. " FROM study_list st, series_list sr, storage_master sm " 
				. " WHERE sr.series_instance_uid=? AND sr.study_instance_uid=?" 
				. " AND sr.study_instance_uid=st.study_instance_uid" 
				. " AND sr.storage_id=sm.storage_id;";

		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute(array($seriesInstanceUID, $studyInstanceUID));

		if($stmt->rowCount() != 1)
		{
			$errorFlg = 1;
		}
		else
		{
			$result = $stmt->fetch(PDO::FETCH_NUM);
	
			$patientID   = $result[0];
			$storagePath = $result[1];

			// Prevent timeout error
			set_time_limit(0);

			$seriesDir = $storagePath . $DIR_SEPARATOR . $patientID
			           . $DIR_SEPARATOR . $studyInstanceUID
			           . $DIR_SEPARATOR . $seriesInstanceUID;

			$baseName = $seriesDir . $DIR_SEPARATOR . $seriesInstanceUID;
			$dstFileName = $baseName . ".zip";
		
		
			if(!is_file($dstFileName))
			{
				//------------------------------------------------------------------------------------------------------
				// Convert DICOM files to Volume-One data
				//------------------------------------------------------------------------------------------------------
				$cmdStr = $cmdForProcess . ' "' . $cmdDcmToVolume . ' ' . $seriesDir . ' ' . $seriesInstanceUID . '"';
				shell_exec($cmdStr);
				//------------------------------------------------------------------------------------------------------
	
				//------------------------------------------------------------------------------------------------------
				// create a zip archive
				//------------------------------------------------------------------------------------------------------
				$zip = new ZipArchive();
		
				if ($zip->open($dstFileName, ZIPARCHIVE::CREATE)!==TRUE)
				{
					$errorFlg == 1;
				}
				else
				{
					if($zip->addFile($baseName . ".vol", "/" . $seriesInstanceUID . ".vol") !== TRUE
					    || $zip->addFile($baseName . ".txt", "/" . $seriesInstanceUID . ".txt") !== TRUE)
					{
						$errorFlg = 1;
					}
				}
				$zip->close();
	
				if($errorFlg == 1 && is_file($dstFileName))  unlink($dstFileName);
				if(is_file($baseName . ".vol"))  unlink($baseName . ".vol");
				if(is_file($baseName . ".txt"))  unlink($baseName . ".txt");
			}
			//----------------------------------------------------------------------------------------------------------
		}
	
		echo $errorFlg;
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;		
		
?>