<?php

	$imgNum = (isset($_REQUEST['imgNum'])) ? $_REQUEST['imgNum'] : 1;

	$img = new Imagick();
	$img->readImage($pathOfCADReslut . $DIR_SEPARATOR . 'result' . sprintf("%03d", $imgNum) . '.png');
	$dispWidth  = $img->getImageWidth();
	$dispHeight = $img->getImageHeight();
	
	$consensualFBFlg = ($_SESSION['groupID'] == 'admin') ? 1 : 0;

	$stmt = $pdo->prepare('SELECT MAX(sub_id) FROM "fat_volumetry_v1.1" WHERE exec_id=?');
	$stmt->bindParam(1, $param['execID']);
	$stmt->execute();
	$maxImgNum = $stmt->fetchColumn();

	$orgImg = $webPathOfCADReslut . '/ct' . sprintf("%03d", $imgNum) . '.png';
	$resImg = $webPathOfCADReslut . '/result' . sprintf("%03d", $imgNum) . '.png';
	
	//------------------------------------------------------------------------------------------------------------------
	// Measuring results
	//------------------------------------------------------------------------------------------------------------------
	$stmt = $pdo->prepare('SELECT * FROM "fat_volumetry_v1.1" WHERE exec_id=? AND sub_id =?');
	$stmt->execute(array($param['execID'], $imgNum));

	$data = $stmt->fetch(PDO::FETCH_ASSOC);
	//------------------------------------------------------------------------------------------------------------------
	
	//------------------------------------------------------------------------------------------------------------------
	// Settings for Smarty
	//------------------------------------------------------------------------------------------------------------------
	require_once('../smarty/SmartyEx.class.php');
	$smarty = new SmartyEx();

	$smarty->assign('param', $param);
	$smarty->assign('data',  $data);

	$smarty->assign('consensualFBFlg', $consensualFBFlg);

	$smarty->assign('dispWidth',  $dispWidth);
	$smarty->assign('dispHeight', $dispHeight);

	$smarty->assign('imgNum',     $imgNum);
	$smarty->assign('maxImgNum',  $maxImgNum);

	$smarty->assign('sliceOffset',     $sliceOffset);

	$smarty->assign('patientID',         $patientID);
	$smarty->assign('patientName',       $patientName);	
	$smarty->assign('sex',               $sex);
	$smarty->assign('age',               $age);
	$smarty->assign('studyID',           $studyID);
	$smarty->assign('studyDate',         $studyDate);
	$smarty->assign('seriesID',          $seriesID);
	$smarty->assign('modality',          $modality);
	$smarty->assign('seriesDescription', $seriesDescription);
	$smarty->assign('seriesDate',        $seriesDate);
	$smarty->assign('seriesTime',        $seriesTime);
	$smarty->assign('bodyPart',          $bodyPart);
	
	
	$smarty->assign('orgImg', $orgImg);	
	$smarty->assign('resImg', $resImg);	
	
	$smarty->display('cad_results/fat_volumetry_v1.tpl');
	//------------------------------------------------------------------------------------------------------------------		
?>
