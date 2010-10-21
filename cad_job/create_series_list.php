<?php
	session_cache_limiter('nocache');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include_once("../auto_logout.php");	
	
	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables 
	//------------------------------------------------------------------------------------------------------------------
	$studyUIDArr = array();
	$seriesUIDArr = array();
	
	$studyUIDArr  = explode('^', $_POST['studyUIDStr']);
	$seriesUIDArr = explode('^', $_POST['seriesUIDStr']);
	
	$seriesNum = count($studyUIDArr);
	//------------------------------------------------------------------------------------------------------------------

	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
		
		$seriesList = array();
		
		for($j=0; $j<$seriesNum; $j++)
		{
			$sqlStr = "SELECT st.study_id, sr.series_number, sr.series_date, sr.series_time, sr.modality,"
					. " sr.image_number, sr.series_description"
					. " FROM patient_list pt, study_list st, series_list sr"
					. " WHERE sr.series_instance_uid=? AND st.study_instance_uid=?" 
					. " AND st.study_instance_uid=sr.study_instance_uid"
					. " AND pt.patient_id=st.patient_id;";
				
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($seriesUIDArr[$j], $studyUIDArr[$j]));

			array_push($seriesList, $stmt->fetch(PDO::FETCH_ASSOC));
		}
		
		echo json_encode($seriesList);
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
