<?php
include('../../common.php');
include('drawRocCurve_v1.1.php');

//------------------------------------------------------------------------------------------------------------------
// Import $_POST variables 
//------------------------------------------------------------------------------------------------------------------
$jobID     = $_POST['jobID'];
$curveType = $_POST['curveType'];
//------------------------------------------------------------------------------------------------------------------

$dstData = array();

try
{
	$pdo = DBConnector::getConnection();

	// Get path of job
	$sqlStr = "SELECT sm.path FROM executed_plugin_list el, storage_master sm"
			. " WHERE el.job_id=? AND el.storage_id=sm.storage_id";
	$inputPath = DBConnector::query($sqlStr, array($jobID), 'SCALAR')
				. $DIR_SEPARATOR . $jobID . $DIR_SEPARATOR;

	// Get path of web cache 
	$sqlStr = "SELECT storage_id, path FROM storage_master WHERE type=3 AND current_use='t'";
	$result =  DBConnector::query($sqlStr, NULL, 'ARRAY_NUM');

	$cachePath = $result[1] . $DIR_SEPARATOR;
	$cachePathWeb = "../storage/" . $result[0] . '/';

	$tmpFname = 'ROC' . $jobID . '_' . microtime(true) . '.png';

	$curveFname = $cachePath . $tmpFname;
	$dstData['imgFname'] = $cachePathWeb . $tmpFname;

	CreateRocCurve($curveType, $inputPath, $curveFname);
}
catch (PDOException $e)
{
	var_dump($e->getMessage());
}

echo json_encode($dstData);

?>
