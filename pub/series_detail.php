<?php
	include_once('common.php');
	Auth::checkSession();

	//------------------------------------------------------------------------------------------------------------------
	// Import $_GET variables and validation
	//------------------------------------------------------------------------------------------------------------------
	$data = array();
	$validator = new FormValidator();

	$validator->addRules(array(
		"sid" => array(
			"type" => "int",
			"min" => 1,
			"errorMes" => "[ERROR] Series sid is invalid."),
		"listTabName" => array(
			"type" => "select",
			"options" => array("Today's series", "Series list"),
			"default" => "Series list",
			"adjValue" => "Series list")
		));

	if($validator->validate($_GET))
	{
		$data = $validator->output;
		$data['errorMessage'] = "";
	}
	else
	{
		$data = $validator->output;
		$data['errorMessage'] = implode('<br/>', $validator->errors);
	}

	//var_dump($data);
	//-----------------------------------------------------------------------------------------------------------------

	try
	{
		if($data['errorMessage'] == "")
		{
			// Connect to SQL Server
			$pdo = DBConnector::getConnection();

			$sqlStr = "SELECT st.study_instance_uid, sr.series_instance_uid,"
					. " pt.patient_id, pt.patient_name, sm.storage_id, sm.path,"
					. " sr.image_width, sr.image_height, pt.sex, st.age, st.study_id,"
					. " sr.series_number, sr.series_date, sr.series_time, sr.modality,"
					. " sr.series_description, sr.body_part"
					. " FROM patient_list pt, study_list st, series_list sr, storage_master sm "
					. " WHERE sr.sid=?"
					. " AND st.study_instance_uid=sr.study_instance_uid"
					. " AND pt.patient_id=st.patient_id"
					. " AND sr.storage_id=sm.storage_id;";

			$result = DBConnector::query($sqlStr, $data['sid'], 'ARRAY_NUM');

			$data['studyInstanceUID']  = $result[0];
			$data['seriesInstanceUID'] = $result[1];
			$data['patientID']         = $result[2];
			$data['patientName']       = $result[3];
			$data['storageID']         = $result[4];
			$data['orgWidth']          = $result[6];
			$data['orgHeight']         = $result[7];
			$data['sex']               = $result[8];
			$data['age']               = $result[9];
			$data['studyID']           = $result[10];
			$data['seriesID']          = $result[11];
			$data['seriesDate']        = $result[12];
			$data['seriesTime']        = $result[13];
			$data['modality']          = $result[14];
			$data['seriesDescription'] = $result[15];
			$data['bodyPart']          = $result[16];

			$data['encryptedPtID']   = PinfoScramble::encrypt($data['patientID'], $_SESSION['key']);
			$data['encryptedPtName'] = PinfoScramble::encrypt($data['patientName'], $_SESSION['key']);

			$data['subPath'] = $data['patientID'] . '/' . $data['studyInstanceUID']
							 . '/' . $data['seriesInstanceUID'];

			$data['seriesDir'] = $result[5] . '/' . $data['subPath'];

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

			//---------------------------------------------------------------------------------------------------------
			// Set default window level, window width, and preset name
			//---------------------------------------------------------------------------------------------------------
			$data['windowLevel']  = 0;
			$data['windowWidth']  = 0;
			$data['presetName']   = "";

			$sqlStr = "SELECT * FROM grayscale_preset WHERE modality=? ORDER BY priolity ASC";
			$result = DBConnector::query($sqlStr, $data['modality'], 'ALL_ASSOC');

			$data['presetArr'] = array();

			foreach($result as $key=>$item)
			{
				if($item['priolity'] == 1)
				{
					$data['windowLevel'] = $item['window_level'];
					$data['windowWidth'] = $item['window_width'];
					$data['presetName']  = $item['preset_name'];
				}

				$data['presetArr'][$key*3]   = $item['preset_name'];
				$data['presetArr'][$key*3+1] = $item['window_level'];
				$data['presetArr'][$key*3+2] = $item['window_width'];
			}

			$data['grayscaleStr'] = implode('^', $data['presetArr']);
			$data['presetNum'] = count($data['presetArr'])/3;
			//---------------------------------------------------------------------------------------------------------

			//---------------------------------------------------------------------------------------------------------
			// Set file name of thumbnail image or Create thumbnail image
			//---------------------------------------------------------------------------------------------------------
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
				$dstBase = $data['dstFname'];
				$data['dstFnameWeb'] .= $data['subPath'] . '/' . $SUBDIR_JPEG . '/' . $tmpFname;

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
					DcmExport::createThumbnailJpg($data['srcFname'], $dstBase, $data['presetName'], $JPEG_QUALITY,
					                               1, $data['windowLevel'], $data['windowWidth']);
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
			}

			//----------------------------------------------------------------------------------------------------------
			// Retrieve tag data
			//----------------------------------------------------------------------------------------------------------
			$sqlStr = "SELECT tag, entered_by FROM tag_list WHERE category=3 AND reference_id=? ORDER BY sid ASC";
			$tagArray = DBConnector::query($sqlStr, $data['sid'], 'ALL_NUM');
			//----------------------------------------------------------------------------------------------------------
		}

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
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
