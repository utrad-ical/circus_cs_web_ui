<?php

	session_start();

	include_once("common.php");
	require_once('class/DcmExport.class.php');

	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables 
	//------------------------------------------------------------------------------------------------------------------
	$studyInstanceUID  = (isset($_REQUEST['studyInstanceUID']))  ? $_REQUEST['studyInstanceUID']  : "";
	$seriesInstanceUID = (isset($_REQUEST['seriesInstanceUID'])) ? $_REQUEST['seriesInstanceUID'] : "";
	
	$imgNum = (is_numeric($_REQUEST['imgNum']) && $_REQUEST['imgNum'] > 0) ? $_REQUEST['imgNum'] : 1;
	
	$windowLevel = (isset($_REQUEST['windowLevel']) && is_numeric($_REQUEST['windowLevel'])) ? $_REQUEST['windowLevel'] : 0;
	$windowWidth = (isset($_REQUEST['windowWidth']) && is_numeric($_REQUEST['windowWidth'])) ? $_REQUEST['windowWidth'] : 0;
	$presetName  = (isset($_REQUEST['presetName'])) ? $_REQUEST['presetName'] : "";	
	//------------------------------------------------------------------------------------------------------------------

	$dstData = array('imgFname'  => '',
	                 'imgNumStr' => sprintf("Img. No. %04d", $imgNum));
	try
	{	
		if(!preg_match('/[^\d\\.]/', $studyInstanceUID) && !preg_match('/[^\d\\.]/', $seriesInstanceUID))
		{
			// Connect to SQL Server
			$pdo = new PDO($connStrPDO);	

			$sqlStr = "SELECT st.patient_id, sm.path, sm.apache_alias"
					. " FROM study_list st, series_list sr, storage_master sm" 
				    . " WHERE sr.study_instance_uid=?" 
				    . " AND sr.series_instance_uid=?" 
				    . " AND sr.study_instance_uid=st.study_instance_uid" 
				    . " AND sr.storage_id=sm.storage_id;";
	
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($studyInstanceUID, $seriesInstanceUID));
			$result = $stmt->fetch(PDO::FETCH_NUM);
	
			$patientID = $result[0];
		
			$seriesDir = $result[1] . $DIR_SEPARATOR . $patientID . $DIR_SEPARATOR . $studyInstanceUID
					   . $DIR_SEPARATOR . $seriesInstanceUID;
					   
			$seriesDirWeb = $result[2]. $patientID . $DIR_SEPARATOR_WEB . $studyInstanceUID
					      . $DIR_SEPARATOR_WEB . $seriesInstanceUID;		   

			$flist = array();
			$flist = GetDicomFileListInPath($seriesDir);

			//$fNum = count($flist);
			
			$subDir = $seriesDir . $DIR_SEPARATOR . $SUBDIR_JPEG;
			if(!is_dir($subDir))	mkdir($subDir);
			
			$tmpFname = $flist[$imgNum-1];
	
			$srcFname = $seriesDir . $DIR_SEPARATOR . $tmpFname;
	
			// For compresed DICOM file
			$tmpFname = str_ireplace("c_", "_", $tmpFname);
			$tmpFname = substr($tmpFname, 0, strlen($tmpFname)-4);
	
			$dstFname    = $subDir . $DIR_SEPARATOR . $tmpFname;
			$dstFnameWeb = $seriesDirWeb . $DIR_SEPARATOR_WEB . $SUBDIR_JPEG . $DIR_SEPARATOR_WEB . $tmpFname;	
		
			$dumpFname = $dstFname . ".txt";
		
			if($presetName != "" && $presetName != "Auto") 
			{
				$dstFname .= "_" . $presetName;
				$dstFnameWeb .= "_" . $presetName;
			}
			$dstFname .= '.jpg';
			$dstFnameWeb .= '.jpg';		
	
			// Create thumbnail image
			if(is_file($dstFname)
			   || DcmExport::createThumbnailJpg($srcFname, $dstFname, $JPEG_QUALITY, 1, $windowLevel, $windowWidth))
			{	
				$dstData['imgFname'] = $dstFnameWeb;
			}
					
			$fp = fopen($dumpFname, "r");
			
			if($fp != NULL)
			{
				while($str = fgets($fp))
				{
					$dumpTitle   = strtok($str,":");
					$dumpContent = strtok("\r\n");
		
					switch($dumpTitle)
					{
						case 'Img. No.':
						case 'Image No.':
							$dstData['sliceNumber'] = $dumpContent;
							break;
			
						case 'Slice location':
							$dstData['sliceLocation'] = sprintf("%.2f [mm]", $dumpContent);
							break;
					}
				}
			
				fclose($fp);
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
