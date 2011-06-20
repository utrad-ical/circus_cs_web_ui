<?php
include_once("common.php");
Auth::checkSession(false);

//----------------------------------------------------------------------------------------------------------------------
// Import $_POST variables and validation
//----------------------------------------------------------------------------------------------------------------------
$params = array();
$validator = new FormValidator();

$validator->addRules(array(
	'seriesInstanceUID' => array(
		'type' => 'uid',
		'required' => true,
		'errorMes' => 'Series Instance UID is invalid.'
	),
	'imgNum' => array(
		'type' => 'int',
		'min' => '1',
		'required' => true),
	'windowLevel' => array(
		'type' => 'int',
		'min' => '-32768',
		'max' => '32767',
		'default' => '0'),
	'windowWidth' => array(
		'type' => 'int',
		'min' => '0',
		'max' => '65536',
		'default' => '0'),
	'imgWidth' => array(
		'type' => 'int',
		'min' => '0',
		'default' => '0'),
	'imgHeight' => array(
		'type' => 'int',
		'min' => '0',
		'default' => '0')
	));

if($validator->validate($_POST))
{
	$params = $validator->output;
	$params['errorMessage'] = "";
}
else
{
	$params = $validator->output;
	$params['errorMessage'] = implode("\n", $validator->errors);
	echo(json_encode($params)); exit;
}
//----------------------------------------------------------------------------------------------------------------------

$dstData = array(
	'errorMessage' => $params['errorMessage'],
	'imgFname'     => '',
	'imgNum'    => $params['imgNum'],
	'imgNumStr'    => sprintf("Img. No. %04d", $params['imgNum'])
);

try
{
	if($params['errorMessage'] == "")
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();
		
		// Get cache area
		$sqlStr = "SELECT storage_id, path FROM storage_master"
				. "  WHERE type=3 AND current_use='t'";
		$webCacheRes = DBConnector::query($sqlStr, NULL, 'ARRAY_ASSOC');

		$sqlStr = "SELECT path, patient_id, study_instance_uid, image_width, image_height"
				. " FROM series_join_list"
				. " WHERE series_instance_uid=?";

		$result = DBConnector::query($sqlStr, $params['seriesInstanceUID'], 'ARRAY_NUM');

		// check original size
		if($params['imgWidth']  == $result[3])  $params['imgWidth']  = 0;
		if($params['imgHeight'] == $result[4])  $params['imgHeight'] = 0;

		$baseName = sprintf('%s_%d_%d_%d_%d_%d.jpg', $params['seriesInstanceUID'],
													 $params['imgNum'],
													 $params['windowLevel'],
													 $params['windowWidth'],
													 $params['imgWidth'],
													 $params['imgHeight']);

		$dstFname = $webCacheRes['path'] . $DIR_SEPARATOR . $baseName;
		$dstData['imgFname'] = 'storage/' . $webCacheRes['storage_id'] . '/' . $baseName;

		$dumpFname = sprintf("%s%s%s_%d.txt", $webCacheRes['path'],
											  $DIR_SEPARATOR,
											  $params['seriesInstanceUID'],
											  $params['imgNum']);

		if(!is_file($dstFname) || !is_file($dumpFname))
		{
			$seriesDir = $result[0] . $DIR_SEPARATOR . $result[1]
					. $DIR_SEPARATOR . $result[2]
					. $DIR_SEPARATOR . $params['seriesInstanceUID'];
					
			$srcFname = sprintf("%s%s%08d.dcm", $seriesDir, $DIR_SEPARATOR, $params['imgNum']);

			if(!DcmExport::createThumbnailJpg($srcFname, $dstFname, $dumpFname, 100,
											 $params['windowLevel'], $params['windowWidth'],
											 $params['imgWidth'], $params['imgHeight']))
			{
				$dstData['imgFname'] = "";
				$dstData['errorMessage'] = "[ERROR] Fail to create thumbnail image.";
			}
		}

		// Get slice number and slice location from dump data
		$dstData['sliceNumber'] = "";
		$dstData['sliceLocation'] = "";
		
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
			$dstData['errorMessage'] = "[ERROR] Fail to open dump file.";
		}
	}
	//header('');
	echo json_encode($dstData);
}
catch (PDOException $e)
{
	var_dump($e->getMessage());
}
$pdo = null;

?>
