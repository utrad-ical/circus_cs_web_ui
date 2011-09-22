<?php
include('../../common.php');
include('drawRocCurve.php');

//----------------------------------------------------------------------------------------------------------------------
// Import $_POST variables 
//----------------------------------------------------------------------------------------------------------------------
$jobID      = $_POST['jobID'];
$pendigType = $_POST['pendigType'];
$curveType  = $_POST['curveType'];
//----------------------------------------------------------------------------------------------------------------------

$dstData = array();

try
{
	$pdo = DBConnector::getConnection();
	
	// Get path of job
	$sqlStr = "SELECT sm.path FROM executed_plugin_list el, storage_master sm"
			. " WHERE el.job_id=? AND el.storage_id=sm.storage_id";
	$inputPath = DBConnector::query($sqlStr, array($jobID), 'SCALAR')
				. $DIR_SEPARATOR . $jobID . $DIR_SEPARATOR;
	
	//------------------------------------------------------------------------------------------------------------------
	// Road parameter file
	//------------------------------------------------------------------------------------------------------------------
	$fp = fopen($inputPath."CAD-SummarizerResult_".$pendigType."_base.txt", "r"); 

	fgets($fp);
	$dstData['undispTpNum']  = rtrim(fgets($fp));
	$dstData['dispTpNum']    = rtrim(fgets($fp));
	$dstData['undispFpNum']  = rtrim(fgets($fp));
	$dstData['dispFpNum']    = rtrim(fgets($fp));
	$dstData['fnNum']        = rtrim(fgets($fp));
	$dstData['underRocArea'] = sprintf("%.3f",rtrim(fgets($fp)));	

	fclose($fp);
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Load dispTp file
	//------------------------------------------------------------------------------------------------------------------
	$totalTP = (int)($dstData['dispTpNum']) + (int)($dstData['undispTpNum']) + (int)($dstData['fnNum']);
	$dstData['dispTpOptionHtml'] = "";

	$fp = fopen($inputPath."CAD-SummarizerResult_".$pendigType."_dispTp.txt", "r"); 

	$maxDispTp = (int)(rtrim(fgets($fp)));

	for($i=0; $i<$maxDispTp; $i++)
	{
		$tmp = (int)(rtrim(fgets($fp)));

		$dstData['dispTpOptionHtml'] .= '<option value="' . sprintf("%.1f", (double)$tmp/$totalTP*100.0) . '">'
									 .  ($i+1) . '</option>';

		if($i==0)  $dstData['dispSensitivity'] = sprintf("%.1f", (double)$tmp/$totalTP*100.0);
	}

	fclose($fp);
	//------------------------------------------------------------------------------------------------------------------

	// Get path of web cache 
	$sqlStr = "SELECT storage_id, path FROM storage_master WHERE type=3 AND current_use='t'";
	$result =  DBConnector::query($sqlStr, NULL, 'ARRAY_NUM');

	$cachePath = $result[1] . $DIR_SEPARATOR;
	$cachePathWeb = "../storage/" . $result[0] . '/';

	$tmpFname = 'ROC' . $jobID . '_' . microtime(true) . '.png';

	$curveFname = $cachePath . $tmpFname;
	$dstData['imgFname'] = $cachePathWeb . $tmpFname;

	CreateRocCurve($pendigType, $curveType, $inputPath, $curveFname);
}
catch (PDOException $e)
{
	var_dump($e->getMessage());
}

echo json_encode($dstData);

?>