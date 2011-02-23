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

	try
	{
		if($params['message'] == "&nbsp;")
		{
			// Connect to SQL Server
			$pdo = DB::getConnection();

			$sqlStr    = "";
			$sqlParams = array();
			$order = 1;

			for($i=0; $i < count($executableList); $i++)
			{
				$pos = strpos($executableList[$i], "_v.");
				$cadName = substr($executableList[$i], 0, $pos);
				$version = substr($executableList[$i], $pos+3, strlen($executableList[$i])-$pos-3);

				$sqlStr .= "UPDATE cad_master SET exec_flg='t', label_order=?"
				        .  " WHERE cad_name=? AND version=?;";
				$sqlParams[] = $order;
				$sqlParams[] = $cadName;
				$sqlParams[] = $version;

				$order++;
			}

			for($i=0; $i < count($hiddenList); $i++)
			{
				$pos = strpos($hiddenList[$i], "_v.");
				$cadName = substr($hiddenList[$i], 0, $pos);
				$version = substr($hiddenList[$i], $pos+3, strlen($hiddenList[$i])-$pos-3);

				$sqlStr .= "UPDATE cad_master SET exec_flg='f', label_order=?"
				        .  " WHERE cad_name=? AND version=?;";
				$sqlParams[] = $order;
				$sqlParams[] = $cadName;
				$sqlParams[] = $version;

				$order++;
			}

			if($order>1)
			{
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($sqlParams);

				if($stmt->errorCode() == '00000')
				{
					$dstData['message'] = '<span style="color:blue;">Setting was successfully saved.</span>';
				}
				else
				{
					$dstData['message'] = '<span style="red;">Fail to save settings.</span>';
					//$errorMessage = $stmt->errorInfo();
					//$dstData['message'] .= $errorMessage[2] . '<br/>';
					$dstData['errorFlg'] = 1;
				}
			}
		}
		else
		{
			$dstData['errorFlg'] = 1;
		}

		echo json_encode($dstData);

	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

?>
