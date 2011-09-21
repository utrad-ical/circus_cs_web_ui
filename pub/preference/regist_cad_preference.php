<?php
include("../common.php");
Auth::checkSession(false);

//------------------------------------------------------------------------------------------------------------------
// Import $_POST variables and validation
//------------------------------------------------------------------------------------------------------------------
$params = array();
$validator = new FormValidator();

$mode = (isset($_POST['mode'])) ? $_POST['mode'] : "";
$validator->addRules(array(
	"cadName" => array(
		"type" => "cadname",
		"required" => true,
		"errorMes" => "[ERROR] 'CAD name' is invalid."),
	"version" => array(
		"type" => "version",
		"required" => true,
		"errorMes" => "[ERROR] 'Version' is invalid."),
	"sortKey" => array(
		"type" => "select",
		"options" => array("confidence", "location_z", "volume_size"),
		'oterwise' => "confidence"),
	"sortOrder" => array(
		"type" => "select",
		"options" => array("ASC", "DESC"),
		'oterwise' => "DESC"),
	"maxDispNum" => array(
		"type" => "string",
		"regex" => "/^(all|[\d]+)$/i"),
	"confidenceTh" => array(
		"type" => "numeric",
		"min" => 0),
	"dispCandidateTagFlg" => array(
		"type" => "select",
		"options" => array("1", "0"),
		'oterwise' => "0"),
	"preferenceFlg" => array(
		"type" => "select",
		"options" => array("1", "0"),
		'oterwise' => "0")
	));

if($validator->validate($_POST))
{
	$params = $validator->output;
	$params['errorMessage'] = "";
	if(preg_match('/^all/i', $params['maxDispNum']))  $params['maxDispNum'] = 0;
}
else
{
	$params = $validator->output;
	$params['errorMessage'] = implode('<br/>', $validator->errors);
}

$userID = $_SESSION['userID'];

$dstData = array('preferenceFlg' => $params['preferenceFlg'],
		         'message'       => $params['errorMessage'],
				 'newMaxDispNum' => $params['maxDispNum']);
//--------------------------------------------------------------------------------------------------------

if($mode == 'update' || $mode == 'delete')
{
	try
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		// Begin transaction
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->beginTransaction();	// Begin transaction

		// Get plugin ID
		$sqlStr = "SELECT plugin_id FROM plugin_master WHERE plugin_name=? AND version=?";
		$params['pluginID'] = DBConnector::query($sqlStr, array($params['cadName'], $params['version']), 'SCALAR');

		//----------------------------------------------------------------------------------------------------
		// regist or delete preference
		//----------------------------------------------------------------------------------------------------
		$sqlParams = array();

		$sqlParams[] = $userID;
		$sqlParams[] = $params['pluginID'];

		$sqlStr = "DELETE FROM plugin_user_preference WHERE user_id=? AND plugin_id=?";
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($sqlParams);

		if($mode == 'update')	// restore default settings
		{
			$keyStr = array('sortKey', 'sortOrder', 'maxDispNum', 'confidenceTh', 'dispCandidateTagFlg');

			$sqlStr = "INSERT INTO plugin_user_preference(user_id, plugin_id, key, value)"
					. " VALUES (?,?,?,?)";

			for($i = 0; $i < count($keyStr); $i++)
			{
				if($i > 0)
				{
					$sqlStr .= ",(?,?,?,?)";
					$sqlParams[] = $userID;
					$sqlParams[] = $params['pluginID'];
				}

				$sqlParams[] = $keyStr[$i];
				$sqlParams[] = $params[$keyStr[$i]];
			}

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);
		}
		
		// Commit transaction
		$pdo->commit();
		
		$dstData['message'] = 'Succeeded!';
		$dstData['preferenceFlg'] = ($mode=='delete') ? 0 : 1;

	}
	catch (PDOException $e)
	{
		$pdo->rollBack();
		$dstData['message'] = 'Fail to ' . $mode . ' the preference.'
							. '(' . $e->getMessage() . ')';
	}
	$pdo = null;
}
echo json_encode($dstData);
?>
