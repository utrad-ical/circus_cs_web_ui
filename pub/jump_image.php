<?php

	session_start();

	include_once("common.php");

	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables and validation
	//------------------------------------------------------------------------------------------------------------------
	$params = array();
	$validator = new FormValidator();

	$validator->addRules(array(
		"studyInstanceUID"  => array("type" => "uid"),
		"seriesInstanceUID" => array("type" => "uid"),
		"imgNum" => array(
			"type" => "int",
			"min" => "1",
			"errorMes" => "Image number is invalid.")
		));

	if($validator->validate($_POST))
	{
		$params = $validator->output;
		$params['errorMessage'] = "";

		$params['windowLevel'] = (isset($_POST['windowLevel']) && is_numeric($_POST['windowLevel'])) ? $_POST['windowLevel'] : 0;
		$params['windowWidth'] = (isset($_POST['windowWidth']) && is_numeric($_POST['windowWidth'])) ? $_POST['windowWidth'] : 0;
		$params['presetName']  = (isset($_POST['presetName'])) ? $_POST['presetName'] : "";
	}
	else
	{
		$params = $validator->output;
		$params['errorMessage'] = implode('<br/>', $validator->errors);
	}
	//------------------------------------------------------------------------------------------------------------------

	$dstData = array('errorMessage' => $params['errorMessage'],
					 'imgFname'     => '',
					 'imgNum'    => $params['imgNum'],
	                 'imgNumStr'    => sprintf("Img. No. %04d", $params['imgNum']));
	try
	{
		if($params['errorMessage'] == "")
		{
			// Connect to SQL Server
			$pdo = new PDO($connStrPDO);

			$sqlStr = "SELECT st.patient_id, sm.path, sm.apache_alias"
					. " FROM study_list st, series_list sr, storage_master sm"
				    . " WHERE sr.study_instance_uid=?"
				    . " AND sr.series_instance_uid=?"
				    . " AND sr.study_instance_uid=st.study_instance_uid"
				    . " AND sr.storage_id=sm.storage_id;";

			$result = PdoQueryOne($pdo, $sqlStr, array($params['studyInstanceUID'], $params['seriesInstanceUID']), 'ARRAY_NUM');

			$patientID = $result[0];

			$seriesDir = $result[1] . $DIR_SEPARATOR . $patientID
					   . $DIR_SEPARATOR . $params['studyInstanceUID']
					   . $DIR_SEPARATOR . $params['seriesInstanceUID'];

			$seriesDirWeb = $result[2]. $patientID
					      . $DIR_SEPARATOR_WEB . $params['studyInstanceUID']
					      . $DIR_SEPARATOR_WEB . $params['seriesInstanceUID'];

			$flist = array();
			$flist = GetDicomFileListInPath($seriesDir);

			//$fNum = count($flist);

			$subDir = $seriesDir . $DIR_SEPARATOR . $SUBDIR_JPEG;
			if(!is_dir($subDir))	mkdir($subDir);

			$tmpFname = $flist[$params['imgNum']-1];

			$srcFname = $seriesDir . $DIR_SEPARATOR . $tmpFname;

			// For compresed DICOM file
			$tmpFname = str_ireplace("c_", "_", $tmpFname);
			$tmpFname = substr($tmpFname, 0, strlen($tmpFname)-4);

			$dstBase   = $subDir . $DIR_SEPARATOR . $tmpFname;
			$dstFname  = $dstBase;
			$dstFnameWeb = $seriesDirWeb . $DIR_SEPARATOR_WEB . $SUBDIR_JPEG . $DIR_SEPARATOR_WEB . $tmpFname;

			$dumpFname = $dstFname . ".txt";

			if($params['presetName'] != "" && $params['presetName'] != "Auto")
			{
				$dstFname .= "_" . $params['presetName'];
				$dstFnameWeb .= "_" . $params['presetName'];
			}
			$dstFname .= '.jpg';
			$dstFnameWeb .= '.jpg';

			// Create thumbnail image
			if(is_file($dstFname)
			   || DcmExport::createThumbnailJpg($srcFname, $dstBase, $params['presetName'],
			                                    $JPEG_QUALITY, 1, $params['windowLevel'], $params['windowWidth']))
			{
				$dstData['imgFname'] = $dstFnameWeb;
			}
			else
			{
				$dstData['errorMessage'] = "[ERROR] Fail to create thumbnail image.";
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
							$dstData['sliceLocation'] = sprintf("%.2f", $dumpContent);
							break;
					}
				}
				fclose($fp);
			}
			else
			{
				$dstData['errorMessage'] = "[ERROR] Fail to open DICOM dump file.";
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
