<?php
	//session_cache_limiter('nocache');
	session_start();

	include("../../common.php");

	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables
	//------------------------------------------------------------------------------------------------------------------
	$version = (isset($_POST['version']))  ? $_POST['version']  : 1;
	$execID = (isset($_POST['execID']))  ? $_POST['execID']  : "";
	$imgNum = (isset($_POST['imgNum'])) ? $_POST['imgNum'] : 1;
	$orgImgFname = (isset($_POST['orgImgFname']))  ? $_POST['orgImgFname']  : "";
	$resImgFname = (isset($_POST['resImgFname']))  ? $_POST['resImgFname']  : "";
	//------------------------------------------------------------------------------------------------------------------

	$dstData = array('imgFname'  => '',
	                 'imgNumStr' => sprintf("Img. No. %04d", $imgNum));

	try
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		//--------------------------------------------------------------------------------------------------------------
		// Set Image file name
		//--------------------------------------------------------------------------------------------------------------
		$dstData['orgImgFname'] = sprintf("%s%03d.png", substr($orgImgFname, 0, strlen($orgImgFname)-7), $imgNum);
		$dstData['resImgFname'] = sprintf("%s%03d.png", substr($resImgFname, 0, strlen($resImgFname)-7), $imgNum);
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// Measurment results
		//--------------------------------------------------------------------------------------------------------------
		$stmt = $pdo->prepare('SELECT * FROM "fat_volumetry_v' . $version . '" WHERE exec_id=? AND sub_id =?');
		$stmt->execute(array($execID, $imgNum));

		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		$dstData['dcmSliceNum']    = $result['image_num'];
		$dstData['sliceLocation']  = sprintf("%.2f", $result['slice_location']);
		$dstData['bodyTrunkArea']  = sprintf("%.2f", $result['body_trunk_area']);
		$dstData['satArea']        = sprintf("%.2f", $result['sat_area']);
		$dstData['vatArea']        = sprintf("%.2f", $result['vat_area']);
		$dstData['areaRatio']      = sprintf("%.3f", $result['area_ratio']);
		$dstData['boundaryLength'] = sprintf("%.2f", $result['boundary_length']);
		//--------------------------------------------------------------------------------------------------------------

		echo json_encode($dstData);
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;

?>
