<?php
	session_cache_limiter('none');
	session_start();

	include("../common.php");
	include("fn_input_private.php");	
	
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
	               'studyInstanceUID'  => (isset($_REQUEST['studyInstanceUID'])) ? $_REQUEST['studyInstanceUID'] : "",
	               'seriesInstanceUID' => (isset($_REQUEST['seriesInstanceUID'])) ? $_REQUEST['seriesInstanceUID'] : "",
				   'execID'            => (isset($_REQUEST['execID'])) ? $_REQUEST['execID'] : "",
				   'cadName'           => (isset($_REQUEST['cadName']))  ? $_REQUEST['cadName']  : "",
				   'version'           => (isset($_REQUEST['version'])) ? $_REQUEST['version'] : "",
				   'feedbackMode'      => (isset($_REQUEST['feedbackMode'])) ? $_REQUEST['feedbackMode'] : "");
				   
	$userID = $_SESSION['userID'];
	
	$interruptFNFlg = (isset($_REQUEST['interruptFNFlg'])) ? $_REQUEST['interruptFNFlg'] : 0;	
	$registFNFlg = (isset($_REQUEST['registFNFlg'])) ? $_REQUEST['registFNFlg'] : 0;
	$registTime = (isset($_REQUEST['registTime'])) ? $_REQUEST['registTime'] : "";
	$visibleFlg = (isset($_REQUEST['visibleFlg'])) ? $_REQUEST['visibleFlg'] : 1;

	if(isset($_REQUEST['seriesDir']))
	{
		if(ini_get('magic_quotes_gpc') == "1")  $seriesDir = stripslashes($_REQUEST['seriesDir']);
		else                                    $seriesDir = $_REQUEST['seriesDir'];
	}
	
	$orgWidth  = $_REQUEST['orgWidth'];
	$orgHeight = $_REQUEST['orgHeight'];
	$encryptedPatientID   = (isset($_REQUEST['encryptedPatientID']))   ? $_REQUEST['encryptedPatientID']   : "";
	$encryptedPatientName = (isset($_REQUEST['encryptedPatientName'])) ? $_REQUEST['encryptedPatientName'] : "";

	if($_SESSION['anonymizeFlg'] == 0)
	{
		$patientID   = PinfoDecrypter($encryptedPatientID, $_SESSION['key']);
		$patientName = PinfoDecrypter($encryptedPatientName, $_SESSION['key']);
	}

	$sex =  $_REQUEST['sex'];
	$age = $_REQUEST['age'];
	$seriesDate = $_REQUEST['seriesDate'];
	$modality = $_REQUEST['modality'];
	//--------------------------------------------------------------------------------------------------------
	
	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		if($seriesDir == "" || !isset($_REQUEST['orgWidth']) || !isset($_REQUEST['orgHeight'])
		   || $encryptedPatientID == "" || $encryptedPatientName == ""
		   || !isset($_REQUEST['sex']) || !isset($_REQUEST['age']) || !isset($_REQUEST['seriesDate'])
		   || !isset($_REQUEST['modality']))
		{
			$sqlStr = "SELECT pt.patient_id, pt.patient_name, sm.path, sr.image_width," 
			        . " sr.image_height, pt.sex, st.age, st.study_id, st.study_date, sr.series_number,"
					. " sr.series_date, sr.modality, sr.series_description"
					. " FROM patient_list pt, study_list st, series_list sr, storage_master sm " 
			        . " WHERE sr.series_instance_uid=? AND sr.study_instance_uid=?" 
			        . " AND sr.study_instance_uid=st.study_instance_uid" 
			        . " AND pt.patient_id=st.patient_id" 
			        . " AND sr.storage_id=sm.storage_id;";
					
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($param['seriesInstanceUID'], $param['studyInstanceUID']));
					
			$result = $stmt->fetch(PDO::FETCH_NUM);
			
			$patientID         = $result[0];
			$patientName       = $result[1];
			$sex               = $result[5];
			$age               = $result[6];
			$studyID           = $result[7];
			$studyDate         = $result[8];
			$seriesID          = $result[9];
			$seriesDate        = $result[10];
			$modality          = $result[11];
			$seriesDescription = $result[12];
			
			$seriesDir = $result[2] . $DIR_SEPARATOR . $result[0]
			           . $DIR_SEPARATOR . $param['studyInstanceUID']
					   . $DIR_SEPARATOR . $param['seriesInstanceUID'];
			$orgWidth  = $result[3];
			$orgHeight = $result[4];
			
			$encryptedPatientID   = PinfoEncrypter($patientID, $_SESSION['key']);
			$encryptedPatientName = PinfoEncrypter($patientName, $_SESSION['key']);
		}
		
		$dispWidth  = $orgWidth;
		$dispHeight = $orgHeight;
		
		if($dispWidth >= $dispHeight && $dispWidth > 256)
		{
			$dispWidth  = 256;
			$dispHeight = (int)((float)$orgHeight * (float)$dispWidth/(float)$orgWidth);
		}
		else if($dispHeight > 256)
		{
			$dispHeight = 256;
			$dispWidth  = (int)((float)$orgWidth * (float)$dispHeight/(float)$orgHeight);
		}
		
		$dispWidth  = (int)($dispWidth  * $RESCALE_RATIO);
		$dispHeight = (int)($dispHeight * $RESCALE_RATIO);
		
		$windowLevel = isset($_REQUEST['windowLevel']) ? $_REQUEST['windowLevel'] : 0;
		$windowWidth = isset($_REQUEST['windowWidth']) ? $_REQUEST['windowWidth'] : 0;
		$presetName  = isset($_REQUEST['presetName'])  ? $_REQUEST['presetName']  : "";
		$grayscaleStr   = $_REQUEST['grayscaleStr'];
	
		if(!isset($_REQUEST['grayscaleStr']))
		{	
			$stmt = $pdo->prepare("SELECT * FROM grayscale_preset WHERE modality=? ORDER BY priolity ASC");
			$stmt->bindParam(1, $modality);
			$stmt->execute();

			$grayscaleStr = "";
			$cnt = 0;
			
			while($result = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				if($result['priolity'] == 1)
				{
					$windowLevel = $result['window_level'];
					$windowWidth = $result['window_width'];
					$presetName  = $result['preset_name'];
				}
				
				if($cnt > 0)  $grayscaleStr .= '^';
				
				$grayscaleStr .= $result['preset_name'] . '^' . $result['window_level'] . '^' . $result['window_width'];
				$cnt++;
			}
		}
		
		$sliceOrigin = $_REQUEST['sliceOrigin'];
		$slicePitch  = $_REQUEST['slicePitch'];
		$sliceOffset = $_REQUEST['sliceOffset'];
		
		if(!isset($_REQUEST['sliceOrigin']) || !isset($_REQUEST['slicePitch']) || !isset($_REQUEST['sliceOffset']))
		{
			$stmt = $pdo->prepare("SELECT * FROM param_set where exec_id=?");
			$stmt->bindParam(1, $param['execID']);
			$stmt->execute();
			
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
		
			$sliceOrigin = $result['slice_location_origin'];
			$slicePitch  = $result['slice_location_pitch'];
			$sliceOffset = $result['slice_offset'];
		}	
	
	
		if(isset($_REQUEST['candStr']) && isset($_REQUEST['tableName']))
		{
			$candStr = $_REQUEST['candStr'];
			$tableName = $_REQUEST['tableName'];
		}
		else
		{
			$stmt = $pdo->prepare("SELECT result_table FROM cad_master WHERE cad_name=? AND version=?");
			$stmt->execute(array($param['cadName'], $param['version']));
			$tableName = $stmt->fetchColumn();
		
			$sqlStr  = 'SELECT sub_id, location_x, location_y, location_z'
			         . ' FROM "' . $tableName . '" WHERE exec_id=? ORDER BY sub_id ASC';
					 
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindParam(1, $param['execID']);
			$stmt->execute();
			$lesionNum = $stmt->rowCount(); 
			
			$candStr = "";
			
			for($i=0; $i<$lesionNum; $i++)
			{
				$result = $stmt->fetch(PDO::FETCH_NUM);
				
				if($i > 0)  $candStr .= "^";
				$candStr .= $result[0] . "^" . $result[1] . "^" . $result[2] . "^" . $result[3];
			}
		}
		
		$imgNum = (isset($_REQUEST['imgNum'])) ? $_REQUEST['imgNum'] : $sliceOffset+1;
		$sliceLoc = sprintf("%.2f", ($imgNum - $sliceOffset - 1) * $slicePitch + $sliceOrigin);
		
		$enteredFnNum = (isset($_REQUEST['rowNum'])) ? $_REQUEST['rowNum'] : 0;
		
		if($_SESSION['ticket'] == $_REQUEST['ticket'])
		{
			$posStr = (isset($_REQUEST['posStr'])) ? $_REQUEST['posStr'] : "";
			$userStr = (isset($_REQUEST['userStr'])) ? $_REQUEST['userStr'] : "";
		}
		
		$posArr = array();
		//echo $posStr;
		//---------------------------------------------------------------------------------------------------------
		
		//---------------------------------------------------------------------------------------------------------
		// Temporary registration of clinical feedback
		//---------------------------------------------------------------------------------------------------------
		$interruptFlg = (isset($_REQUEST['interruptFlg'])) ? $_REQUEST['interruptFlg'] : 0;
		
		if($interruptFlg == 1 && ($_SESSION['ticket'] == $_REQUEST['ticket']) && $_SESSION['groupID'] != 'demo')
		{
			$resultType = $_REQUEST['resultType'];
			$lesionStr = (isset($_REQUEST['lesionStr'])) ? $_REQUEST['lesionStr'] : "";
			$evalStr   = (isset($_REQUEST['evalStr'])) ? $_REQUEST['evalStr'] : "";
			$fnNum     = (isset($_REQUEST['fnNum'])) ? $_REQUEST['fnNum'] : -1;
			include("registration_of_feedbacks.php");
		}
		//---------------------------------------------------------------------------------------------------------
		
		//---------------------------------------------------------------------------------------------------------
		// Registration of feedback
		//---------------------------------------------------------------------------------------------------------
		$nearestLesionId= 0;
		$enteredBy = "";
		$registMsg = "";
		$consRegistSucessFlg = 0;
		
	
		if(($_SESSION['ticket'] == $_REQUEST['ticket']) && $_SESSION['groupID'] != 'demo' && $registFNFlg == 1)
		//    && ($registFNFlg == 1 || $interruptFNFlg == 1))
		{
			include("fn_registration_private.php");
			include("fn_registration_".$param['feedbackMode'].".php");
		}
		else if(($interruptFNFlg == 0 && $posStr == "") || ($param['feedbackMode'] == "consensual" &&  $userStr == ""))
		{
			$sqlStr = "SELECT * FROM false_negative_location WHERE exec_id=?";
			if($param['feedbackMode'] == "personal")         $sqlStr .= " AND consensual_flg='f' AND entered_by=?";		
			else if($param['feedbackMode'] == "consensual")  $sqlStr .= " AND consensual_flg='t'";
			$sqlStr .= " ORDER BY location_z ASC, location_y ASC, location_x ASC";
			
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindParam(1, $param['execID']);
			if($param['feedbackMode'] == "personal")  $stmt->bindParam(2, $userID);
			$stmt->execute();
			$enteredFnNum = $stmt->rowCount();
			
			if($enteredFnNum >= 1)
			{
				while($result = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$registTime = $result['registered_at'];
					$enteredBy  = $result['entered_by'];
					array_push($posArr, $result['location_x']);
					array_push($posArr, $result['location_y']);
					array_push($posArr, $result['location_z']);
					
					//echo $result['location_x'] . ' ' . $result['location_y'] . ' ' . $result['location_z'] . ' ' . $result['nearest_lesion_id'] . '<br>';
					
					if($result['nearest_lesion_id'] > 0)
					{
						$sqlStr = 'SELECT location_x, location_y, location_z FROM "' . $tableName . '"'
								. ' WHERE exec_id=? AND sub_id=?';
								
						$stmt2 = $pdo->prepare($sqlStr);
						$stmt2->execute(array($param['execID'], $result['nearest_lesion_id']));
									 
						$result2 = $stmt2->fetch(PDO::FETCH_NUM);
							
						$dist = (($result['location_x']-$result2[0])*($result['location_x']-$result2[0])
						      + ($result['location_y']-$result2[1])*($result['location_y']-$result2[1]))
						      + ($result['location_z']-$result2[2])*($result['location_z']-$result2[2]);
					
						array_push($posArr, sprintf("%d / %.2f", $result['nearest_lesion_id'],  sqrt($dist)));
					}
					else array_push($posArr, '- / -');
	
					array_push($posArr, $result['entered_by']);
					array_push($posArr, $result['location_id']);
				}
				$userStr = $enteredBy . "^0";
			
				$sqlStr = "SELECT COUNT(*) FROM false_negative_location WHERE exec_id=?"
						. " AND entered_by=? AND interrupt_flg='t'";	
				
				if($param['feedbackMode'] == "personal")         $sqlStr .= " AND consensual_flg='f'";
				else if($param['feedbackMode'] == "consensual")  $sqlStr .= " AND consensual_flg='t'";
				
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute(array($param['execID'], $userID));

				if($stmt->fetchColumn()>0)	$registTime ="";
			
			}
			else if($param['feedbackMode'] == "consensual")
			{
				$sqlStr = "SELECT registered_at, entered_by FROM false_negative_count WHERE exec_id=?"
						. " AND consensual_flg='t' AND status=2";	
							
				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindParam(1, $param['execID']);
				$stmt->execute();
			
				if($stmt->rowCount() == 1)
				{
					$result = $stmt->fetch(PDO::FETCH_NUM);
					$registTime = $result[0];
					$userStr = $result[1] . "^0";
					$enteredBy = $result[1];
				}
				else
				{
					$userStr = $userID . "^0";
			
					$sqlStr = "SELECT * FROM false_negative_location WHERE exec_id=?"
					        . " AND consensual_flg='f' AND interrupt_flg='f'"
							. " ORDER BY location_z ASC, location_y ASC, location_x ASC";
	
					$stmt = $pdo->prepare($sqlStr);
					$stmt->bindParam(1, $param['execID']);
					$stmt->execute();
					$enteredFnNum = $stmt->rowCount();
				
					if($enteredFnNum >= 1)
					{
						$nearestLesionCnt = 0;
						$nearestLesionArr = array();
					
						while($result = $stmt->fetch(PDO::FETCH_ASSOC))
						{
	
							if($result['nearest_lesion_id'] > 0) // 近傍病変候補がある場合
							{
								$dupePos = -1;
								
								for($i=0; $i<$nearestLesionCnt; $i++)
								{
									if($nearestLesionArr[$i] == $result['nearest_lesion_id'])
									{
										$dupePos = $i;
										break;
									}
								}

								if($dupePos == -1) // 近傍病変候補が既にposArrに含まれていない場合
								{
									$sqlStr = 'SELECT location_x, location_y, location_z FROM "' . $tableName . '"'
											. ' WHERE exec_id=? AND sub_id=?';
								
									$stmt2 = $pdo->prepare($sqlStr);
									$stmt2->execute(array($param['execID'], $result['nearest_lesion_id']));
									$result2 = $stmt2->fetch(PDO::FETCH_NUM);

									array_push($posArr, $result2[0]);
									array_push($posArr, $result2[1]);
									array_push($posArr, $result2[2]);
									array_push($posArr, sprintf("%d / 0.00", $result['nearest_lesion_id']));
									array_push($posArr, $result['entered_by']);
									array_push($posArr, $result['location_id']);
									
									$nearestLesionArr[$nearestLesionCnt] = $result['nearest_lesion_id'];
									$nearestLesionCnt++;
								}
								else // 近傍病変候補が既にposArrに含まれている場合（自動統合）
								{
									$posArr[$dupePos * $DEFAULT_COL_NUM + 4] .= ', ' . $result['entered_by'];
									$posArr[$dupePos * $DEFAULT_COL_NUM + 5] .= ', ' . $result['location_id'];
									$enteredFnNum--; // 重複分を減算
								}
							}
							else // 近傍病変候補がない場合
							{
								//----------------------------------------------------------------------------
								// 同一座標の有無をチェック
								//----------------------------------------------------------------------------
								$posCnt = count($posArr) / $DEFAULT_COL_NUM;
								
								$dupePos = -1;
								
								for($i=0; $i<$posCnt; $i++)
								{
									if($posArr[$i * $DEFAULT_COL_NUM] == $result['location_x']
									   && $posArr[$i * $DEFAULT_COL_NUM + 1] == $result['location_y']
									   && $posArr[$i * $DEFAULT_COL_NUM + 2] == $result['location_z'])
									{
										$dupePos = $i;
										break;
									}
								}
								//----------------------------------------------------------------------------
							
								if($dupePos == -1) // 重複がない場合
								{
									array_push($posArr, $result['location_x']);
									array_push($posArr, $result['location_y']);
									array_push($posArr, $result['location_z']);
							
									if($result['nearest_lesion_id'] == -1)  array_push($posArr, 'BT');
									else                                    array_push($posArr, '- / -');
		
									array_push($posArr, $result['entered_by']);
									array_push($posArr, $result['location_id']);
								}
								else // 重複がある場合（自動統合）
								{
									$posArr[$dupePos * $DEFAULT_COL_NUM + 4] .= ', ' . $result['entered_by'];
									$posArr[$dupePos * $DEFAULT_COL_NUM + 5] .= ', ' . $result['location_id'];
									$enteredFnNum--; // 重複分を減算
								}
							}
						}
					
						$sqlStr = "SELECT DISTINCT entered_by FROM false_negative_location"
						        . " WHERE exec_id=? AND consensual_flg='f' ORDER BY entered_by ASC";
		
						$stmt = $pdo->prepare($sqlStr);
						$stmt->bindParam(1, $param['execID']);
						$stmt->execute();
						
						$userCnt = 0;
					
						while($result = $stmt->fetch(PDO::FETCH_NUM))
						{			
							if($result[0] != $userID)
							{
								$userCnt = (++$userCnt % 4);
								$userStr .= "^" . $result[0] . "^" . $userCnt;
							}
						}
						
						$sqlStr = "SELECT COUNT(*) FROM false_negative_location WHERE exec_id=?"
								. " AND consensual_flg='t'" . " AND interrupt_flg='t'";	
								
						$stmt = $pdo->prepare($sqlStr);
						$stmt->bindParam(1, $param['execID']);
						$stmt->execute();
				
						if($stmt->fetchColumn()>0)	$registTime ="";
					}
				}
			}
			else	$posArr = explode('^', $posStr);
		}
		else	$posArr = explode('^', $posStr);
	
		$tmpArr = explode('^', $userStr);
		$userArr = array();
		
		for($i=0; $i<count($tmpArr)/2; $i++)
		{
			$userArr[$tmpArr[$i*2]] = $tmpArr[$i*2+1];
		}
		//--------------------------------------------------------------------------------------------------------
	
		//--------------------------------------------------------------------------------------------------------
		// Make one-time ticket
		//--------------------------------------------------------------------------------------------------------
		$_SESSION['ticket'] = md5(uniqid().mt_rand());
		//--------------------------------------------------------------------------------------------------------
	
		$flist = array();
		$flist = GetDicomFileListInPath($seriesDir);
		$fNum = count($flist);
			
		$subDir = $seriesDir . $DIR_SEPARATOR . $SUBDIR_JPEG;
		if(!is_dir($subDir))	mkdir($subDir);
			
		$tmpFname = $flist[$imgNum-1];
	
		$inFname  = $seriesDir . $DIR_SEPARATOR . $tmpFname;
		$outFname = $subDir . $DIR_SEPARATOR . substr($tmpFname, 0, strlen($tmpFname)-4);
		if($presetName != "" && $presetName != "Auto")  $outFname .= "_" . $presetName;
		$outFname .= '.jpg';
		//--------------------------------------------------------------------------------------------------------
	
		//--------------------------------------------------------------------------------------------------------
		// Grascale preset
		//--------------------------------------------------------------------------------------------------------
		$presetArr = array();
		$presetNum = 0;
	
		if($grayscaleStr != "")
		{
			$tmpArr = explode("^", $grayscaleStr);
			
			$presetNum = (int)(count($tmpArr)/3);
		
			for($i=0; $i<$presetNum; $i++)
			{
				$presetArr[$i][0] = $tmpArr[$i * 3];
				$presetArr[$i][1] = $tmpArr[$i * 3 + 1];
				$presetArr[$i][2] = $tmpArr[$i * 3 + 2];
			}
		}
		//--------------------------------------------------------------------------------------------------------
	
		//echo $posStr;
	
		//--------------------------------------------------------------------------------------------------------
		// Location list
		//--------------------------------------------------------------------------------------------------------
		$locationList = array();
	
		for($j=0; $j<$enteredFnNum; $j++)
		{
			$fontColor = "black";
			
			if($param['feedbackMode'] == "consensual")
			{
				if($registTime != "")
				{
					$fontColor = "#ff00ff";
				}
				else
				{
					$tmpUserID = strtok($posArr[$j * $DEFAULT_COL_NUM + 4], ',');
					$fontColor = $colorList[$userArr[$tmpUserID]];	
				}
			}
		
			$locationList[$j][0] = $fontColor;
			$locationList[$j][1] = $posArr[$j*$DEFAULT_COL_NUM];											// posX
			$locationList[$j][2] = $posArr[$j*$DEFAULT_COL_NUM+1];											// posY
			$locationList[$j][3] = $posArr[$j*$DEFAULT_COL_NUM+2];											// posZ
			$locationList[$j][4] = $posArr[$j*$DEFAULT_COL_NUM+3];											// information of nearest cand.
			$locationList[$j][5] = ($consRegistSucessFlg != 1) ? $posArr[$j*$DEFAULT_COL_NUM+4] : $userID;	// entered by
			$locationList[$j][6] = $posArr[$j*$DEFAULT_COL_NUM+5];
			
			// Position for label
			$locationList[$j][7] = 0;
			if($param['feedbackMode'] == "consensual")
			{
				$tmpUserID = strtok($posArr[$j * $DEFAULT_COL_NUM + 4], ',');
				$locationList[$j][7] = $userArr[$tmpUserID];
			}
		}
		
		// ↓画面遷移毎にFN一時登録が必要な場合は入れる？（要動作確認、特にConsensual)
		//if($registTime == "")  $interruptFNFlg = 1;
		
		//--------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------
		//エラーが発生した場合にエラー表示をする設定
		ini_set( 'display_errors', 1 );
	
		require_once('../smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
	
		$smarty->assign('param', $param);
	
		$smarty->assign('inFname',  $inFname);
		$smarty->assign('outFname', $outFname);
		
		$smarty->assign('seriesDir',  $seriesDir);

		$smarty->assign('imgNum',  $imgNum);
		$smarty->assign('fNum',    $fNum);
		
		$smarty->assign('userStr',  $userStr);
		$smarty->assign('candStr',  $candStr);
		
		$smarty->assign('userID',  $userID);
		$smarty->assign('encryptedPatientID',  $encryptedPatientID);
		$smarty->assign('encryptedPatientName',  $encryptedPatientName);
		
		if($_SESSION['anonymizeFlg'] == 1)
		{
			$smarty->assign('patientID',   $encryptedPatientID);
			$smarty->assign('patientName', ScramblePatientName());
		}
		else
		{
			$smarty->assign('patientID',   $patientID);
			$smarty->assign('patientName', $patientName);	
		}
		$smarty->assign('sex',  $sex);
		$smarty->assign('age',  $age);
		$smarty->assign('studyID',           $studyID);
		$smarty->assign('studyDate',         $studyDate);
		$smarty->assign('seriesID',          $seriesID);
		$smarty->assign('seriesDate',        $seriesDate);
		$smarty->assign('seriesDescription', $seriesDescription);
		$smarty->assign('modality',          $modality);
	
		$smarty->assign('tableName',    $tableName);
		$smarty->assign('grayscaleStr', $grayscaleStr);
		$smarty->assign('presetName',   $presetName);
		$smarty->assign('windowLevel',  $windowLevel);
		$smarty->assign('windowWidth',  $windowWidth);
	
		$smarty->assign('sliceOrigin',  $sliceOrigin);
		$smarty->assign('slicePitch',   $slicePitch);
		$smarty->assign('sliceOffset',  $sliceOffset);
		$smarty->assign('sliceLoc',     $sliceLoc);
			
		$smarty->assign('distTh',       $DIST_THRESHOLD);
		$smarty->assign('orgWidth',     $orgWidth);
		$smarty->assign('orgHeight',    $orgHeight);
		$smarty->assign('dispWidth',    $dispWidth);
		$smarty->assign('dispHeight',   $dispHeight);
		
		$smarty->assign('registFNFlg',     $registFNFlg);
		$smarty->assign('visibleFlg',      $visibleFlg);
		$smarty->assign('interruptFNFlg',  $interruptFNFlg);
		
		
		$smarty->assign('registTime',  $registTime);
		$smarty->assign('ticket',      htmlspecialchars($_SESSION['ticket'], ENT_QUOTES));
		
		if($param['feedbackMode'] =="consensual")
		{
			$smarty->assign('enteredBy', $enteredBy);
		}
	
		$smarty->assign('registMsg', $registMsg);
	
		$smarty->assign('presetArr', $presetArr);
		$smarty->assign('presetNum', $presetNum);
	
		$smarty->assign('enteredFnNum',  $enteredFnNum);
		$smarty->assign('locationList', $locationList);
		
		if($dispWidth >=256)
		{
			$smarty->assign('widthOfPlusButton', (int)(($dispWidth-256)/2));	
		}
	
		$smarty->display('cad_results/fn_input.tpl');
		//--------------------------------------------------------------------------------------------------------		
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;

?>
