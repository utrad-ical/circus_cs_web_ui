<?php
	include("../common.php");
	Auth::checkSession();
	Auth::purgeUnlessGranted(AUTH::VOLUME_DOWNLOAD);

	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables and validation
	//------------------------------------------------------------------------------------------------------------------
	$params = array();
	$validator = new FormValidator();

	$validator->addRules(array(
		"studyInstanceUID" => array(
			"type" => "uid",
			"required" => true,
			"errorMes" => "[ERROR] Parameter of URL (studyInstanceUID) is invalid."),
		"seriesInstanceUID" => array(
			"type" => "uid",
			"required" => true,
			"errorMes" => "[ERROR] Parameter of URL (seriesInstanceUID) is invalid.")
		));

	if($validator->validate($_GET))
	{
		$params = $validator->output;
		$params['message'] = "";
	}
	else
	{
		$params = $validator->output;
		$params['message'] = implode('<br/>', $validator->errors);
	}

	$params['fileName'] = '';
	//-----------------------------------------------------------------------------------------------------------------

	try
	{
		if($params['message'] == "")
		{
			// Connect to SQL Server
			$pdo = DBConnector::getConnection();

			$sqlStr = "SELECT st.patient_id, sr.storage_id"
					. " FROM study_list st, series_list sr"
					. " WHERE sr.series_instance_uid=? AND sr.study_instance_uid=?"
					. " AND sr.study_instance_uid=st.study_instance_uid";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($params['seriesInstanceUID'], $params['studyInstanceUID']));

			if($stmt->rowCount() != 1)
			{
				$params['message'] = "[Error] DICOM series is unspecified!!";
			}
			else
			{
				$result = $stmt->fetch(PDO::FETCH_NUM);

				$webPathOfseriesDir = 'storage/' . $result[1] . '/' . $result[0]
				                    . '/' . $params['studyInstanceUID']
		    		                . '/' . $params['seriesInstanceUID'];

				$params['fileName'] = "../" . $webPathOfseriesDir . '/' . $params['seriesInstanceUID'] . ".zip";
			}
		}

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		$smarty = new SmartyEx();

		$smarty->assign('params', $params);

		$smarty->display('research/download_volume.tpl');
		//--------------------------------------------------------------------------------------------------------------

	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
