<?php

	$radioButtonList[0][0][0] = 'known TP';  													$radioButtonList[0][0][1] =  1;
	$radioButtonList[0][1][0] = 'missed TP'; 													$radioButtonList[0][1][1] =  2;
	$radioButtonList[0][2][0] = '&nbsp;&nbsp;&nbsp;&nbsp;F&nbsp;P&nbsp;&nbsp;&nbsp;&nbsp;';		$radioButtonList[0][2][1] =  0;
	$radioButtonList[0][3][0] = 'pending';														$radioButtonList[0][3][1] = -1;

	$radioButtonList[1][0][0] = 'TP';			$radioButtonList[1][0][1] =  1;
	$radioButtonList[1][1][0] = 'FP';			$radioButtonList[1][1][1] =  0;
	$radioButtonList[1][2][0] = 'pending';		$radioButtonList[1][2][1] = -1;


	function dcm2png($cmd1, $cmd2, $dirSeparator, $srcFname, $sliceNum, $lesionID, $windowLevel, $windowWidth)
	{
		$dcmPath = substr($srcFname, 0, strrpos($srcFname, ($dirSeparator . "cad_results")));
		
		$tmpFlist = scandir($dcmPath);

		$inFname = "";


		for($i=0; $i<count($tmpFlist); $i++)
		{
			//if(ereg(sprintf("%04d", $sliceNum) . '.*dcm$', $tmpFlist[$i]))  $inFname = $dcmPath . $dirSeparator . $tmpFlist[$i];
			if(preg_match('/^'.sprintf("%04d", $sliceNum).'(c\_|\_).*dcm/', $tmpFlist[$i]))
			{
				$inFname = $dcmPath . $dirSeparator . $tmpFlist[$i];
			}
		}

		if($inFname != "")
		{
			$cmdStr  = $cmd1 . ' "' . $cmd2 . ' ' . $inFname . ' ' . $srcFname . ' ' . $windowLevel . ' ' . $windowWidth . '"';
			shell_exec($cmdStr);
		
			$img = new Imagick();

			for($i=0; $i<100; $i++)
			{
				if($img->readImage($srcFname) == TRUE)	return TRUE;
				else                                    sleep(100000);
			}
		}
			
		return FALSE;
	}
	
	function CreateThumbnail($cmd1, $cmd2, $srcFname, $dstFname, $quality, $dumpFlg, $windowLevel, $windowWidth)
	{
		$cmdStr  = $cmd1 . ' "' . $cmd2 . ' ' . $srcFname . ' ' . $dstFname . ' ' . $quality . ' ' . $dumpFlg
		         . ' ' . $windowLevel . ' ' . $windowWidth . '"';	

		shell_exec($cmdStr);
		
		$img = new Imagick();

		for($i=0; $i<100; $i++)
		{
			if($img->readImage($dstFname) == TRUE)	break;
			else                                    sleep(100000);
		}
	}	

?>