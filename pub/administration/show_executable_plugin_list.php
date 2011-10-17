<?php
	include_once("../common.php");
	Auth::checkSession(false);

	//-----------------------------------------------------------------------------------------------------------------
	// Import $_POST variables and validation
	//-----------------------------------------------------------------------------------------------------------------
	$dstData = array();
	$validator = new FormValidator();


	$validator->addRules(array(
		"type" => array(
			"type" => "select",
			"options" => array("1", "2"),
			"default" => "1",
			"otherwise" => "1"),
		));

	if($validator->validate($_POST))
	{
		$dstData = $validator->output;
		$dstData['errorMessage'] = "&nbsp;";
	}
	else
	{
		$dstData = $validator->output;
		$dstData['errorMessage'] = implode('<br/>', $validator->errors);
	}
	//-----------------------------------------------------------------------------------------------------------------

	try
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		$dstData['executableList'] = array();
		$dstData['hiddenList']      = array();

		if($dstData['type'] == 2)
		{
			$sqlStr = "SELECT pm.plugin_name, pm.version, pm.exec_enabled"
					. " FROM plugin_master pm, plugin_research_master pr"
					. " WHERE pr.plugin_id=pm.plugin_id"
					. " AND pm.type=2 ORDER BY pr.label_order ASC";
		}
		else
		{
			$sqlStr = "SELECT pm.plugin_name, pm.version, pm.exec_enabled"
					. " FROM plugin_master pm, plugin_cad_master cm"
					. " WHERE cm.plugin_id=pm.plugin_id"
					. " AND pm.type=1 ORDER BY cm.label_order ASC";
		}

		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute();

		while($result = $stmt->fetch(PDO::FETCH_NUM))
		{
			$tmp = $result[0] . "_v." . $result[1];

			if($result[2] == 't')  $dstData['executableList'][] = $tmp;
			else                   $dstData['hiddenList'][] =$tmp;
		}
		echo json_encode($dstData);

	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

?>
