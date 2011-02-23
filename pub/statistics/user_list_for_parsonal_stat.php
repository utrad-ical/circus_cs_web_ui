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
			$pdo = DB::getConnection();

			$sqlStr = "SELECT DISTINCT lf.entered_by FROM executed_plugin_list el, lesion_feedback lf"
					. " WHERE el.exec_id=lf.exec_id AND el.plugin_name=?";
			$sqlParams = array($params['cadName']);

			if($params['version'] != 'all')
			{
				$sqlStr .= " AND version=?";
				$sqlParams[] = $params['version'];
			}

			$sqlStr .= " AND lf.consensual_flg='f' AND lf.interrupt_flg='f'"
					.  " ORDER BY entered_by ASC";

			//echo $sqlStr;

			$userList = DB::query($sqlStr, $sqlParams, 'ALL_COLUMN');

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
