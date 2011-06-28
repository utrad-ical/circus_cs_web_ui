<?php
$params = array('toTopDir' => "../");
include_once("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(Auth::RESEARCH_SHOW);

//------------------------------------------------------------------------------------------------------------------
// Import $_GET variables and validation
//------------------------------------------------------------------------------------------------------------------
$params = array();
$validator = new FormValidator();

$validator->addRules(array(
	"jobID" => array(
	"type" => "int",
	"required" => true,
	"min" => 1,
	"errorMes" => "[ERROR] Research ID is invalid."),
	"srcList" => array(
	"type" => "string")
	));

if($validator->validate($_GET))
{
	$params = $validator->output;
	$params['errorMessage'] = "";
}
else
{
	$params = $validator->output;
	$params['errorMessage'] = implode('<br/>', $validator->errors);
}

$params['toTopDir'] = '../';
$params['pluginType'] = 2;
//------------------------------------------------------------------------------------------------------------------

try
{
	if($params['errorMessage'] == "")
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		//echo json_encode($dstData);
		$sqlStr .= "SELECT pm.plugin_name, pm.version, el.executed_at, sm.storage_id, sm.path"
				.  " FROM executed_plugin_list el, plugin_master pm, storage_master sm"
				.  " WHERE el.job_id=? AND pm.plugin_id=el.plugin_id"
				.  " AND el.storage_id = sm.storage_id";

		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindParam(1, $params['jobID']);
		$stmt->execute();

		$result = $stmt->fetch(PDO::FETCH_NUM);

		$params['pluginName'] = $result[0];
		$params['version']    = $result[1];
		$params['executedAt'] = $result[2];
		$params['resPath']    = $result[4] . $DIR_SEPARATOR . $params['jobID'] . $DIR_SEPARATOR;
		$params['resPathWeb'] = "../storage/" . $result[3] . '/' . $params['jobID'] . $DIR_SEPARATOR_WEB;

		//----------------------------------------------------------------------------------------------------------
		// Retrieve tag data
		//----------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT tag, entered_by FROM tag_list WHERE category=6 AND reference_id=? ORDER BY sid ASC";
		$params['tagArray'] = DBConnector::query($sqlStr, $params['jobID'], 'ALL_NUM');
		//----------------------------------------------------------------------------------------------------------

		$templateName = 'plugin_template/show_' . $params['pluginName'] . '_v.' . $params['version'] . '.php';
		include($templateName);
	}
}
catch (PDOException $e)
{
	var_dump($e->getMessage());
}

$pdo = null;

?>
