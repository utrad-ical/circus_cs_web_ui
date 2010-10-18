<?php
	//session_cache_limiter('none');
	session_start();

	include("../common.php");
	include("show_cad_results_private.php");
	require_once('../class/PersonalInfoScramble.class.php');
	
	//------------------------------------------------------------------------------------------------------------------
	// Auto logout (session timeout)
	//------------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'])  header('location: ../index.php?mode=timeout');
	else	$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//------------------------------------------------------------------------------------------------------------------
	$param = array('toTopDir'          => "../",
	               'pluginType'        => 1,
	               'studyInstanceUID'  => (isset($_REQUEST['studyInstanceUID'])) ? $_REQUEST['studyInstanceUID'] : "",
	               'seriesInstanceUID' => (isset($_REQUEST['seriesInstanceUID'])) ? $_REQUEST['seriesInstanceUID'] : "",
				   'execID'            => (isset($_REQUEST['execID'])) ? $_REQUEST['execID'] : "",
				   'cadName'           => (isset($_REQUEST['cadName']))  ? $_REQUEST['cadName']  : "",
				   'version'           => (isset($_REQUEST['version'])) ? $_REQUEST['version'] : "",
				   'registFlg'         => (isset($_REQUEST['registFlg'])) ? $_REQUEST['registFlg'] : 0,
				   'interruptFlg'      => (isset($_REQUEST['interruptFlg'])) ? $_REQUEST['interruptFlg'] : 0,
				   'feedbackMode'      => (isset($_REQUEST['feedbackMode'])) ? $_REQUEST['feedbackMode'] : "personal",
				   'srcList'           => (isset($_REQUEST['srcList'])) ? $_REQUEST['srcList'] : "",
				   'tagStr'            => "",
				   'tagArray'          => array(),
				   'tagEnteredBy'      => "");
				   
	$userID = $_SESSION['userID'];
	
	if($param['registFlg']==1 || $param['interruptFlg'] == 1)
	{
		$lesionStr = (isset($_REQUEST['lesionStr'])) ? $_REQUEST['lesionStr'] : "";
		$evalStr   = (isset($_REQUEST['evalStr'])) ? stripslashes($_REQUEST['evalStr']) : "";
	}
	
	$fnNum     = (isset($_REQUEST['fnNum'])) ? $_REQUEST['fnNum'] : -1;
	
	if(isset($_REQUEST['limitDT']))	$limitDT = $_REQUEST['limitDT'];
	else							$limitDT = date('Y-m-d H:i:s', strtotime("-" . $LIMIT_REGIST_DATE . " day"));

	switch($param['srcList'])
	{
		case 'todaysCAD':		$param['listTabTitle'] = "Today's CAD";		break;
		case 'cadLog':			$param['listTabTitle'] = "CAD log";			break;
		case 'todaysSeries':	$param['listTabTitle'] = "Today's series";	break;
		default:				$param['listTabTitle'] = "Series list";		break;	// series
	}
	//------------------------------------------------------------------------------------------------------------------

	try
	{	
		$PinfoScramble = new PinfoScramble();
	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		//--------------------------------------------------------------------------------------------------------------
		// Retrieve data from database
		//--------------------------------------------------------------------------------------------------------------
		
		if($param['execID'] != ""
		   && ($param['studyInstanceUID'] == "" || $param['seriesInstanceUID'] == "" || $param['cadName'] == "" || $param['version'] == ""))
		{
			$sqlStr = "SELECT el.plugin_name, el.version, es.study_instance_uid, es.series_instance_uid,"
					. " el.plugin_type, el.executed_at"
					. " FROM executed_plugin_list el, executed_series_list es"
					. " WHERE el.exec_id=? AND es.exec_id=el.exec_id AND es.series_id=1";
					
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindValue(1, $param['execID']);
			$stmt->execute();
			
			if($stmt->rowCount() == 1)
			{
				$result = $stmt->fetch(PDO::FETCH_NUM);
		
				$param['cadName'] = $result[0];
				$param['version'] = $result[1];
				$param['studyInstanceUID']  = $result[2];
				$param['seriesInstanceUID'] = $result[3];
				$param['cadExecutedAt']     = $result[5];
			
				if($result[4] != 1)
				{
					die("Error: Specified exec ID (" . $param['execID'] . ") is not CAD result.");
				}
			}
			else
			{
				die("Error: Specified exec ID (" . $param['execID'] . ") is not existed.");
			}
		}
		else if($param['execID'] == ""
		     && ($param['studyInstanceUID'] != "" && $param['seriesInstanceUID'] != "" && $param['cadName'] != "" && $param['version'] != ""))
		{
			$sqlStr = "SELECT el.exec_id, el.executed_at FROM executed_plugin_list el, executed_series_list es"
					. " WHERE es.exec_id=el.exec_id AND el.plugin_name=? AND el.version=?"
					. " AND es.series_id=1 AND es.study_instance_uid=? AND es.series_instance_uid=?";
			
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($param['cadName'], $param['version'], $param['studyInstanceUID'], $param['seriesInstanceUID']));

			$result = $stmt->fetch(PDO::FETCH_NUM);
			$param['execID']        = $result[0];
			$param['cadExecutedAt'] = $result[1];
		}
		else
		{
			die("Error: CAD result is not specified!!");
		}
		
		$stmt = $pdo->prepare("SELECT * FROM cad_preference WHERE user_id=? AND cad_name=? AND version=?");
		$stmt->execute(array($userID, $param['cadName'], $param['version']));
				 
		$cadPreferenceFlg = ($stmt->rowCount() == 1) ? 1 : 0;
		
		if($cadPreferenceFlg == 1)
		{
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
	
			$param['maxDispNum']   = $result['max_disp_num'];
			$param['confidenceTh'] = $result['confidence_threshold'];
			$param['sortKey']      = (isset($_REQUEST['sortKey'])) ? $_REQUEST['sortKey'] : $result['default_sort_key'];
		
			if(isset($_REQUEST['sortOrder']))  $param['sortOrder'] = $_REQUEST['sortOrder'];
			else                               $param['sortOrder'] = ($result['default_sort_order']) ? 't' : 'f';
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
		$stmt->execute(array($param['seriesInstanceUID'], $param['studyInstanceUID']));
				 
		$result = $stmt->fetch(PDO::FETCH_NUM);
		
		$patientID         = $result[0];
		$patientName       = $result[1];
		$sex               = $result[2];
		$age               = $result[3];
		$studyID           = $result[4];
		$studyDate         = $result[5];
		$seriesID          = $result[6];
		$seriesDate        = $result[7];
		$seriesTime        = $result[8];
		$modality          = $result[9];
		$seriesDescription = $result[10];
		$bodyPart          = $result[11];
		$orgWidth          = $result[12];
		$orgHeight         = $result[13];
		$storagePath       = $result[14];
		$webPath           = $result[15];
		
		$stmt = $pdo->prepare("SELECT * FROM cad_master WHERE cad_name=? AND version=?");
		$stmt->execute(array($param['cadName'], $param['version']));		
		
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		
		$resultType      = $result['result_type'];
		$presentType     = $result['present_type'];
	
		$resultTableName = $result['result_table'];
		$scoreTableName  = $result['score_table'];
	
		$yellowCircleTh = $result['yellow_circle_th'];
		$doubleCircleTh = $result['double_circle_th'];
		
		if($cadPreferenceFlg == 0)
		{
			$param['maxDispNum']   = $result['max_disp_num'];
			$param['confidenceTh'] = $result['confidence_threshold'];
			$param['sortKey']      = (isset($_REQUEST['sortKey'])) ? $_REQUEST['sortKey'] : $result['default_sort_key'];

			if(isset($_REQUEST['sortOrder']))  $param['sortOrder'] = $_REQUEST['sortOrder'];
			else                               $param['sortOrder'] = ($result['default_sort_order']) ? 't' : 'f'; // 't' : DESC
		}
		
		$seriesDir = $storagePath . $DIR_SEPARATOR . $patientID
		           . $DIR_SEPARATOR . $param['studyInstanceUID']
		           . $DIR_SEPARATOR . $param['seriesInstanceUID'];
		$seriesDirWeb = $webPath . $patientID
		              . $DIR_SEPARATOR_WEB . $param['studyInstanceUID']
		              . $DIR_SEPARATOR_WEB . $param['seriesInstanceUID'];
		$pathOfCADReslut = $seriesDir . $DIR_SEPARATOR . $SUBDIR_CAD_RESULT . $DIR_SEPARATOR . $param['cadName'] . '_v.' . $param['version'];
		$webPathOfCADReslut = $seriesDirWeb . $DIR_SEPARATOR_WEB . $SUBDIR_CAD_RESULT . $DIR_SEPARATOR_WEB . $param['cadName']
		                    . '_v.' . $param['version'];
		
		$param['encryptedPtID'] = $PinfoScramble->Encrypt($patientID, $_SESSION['key']);

		if($_SESSION['anonymizeFlg'] == 1)
		{
			$patientID   = $param['encryptedPtID'];
			$patientName = $PinfoScramble->ScramblePtName();
		}
		//--------------------------------------------------------------------------------------------------------------
	
		//--------------------------------------------------------------------------------------------------------------
		// Retrieve feedback data
		//--------------------------------------------------------------------------------------------------------------
		$registMsg = "";
		$registTime = "";
		$enteredBy = "";
		$consensualFBFlg = ($_SESSION['groupID'] == 'admin' || $_SESSION['groupID'] == 'demo') ? 1 : 0;
	
		if($resultType == 1 || $resultType == 2)
		{
			if($resultType == 2) $tableName = ($scoreTableName !== "") ? $scoreTableName : "visual_assessment";
			else				 $tableName = "lesion_feedback";
		
			$sqlStr = 'SELECT * FROM "' . $tableName . '" WHERE exec_id=?';
			if($param['feedbackMode'] == "personal")  $sqlStr .= " AND consensual_flg='f' AND entered_by=?";		
			else                                      $sqlStr .= " AND consensual_flg='t'";
				
			$sqlStr .= " AND interrupt_flg='f'";
			
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindparam(1, $param['execID']);
			if($param['feedbackMode'] == "personal")  $stmt->bindParam(2, $userID);
			$stmt->execute();

			if($stmt->rowCount() >= 1)
			{
				$result = $stmt->fetch();
				$registTime = $result['registered_at'];
				$enteredBy  = $result['entered_by'];
				$consensualFBFlg = 1;
			}
			else
			{
				$sqlStr = substr_replace($sqlStr, "'t'", (strlen($sqlStr)-3));
				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindparam(1, $param['execID']);
				if($param['feedbackMode'] == "personal")  $stmt->bindParam(2, $userID);
				$stmt->execute();
				if($stmt->rowCount() >= 1)  $param['interruptFlg'] = 1;
			}
			
		}
		//--------------------------------------------------------------------------------------------------------------
	
		//--------------------------------------------------------------------------------------------------------------
		// Retrieve tag data
		//--------------------------------------------------------------------------------------------------------------
		$param['tagArray'] = array();
		
		$stmt = $pdo->prepare("SELECT tag, entered_by FROM executed_plugin_tag WHERE exec_id=? ORDER BY tag_id ASC");
		$stmt->bindValue(1, $param['execID']);
		$stmt->execute();
		$tagNum = $stmt->rowCount();
			
		for($i=0; $i<$tagNum; $i++)
		{
			$result = $stmt->fetch(PDO::FETCH_NUM);
		
			$param['tagArray'][$i] = $result[0];
			
			if($i == 0)
			{
				$param['tagEnteredBy'] = $result[1];
				$param['tagStr'] = $result[0];
			}
			else
			{
				$param['tagStr'] .= ", " . $result[0];
			}
		}	
		//--------------------------------------------------------------------------------------------------------------
	
		if($resultType == 1)
		{
			$stmt = $pdo->prepare("SELECT * FROM param_set WHERE exec_id=?");
			$stmt->bindParam(1, $param['execID']);
			$stmt->execute();
			
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			
			$orgX         = $result['crop_org_x'];
			$orgY         = $result['crop_org_y'];
			$cropWidth    = $result['crop_width'];
			$cropHeight   = $result['crop_height'];
			$pixelSize    = $result['pixel_size'];
			$distSlice    = $result['dist_slice'];
			$isotropicFlg = $result['isotropic_flg'];
			$sliceOffset  = $result['slice_offset'];
			
			$windowLevel  = $result['window_level'];
			$windowWidth  = $result['window_width'];
					
			$dispWidth = 256;	
			$dispHeight = (int)($cropHeight * (256 / $cropWidth) + 0.5);
	
			$stmt = $pdo->prepare("SELECT modality FROM cad_series WHERE cad_name=? AND version=? AND series_id=1");
			$stmt->execute(array($param['cadName'], $param['version']));	
			
			$mainModality = $stmt->fetchColumn();
			
			$param['remarkCand'] = (isset($_REQUEST['remarkCand'])) ? $_REQUEST['remarkCand'] : 0;
				
			include('lesion_cad_display.php');
		}
		else if($resultType == 0 || $resultType == 2)
		{
			// Use preferable template
			$templateName = 'plugin_template/show_' . $param['cadName'] . '_v' . $param['version'] . '.php';
			include($templateName);
		}
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;
?>

