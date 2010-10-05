<?php
	session_cache_limiter('none');
	session_start();

	include("../common.php");

	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//--------------------------------------------------------------------------------------------------------	
	$param = array('toTopDir'          => '../',
	               'message'           => '',
	               'studyInstanceUID'  => $_POST['studyInstanceUID'],
	               'seriesInstanceUID' => $_POST['seriesInstanceUID']);
	//--------------------------------------------------------------------------------------------------------	

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
	
		$sqlStr = "SELECT pt.patient_id, pt.patient_name, sr.series_date, sr.series_time,"
				. " sr.modality, sr.series_description" 
				. " FROM patient_list pt, study_list st, series_list sr" 
				. " WHERE sr.series_instance_uid=? AND sr.study_instance_uid=?" 
				. " AND sr.study_instance_uid=st.study_instance_uid" 
				. " AND pt.patient_id=st.patient_id";

		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute(array($param['seriesInstanceUID'], $param['studyInstanceUID']));

		if($stmt->rowCount() != 1)
		{
			$param['message'] = "[Error] DICOM series is unspecified!!";
		}
		else
		{
			$result = $stmt->fetch(PDO::FETCH_NUM);
	
			$param['patientID']         = $result[0];
			$param['seriesTime']        = $result[2] . ' ' . $result[3];
			$param['modality']          = $result[4];
			$param['seriesDescription'] = $result[5];
			

			if($_SESSION['anonymizeFlg'] == 1)
			{
				$param['encryptedPtID'] = PinfoEncrypter($param['patientID'], $_SESSION['key']);
			}
		}
		
		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		require_once('../smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
		
		$smarty->assign('param', $param);
		
		$smarty->display('research/convert_volume_data.tpl');
		//--------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;

?>
