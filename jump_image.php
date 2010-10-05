<?php
	//session_cache_limiter('nocache');
	session_start();

	include("common.php");

	function CreateThumbnail($cmd1, $cmd2, $srcFname, $dstFname, $quality, $dumpFlg, $windowLevel, $windowWidth)
	{
		$cmdStr  = $cmd1 . ' "' . $cmd2 . ' ' . $srcFname . ' ' . $dstFname . ' ' . $quality . ' ' . $dumpFlg
		         . ' ' . $windowLevel . ' ' . $windowWidth . '"';	

		shell_exec($cmdStr);
		
		$img = new Imagick();

		for($i=0; $i<100; $i++)
		{
			if($img->readImage($dstFname) == TRUE)	break;
			else                                    sleep(100000);
		}
	}

	//------------------------------------------------------------------------------------------------------------------
	// Auto logout (session timeout)
	//------------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'])  header('location: index.php?mode=timeout');
	else	$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables 
	//------------------------------------------------------------------------------------------------------------------
	$studyInstanceUID  = (isset($_POST['studyInstanceUID']))  ? $_POST['studyInstanceUID']  : "";
	$seriesInstanceUID = (isset($_POST['seriesInstanceUID'])) ? $_POST['seriesInstanceUID'] : "";
	$imgNum = (isset($_POST['imgNum'])) ? $_POST['imgNum'] : 1;	

	$windowLevel = (isset($_POST['windowLevel'])) ? $_POST['windowLevel'] : "";	
	$windowWidth = (isset($_POST['windowWidth'])) ? $_POST['windowWidth'] : "";	
	$presetName  = (isset($_POST['presetName'])) ? $_POST['presetName'] : "";	
	//------------------------------------------------------------------------------------------------------------------

	$dstData = array('imgFname'  => '',
	                 'imgNumStr' => sprintf("Img. No. %04d", $imgNum));

	try
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
		if(!is_file($dstFname))
		{
			CreateThumbnail($cmdForProcess, $cmdCreateThumbnail, $srcFname, $dstFname, $JPEG_QUALITY, 1, $windowLevel, $windowWidth);
		}
		
		$dstData['imgFname'] = $dstFnameWeb;
				
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
		
		echo json_encode($dstData);
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;

	
?>
