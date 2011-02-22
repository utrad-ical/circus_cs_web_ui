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
		"execID" => array(
			"type" => "int",
			"min" => 1,
			"errorMes" => "[ERROR] CAD ID is invalid."),
		"feedbackMode" => array(
			"type" => "select",
			"options" => array("personal", "consensual"),
			'oterwise' => "personal"),
		"cadName" => array(
			"type" => "cadname",
			"errorMes" => "[ERROR] 'CAD name' is invalid."),
		"version" => array(
			"type" => "version",
			"errorMes" => "[ERROR] 'Version' is invalid."),
		"studyInstanceUID" => array(
			"type" => "uid",
			"errorMes" => "[ERROR] 'Study instance UID' is invalid."),
		"seriesInstanceUID" => array(
			"type" => "uid",
			"errorMes" => "[ERROR] 'Series instance UID' is invalid."),
		"srcList" => array(
			"type" => "select",
			"options" => array("todaysCAD", "cadLog", "todaysSeries", "series"),
			'oterwise' => "series")
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
			$pdo = new PDO($connStrPDO);

			//----------------------------------------------------------------------------------------------------------
			// Retrieve data from database
			//----------------------------------------------------------------------------------------------------------

			if(isset($params['execID']))
			{
				$sqlStr = "SELECT el.plugin_name, el.version, es.study_instance_uid, es.series_instance_uid,"
						. " el.plugin_type, el.executed_at"
						. " FROM executed_plugin_list el, executed_series_list es"
						. " WHERE el.exec_id=? AND es.exec_id=el.exec_id AND es.series_id=1";

				$result = PdoQueryOne($pdo, $sqlStr, $params['execID'], 'ARRAY_NUM');

				if(!is_null($result))
				{
					$params['cadName'] = $result[0];
					$params['version'] = $result[1];
					$params['studyInstanceUID']  = $result[2];
					$params['seriesInstanceUID'] = $result[3];
					$params['cadExecutedAt']     = $result[5];

					if($result[4] != 1)
					{
						$params['errorMessage'] = "[ERROR] Specified exec ID (" . $params['execID'] . ") is not CAD result.";
					}
				}
				else
				{
					$params['errorMessage'] = "[ERROR] Specified exec ID (" . $params['execID'] . ") is not existed.";
				}
			}
			else if(isset($params['studyInstanceUID']) && isset($params['seriesInstanceUID'])
			        && isset($params['cadName']) && isset($params['version']))
			{
				$sqlStr = "SELECT el.exec_id, el.executed_at FROM executed_plugin_list el, executed_series_list es"
						. " WHERE es.exec_id=el.exec_id AND el.plugin_name=? AND el.version=?"
						. " AND es.series_id=1 AND es.study_instance_uid=? AND es.series_instance_uid=?";
				$sqlParams = array($params['cadName'], $params['version'], $params['studyInstanceUID'], $params['seriesInstanceUID']);

				$result = PdoQueryOne($pdo, $sqlStr, $sqlParams, 'ARRAY_NUM');

				if(!is_null($result))
				{
					$params['execID']        = $result[0];
					$params['cadExecutedAt'] = $result[1];
				}
				else
				{
					$params['errorMessage'] = "[ERROR] CAD result is not specified.";
				}
			}
			else
			{
				$params['errorMessage'] = "[ERROR] CAD result is not specified!!";
			}

			if($params['errorMessage'] == "")
			{
				$params['dispConfidenceFlg'] = 0;
				$params['dispCandidateTagFlg']  = 0;

				$stmt = $pdo->prepare("SELECT * FROM cad_preference WHERE user_id=? AND cad_name=? AND version=?");
				$stmt->execute(array($userID, $params['cadName'], $params['version']));

				$cadPreferenceFlg = ($stmt->rowCount() == 1) ? 1 : 0;

				if($cadPreferenceFlg == 1)
				{
					$result = $stmt->fetch(PDO::FETCH_ASSOC);

					$params['maxDispNum']   = $result['max_disp_num'];
					$params['confidenceTh'] = $result['confidence_threshold'];
					$params['sortKey']      = (isset($_REQUEST['sortKey'])) ? $_REQUEST['sortKey'] : $result['default_sort_key'];

					if(isset($_REQUEST['sortOrder']))  $params['sortOrder'] = $_REQUEST['sortOrder'];
					else                               $params['sortOrder'] = ($result['default_sort_order']) ? 't' : 'f';

					$params['dispConfidenceFlg'] = ($result['disp_confidence_flg']) ? 1 : 0;
					$params['dispCandidateTagFlg']  = ($result['disp_candidate_tag_flg']) ? 1 : 0;
				}

				$sqlStr = "SELECT pt.patient_id, pt.patient_name, pt.sex, st.age, st.study_id, st.study_date,"
						. " sr.series_number, sr.series_date, sr.series_time, sr.modality, sr.series_description,"
						. " sr.body_part, sr.image_width, sr.image_height, sm.path, sm.apache_alias"
						. " FROM patient_list pt, study_list st, series_list sr, storage_master sm"
						. " WHERE sr.series_instance_uid=? AND sr.study_instance_uid=?"
						. " AND sr.study_instance_uid=st.study_instance_uid"
						. " AND pt.patient_id=st.patient_id"
						. " AND sr.storage_id=sm.storage_id";

				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute(array($params['seriesInstanceUID'], $params['studyInstanceUID']));

				$result = $stmt->fetch(PDO::FETCH_NUM);

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
				$params['storagePath']       = $result[14];
				$params['webPath']           = $result[15];

				$stmt = $pdo->prepare("SELECT * FROM cad_master WHERE cad_name=? AND version=?");
				$stmt->execute(array($params['cadName'], $params['version']));

				$result = $stmt->fetch(PDO::FETCH_ASSOC);

				$params['resultType']  = $result['result_type'];
				$params['presentType'] = $result['present_type'];

				$params['resultTableName'] = $result['result_table'];
				$params['scoreTableName']  = $result['score_table'];

				$params['yellowCircleTh'] = $result['yellow_circle_th'];
				$params['doubleCircleTh'] = $result['double_circle_th'];

				if($cadPreferenceFlg == 0)
				{
					$params['maxDispNum']   = $result['max_disp_num'];
					$params['confidenceTh'] = $result['confidence_threshold'];
					$params['sortKey']      = (isset($_REQUEST['sortKey'])) ? $_REQUEST['sortKey'] : $result['default_sort_key'];

					if(isset($_REQUEST['sortOrder']))  $params['sortOrder'] = $_REQUEST['sortOrder'];
					else                               $params['sortOrder'] = ($result['default_sort_order']) ? 't' : 'f'; // 't' : DESC
				}

				$params['seriesDir'] = $params['storagePath'] . $DIR_SEPARATOR . $params['patientID']
									 . $DIR_SEPARATOR . $params['studyInstanceUID']
									 . $DIR_SEPARATOR . $params['seriesInstanceUID'];
				$params['seriesDirWeb'] = $params['webPath'] . $params['patientID']
									    . $DIR_SEPARATOR_WEB . $params['studyInstanceUID']
									    . $DIR_SEPARATOR_WEB . $params['seriesInstanceUID'];

				$params['pathOfCADReslut'] = $params['seriesDir'] . $DIR_SEPARATOR . $SUBDIR_CAD_RESULT
										   . $DIR_SEPARATOR . $params['cadName'] . '_v.' . $params['version'];
				$params['webPathOfCADReslut'] = $params['seriesDirWeb'] . $DIR_SEPARATOR_WEB
											  . $SUBDIR_CAD_RESULT . $DIR_SEPARATOR_WEB . $params['cadName']
											  . '_v.' . $params['version'];

				$params['encryptedPtID'] = PinfoScramble::encrypt($params['patientID'], $_SESSION['key']);

				if($_SESSION['anonymizeFlg'] == 1)
				{
					$params['patientID'] = $params['encryptedPtID'];
					$params['patientName'] = PinfoScramble::scramblePtName();
				}
				//------------------------------------------------------------------------------------------------------

				//--------------------------------------------------------------------------------------------------------------
				// Retrieve feedback data
				//--------------------------------------------------------------------------------------------------------------
				$registMsg = "";
				$params['registTime'] = "";
				$enteredBy = "";
				$consensualFBFlg = ($_SESSION['groupID'] == 'admin' || $_SESSION['groupID'] == 'demo') ? 1 : 0;

				if($params['resultType'] == 1 || $params['resultType'] == 2)
				{
					if($params['resultType'] == 2)
					{
						$params['tableName'] = ($scoreTableName !== "") ? $scoreTableName : "visual_assessment";
					}
					else
					{
						$params['tableName'] = "lesion_feedback";
					}

					$sqlStr = 'SELECT * FROM "' . $params['tableName'] . '" WHERE exec_id=?';
					if($params['feedbackMode'] == "personal")  $sqlStr .= " AND consensual_flg='f' AND entered_by=?";
					else                                       $sqlStr .= " AND consensual_flg='t'";

					$sqlStr .= " AND interrupt_flg='f'";

					$stmt = $pdo->prepare($sqlStr);
					$stmt->bindparam(1, $params['execID']);
					if($params['feedbackMode'] == "personal")  $stmt->bindParam(2, $userID);
					$stmt->execute();

					if($stmt->rowCount() >= 1)
					{
						$result = $stmt->fetch();
						$params['registTime'] = $result['registered_at'];
						$enteredBy  = $result['entered_by'];
						$consensualFBFlg = 1;
					}
					else
					{
						$sqlStr = substr_replace($sqlStr, "'t'", (strlen($sqlStr)-3));
						$stmt = $pdo->prepare($sqlStr);
						$stmt->bindparam(1, $params['execID']);
						if($params['feedbackMode'] == "personal")  $stmt->bindParam(2, $userID);
						$stmt->execute();
						if($stmt->rowCount() >= 1)  $params['interruptFlg'] = 1;
					}

				}
				//------------------------------------------------------------------------------------------------------

				//------------------------------------------------------------------------------------------------------
				// Retrieve tag data
				//------------------------------------------------------------------------------------------------------
				$sqlStr = "SELECT tag, entered_by FROM tag_list WHERE category=4 AND reference_id=? ORDER BY sid ASC";
				$params['tagArray'] = PdoQueryOne($pdo, $sqlStr, $params['execID'], 'ALL_NUM');
				//------------------------------------------------------------------------------------------------------

				if($params['resultType'] == 1)
				{
					$stmt = $pdo->prepare("SELECT * FROM param_set WHERE exec_id=?");
					$stmt->bindParam(1, $params['execID']);
					$stmt->execute();

					$result = $stmt->fetch(PDO::FETCH_ASSOC);

					$params['orgX']         = $result['crop_org_x'];
					$params['orgY']         = $result['crop_org_y'];
					$params['cropWidth']    = $result['crop_width'];
					$params['cropHeight']   = $result['crop_height'];
					$params['pixelSize']    = $result['pixel_size'];
					$params['distSlice']    = $result['dist_slice'];
					$params['isotropicFlg'] = $result['isotropic_flg'];
					$params['sliceOffset']  = $result['slice_offset'];

					$params['windowLevel']  = $result['window_level'];
					$params['windowWidth']  = $result['window_width'];

					$params['dispWidth'] = 256;
					$params['dispHeight'] = (int)($params['cropHeight'] * (256 / $params['cropWidth']) + 0.5);

					$stmt = $pdo->prepare("SELECT modality FROM cad_series WHERE cad_name=? AND version=? AND series_id=1");
					$stmt->execute(array($params['cadName'], $params['version']));

					$params['mainModality'] = $stmt->fetchColumn();

					$params['remarkCand'] = (isset($_REQUEST['remarkCand'])) ? $_REQUEST['remarkCand'] : 0;

					include('lesion_cad_display.php');
				}
				else if($params['resultType'] == 0 || $params['resultType'] == 2)
				{
					// Use preferable template
					$templateName = 'plugin_template/show_' . $params['cadName'] . '_v' . $params['version'] . '.php';
					include($templateName);
				}
			}
		}
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;
?>
