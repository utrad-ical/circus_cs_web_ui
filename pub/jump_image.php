<?php

/**
 * Converts DICOM image on-demand and returns the path to that file.
 */

include_once("common.php");
//Auth::checkSession(false);

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
		'required' => true
	),
	'windowLevel' => array(
		'type' => 'int',
		'min' => '-32768',
		'max' => '32767',
		'default' => '0'
	),
	'windowWidth' => array(
		'type' => 'int',
		'min' => '0',
		'max' => '65536',
		'default' => '0'
	),
	'imgWidth' => array(
		'type' => 'int',
		'min' => '0',
		'default' => '0'
	),
	'imgHeight' => array(
		'type' => 'int',
		'min' => '0',
		'default' => '0'
	)
));

$dstData = array(
	'status'   => 'OK',
	'imgFname' => ''
);

try
{
	//if (Auth::currentUser() === null)
	//	throw new Exception('Session not established properly.');

	if (!$validator->validate($_REQUEST))
		throw new Exception(implode("\n", $validator->errors));
	$req = $validator->output;
	$dstData['request'] = $req;


	$pdo = DBConnector::getConnection();

	// Get cache area
	$sqlStr = "SELECT storage_id, path FROM storage_master"
			. "  WHERE type=3 AND current_use='t'";
	$webCacheRes = DBConnector::query($sqlStr, NULL, 'ARRAY_ASSOC');
	if (!is_array($webCacheRes))
		throw new Exception('Web cache directory not configured');

	$sqlStr = "SELECT path, patient_id, study_instance_uid, image_width, image_height"
			. " FROM series_join_list"
			. " WHERE series_instance_uid=?";
	$result = DBConnector::query($sqlStr, array($req['seriesInstanceUID']), 'ARRAY_ASSOC');

	// check original size
	if($req['imgWidth']  == $result['image_width'] && $req['imgHeight'] == $result['image_height'])
	{
		$req['imgWidth']  = 0;
		$req['imgHeight'] = 0;
	}
	if ($req['windowWidth'] == 0) $req['windowLevel'] = 0;

	$baseName = sprintf(
		'%s_%d_%d_%d_%d_%d.jpg',
		$req['seriesInstanceUID'],
		$req['imgNum'],
		$req['windowLevel'],
		$req['windowWidth'],
		$req['imgWidth'],
		$req['imgHeight']
	);

	$dstFname = $webCacheRes['path'] . $DIR_SEPARATOR . $baseName;
	$dstData['imgFname'] = 'storage/' . $webCacheRes['storage_id'] . '/' . $baseName;

	$dumpFname = sprintf(
		"%s%s%s_%d.txt",
		$webCacheRes['path'],
		$DIR_SEPARATOR,
		$req['seriesInstanceUID'],
		$req['imgNum']);

	if(!is_file($dstFname) || !is_file($dumpFname))
	{
		$seriesDir = $result['path'] . $DIR_SEPARATOR . $result['patient_id']
			. $DIR_SEPARATOR . $result['study_instance_uid']
			. $DIR_SEPARATOR . $req['seriesInstanceUID'];

		$srcFname = sprintf("%s%s%08d.dcm", $seriesDir, $DIR_SEPARATOR, $req['imgNum']);

		$dcmResult = DcmExport::createThumbnailJpg(
			$srcFname, $dstFname, $dumpFname, 100,
			$req['windowLevel'], $req['windowWidth'],
			$req['imgWidth'], $req['imgHeight']
		);
		if (!$dcmResult)
			throw new Exception('Image convertion failed.');
	}
	else
	{
		$dstData['cached'] = 'true';
	}
	$dstData['windowLevel'] = $req['windowLevel'];
	$dstData['windowWidth'] = $req['windowWidth'];
	$dstData['sliceNumber'] = $req['imgNum'];

	// Get and slice location from dump data
	$fp = @fopen($dumpFname, "r");
	if($fp == null)
		throw new Exception('Could not open dump file.');
	while($str = fgets($fp))
	{
		$dumpTitle   = strtok($str,":");
		$dumpContent = strtok("\r\n");
		switch($dumpTitle)
		{
			case 'Slice location':
				$dstData['sliceLocation'] = sprintf("%.2f", $dumpContent);
				break;
		}
	}
	fclose($fp);
	if (!isset($dstData['sliceLocation']))
		throw new Exception('Could not determine slice location from dump file.');

	echo json_encode($dstData);
}
catch (Exception $e)
{
	$message = $e->getMessage();
	if ($e instanceof LogicException)
	{
		$message .= "\n" . $e->getTraceAsString();
	}
	echo json_encode(array(
		'status' => 'OperationError',
		'error' => array('message' => $message)
	));
}


?>
