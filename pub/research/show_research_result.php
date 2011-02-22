<?php
	session_cache_limiter('nocache');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include_once("auto_logout_research.php");

	//------------------------------------------------------------------------------------------------------------------
	// Import $_GET variables and validation
	//------------------------------------------------------------------------------------------------------------------
	$params = array();
	$validator = new FormValidator();

	$validator->addRules(array(
		"execID" => array(
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
			$pdo = new PDO($connStrPDO);

			//echo json_encode($dstData);
			$sqlStr .= "SELECT el.plugin_name, el.version, el.executed_at, sm.path, sm.apache_alias"
					.  " FROM executed_plugin_list el, storage_master sm"
					.  " WHERE el.exec_id=? AND el.storage_id = sm.storage_id";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindParam(1, $params['execID']);
			$stmt->execute();

			$result = $stmt->fetch(PDO::FETCH_NUM);

			$params['pluginName'] = $result[0];
			$params['version']    = $result[1];
			$params['executedAt'] = $result[2];
			$params['resPath']    = $result[3] . $DIR_SEPARATOR . $params['execID'] . $DIR_SEPARATOR;
			$params['resPathWeb'] = "../" . $result[4] . $params['execID'] . $DIR_SEPARATOR_WEB;

			//----------------------------------------------------------------------------------------------------------
			// Retrieve tag data
			//----------------------------------------------------------------------------------------------------------
			$sqlStr = "SELECT tag, entered_by FROM tag_list WHERE category=6 AND reference_id=? ORDER BY sid ASC";
			$params['tagArray'] = PdoQueryOne($pdo, $sqlStr, $params['execID'], 'ALL_NUM');
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
