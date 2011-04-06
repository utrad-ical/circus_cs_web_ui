<?php
	session_cache_limiter('none');
	session_start();

	include_once("../common.php");

	//-----------------------------------------------------------------------------------------------------------------
	// Import $_POST variables and validation
	//-----------------------------------------------------------------------------------------------------------------
	$params = array();
	$validator = new FormValidator();

	$validator->addRules(array(
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

	$executableList = explode("^", $params['executableStr']);
	$hiddenList     = explode("^", $params['hiddenStr']);

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

				for($i=0; $i < count($executableList); $i++)
				{
					$pos = strpos($executableList[$i], "_v.");
					$cadName = substr($executableList[$i], 0, $pos);
					$version = substr($executableList[$i], $pos+3, strlen($executableList[$i])-$pos-3);

					$sqlStr = "UPDATE plugin_master SET exec_enabled='t'"
							. " WHERE plugin_name=:cadName AND version=:version;"
							. "UPDATE cad_master SET label_order=:order"
							. " WHERE plugin_name=:cadName AND version=:version;";

					$sqlParams['cadName'] = $cadName;
					$sqlParams['version'] = $version;
					$sqlParams['order']   = $order;

					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute($sqlParams);

					$order++;
				}

				for($i=0; $i < count($hiddenList); $i++)
				{
					$pos = strpos($hiddenList[$i], "_v.");
					$cadName = substr($hiddenList[$i], 0, $pos);
					$version = substr($hiddenList[$i], $pos+3, strlen($hiddenList[$i])-$pos-3);

					$sqlStr = "UPDATE plugin_master SET exec_enabled='f'"
							. " WHERE plugin_name=:cadName AND version=:version;"
							. "UPDATE cad_master SET label_order=:order"
							. " WHERE plugin_name=:cadName AND version=:version;";

					$sqlParams['cadName'] = $cadName;
					$sqlParams['version'] = $version;
					$sqlParams['order']   = $order;

					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute($sqlParams);

					$order++;
				}

				$pdo->commit();
				$dstData['message'] = '<span style="color:blue;">Setting was successfully saved.</span>';
			}
			catch (PDOException $e)
			{
				$pdo->rollBack();
				//$dstData['message']  = '<span style="red;">' . $e->getMessage() . '</span>';
				$dstData['message'] = '<span style="red;">Fail to save settings.</span>';
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
