<?php
	//----------------------------------------------------------------------------------------------
	// Create job list
	//----------------------------------------------------------------------------------------------
	$jobList = array();

	$sqlStr = "SELECT * FROM job_queue jq, plugin_master pm"
			. " WHERE pm.plugin_id=jq.plugin_id AND jq.status > 0"
			. " ORDER BY jq.registered_at ASC";
	$result = DBConnector::query($sqlStr, null, 'ALL_ASSOC');

	foreach($result as $item)
	{
		$pluginType = '';

		switch($item['plugin_type'])
		{
			case 1: $pluginType = 'CAD';             break;
			case 2: $pluginType = 'Research';        break;
		}

		$patientID    = "";
		$studyID      = "";
		$seriesNumber = "";

		if($item['plugin_type'] == 1)
		{
			$sqlStr = "SELECT *"
					. " FROM job_queue jq, job_queue_series js, study_list st, series_list sr"
					. " WHERE jq.job_id=? AND jq.job_id=js.job_id"
					. " AND js.series_id=0"
					. " AND sr.sid=js.series_sid"
					. " AND st.study_instance_uid=sr.study_instance_uid";

			$resDetail = DBConnector::query($sqlStr, $item['job_id'], 'ARRAY_ASSOC');

			if($_SESSION['anonymizeFlg'] == 1)
			{
				$patientID = PinfoScramble::encrypt($resDetail['patient_id'], $_SESSION['key']);
			}
			else
			{
				$patientID = $resDetail['patient_id'];
			}

			$studyID      = $resDetail['study_id'];
			$seriesNumber = $resDetail['series_number'];
		}

		$jobList[] = array($item['job_id'],
		                   $item['registered_at'],
						   $item['exec_user'],
		                   $item['plugin_name'] . ' v.' . $item['version'],
						   $pluginType,
						   $patientID,
						   $studyID,
						   $seriesNumber,
						   $item['status']);
	}
	//--------------------------------------------------------------------------------------------------------

?>
