<?php
	$params = array('toTopDir' => "../");
	include_once("../common.php");
	Auth::checkSession();
	Auth::purgeUnlessGranted(Auth::RESEARCH_EXEC);

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

	if($validator->validate($_POST))
	{
		$params = $validator->output;
		$params['message'] = "";
	}
	else
	{
		$params = $validator->output;
		$params['message'] = implode('<br/>', $validator->errors);
	}

	$params['toTopDir'] = "../";
	//-----------------------------------------------------------------------------------------------------------------

	try
	{
		if($params['message'] == "")
		{
			// Connect to SQL Server
			$pdo = DBConnector::getConnection();

			$sqlStr = "SELECT pt.patient_id, pt.patient_name, sr.series_date, sr.series_time,"
					. " sr.modality, sr.series_description"
					. " FROM patient_list pt, study_list st, series_list sr"
					. " WHERE sr.series_instance_uid=? AND sr.study_instance_uid=?"
					. " AND sr.study_instance_uid=st.study_instance_uid"
					. " AND pt.patient_id=st.patient_id";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($params['seriesInstanceUID'], $params['studyInstanceUID']));

			if($stmt->rowCount() != 1)
			{
				$params['message'] = "[Error] DICOM series is unspecified!!";
			}
			else
			{
				$result = $stmt->fetch(PDO::FETCH_NUM);

				$params['patientID']         = $result[0];
				$params['seriesTime']        = $result[2] . ' ' . $result[3];
				$params['modality']          = $result[4];
				$params['seriesDescription'] = $result[5];


				if($_SESSION['anonymizeFlg'] == 1)
				{
					$params['encryptedPtID'] =  PinfoScramble::encrypt($params['patientID'], $_SESSION['key']);
				}
			}
		}

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		$smarty = new SmartyEx();

		$smarty->assign('params', $params);

		$smarty->display('research/convert_volume_data.tpl');
		//--------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;

?>
