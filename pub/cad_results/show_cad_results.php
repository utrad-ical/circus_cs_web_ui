<?php
	//session_cache_limiter('none');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include_once("../auto_logout.php");

	//------------------------------------------------------------------------------------------------------------------
	// Import $_GET variables and validation
	//------------------------------------------------------------------------------------------------------------------
	$params = array();
	$validator = new FormValidator();

	$validator->addRules(array(
		"jobID" => array(
			"type" => "int",
			"min" => 1,
			"errorMes" => "[ERROR] job ID is invalid."),
		"feedbackMode" => array(
			"type" => "select",
			"options" => array("personal", "consensual"),
			"otherwise" => "personal"),
		"srcList" => array(
			"type" => "select",
			"options" => array("todaysCAD", "cadLog", "todaysSeries", "series"),
			'otherwise' => "series")
		));

	if($validator->validate($_GET))
	{
		$params = $validator->output;
		$params['errorMessage'] = "";
	}
	else
	{
		$params = $validator->output;
		$params['errorMessage'] = implode('<br/>', $validator->errors);
	}

	$params['toTopDir'] = '../';
	$params['pluginType'] = 1;
	$params['tagStr']       = "";
	$params['tagArray']     = array();
	$params['tagEnteredBy'] = "";

	switch($params['srcList'])
	{
		case 'todaysCAD':		$params['listTabTitle'] = "Today's CAD";		break;
		case 'cadLog':			$params['listTabTitle'] = "CAD log";			break;
		case 'todaysSeries':	$params['listTabTitle'] = "Today's series";		break;
		default:				$params['listTabTitle'] = "Series list";		break;	// series
	}

	$userID = $_SESSION['userID'];
	//------------------------------------------------------------------------------------------------------------------

	try
	{

		if($params['errorMessage'] == "")
		{
			// Connect to SQL Server
			$pdo = DBConnector::getConnection();

			//----------------------------------------------------------------------------------------------------------
			// Retrieve data from database
			//----------------------------------------------------------------------------------------------------------
			$sqlStr = "SELECT el.plugin_id, pm.plugin_name, pm.version,"
					. " sr.study_instance_uid, sr.series_instance_uid,"
					. " el.plugin_type, el.executed_at"
					. " FROM executed_plugin_list el, executed_series_list es,"
					. " plugin_master pm, series_list sr"
					. " WHERE el.job_id=? AND es.job_id=el.job_id AND es.series_id=0"
					. " AND pm.plugin_id=el.plugin_id AND sr.sid=es.series_sid";
			$result = DBConnector::query($sqlStr, $params['jobID'], 'ARRAY_NUM');

			if(!is_null($result))
			{
				$params['pluginID']          = $result[0];
				$params['cadName']           = $result[1];
				$params['version']           = $result[2];
				$params['studyInstanceUID']  = $result[3];
				$params['seriesInstanceUID'] = $result[4];
				$params['cadExecutedAt']     = $result[6];

				if($result[5] != 1)
				{
					$params['errorMessage'] = "[ERROR] Specified job ID (" . $params['jobID'] . ") is not CAD result.";
				}
			}
			else
			{
				$params['errorMessage'] = "[ERROR] Specified job ID (" . $params['jobID'] . ") is not existed.";
			}
		}
		
		if($params['errorMessage'] == "")
		{
			// Connect to SQL Server
			$pdo = DBConnector::getConnection();

			$params['dispConfidenceFlg'] = 0;
			$params['dispCandidateTagFlg']  = 0;

			$sqlStr = "SELECT pt.patient_id, pt.patient_name, pt.sex, st.age, st.study_id, st.study_date,"
					. " sr.series_number, sr.series_date, sr.series_time, sr.modality, sr.series_description,"
					. " sr.body_part, sr.image_width, sr.image_height, sm.storage_id, sm.path"
					. " FROM patient_list pt, study_list st, series_list sr, storage_master sm"
					. " WHERE sr.series_instance_uid=?"
					. " AND sr.study_instance_uid=st.study_instance_uid"
					. " AND pt.patient_id=st.patient_id"
					. " AND sr.storage_id=sm.storage_id";
			$result = DBConnector::query($sqlStr, $params['seriesInstanceUID'], 'ARRAY_NUM');

			$params['patientID']         = $result[0];
			$params['patientName']       = $result[1];
			$params['sex']               = $result[2];
			$params['age']               = $result[3];
			$params['studyID']           = $result[4];
			$params['studyDate']         = $result[5];
			$params['seriesID']          = $result[6];
			$params['seriesDate']        = $result[7];
			$params['seriesTime']        = $result[8];
			$params['modality']          = $result[9];
			$params['seriesDescription'] = $result[10];
			$params['bodyPart']          = $result[11];
			$params['orgWidth']          = $result[12];
			$params['orgHeight']         = $result[13];
			$params['storageID']         = $result[14];
			$params['storagePath']       = $result[15];

			// Retrieve parameters for the plug-in
			$sqlStr = "SELECT result_type, result_table, score_table"
					. " FROM plugin_cad_master WHERE plugin_id=?";
			$result = DBConnector::query($sqlStr, $params['pluginID'], 'ARRAY_NUM');

			$params['resultType']      = $result[0];
			$params['resultTableName'] = $result[1];
			$params['scoreTableName']  = $result[2];

			//------------------------------------------------------------------------------------------------------
			// Retrieve paramters from plugin_user_preference table
			//------------------------------------------------------------------------------------------------------
			$sqlStr = "SELECT key, value FROM plugin_user_preference WHERE plugin_id=? AND user_id=?";
			$sqlParams = array($params['pluginID'], $userID);

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);

			if($stmt->rowCount() == 0)
			{
				$sqlParams[1] = $DEFAULT_CAD_PREF_USER;
				$stmt->execute($sqlParams);
			}

			while($result = $stmt->fetch(PDO::FETCH_NUM))
			{
				$params[$result[0]] = $result[1];
			}

			if($params['resultType'] == 1)
			{
				if(isset($_REQUEST['sortKey']))    $params['sortKey'] = $_REQUEST['sortKey'];
				if(isset($_REQUEST['sortOrder']))  $params['sortOrder'] = $_REQUEST['sortOrder'];
			}
			//------------------------------------------------------------------------------------------------------

			$params['seriesDir'] = $params['storagePath'] . $DIR_SEPARATOR . $params['patientID']
								 . $DIR_SEPARATOR . $params['studyInstanceUID']
								 . $DIR_SEPARATOR . $params['seriesInstanceUID'];
			$params['seriesDirWeb'] = 'storage/' . $params['storageID']
								    . '/' . $params['patientID']
								    . '/' . $params['studyInstanceUID']
								    . '/' . $params['seriesInstanceUID'];
			$params['pathOfCADReslut'] = $params['seriesDir'] . $DIR_SEPARATOR . $SUBDIR_CAD_RESULT
									   . $DIR_SEPARATOR . $params['cadName'] . '_v.' . $params['version'];
			$params['webPathOfCADReslut'] = $params['seriesDirWeb'] . '/' . $SUBDIR_CAD_RESULT
										  . '/' . $params['cadName'] . '_v.' . $params['version'];

			$params['encryptedPtID'] = PinfoScramble::encrypt($params['patientID'], $_SESSION['key']);

			if($_SESSION['anonymizeFlg'] == 1)
			{
				$params['patientID'] = $params['encryptedPtID'];
				$params['patientName'] = PinfoScramble::scramblePtName();
			}
			//------------------------------------------------------------------------------------------------------

			//------------------------------------------------------------------------------------------------------
			// Retrieve tag data
			//------------------------------------------------------------------------------------------------------
			$sqlStr = "SELECT tag, entered_by FROM tag_list WHERE category=4 AND reference_id=? ORDER BY sid ASC";
			$params['tagArray'] = DBConnector::query($sqlStr, $params['jobID'], 'ALL_NUM');
			//------------------------------------------------------------------------------------------------------

			if($params['resultType'] == 1)
			{
				$params['remarkCand'] = (isset($_REQUEST['remarkCand'])) ? $_REQUEST['remarkCand'] : 0;

				include('lesion_cad_display.php');
			}
			else if($params['resultType'] == 0 || $params['resultType'] == 2)
			{
				//--------------------------------------------------------------------------------------------------
				// Retrieve feedback data
				//--------------------------------------------------------------------------------------------------
				$params['registMsg'] = "";
				$params['registTime'] = "";
				$enteredBy = "";
				$consensualFBFlg = ($_SESSION['groupID'] == 'admin' || $_SESSION['groupID'] == 'demo') ? 1 : 0;

				if($params['resultType'] == 2)
				{
					$params['tableName'] = ($params['scoreTableName'] !== "") ? $params['scoreTableName'] : "visual_assessment";
				}

				$sqlStr = "SELECT registered_at, entered_by FROM feedback_list WHERE job_id=? AND status=1";
				
				if($params['feedbackMode'] == "personal")  $sqlStr .= " AND is_consensual='f' AND entered_by=?";
				else                                       $sqlStr .= " AND is_consensual='t'";

				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindparam(1, $params['jobID']);
				if($params['feedbackMode'] == "personal")  $stmt->bindParam(2, $userID);
				$stmt->execute();

				if($stmt->rowCount() >= 1)
				{
					$result = $stmt->fetch(PDO::FETCH_NUM);
					$params['registTime'] = $result[0];
					$enteredBy  = $result[1];
					$consensualFBFlg = 1;

					$params['registMsg'] = 'registered at ' . $params['registTime'];
					if($params['feedbackMode'] == "consensual")
					{
						$params['registMsg'] .= ' (by ' . $enteredBy. ')';
					}
				}
				//--------------------------------------------------------------------------------------------------

				// Use preferable template
				$templateName = 'plugin_template/show_' . $params['cadName'] . '_v' . $params['version'] . '.php';
				include($templateName);
			}
		}
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;
?>
