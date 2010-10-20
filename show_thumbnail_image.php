<?php

	include("common.php");

	//------------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//------------------------------------------------------------------------------------------------------------------
	if(ini_get('magic_quotes_gpc') == "1")  $inFname = stripslashes($_REQUEST['inFname']);
	else                                    $inFname = $_REQUEST['inFname'];
	
	$pathParts = pathinfo($inFname);
	if(!is_dir($pathParts['dirname']) && preg_match('/\\.dcm$/i', $pathParts['basename'])==0) $inFname = "";
	
	if(ini_get('magic_quotes_gpc') == "1")  $outFname = stripslashes($_REQUEST['outFname']);
	else                                    $outFname = $_REQUEST['outFname'];

	$pathParts = pathinfo($outFname);
	if(!is_dir($pathParts['dirname']) && preg_match('/\\.jpg$/i', $pathParts['basename'])==0) $outFname = "";
	
	$quality = (isset($_REQUEST['quality']) && ctype_digit($_REQUEST['quality'])) ? $_REQUEST['quality'] : $JPEG_QUALITY;
	if($quality < 0 || $quality > 100)  $quality = $JPEG_QUALITY;
	
	$dumpFlg = (isset($_REQUEST['dumpFlg']) && $_REQUEST['dumpFlg']==1) ? 1 : 0;

	$windowLevel = (isset($_REQUEST['windowLevel']) && ctype_digit($_REQUEST['windowLevel'])) ? $_REQUEST['windowLevel'] : 0;
	$windowWidth = (isset($_REQUEST['windowWidth']) && ctype_digit($_REQUEST['windowWidth'])) ? $_REQUEST['windowWidth'] : 0;
	
	$imgNum = (ctype_digit($_REQUEST['imgNum']) && $_REQUEST['imgNum'] > 0) ? $_REQUEST['imgNum'] : 1;
	$dispWidth = (ctype_digit($_REQUEST['dispWidth']) && $_REQUEST['dispWidth']>=0) ? $_REQUEST['dispWidth']  : $DEFAULT_WIDTH;
	$dispHeight = (ctype_digit($_REQUEST['dispHeight']) && $_REQUEST['dispHeight']>=0) ? $_REQUEST['dispHeight'] : $DEFAULT_WIDTH;	
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Convert from DICOM file to JPEG file
	//------------------------------------------------------------------------------------------------------------------
	if($inFname == "" || $outFname == "")
	{
		$outFname = "images/fail_convert.jpg";
	}
	else if(!is_file($outFname))
	{
		$cmdStr  = $cmdForProcess . ' "' . $cmdCreateThumbnail . ' ' . $inFname . ' ' . $outFname . ' '
		         . $quality . ' ' . $dumpFlg . ' ' . $windowLevel . ' ' . $windowWidth . '"';
	
		shell_exec($cmdStr);
	}
	//------------------------------------------------------------------------------------------------------------------
	
	//------------------------------------------------------------------------------------------------------------------
	// Load converted image (100 times?)
	//------------------------------------------------------------------------------------------------------------------
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
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Display converted image (including size conversionj
	//------------------------------------------------------------------------------------------------------------------
	$width  = $img->getImageWidth();
	$height = $img->getImageHeight();

	if(isset($_REQUEST['dispWidth']) && isset($_REQUEST['dispHeight']))
	{
	
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
	$draw->annotation (4, 15, sprintf("Img. No. %04d", $imgNum));

	$img->drawImage($draw);
	$draw->destroy();

	header("Content-type: image/jpg");
	echo $img;
	
	$img->destroy();
	//--------------------------------------------------------------------------------------------------------

?>