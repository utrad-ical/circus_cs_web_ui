<?php

	session_start();

	include("../common.php");

	//-----------------------------------------------------------------------------------------------------------------
	// Import $_POST variables and validation
	//-----------------------------------------------------------------------------------------------------------------
	$params = array();
	$validator = new FormValidator();

	$validator->addRules(array(
		"cadName" => array(
			"type" => "cadname",
			"errorMes" => "'CAD' is invalid."),
		"version" => array(
			"type" => "version",
			"otherwise" => "all",
			"errorMes" => "'Version' is invalid.")
		));

	if($validator->validate($_POST))
	{
		$params = $validator->output;
		$params['errorMessage'] = "";

		if($params['cadName'] === '(Select)')
		{
			$params['errorMessage'] = 'Please select CAD name';
		}
	}
	else
	{
		$params = $validator->output;
		$params['errorMessage'] = implode('<br/>', $validator->errors);
	}
	//--------------------------------------------------------------------------------------------------------

	//if($_SESSION['allStatFlg'])		$userID = $_POST['evalUser'];
	//else							$userID = $_SESSION['userID'];

	$dstData = array('errorMessage' => $params['errorMessage'],
					 'userOptionStr'    => "");

	if($_SESSION['allStatFlg'] == 1 && $dstData['errorMessage'] == "")
	{
		$dstData['userOptionStr'] = '<option value="">(Select)</option>';

		try
		{
			// Connect to SQL Server
			$pdo = DBConnector::getConnection();

			$sqlStr = "SELECT DISTINCT fl.entered_by"
					. " FROM executed_plugin_list el, feedback_list fl, plugin_master pm"
					. " WHERE el.job_id=fl.job_id"
					. " AND pm.plugin_id=el.plugin_id"
					. " AND pm.plugin_name=?";

			$sqlParams = array($params['cadName']);

			if($params['version'] != 'all')
			{
				$sqlStr .= " AND pm.version=?";
				$sqlParams[] = $params['version'];
			}

			$sqlStr .= " AND fl.is_consensual='f' AND fl.status=1"
					.  " ORDER BY fl.entered_by ASC";

			//echo $sqlStr;

			$userList = DBConnector::query($sqlStr, $sqlParams, 'ALL_COLUMN');

			//var_dump($userList);

			foreach($userList as $item)
			{
				$dstData['userOptionStr'] .= '<option value="' . $item . '">' . $item . '</option>';
			}
		}
		catch (PDOException $e)
		{
			var_dump($e->getMessage());
		}

		$pdo = null;
	}

	echo json_encode($dstData);
?>
