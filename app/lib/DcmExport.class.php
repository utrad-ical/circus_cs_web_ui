<?php

class DcmExport
{
	public static function dcm2png($srcFname, $dstFname, $windowLevel, $windowWidth)
	{
		// validation
		if(!is_numeric($windowLevel) || $windowLevel < -10000 || $windowLevel > 10000)  $windowLevel = 0;
		if(!is_numeric($windowWidth) || $windowWidth < 0      || $windowWidth > 20000)  $windowWidth = 0;

		global $cmdForProcess, $cmdDcmToPng;	// refer common.php

		if(is_file($srcFname))
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
	public static function createThumbnailJpg($srcFname, $dstFname, $dumpFname, $quality,
												$windowLevel, $windowWidth, $imgWidth, $imgHeight)
	{
		// validation
		if(!is_numeric($quality)     || $sliceNum < 0         || $quality > 100)        $quality = 100;
		if(!is_numeric($windowLevel) || $windowLevel < -32768 || $windowLevel > 32767)  $windowLevel = 0;
		if(!is_numeric($windowWidth) || $windowWidth < 0      || $windowWidth > 65536)  $windowWidth = 0;

		global $cmdForProcess, $cmdCreateThumbnail;	// refer common.php

		$pathInfo = pathinfo($srcFname);

		if(is_file($srcFname) && $pathInfo['extension'] == 'dcm')
		{
			$cmdStr = sprintf('%s "%s %s %s %d %d %d %d %d', $cmdForProcess, $cmdCreateThumbnail, $srcFname, $dstFname,
															 $quality, $windowLevel, $windowWidth, $imgWidth, $imgHeight);
			if(!is_file($dumpFname))  $cmdStr .= " " . $dumpFname;
			$cmdStr .= '"';

			//echo $cmdStr;

			shell_exec($cmdStr);

			for($i=0; $i<100; $i++)
			{
				if(!(@imagecreatefrompng($dstFname)))	return true;
				else                                    sleep(100000);
			}
		}
		return false;
	}
}
?>
