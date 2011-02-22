<?php

	class DcmExport
	{
		public static function dcm2png($dstFname, $sliceNum, $windowLevel, $windowWidth)
		{
			// validation
			if(!is_numeric($sliceNum)    || $sliceNum < 0         || $sliceNum > 10000)     return false;
			if(!is_numeric($windowLevel) || $windowLevel < -10000 || $windowLevel > 10000)  $windowLevel = 0;
			if(!is_numeric($windowWidth) || $windowWidth < 0      || $windowWidth > 20000)  $windowWidth = 0;
			
			global $cmdForProcess, $cmdDcmToPng, $DIR_SEPARATOR;	// refer common.php
			
			$dcmPath = substr($dstFname, 0, strrpos($dstFname, ($DIR_SEPARATOR . "cad_results")));
			
			if(!is_dir($dcmPath))	return false;
			
			$tmpFlist = scandir($dcmPath);
			$srcFname = "";
			
			for($i = 0; $i < count($tmpFlist); $i++)
			{
				if(preg_match('/^'.sprintf("%04d", $sliceNum).'(c\_|\_).*\\.dcm/', $tmpFlist[$i]))
				{
					$srcFname = $dcmPath . $DIR_SEPARATOR . $tmpFlist[$i];
				}
			}
			
			if($srcFname != "")
			{
				$cmdStr = sprintf('%s "%s %s %s %d %d"', $cmdForProcess, $cmdDcmToPng, $srcFname, $dstFname, $windowLevel, $windowWidth);
				shell_exec($cmdStr);
				
				for($i=0; $i<100; $i++)
				{
					if(!(@imagecreatefromjpeg($dstFname)))	return true;
					else                                    sleep(100000);
				}
			}
			return false;
		}
		
		// CreateThumbnail
		public static function createThumbnailJpg($srcFname, $dstBase, $presetName, $quality, $dumpFlg, $windowLevel, $windowWidth)
		{
			// validation
			if(!is_numeric($sliceNum)    || $dumpFlg != 0)  $dumpFlg = 1;
			if(!is_numeric($quality)     || $sliceNum < 0         || $quality > 100)        $quality = 100;
			if(!is_numeric($windowLevel) || $windowLevel < -10000 || $windowLevel > 10000)  $windowLevel = 0;
			if(!is_numeric($windowWidth) || $windowWidth < 0      || $windowWidth > 20000)  $windowWidth = 0;
			
			global $cmdForProcess, $cmdCreateThumbnail;	// refer common.php
	
			$pathInfo = pathinfo($srcFname);
		
			if(!is_file($srcFname) || $pathInfo['extension'] != 'dcm')	return false;
	
			$cmdStr = sprintf('%s "%s %s %s %s %d %d %d %d"', $cmdForProcess, $cmdCreateThumbnail, $srcFname, $dstBase,
				                                              $presetName, $quality, $dumpFlg, $windowLevel, $windowWidth);
			shell_exec($cmdStr);
			
			$dstFname = $dstBase;
			
			if($presetName != "" && $presetName != "Auto")
			{
				$dstFname .= "_" . $presetName;
			}
			$dstFname .= '.jpg';
			
			for($i=0; $i<100; $i++)
			{
				if(!(@imagecreatefrompng($dstFname)))	return true;
				else                                    sleep(100000);
			}
			return false;
		}
	}

?>
