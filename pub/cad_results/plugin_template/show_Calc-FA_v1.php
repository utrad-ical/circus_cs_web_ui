<?php

	$imgNum = (isset($_REQUEST['imgNum'])) ? $_REQUEST['imgNum'] : 1;

	// Get width and height of PNG image Using GD library
	$img = @imagecreatefrompng($params['pathOfCADReslut'] . $DIR_SEPARATOR . 'result' . sprintf("%04d", $imgNum) . '.png');
	$dispWidth  = imagesx($img);
	$dispHeight = imagesy($img);
	imagedestroy($img);

	$stmt = $pdo->prepare('SELECT MAX(sub_id) FROM "calc_fa_v1" WHERE job_id=?');
	$stmt->bindParam(1, $params['jobID']);
	$stmt->execute();
	$chNum = $stmt->fetchColumn();

	$stmt = $pdo->prepare('SELECT image_number FROM series_list WHERE series_instance_uid=?');
	$stmt->bindParam(1, $params['seriesInstanceUID']);
	$stmt->execute();
	$totalImgNum = $stmt->fetchColumn();

	//------------------------------------------------------------------------------------------------------------------
	// Measuring results
	//------------------------------------------------------------------------------------------------------------------
	$stmt = $pdo->prepare('SELECT * FROM "calc_fa_v1" WHERE job_id=? ORDER BY sub_id ASC');
	$stmt->bindParam(1, $params['jobID']);
	$stmt->execute();
	$data = $stmt->fetchAll(PDO::FETCH_NUM);
	//------------------------------------------------------------------------------------------------------------------

	$b0Img = $params['webPathOfCADReslut'] . '/b0_' . sprintf("%04d", $imgNum) . '.png';
	$resImg = $params['webPathOfCADReslut'] . '/result' . sprintf("%04d", $imgNum) . '.png';

	//------------------------------------------------------------------------------------------------------------------
	// Settings for Smarty
	//------------------------------------------------------------------------------------------------------------------
	$smarty = new SmartyEx();

	$smarty->assign('params', $params);
	$smarty->assign('data',   $data);

	$smarty->assign('dispWidth',  $dispWidth);
	$smarty->assign('dispHeight', $dispHeight);

	$smarty->assign('imgNum',     $imgNum);
	$smarty->assign('maxImgNum',  (int)($totalImgNum/$chNum));

	$smarty->assign('b0Img',  $b0Img);
	$smarty->assign('resImg', $resImg);

	$smarty->display('cad_results/Calc-FA_v1.tpl');
	//------------------------------------------------------------------------------------------------------------------
?>
