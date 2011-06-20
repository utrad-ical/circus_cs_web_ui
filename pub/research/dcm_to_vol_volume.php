<?php
$params['toTopDir'] = "../";
include("../common.php");
Auth::checkSession(false);

$errorFlg = 0;

if(!Auth::currentUser()->hasPrivilege(Auth::VOLUME_DOWNLOAD))
{
	$errorFlg = 1;
}

//------------------------------------------------------------------------------------------------------------------
// Import $_POST variables and validation
//------------------------------------------------------------------------------------------------------------------
$params = array();
$validator = new FormValidator();

$validator->addRules(array(
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

//-----------------------------------------------------------------------------------------------------------------

if(!$errorFlg)
{
	try
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		// Get cache area
		$sqlStr = "SELECT path FROM storage_master WHERE type=3 AND current_use='t'";
		$webCachePath = DBConnector::query($sqlStr, NULL, 'SCALAR');

		$sqlStr = "SELECT path, patient_id, study_instance_uid"
				. " FROM series_join_list"
				. " WHERE series_instance_uid=?";

		$result = DBConnector::query($sqlStr, $params['seriesInstanceUID'], 'ARRAY_NUM');

		if(!is_array($result))
		{
			$errorFlg = 1;
		}
		else
		{
			// Prevent timeout error
			set_time_limit(0);

			$seriesDir = $result[0] . $DIR_SEPARATOR . $result[1]
						. $DIR_SEPARATOR . $result[2]
						. $DIR_SEPARATOR .  $params['seriesInstanceUID'];

			$baseName = $webCachePath . $DIR_SEPARATOR . $params['seriesInstanceUID'];
			$dstFileName = $baseName . ".zip";

			if(!is_file($dstFileName))
			{
				//--------------------------------------------------------------------------------------------------
				// Convert DICOM files to Volume-One data
				//--------------------------------------------------------------------------------------------------
				$cmdStr = $cmdForProcess . ' "' . $cmdDcmToVolume . ' ' . $seriesDir . ' ' . $webCachePath
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
				//------------------------------------------------------------------------------------------------------
			}
		}
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
}
echo $errorFlg;

?>
