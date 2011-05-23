<?php
	include("../common.php");
	Auth::checkSession(false);

	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables and validation
	//------------------------------------------------------------------------------------------------------------------
	$params = array();
	$validator = new FormValidator();

	$validator->addRules(array(
		"cadName" => array(
			"type" => "cadname",
			"required" => true,
			"errorMes" => "[ERROR] 'CAD name' is invalid."),
		"version" => array(
			"type" => "version",
			"required" => true,
			"errorMes" => "[ERROR] 'Version' is invalid."),
		));

	if($validator->validate($_POST))
	{
		$params = $validator->output;
		$params['message'] = "&nbsp;";
	}
	else
	{
		$params = $validator->output;
		$params['message'] = implode('<br/>', $validator->errors);
	}

	$params['userID'] = $_SESSION['userID'];
	$params['preferenceFlg'] = 0;
	$params['sortKey']          = array("", "");
	$params['sortOrder']        = array("", "");
	$params['maxDispNum']       = array("", "");
	$params['confidenceTh']     = array("", "");
	$params['yellowCircleTh']   = array("", "");
	$params['doubleCircleTh']   = array("", "");
	$params['dispConfidence']   = array("", "");
	$params['dispCandidateTag'] = array("", "");
	//--------------------------------------------------------------------------------------------------------

	try
	{
		if($params['message'] == "&nbsp;")
		{
			$params['message'] = "Default settings";

			// Connect to SQL Server
			$pdo = DBConnector::getConnection();

			$sqlStr = "SELECT key, value FROM plugin_master pm, plugin_user_preference pp"
					. " WHERE pp.plugin_id=pm.plugin_id AND pm.plugin_name=? AND pm.version=?"
					. " AND pp.user_id=?";
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($params['cadName'], $params['version'], $DEFAULT_CAD_PREF_USER));

			while($result = $stmt->fetch(PDO::FETCH_NUM))
			{
				$params[$result[0]][0] = $params[$result[0]][1] = $result[1];
			}

			$stmt->execute(array($params['cadName'], $params['version'], $params['userID']));

			if($stmt->rowCount() > 0)
			{
				$params['preferenceFlg'] = 1;
				$params['message'] = "&nbsp;";

				while($result = $stmt->fetch(PDO::FETCH_NUM))
				{
					$params[$result[0]][1] = $result[1];
				}
			}
		}
		echo json_encode($params);
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;

?>
