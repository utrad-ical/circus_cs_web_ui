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
		
		// Get cache area
		$sqlStr = "SELECT storage_id, path FROM storage_master WHERE type=3 AND current_use='t'";
		$webCacheRes = DBConnector::query($sqlStr, NULL, 'ARRAY_ASSOC');
		$data['storageID'] = $webCacheRes['storage_id'];

		// Get parameters
		$sqlStr = "SELECT study_instance_uid, series_instance_uid,"
				. " patient_id, patient_name, storage_id, path,"
				. " image_width, image_height, sex, age, study_id,"
				. " series_number, series_date, series_time, modality,"
				. " series_description, body_part, image_number"
				. " FROM series_join_list"
				. " WHERE series_sid=?";

		$result = DBConnector::query($sqlStr, $data['sid'], 'ARRAY_NUM');

		$data['studyInstanceUID']  = $result[0];
		$data['seriesInstanceUID'] = $result[1];
		$data['patientID']         = $result[2];
		$data['patientName']       = $result[3];
		//$data['storageID']         = $result[4];
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
		$data['fNum']              = $result[17];

		$data['encryptedPtID']   = PinfoScramble::encrypt($data['patientID'], $_SESSION['key']);
		$data['encryptedPtName'] = PinfoScramble::encrypt($data['patientName'], $_SESSION['key']);

		$data['seriesDir'] = $result[5] . '/' . $data['patientID'] . '/' . $data['studyInstanceUID']
						 . '/' . $data['seriesInstanceUID'];

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

		$sqlStr = "SELECT * FROM grayscale_preset WHERE modality=? ORDER BY priority ASC";
		$result = DBConnector::query($sqlStr, $data['modality'], 'ALL_ASSOC');

		$data['presetArr'] = array();

		foreach($result as $key=>$item)
		{
			if($item['priority'] == 1)
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
		//else
		//{
		//	$data['srcFname'] = sprintf("%s%s%08d.dcm", $data['seriesDir'], $DIR_SEPARATOR, $data['imgNum']);
		//	
		//	$baseName = implode('_', array($data['seriesInstanceUID'],
		//									$data['imgNum'],
		//									$data['windowLevel'],
		//									$data['windowWidth'],
		//									$data['dispWidth'],
		//									$data['dispHeight']));
//
//			$baseName .= ".jpg";
//			//$baseName =	sprintf("%s.jpg", md5($baseName));
//
//			$data['dstFname'] = $webCacheRes['path'] . $DIR_SEPARATOR . $baseName;
//			$data['dstFnameWeb'] = $baseName;
//
//			//$dumpFname = sprintf("%s%s%08d.txt", $data['seriesDir'], $DIR_SEPARATOR, $data['imgNum']);
//			$dumpFname = sprintf("%s%s%s_%d.txt", $webCacheRes['path'], $DIR_SEPARATOR, $data['seriesInstanceUID'], $data['imgNum']);
//
//			if(!is_file($data['dstFname']) || !is_file($dumpFname))
//			{
//				DcmExport::createThumbnailJpg($data['srcFname'], $data['dstFname'], 100,
//											  $data['windowLevel'], $data['windowWidth'], $dumpFname);
//			}
//
//			$data['sliceNumber'] = 0;
//			$data['sliceLocation'] = 0;
//			
//			$fp = fopen($dumpFname, "r");
//
//			if($fp != NULL)
//			{
//				while($str = fgets($fp))
//				{
//					$dumpTitle   = strtok($str,":");
//					$dumpContent = strtok("\r\n");
//
//					switch($dumpTitle)
//					{
//						case 'Img. No.':
//						case 'Image No.':
//							$data['sliceNumber'] = $dumpContent;
//							break;
//
//						case 'Slice location':
//							$data['sliceLocation'] = sprintf("%.2f [mm]", $dumpContent);
//							break;
//					}
//				}
//				fclose($fp);
//			}
//		}
	}

	//--------------------------------------------------------------------------------------------------------------
	// Settings for Smarty
	//--------------------------------------------------------------------------------------------------------------
	$smarty = new SmartyEx();

	$smarty->assign('data',     $data);
	$smarty->display('series_detail.tpl');
	//-------------------------------------------------------------------------------------------------------------
}
catch (PDOException $e)
{
	var_dump($e->getMessage());
}
$pdo = null;

?>
