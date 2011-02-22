<?php
	session_cache_limiter('none');
	session_start();

	include_once("../common.php");

	//-----------------------------------------------------------------------------------------------------------------
	// Import $_POST variables and validation
	//-----------------------------------------------------------------------------------------------------------------
	$dstData = array();
	$validator = new FormValidator();


	$validator->addRules(array(
		"modality" => array(
			"type" => "select",
			"options" => $modalityList,
			"default" => "CT",
			"otherwise" => "CT"),
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
		$pdo = new PDO($connStrPDO);

		$dstData['executableList'] = array();
		$dstData['hiddenList']      = array();

		$sqlStr  = "SELECT * FROM cad_master cm, cad_series cs"
				 . " WHERE cm.cad_name=cs.cad_name AND cm.version=cs.version AND cs.series_id=1"
				 . " AND cs.modality=? ORDER BY cm.label_order ASC";

		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindValue(1, $dstData['modality']);
		$stmt->execute();

		while($result = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			$tmp = $result['cad_name'] . "_v." . $result['version'];

			if($result['exec_flg'] == 't')  $dstData['executableList'][] = $tmp;
			else                            $dstData['hiddenList'][] =$tmp;
		}
		echo json_encode($dstData);

	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

?>
