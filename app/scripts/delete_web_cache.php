<?php

/**
 * PHP script to delete web cache files older than specified date/time.
 * @author Yukihiro NOMURA <nomuray-tky@umin.ac.jp>
 */

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
		$objects = scandir($path);
		
		$fileDate = new DateTime();

		foreach ($objects as $object)
		{
			if($object != "." && $object != "..")
			{
				$fileName = $path . $DIR_SEPARATOR . $object;

				$fileDate->setTimestamp(filemtime($fileName));
				
				if($fileDate < $rmDate)  @unlink($fileName);
			}
		}
	}

}
catch (PDOException $e)
{
	var_dump($e->getMessage());
}
$pdo = null;

?>