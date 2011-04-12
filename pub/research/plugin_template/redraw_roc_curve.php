<?php
	include('../../common.php');
	include('drawRocCurve.php');

	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables 
	//------------------------------------------------------------------------------------------------------------------
	$jobID      = $_POST['jobID'];
	$pendigType = $_POST['pendigType'];
	$curveType  = $_POST['curveType'];
	$inputPath  = $_POST['inputPath'];
	//------------------------------------------------------------------------------------------------------------------

	$dstData = array();

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

	$tmpFname = 'ROC' . $jobID . '_' . microtime(true) . '.png';

	$curveFname = $WEB_UI_ROOT . $DIR_SEPARATOR . 'pub' . $DIR_SEPARATOR . 'tmp'
	            . $DIR_SEPARATOR . $tmpFname;

	CreateRocCurve($pendigType, $curveType, $inputPath, $curveFname);

	$dstData['imgFname'] = '../tmp/' . $tmpFname;

	echo json_encode($dstData);

?>