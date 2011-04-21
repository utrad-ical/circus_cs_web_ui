<?php

	function CreateThumbnailMPR($ifname, $ofname, $dstWidth)
	{
		$srcImg = @imagecreatefrompng($ifname);

		$srcWidth  = imagesx($srcImg);
		$srcHeight = imagesy($srcImg);
		$dstHeight = (int)($dstWidth / $srcWidth * $srcHeight);

		$dstImg = @imagecreatetruecolor($dstWidth, $dstHeight);

		imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $dstWidth, $dstHeight, $srcWidth, $srcHeight);
		imagealphablending($dstImg, false );
		imagepng($dstImg, $ofname, 9);

		imagedestroy($srcImg);
		imagedestroy($dstImg);
	}

	$anotation = array();

	$title = array('Normalized image', 'Curved MPR');

	$anotation[0][0] = "Sagittal";		$anotation[0][1] = "&nbsp;";
	$anotation[1][0] = "Coronal";		$anotation[1][1] = "vertebral body";
	$anotation[2][0] = "Coronal";		$anotation[2][1] = "anterior wall of the canal";
	$anotation[3][0] = "Coronal";		$anotation[3][1] = "center of the canal";
	$anotation[4][0] = "Coronal";		$anotation[4][1] = "posterior wall of the canal";

	$COL_WIDTH = 150;

	if($_SESSION['personalFBFlg'] || $_SESSION['consensualFBFlg'] || $_SESSION['groupID'] == 'demo')
	{
		if($registTime != "")
		{
			$registMsg = 'registered at ' . $registTime;
		}
	}

	//----------------------------------------------------------------------------------------------
	// Show images
	//----------------------------------------------------------------------------------------------
	$thumbnailImgFname = array();
	$orgImgFname = array();

	for($k=0; $k<2; $k++)
	{
		for($j=1; $j<=5; $j++)
		{
			$srcImgFname = sprintf("result%03d.png",  $k * 5 + $j);
			$thumbnailFname = sprintf("result%03d_thumb.png", $k * 5 + $j);

			$ifname = $params['pathOfCADReslut'] . $DIR_SEPARATOR . $srcImgFname;
			$ofname = $params['pathOfCADReslut'] . $DIR_SEPARATOR . $thumbnailFname;
			$dstWidth = $COL_WIDTH;

			if(!is_file($ofname))	CreateThumbnailMPR($ifname, $ofname, $dstWidth);

			$orgImgFname[$k][$j-1] = '../' . $params['webPathOfCADReslut'] . $DIR_SEPARATOR_WEB . $srcImgFname;
			$thumbnailImgFname[$k][$j-1] = '../' . $params['webPathOfCADReslut'] . $DIR_SEPARATOR_WEB . $thumbnailFname;
		}
	} // end for : $k
	//----------------------------------------------------------------------------------------------

	//----------------------------------------------------------------------------------------------
	// Create HTML for scoring interface
	//----------------------------------------------------------------------------------------------
	if($_SESSION['personalFBFlg'] || $_SESSION['consensualFBFlg'])
	{
		include("visual_scoring_interface.php");
	}
	//----------------------------------------------------------------------------------------------

	//----------------------------------------------------------------------------------------------
	// Make one-time ticket
	//----------------------------------------------------------------------------------------------
	$_SESSION['ticket'] = md5(uniqid().mt_rand());
	$params['ticket'] = $_SESSION['ticket'];
	//----------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Settings for Smarty
	//------------------------------------------------------------------------------------------------------------------
	$smarty = new SmartyEx();

	$smarty->assign('params', $params);

	$smarty->assign('consensualFBFlg',   $consensualFBFlg);
	$smarty->assign('registTime',        $registTime);
	$smarty->assign('registMsg',         $registMsg);
	$smarty->assign('orgImgFname',       $orgImgFname);
	$smarty->assign('thumbnailImgFname', $thumbnailImgFname);
	$smarty->assign('scoringHtml',       $scoringHtml);

	$smarty->display('cad_results/Spine-CPR_v2.2.tpl');
	//------------------------------------------------------------------------------------------------------------------

?>
