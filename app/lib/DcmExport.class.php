<?php

class DcmExport
{
	// CreateThumbnail
	public static function createThumbnailJpg($srcFname, $dstFname, $dumpFname, $quality,
												$windowLevel, $windowWidth, $imgWidth, $imgHeight)
	{
		// validation
		if(!is_numeric($windowLevel) || $windowLevel < -10000 || $windowLevel > 10000)  $windowLevel = 0;
		if(!is_numeric($windowWidth) || $windowWidth < 0      || $windowWidth > 20000)  $windowWidth = 0;

		global $cmdCreateThumbnail;	// refer common.php

		if (!is_file($srcFname))
			return false;

		$cmdStr = sprintf(
			'"%s" "%s" "%s" %d %d %d %d %d "%s"',
			$cmdCreateThumbnail, $srcFname, $dstFname,
			$quality, $windowLevel, $windowWidth, $imgWidth, $imgHeight,
			$dumpFname
		);

		shell_exec($cmdStr);

		for($i=0; $i<5; $i++)
		{
			if(is_file($dstFname))
				return true;
			else
				sleep(1);
		}
		return false;
	}
}
