<?
	include('../../common.php');
	include('drawRocCurve_v1.1.php');

	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables 
	//------------------------------------------------------------------------------------------------------------------
	$execID     = $_POST['execID'];
	$curveType  = $_POST['curveType'];
	$inputPath  = $_POST['inputPath'];
	//------------------------------------------------------------------------------------------------------------------

	$dstData = array();

	//------------------------------------------------------------------------------------------------------------------
	// Road parameter file
	//------------------------------------------------------------------------------------------------------------------
	//$fp = fopen($inputPath."CAD-SummarizerResult_base.txt", "r"); 
	//
	//$dstData['caseNum']      = rtrim(fgets($fp));
	//$dstData['totalTpNum']   = rtrim(fgets($fp));
	//$dstData['totalFpNum']   = rtrim(fgets($fp));
	//$dstData['fnNum']        = rtrim(fgets($fp));
	//$dstData['underRocArea'] = sprintf("%.3f",rtrim(fgets($fp)));
	//
	//fclose($fp);
	//------------------------------------------------------------------------------------------------------------------

	$tmpFname = 'ROC' . $execID . '_' . microtime(true) . '.png';

	$curveFname = $WEB_UI_ROOT . $DIR_SEPARATOR . 'pub' . $DIR_SEPARATOR . 'tmp'
	            . $DIR_SEPARATOR . $tmpFname;

	CreateRocCurve($curveType, $inputPath, $curveFname);

	$dstData['imgFname'] = '../tmp/' . $tmpFname;

	echo json_encode($dstData);

?>