<?php

	include("common.php");

	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//--------------------------------------------------------------------------------------------------------
	if(ini_get('magic_quotes_gpc') == "1")  $inFname = stripslashes($_REQUEST['inFname']);
	else                                    $inFname = $_REQUEST['inFname'];
	
	if(ini_get('magic_quotes_gpc') == "1")  $outFname = stripslashes($_REQUEST['outFname']);
	else                                    $outFname = $_REQUEST['outFname'];
	
	$quality = (isset($_REQUEST['quality'])) ? $_REQUEST['quality'] : $JPEG_QUALITY;
	$dumpFlg = (isset($_REQUEST['dumpFlg'])) ? $_REQUEST['dumpFlg'] : 0;

	$windowLevel = (isset($_REQUEST['windowLevel'])) ? $_REQUEST['windowLevel'] : 0;
	$windowWidth = (isset($_REQUEST['windowWidth'])) ? $_REQUEST['windowWidth'] : 0;
	
	$imgNum = $_REQUEST['imgNum'];
	
	$sliceLocationFlg = (isset($_REQUEST['sliceLocationFlg'])) ? $_REQUEST['sliceLocationFlg'] : 0;
	$sliceOrigin = $_REQUEST['sliceOrigin'];
	$slicePitch  = $_REQUEST['slicePitch'];
	$sliceOffset = $_REQUEST['sliceOffset'];
	
	//--------------------------------------------------------------------------------------------------------

	//--------------------------------------------------------------------------------------------------------
	// Convert from DICOM file to JPEG file
	//--------------------------------------------------------------------------------------------------------
	if(!is_file($outFname))
	{
		$cmdStr  = $cmdForProcess . ' "' . $cmdCreateThumbnail . ' ' . $inFname . ' ' . $outFname . ' '
		         . $quality . ' ' . $dumpFlg . ' ' . $windowLevel . ' ' . $windowWidth . '"';
		
		shell_exec($cmdStr);
	}
	//--------------------------------------------------------------------------------------------------------
	
	//--------------------------------------------------------------------------------------------------------
	// Load converted image (100 times?)
	//--------------------------------------------------------------------------------------------------------
	$img = new Imagick();
	
	for($i=0; $i<100; $i++)
	{
		if($img->readImage($outFname) == TRUE)	break;
		else
		{
			if($i==99)	$img->readImage("images/fail_convert.jpg");
			else		usleep(100000);
		}
	}
	//--------------------------------------------------------------------------------------------------------

	//--------------------------------------------------------------------------------------------------------
	// Display converted image (including size conversionj
	//--------------------------------------------------------------------------------------------------------
	$width  = $img->getImageWidth();
	$height = $img->getImageHeight();

	if(isset($_REQUEST['dispWidth']) && isset($_REQUEST['dispHeight']))
	{
		$dispWidth  = $_REQUEST['dispWidth'];
		$dispHeight = $_REQUEST['dispHeight'];	
	
		if($dispWidth != $width || $dispHeight != $height)
		{
			$img->resizeImage($dispWidth, $dispHeight, Imagick::FILTER_SINC,1);
		}
	}

	$color=new ImagickPixel();
  	$color->setColor("white");
	
	$draw = new ImagickDraw($dispWidth, $dispHeight);

	$draw->setStrokeColor($color);
	$draw->setFont('Arial-Bold');
	$draw->setFontSize(14);
	$draw->setFillAlpha(1.0);
	$draw->setStrokeAlpha(0.0);			
	$draw->setFillColor($color);
	$draw->setTextAntialias(TRUE);
	
	if($sliceLocationFlg == 1)
	{
		$sliceLoc = ($imgNum - $sliceOffset - 1) * $slicePitch + $sliceOrigin;
		$draw->annotation (4, 15, sprintf("Img. No. %04d", $imgNum));
		$draw->setTextAlignment(imagick::ALIGN_RIGHT);
		$draw->annotation ($dispWidth-4, 15, sprintf("Slice Loc. %.2f [mm]", $sliceLoc));
	}
	else
	{
		$draw->annotation (4, 15, sprintf("Img. No. %04d", $imgNum));
	}

	$img->drawImage($draw);
	$draw->destroy();

	header("Content-type: image/jpg");
	echo $img;
	
	$img->destroy();
	//--------------------------------------------------------------------------------------------------------

?>