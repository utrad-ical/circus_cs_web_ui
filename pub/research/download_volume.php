<?php
include("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(AUTH::VOLUME_DOWNLOAD);

//------------------------------------------------------------------------------------------------------------------
// Import $_POST variables and validation
//------------------------------------------------------------------------------------------------------------------
$params = array();
$validator = new FormValidator();

$validator->addRules(array(
	"seriesInstanceUID" => array(
		"type" => "uid",
		"required" => true,
		"errorMes" => "[ERROR] Parameter of URL (seriesInstanceUID) is invalid.")
	));

if($validator->validate($_GET))
{
	$params = $validator->output;
	$params['message'] = "";
}
else
{
	$params = $validator->output;
	$params['message'] = implode('<br/>', $validator->errors);
}

$params['fileName'] = '';
//-----------------------------------------------------------------------------------------------------------------

if($params['message'] == "")
{
	try
	{

		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		// Get cache area
		$sqlStr = "SELECT storage_id FROM storage_master WHERE type=3 AND current_use='t'";
		$webCacheID = DBConnector::query($sqlStr, NULL, 'SCALAR');

		$params['fileName'] = '../storage/' . $webCacheID . '/' . $params['seriesInstanceUID'] . '.zip';

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		$smarty = new SmartyEx();

		$smarty->assign('params', $params);

		$smarty->display('research/download_volume.tpl');
		//--------------------------------------------------------------------------------------------------------------

	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
}
?>
