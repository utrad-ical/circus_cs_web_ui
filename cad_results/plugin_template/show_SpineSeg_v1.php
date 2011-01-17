<?php

	$imgNum = (isset($_REQUEST['imgNum'])) ? $_REQUEST['imgNum'] : 1;

	// Get width and height of PNG image Using GD library
	$img = @imagecreatefromjpeg($params['pathOfCADReslut'] . $DIR_SEPARATOR . 'in' . sprintf("%04d", $imgNum) . '.jpg');
	$dispWidth  = imagesx($img) * 0.75;
	$dispHeight = imagesy($img) * 0.75;
	imagedestroy($img);	
	
	$consensualFBFlg = ($_SESSION['groupID'] == 'admin') ? 1 : 0;

	$orgImg = $params['webPathOfCADReslut'] . '/in' . sprintf("%04d", $imgNum) . '.jpg';
	$resImg = $params['webPathOfCADReslut'] . '/out' . sprintf("%04d", $imgNum) . '.jpg';
	
	$segResultFile = '../' . $params['webPathOfCADReslut'] . '/' . $params['seriesInstanceUID'] . '_opt.zip';
	
	$maxImgNum = 101;

	//------------------------------------------------------------------------------------------------------------------
	// Settings for Smarty
	//------------------------------------------------------------------------------------------------------------------
	require_once('../smarty/SmartyEx.class.php');
	$smarty = new SmartyEx();

	$smarty->assign('params', $params);
	$smarty->assign('data',   $data);

	$smarty->assign('consensualFBFlg', $consensualFBFlg);

	$smarty->assign('dispWidth',  $dispWidth);
	$smarty->assign('dispHeight', $dispHeight);

	$smarty->assign('imgNum',     $imgNum);
	$smarty->assign('maxImgNum',  $maxImgNum);

	$smarty->assign('segResultFile',      $segResultFile);
	$smarty->assign('webPathOfCADReslut', $webPathOfCADReslut);

	$smarty->assign('orgImg', $orgImg);	
	$smarty->assign('resImg', $resImg);	

	//$smarty->assign('scoringHTML', $scoringHTML);	
	//$smarty->assign('registTime',  $registTime);
	//$smarty->assign('scoreStr',    $scoreStr);
	//$smarty->assign('evalComment',  $evalComment);
	
	$smarty->display('cad_results/spine_seg_v1.tpl');
	//------------------------------------------------------------------------------------------------------------------
?>
