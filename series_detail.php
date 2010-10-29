<?php
	//session_cache_limiter('nocache');
	session_start();

	include_once('common.php');
	include_once("auto_logout.php");
	require_once('class/PersonalInfoScramble.class.php');
	require_once('class/DcmExport.class.php');
	require_once('class/validator.class.php');	

	//------------------------------------------------------------------------------------------------------------------
	// Import $_GET variables 
	//------------------------------------------------------------------------------------------------------------------
	$request = array('studyInstanceUID'  => (isset($_GET['studyInstanceUID']))  ? $_GET['studyInstanceUID']  : "",
		 		     'seriesInstanceUID' => (isset($_GET['seriesInstanceUID'])) ? $_GET['seriesInstanceUID'] : "",
				     'listTabName'       => (isset($_GET['listTabName'])) ? $_GET['listTabName'] : "");

	$data = array();
	//------------------------------------------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------------------------------------
	// Validation
	//-----------------------------------------------------------------------------------------------------------------
	$validator = new FormValidator();
	
	$validator->addRules(array(
		"studyInstanceUID" => array(
			"type" => "uid",
			"required" => 1,
			"errorMes" => "[ERROR] Parameter of URL (studyInstanceUID) is invalid."),
		"seriesInstanceUID" => array(
			"type" => "uid",
			"required" => 1,
			"errorMes" => "[ERROR] Parameter of URL (seriesInstanceUID) is invalid."),
		"listTabName" => array(
			"type" => "select",
			"options" => array("Today's series", "Series list"),
			"default" => "Series list",
			"adjValue" => "Series list")
		));				

	if($validator->validate($request))
	{
		$data = $validator->output;
		$data['errorMessage'] = "";
	}
	else
	{
		$data = $request;
		$params['errorMessage'] = implode('<br/>', $validator->errors);
	}
	
	//var_dump($data);
	//-----------------------------------------------------------------------------------------------------------------

	try
	{	
		if($data['errorMessage'] == "")
		{
			// Connect to SQL Server
			$pdo = new PDO($connStrPDO);	
	
			$sqlStr = "SELECT sr.sid, pt.patient_id, pt.patient_name, sm.path, sm.apache_alias," 
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
					
			$data['sid']               = $result[0];
			$data['patientID']         = $result[1];
			$data['patientName']       = $result[2];
			$data['sex']               = $result[7];
			$data['age']               = $result[8];
			$data['studyID']           = $result[9];
			$data['seriesID']          = $result[10];
			$data['seriesDate']        = $result[11];
			$data['seriesTime']        = $result[12];
			$data['modality']          = $result[13];
			$data['seriesDescription'] = $result[14];
			$data['bodyPart']          = $result[15];
				
			$data['encryptedPtID']   = PinfoScramble::encrypt($data['patientID'], $_SESSION['key']);
			$data['encryptedPtName'] = PinfoScramble::encrypt($data['patientName'], $_SESSION['key']);			
				
			$data['seriesDir'] = $result[3] . $DIR_SEPARATOR . $data['patientID'] . $DIR_SEPARATOR . $data['studyInstanceUID']
					           . $DIR_SEPARATOR . $data['seriesInstanceUID'];
						   
			$data['seriesDirWeb'] = $result[4]. $data['patientID'] . $DIR_SEPARATOR_WEB . $data['studyInstanceUID']
					              . $DIR_SEPARATOR_WEB . $data['seriesInstanceUID'];		   
						   
			$data['orgWidth'] = $result[5];
			$data['orgHeight'] = $result[6];
			
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
		
			if(!is_dir($data['seriesDir']))
			{
				$data['errorMessage'] = '[ERROR] Series dir is not exist.';
			}
			else
			{
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
				$data['dstFnameWeb'] .= $data['seriesDirWeb'] . $DIR_SEPARATOR_WEB
				                     .  $SUBDIR_JPEG . $DIR_SEPARATOR_WEB . $tmpFname;	
		
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
					DcmExport::createThumbnailJpg($data['srcFname'], $data['dstFname'], $JPEG_QUALITY, 1, 
												  $data['windowLevel'], $data['windowWidth']);
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
			
				//------------------------------------------------------------------------------------------------------
				// Retrieve tag data
				//------------------------------------------------------------------------------------------------------
				$tagArray = array();
			
				$sqlStr = "SELECT tag, entered_by FROM tag_list WHERE category=3 AND reference_id=? ORDER BY sid ASC";
			
				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindValue(1, $data['sid']);
				$stmt->execute();
				$tagNum = $stmt->rowCount();
				
				$tagArray = $stmt->fetchAll(PDO::FETCH_NUM);
				//------------------------------------------------------------------------------------------------------
			}
			//var_dump($data);	
		}
		
		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		require_once('smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
	
		$smarty->assign('data',     $data);
		$smarty->assign('tagArray', $tagArray);
	
		$smarty->display('series_detail.tpl');
		//-------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;

	
?>
