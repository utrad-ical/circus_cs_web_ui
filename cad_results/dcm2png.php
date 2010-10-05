<?php

	include("../common.php");

	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//--------------------------------------------------------------------------------------------------------
	if(ini_get('magic_quotes_gpc') == "1")  $srcFname = stripslashes($_REQUEST['srcFname']);
	else                                    $srcFname = $_REQUEST['srcFname'];

	$cropFlg = (isset($_REQUEST['cropFlg'])) ? $_REQUEST['cropFlg'] : 0;

	$orgX       = $_REQUEST['orgX'];
	$orgY       = $_REQUEST['orgY'];
	$cropWidth  = $_REQUEST['cropWidth'];
	$cropHeight = $_REQUEST['cropHeight'];
	$dispWidth  = $_REQUEST['cropWidth'];
	$dispHeight = $_REQUEST['cropHeight'];
	//--------------------------------------------------------------------------------------------------------

	//--------------------------------------------------------------------------------------------------------
	// Load png image 
	//--------------------------------------------------------------------------------------------------------
	$img = new Imagick();
		
	if(!is_file($srcFname))  // Convert from DICOM file to PNG file
	{
		$sliceNum = sprintf("%04d", $_REQUEST['posZ']);
		$lesionID = $_REQUEST['lesionID'];
		$windowLevel = (isset($_REQUEST['windowLevel'])) ? $_REQUEST['windowLevel'] : 0;
		$windowWidth = (isset($_REQUEST['windowWidth'])) ? $_REQUEST['windowWidth'] : 0;
		
		$dcmPath = substr($srcFname, 0, strrpos($srcFname, ($DIR_SEPARATOR . "cad_results")));
		
		$tmpFlist = scandir($dcmPath);
		
		for($i=0; $i<count($tmpFlist); $i++)
		{
			if(ereg($sliceNum . '_.*dcm$', $tmpFlist[$i]))  $inFname = $dcmPath . $DIR_SEPARATOR . $tmpFlist[$i];
		}		
		
		$cmdStr  = $cmdForProcess . ' "' . $cmdDcmToPng . ' ' . $inFname . ' ' . $srcFname . ' '
		         . $windowLevel . ' ' . $windowWidth . '"';
		
		//echo $cmdStr;
		
		shell_exec($cmdStr);
	
		for($i=0; $i<100; $i++)
		{
			if($img->readImage($srcFname) == TRUE)	break;
			else
			{
				if($i==99)
				{
					$img->readImage("../images/fail_convert.jpg");
					$img->resizeImage($dispWidth, $dispHeight, Imagick::FILTER_SINC,1);
					header("Content-type: image/jpg");
					echo $img;
					$img->destroy();
					return 1;
				}
				else		usleep(100000);
			}
		}
	}
	else
	{
		$img->readImage($srcFname);
	}
	
	if($cropFlg == 1)  $img->cropImage($cropWidth, $cropHeight, $orgX, $orgY);	
	
	header("Content-type: image/png");
	echo $img;
	$img->destroy();
			
?>