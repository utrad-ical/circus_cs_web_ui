<?php

	$imgNum = (isset($_REQUEST['imgNum'])) ? $_REQUEST['imgNum'] : 1;

	// Get width and height of PNG image Using GD library
	$img = @imagecreatefrompng($params['pathOfCADReslut'] . $DIR_SEPARATOR . 'result' . sprintf("%03d", $imgNum) . '.png');
	$dispWidth  = imagesx($img);
	$dispHeight = imagesy($img);
	imagedestroy($img);

	$consensualFBFlg = ($_SESSION['groupID'] == 'admin') ? 1 : 0;

	$stmt = $pdo->prepare('SELECT MAX(sub_id) FROM "fat_volumetry_v1.2" WHERE exec_id=?');
	$stmt->bindParam(1, $params['execID']);
	$stmt->execute();
	$maxImgNum = $stmt->fetchColumn();

	$orgImg = $params['webPathOfCADReslut'] . '/ct' . sprintf("%03d", $imgNum) . '.png';
	$resImg = $params['webPathOfCADReslut'] . '/result' . sprintf("%03d", $imgNum) . '.png';

	//------------------------------------------------------------------------------------------------------------------
	// Measuring results
	//------------------------------------------------------------------------------------------------------------------
	$stmt = $pdo->prepare('SELECT * FROM "fat_volumetry_v1.2" WHERE exec_id=? AND sub_id =?');
	$stmt->execute(array($params['execID'], $imgNum));

	$data = $stmt->fetch(PDO::FETCH_ASSOC);
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Settings for Smarty
	//------------------------------------------------------------------------------------------------------------------
	$smarty = new SmartyEx();

	$smarty->assign('params', $params);
	$smarty->assign('data',   $data);

	$smarty->assign('consensualFBFlg', $consensualFBFlg);

	$smarty->assign('dispWidth',  $dispWidth);
	$smarty->assign('dispHeight', $dispHeight);

	$smarty->assign('imgNum',     $imgNum);
	$smarty->assign('maxImgNum',  $maxImgNum);

	$smarty->assign('sliceOffset',     $sliceOffset);

	$smarty->assign('orgImg', $orgImg);
	$smarty->assign('resImg', $resImg);

	$smarty->display('cad_results/fat_volumetry_v1.tpl');
	//------------------------------------------------------------------------------------------------------------------
?>
