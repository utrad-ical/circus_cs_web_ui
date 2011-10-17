<?php
include_once("../common.php");
Auth::checkSession(false);

//-----------------------------------------------------------------------------------------------------------------
// Import $_POST variables and validation
//-----------------------------------------------------------------------------------------------------------------
$params = array();
$validator = new FormValidator();

$validator->addRules(array(
	"type" => array(
		"type" => "select",
		"options" => array("1", "2"),
		"default" => "1",
		"otherwise" => "1"),
	"executableStr" => array(
		"type" => "string",
		"regex" => "/^[\w\s-_\.\^]+$/"),
	"hiddenStr" => array(
		"type" => "string",
		"regex" => "/^[\w\s-_\.\^]+$/"),
	));

if($validator->validate($_POST))
{
	$params = $validator->output;
	$params['message'] = "&nbsp;";
} else {
	$params = $validator->output;
	$params['message'] = implode('<br/>', $validator->errors);
}

$dstData = array('errorFlg' => 0,
				 'message'  => $params['message']);
//-----------------------------------------------------------------------------------------------------------------

if($params['message'] == "&nbsp;")
{
	// Connect to SQL Server
	try
	{
		$pdo = DBConnector::getConnection();
	}
	catch (PDOException $e)
	{
		$dstData['message']  = '<span style="red;">' . $e->getMessage() . '</span>';
		$dstData['errorFlg'] = 1;
	}

	if($dstData['errorFlg'] == 0)
	{
		//
		try
		{
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			// begin transaction
			$pdo->beginTransaction();

			$sqlParams = array();
			$order = 1;
			
			if($params['executableStr'] != "")
			{
				$executableList = explode("^", $params['executableStr']);
				
				for($i=0; $i < count($executableList); $i++)
				{
					$pos = strpos($executableList[$i], "_v.");
					$cadName = substr($executableList[$i], 0, $pos);
					$version = substr($executableList[$i], $pos+3, strlen($executableList[$i])-$pos-3);

					// Get plugin ID
					$sqlStr = "SELECT plugin_id FROM plugin_master WHERE plugin_name=? AND version=?";
					$pluginID = DBConnector::query($sqlStr, array($cadName, $version), 'SCALAR');
					
					$sqlStr = "UPDATE plugin_master SET exec_enabled='t' WHERE plugin_id=?";
					$stmt = $pdo->prepare($sqlStr);
					$stmt->bindValue(1, $pluginID);
					$stmt->execute();
					
					if($params['type'] == 2)
					{
						$sqlStr = "UPDATE plugin_research_master SET label_order=? WHERE plugin_id=?";
					}
					else
					{
						$sqlStr = "UPDATE plugin_cad_master SET label_order=? WHERE plugin_id=?";
					}
					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute(array($order, $pluginID));
					$order++;
				}
			}
			
			if($params['hiddenStr'] != "")
			{
				$hiddenList = explode("^", $params['hiddenStr']);

				for($i=0; $i < count($hiddenList); $i++)
				{
					$pos = strpos($hiddenList[$i], "_v.");
					$cadName = substr($hiddenList[$i], 0, $pos);
					$version = substr($hiddenList[$i], $pos+3, strlen($hiddenList[$i])-$pos-3);

					// Get plugin ID
					$sqlStr = "SELECT plugin_id FROM plugin_master WHERE plugin_name=? AND version=?";
					$pluginID = DBConnector::query($sqlStr, array($cadName, $version), 'SCALAR');

					$sqlStr = "UPDATE plugin_master SET exec_enabled='f' WHERE plugin_id=?";
					$stmt = $pdo->prepare($sqlStr);
					$stmt->bindValue(1, $pluginID);
					$stmt->execute();

					if($params['type'] == 2)
					{
						$sqlStr = "UPDATE plugin_research_master SET label_order=? WHERE plugin_id=?";
					}
					else
					{
						$sqlStr = "UPDATE plugin_cad_master SET label_order=? WHERE plugin_id=?";
					}
					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute(array($order, $pluginID));

					$order++;
				}
			}
			
			$pdo->commit();
			$dstData['message'] = '<span style="color:blue;">Setting was successfully saved.</span>';
		}
		catch (PDOException $e)
		{
			$pdo->rollBack();
			$dstData['message'] = '<span style="red;">Fail to save settings (' . $e->getMessage() . ').</span>';
			$dstData['errorFlg'] = 1;
		}
	}
}
else
{
	$dstData['errorFlg'] = 1;
}
echo json_encode($dstData);

?>
