<?php
	session_start();

	include("../common.php");

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
	$params['sortKey']             = "";
	$params['sortOrder']           = "";
	$params['maxDispNum']          = "";
	$params['confidenceTh']        = "";
	$params['defaultSortKey']      = "";
	$params['defaultSortOrder']    = "";
	$params['defaultMaxDispNum']   = "";
	$params['defaultConfidenceTh'] = "";
	$params['dispConfidence']      = "f";
	$params['dispCandidateTag']    = "f";
	//--------------------------------------------------------------------------------------------------------

	try
	{
		if($params['message'] == "&nbsp;")
		{
			// Connect to SQL Server
			$pdo = DBConnector::getConnection();

			$sqlStr = "SELECT * FROM cad_master WHERE cad_name=? AND version=?";
			$result = DBConnector::query($sqlStr, array($params['cadName'], $params['version']), 'ARRAY_ASSOC');

			$params['defaultSortKey']      = $result['default_sort_key'];
			$params['defaultSortOrder']    = ($result['default_sort_order']) ? "t" : "f";
			$params['defaultMaxDispNum']   = $result['max_disp_num'];
			$params['defaultConfidenceTh'] = $result['confidence_threshold'];

			$stmt = $pdo->prepare("SELECT * FROM cad_preference WHERE cad_name=? AND version=? AND user_id=?");
			$stmt->execute(array($params['cadName'], $params['version'], $params['userID']));

			if($stmt->rowCount() == 1)
			{
				$params['preferenceFlg'] = 1;

				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				$params['sortKey']          = $result['default_sort_key'];
				$params['sortOrder']        = ($result['default_sort_order']) ? "t" : "f";
				$params['maxDispNum']       = ($result['max_disp_num']==0) ? "all" : $result['max_disp_num'];
				$params['confidenceTh']     = $result['confidence_threshold'];
				$params['dispConfidence']   = ($result['disp_confidence_flg']) ? "t" : "f";
				$params['dispCandidateTag'] = ($result['disp_candidate_tag_flg']) ? "t" : "f";
			}
			else
			{
				$params['preferenceFlg'] = 0;
				$params['message']      = 'Default settings.';
				$params['sortKey']      = $params['defaultSortKey'];
				$params['sortOrder']    = $params['defaultSortOrder'];
				$params['maxDispNum']   = $params['defaultMaxDispNum'];
				$params['confidenceTh'] = $params['defaultConfidenceTh'];
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
