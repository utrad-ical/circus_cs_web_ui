<?php
	//session_cache_limiter('nocache');
	session_start();

	include("common.php");
	require_once('class/PersonalInfoScramble.class.php');

	//------------------------------------------------------------------------------------------------------------------
	// Auto logout (session timeout)
	//------------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'])  header('location: index.php?mode=timeout');
	else	$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//------------------------------------------------------------------------------------------------------------------
	$data = array( 'studyInstanceUID'  => (isset($_REQUEST['studyInstanceUID']))  ? $_REQUEST['studyInstanceUID']  : "",
		 		   'seriesInstanceUID' => (isset($_REQUEST['seriesInstanceUID'])) ? $_REQUEST['seriesInstanceUID'] : "",
				   'listTabAddress'    => (isset($_REQUEST['listTabAddress'])) ? $_REQUEST['listTabAddress'] : "",
				   'listTabName'       => (isset($_REQUEST['listTabName'])) ? $_REQUEST['listTabName'] : "",
				   'seriesDir'         => (isset($_REQUEST['seriesDir'])) ? $_REQUEST['seriesDir'] : "",
				   'seriesDirWeb'      => (isset($_REQUEST['seriesDirWeb'])) ? $_REQUEST['seriesDirWeb'] : "",
				   'orgWidth'          => (isset($_REQUEST['orgWidth']) && $_REQUEST['orgWidth'] > 0) ? $_REQUEST['orgWidth'] : 0,
				   'orgHeight'         => (isset($_REQUEST['orgHeight']) && $_REQUEST['orgHeight'] > 0) ? $_REQUEST['orgHeight'] : 0,
				   'encryptedPtID'     => (isset($_REQUEST['encryptedPtID'])) ? $_REQUEST['encryptedPtID'] : "",
				   'encryptedPtName'   => (isset($_REQUEST['encryptedPtName'])) ? $_REQUEST['encryptedPtName'] : "",
				   'sex'               => (isset($_REQUEST['sex'])) ? $_REQUEST['sex'] : "",
				   'age'               => (isset($_REQUEST['age'])) ? $_REQUEST['age'] : "",
				   'studyID'           => (isset($_REQUEST['studyID'])) ? $_REQUEST['studyID'] : "",
				   'seriesID'          => (isset($_REQUEST['seriesID'])) ? $_REQUEST['seriesID'] : "",
				   'seriesDate'        => (isset($_REQUEST['seriesDateyID'])) ? $_REQUEST['seriesDate'] : "",
				   'seriesTime'        => (isset($_REQUEST['seriesTime'])) ? $_REQUEST['seriesTime'] : "",	
				   'modality'          => (isset($_REQUEST['modality'])) ? $_REQUEST['modality'] : "",
				   'seriesDescription' => (isset($_REQUEST['seriesDescription'])) ? $_REQUEST['seriesDescription'] : "",
				   'bodyPart'          => (isset($_REQUEST['bodyPart'])) ? $_REQUEST['bodyPart'] : "");

	$data['patientID']   = PinfoScramble::decrypt($data['encryptedPtID'], $_SESSION['key']);
	$data['patientName'] = PinfoScramble::decrypt($data['encryptedPtName'], $_SESSION['key']);
	
	//------------------------------------------------------------------------------------------------------------------

	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);	

		if($data['seriesDir'] == "" || $data['seriesDirWeb'] == "" || $data['orgWidth'] == 0 || $data['orgHeight'] == 0
		   || $data['encryptedPtID'] == "" || $data['encryptedPtName'] == "" || $data['sex'] == ""
		   || $data['age'] == "" || $data['studyID'] == "" || $data['seriesID'] == "" || $data['seriesDate'] == ""
		   || $data['seriesTime'] == "" || $data['modality'] == "" || $data['seriesDescription'] == ""
		   || $data['bodyPart'] == "")				   
		{
			$sqlStr = "SELECT pt.patient_id, pt.patient_name, sm.path, sm.apache_alias," 
			        . " sr.image_width, sr.image_height, pt.sex, st.age, st.study_id,"
					. " sr.series_number, sr.series_date, sr.series_time, sr.modality,"
					. " sr.series_description, sr.body_part, sr.compress_flg"
					. " FROM patient_list pt, study_list st, series_list sr, storage_master sm " 
			        . " WHERE sr.study_instance_uid=?" 
			        . " AND sr.series_instance_uid=?" 
			        . " AND sr.study_instance_uid=st.study_instance_uid" 
			        . " AND pt.patient_id=st.patient_id" 
			        . " AND sr.storage_id=sm.storage_id;";
	
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($data['studyInstanceUID'], $data['seriesInstanceUID']));
	
			$result = $stmt->fetch(PDO::FETCH_NUM);
				
			$data['patientID']         = $result[0];
			$data['patientName']       = $result[1];
			$data['sex']               = $result[6];
			$data['age']               = $result[7];
			$data['studyID']           = $result[8];
			$data['seriesID']          = $result[9];
			$data['seriesDate']        = $result[10];
			$data['seriesTime']        = $result[11];
			$data['modality']          = $result[12];
			$data['seriesDescription'] = $result[13];
			$data['bodyPart']          = $result[14];
			
			$data['encryptedPtID']   = PinfoScramble::encrypt($data['patientID'], $_SESSION['key']);
			$data['encryptedPtName'] = PinfoScramble::encrypt($data['patientName'], $_SESSION['key']);			
			
			$data['seriesDir'] = $result[2] . $DIR_SEPARATOR . $data['patientID'] . $DIR_SEPARATOR . $data['studyInstanceUID']
					           . $DIR_SEPARATOR . $data['seriesInstanceUID'];
					   
			$data['seriesDirWeb'] = $result[3]. $data['patientID'] . $DIR_SEPARATOR_WEB . $data['studyInstanceUID']
					              . $DIR_SEPARATOR_WEB . $data['seriesInstanceUID'];		   
					   
			$data['orgWidth'] = $result[4];
			$data['orgHeight'] = $result[5];
		}
	
		if($_SESSION['anonymizeFlg'] == 1)
		{	
			$data['patientID'] = $data['encryptedPtID'];
			$data['patientName'] = PinfoScramble::scramblePtName();
		}
	
		$data['dispWidth']  = $data['orgWidth'];
		$data['dispHeight'] = $data['orgHeight'];
		
		if($data['dispWidth'] >= $data['dispHeight'] && $data['dispWidth'] > 256)
		{
			$data['dispWidth']  = 256;
			$data['dispHeight'] = (int)((float)$data['orgHeight'] * (float)$data['dispWidth']/(float)$data['orgWidth']);
		}
		else if($data['dispHeight'] > 256)
		{
			$data['dispHeight'] = 256;
			$data['dispWidth']  = (int)((float)$data['orgWidth'] * (float)$data['dispHeight']/(float)$data['orgHeight']);
		}	
		$data['dispWidth']  = (int)($data['dispWidth']  * $RESCALE_RATIO_OF_SERIES_DETAIL);
		$data['dispHeight'] = (int)($data['dispHeight'] * $RESCALE_RATIO_OF_SERIES_DETAIL);
		
		$data['imgLeftPos'] = (256 * $RESCALE_RATIO_OF_SERIES_DETAIL / 2) - ($data['dispWidth'] / 2);
		$data['imgNumStrLeftPos'] = $data['imgLeftPos'] + 5;
		
		$data['imgNum'] = (isset($_REQUEST['imgNum'])) ? $_REQUEST['imgNum'] : 1;
	
		$data['windowLevel']  = isset($_REQUEST['windowLevel']) ? $_REQUEST['windowLevel'] : 0;
		$data['windowWidth']  = isset($_REQUEST['windowWidth']) ? $_REQUEST['windowWidth'] : 0;
		$data['presetName']   = isset($_REQUEST['presetName'])  ? $_REQUEST['presetName']  : "";
		$data['grayscaleStr'] = isset($_REQUEST['grayscaleStr']) ? $_REQUEST['grayscaleStr'] : "";

		if($data['grayscaleStr'] == "")
		{	
			$stmt = $pdo->prepare("SELECT * FROM grayscale_preset WHERE modality=? ORDER BY priolity ASC");
			$stmt->bindParam(1, $data['modality']);
			$stmt->execute();
			
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			foreach($result as $key => $item)
			{
				if($item['priolity'] == 1)
				{
					$data['windowLevel'] = $item['window_level'];
					$data['windowWidth'] = $item['window_width'];
					$data['presetName']  = $item['preset_name'];
				}
			
				if($key > 0)  $data['grayscaleStr'] .= '^';
			
				$data['grayscaleStr'] .= $item['preset_name'] . '^' . $item['window_level'] . '^' . $item['window_width'];
			}
		}
	
		$flist = array();
		$flist = GetDicomFileListInPath($data['seriesDir']);
		$data['fNum'] = count($flist);
		
		$subDir = $data['seriesDir'] . $DIR_SEPARATOR . $SUBDIR_JPEG;
		if(!is_dir($subDir))	mkdir($subDir);
		
		$tmpFname = $flist[$data['imgNum']-1];

		$data['srcFname'] = $data['seriesDir'] . $DIR_SEPARATOR . $tmpFname;

		// For compresed DICOM file
		$tmpFname = str_ireplace("c_", "_", $tmpFname);
		$tmpFname = substr($tmpFname, 0, strlen($tmpFname)-4);

		$data['dstFname']    .= $subDir . $DIR_SEPARATOR . $tmpFname;
		$data['dstFnameWeb'] .= $data['seriesDirWeb'] . $DIR_SEPARATOR_WEB . $SUBDIR_JPEG . $DIR_SEPARATOR_WEB . $tmpFname;	
	
		$dumpFname = $data['dstFname'] . ".txt";
	
		if($data['presetName'] != "" && $data['presetName'] != "Auto") 
		{
			$data['dstFname'] .= "_" . $data['presetName'];
			$data['dstFnameWeb'] .= "_" . $data['presetName'];
		}
		$data['dstFname'] .= '.jpg';
		$data['dstFnameWeb'] .= '.jpg';		
		
		if(!is_file($data['dstFname']))
		{
			dcm2jpg($cmdForProcess, $cmdCreateThumbnail, $data['srcFname'], $data['dstFname'],
			         $JPEG_QUALITY, 1, $data['windowLevel'], $data['windowWidth']);
		}
				
		$fp = fopen($dumpFname, "r");
	
		$data['sliceNumber'] = 0;
		$data['sliceLocation'] = 0;
		
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
						$data['sliceNumber'] = $dumpContent;
						break;

					case 'Slice location':
						$data['sliceLocation'] = sprintf("%.2f [mm]", $dumpContent);
						break;
				}
			}
		
			fclose($fp);
		}
		
		//echo $data['listTabAddress'];
	
		if($data['grayscaleStr'] != "")
		{
			$data['presetArr'] = explode("^", $data['grayscaleStr']);
		}
		$data['presetNum'] = count($data['presetArr'])/3;
		
		//--------------------------------------------------------------------------------------------------------------
		// Retrieve tag data
		//--------------------------------------------------------------------------------------------------------------
		$data['tagArray'] = array();
		
		$stmt = $pdo->prepare("SELECT tag, entered_by FROM series_tag WHERE series_instance_uid=? ORDER BY tag_id ASC");
		$stmt->bindValue(1, $data['seriesInstanceUID']);
		$stmt->execute();
		$tagNum = $stmt->rowCount();
			
		for($i=0; $i<$tagNum; $i++)
		{
			$result = $stmt->fetch(PDO::FETCH_NUM);
		
			$data['tagArray'][$i] = $result[0];
			
			if($i == 0)
			{
				$data['tagEnteredBy'] = $result[1];
				$data['tagStr'] = $result[0];
			}
			else
			{
				$data['tagStr'] .= ", " . $result[0];
			}
		}	
		//--------------------------------------------------------------------------------------------------------------		

		//var_dump($data);	
		//--------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------
		require_once('smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
	
		$smarty->assign('data', $data);
	
		$smarty->display('series_detail.tpl');
		//--------------------------------------------------------------------------------------------------------	
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;

	
?>
