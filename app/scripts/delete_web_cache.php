<?php

/**
 * PHP script to delete web cache files older than specified date/time.
 * @author Yukihiro NOMURA <nomuray-tky@umin.ac.jp>
 */

/**
 * Recursively delete a directory that is not empty.
 * @param string $dir The path to the directory to delete.
 * @param DateTime $rmDate condition for date/time of deleting files
 * @param boolean $selfDeleteFlg Flag for deleting root directory
 */
function DeleteDirRecursivelyWithDateCondition($dir, $rmDate, $selfDeleteFlg)
{
	if(is_dir($dir))
	{
		$objects = scandir($dir);
		
		$fileDate = new DateTime();
		
		foreach ($objects as $object)
		{
			if($object != "." && $object != "..")
			{
				$fileName = "$dir/$object";
				if(filetype($fileName) == "dir")
					DeleteDirRecursivelyWithDateCondition($fileName, $rmDate, true);
				else
					$fileDate->setTimestamp(filemtime($fileName));
					
					if($fileDate < $rmDate)  @unlink($fileName);
			}
		}
		reset($objects);
		if($selfDeleteFlg == true)  rmdir($dir);
	}
	return true;
}



include_once("../../pub/common.php");

$DEFAULT_RM_DATES = '-1 week';
$rmDate = new DateTime($DEFAULT_RM_DATES);

try
{
	$pdo = DBConnector::getConnection();
	$sqlStr = "SELECT path FROM storage_master WHERE type=3";
	$pathList = DBConnector::query($sqlStr, NULL, 'ALL_COLUMN');

	foreach($pathList as $path)
	{
		DeleteDirRecursivelyWithDateCondition($path, $rmDate, false);
	}

}
catch (PDOException $e)
{
	var_dump($e->getMessage());
}
$pdo = null;
