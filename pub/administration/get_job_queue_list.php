<?php
include_once("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(Auth::SERVER_OPERATION);

$dstData = array('message' => "",
				 'jobListHtml' =>"");

try
{
	$jobList = array();

	$pdo = DBConnector::getConnection();

	$sqlStr = "SELECT * FROM job_queue jq, plugin_master pm"
			. " WHERE pm.plugin_id=jq.plugin_id AND jq.status > 0"
			. " ORDER BY jq.registered_at ASC";
	$result = DBConnector::query($sqlStr, null, 'ALL_ASSOC');

	foreach($result as $item)
	{
		$pluginType = '';

		switch($item['type'])
		{
			case 1: $pluginType = 'CAD';		break;
			case 2: $pluginType = 'Research';	break;
		}

		$patientID    = "";
		$studyID      = "";
		$seriesNumber = "";

		if($item['type'] == 1)
		{
			$sqlStr = "SELECT *"
					. " FROM job_queue jq, job_queue_series js, series_join_list sr"
					. " WHERE jq.job_id=?"
					. " AND jq.job_id=js.job_id"
					. " AND js.volume_id=0"
					. " AND sr.series_sid=js.series_sid";

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

		$dstData['jobListHtml'] .= '<tr>'
								.  '<td>' . $item['job_id'] . '</td>'
								.  '<td>' . $item['registered_at'] . '</td>'
								.  '<td>' . $item['exec_user'] . '</td>'
								.  '<td>' . $item['plugin_name'] . ' v.' . $item['version'] . '</td>'
								.  '<td>' . $pluginType . '</td>'
								.  '<td>' . $patientID . '</td>'
								.  '<td>' . $studyID . '</td>'
								.  '<td>' . $seriesNumber . '</td>'
								.  '<td>' . $item['priority'] . '</td>'
								.  '<td>' . $item['pm_id'] . '</td>';

		if( $item['status'] == $PLUGIN_ALLOCATED || $item['status'] == $PLUGIN_PROCESSING)
		{
			$dstData['jobListHtml'] .= '<td>Processing</td>';
		}
		else if($_SESSION['serverOperationFlg'] == 1
				 || $_SESSION['serverSettingsFlg'] == 1 || $userID == $item['exec_user'])
		{
			$dstData['jobListHtml'] .= '<td>'
									.  '<input type="button" class="form-btn" value="delete"'
									.  ' onClick="DeleteJob(' . $item['job_id'] . ');">'
									.  '</td>';
		}
		else
		{
			$dstData['jobListHtml'] .= '<td>&nbsp;</td>';
		}
		$dstData['jobListHtml'] .= '</tr>';
	}
}
catch (PDOException $e)
{
	$pdo->rollBack();
	$dstData['message'] .= '[ERROR] Fail to retrieve plug-in job queue list';
	//$dstData['message'] = $e->getMessage();
}
$pdo = null;
echo json_encode($dstData);

