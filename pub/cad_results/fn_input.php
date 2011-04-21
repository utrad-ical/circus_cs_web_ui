<?php
	session_cache_limiter('none');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include_once("../auto_logout.php");

	//------------------------------------------------------------------------------------------------------------------
	// Definitions
	//------------------------------------------------------------------------------------------------------------------
	//$colorList = array ("#ff00ff", "#228b22", "#ff8000", "#ff0000");
	$colorList = array ("#ff00ff", "#ff8000", "#1e90ff", "#32cd32");

	$RESCALE_RATIO = 1.25;
	$DEFAULT_COL_NUM = 6;
	$DIST_THRESHOLD = 5.0;
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Import $_GET variables and validation
	//------------------------------------------------------------------------------------------------------------------
	$params = array();
	$validator = new FormValidator();

	$validator->addRules(array(
		"jobID" => array(
			"type" => "int",
			"required" => true,
			"min" => 1,
			"errorMes" => "[ERROR] CAD ID is invalid."),
		"feedbackMode" => array(
			"type" => "select",
			"options" => array("personal", "consensual"),
			"errorMes" => "[ERROR] 'Feedback mode' is invalid.")
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
	$params['registTime'] = "";
	$params['distTh'] = $DIST_THRESHOLD;
	$params['enteredFnNum'] = 0;
	$params['status'] = 0;
	$params['userID'] = $_SESSION['userID'];
	//------------------------------------------------------------------------------------------------------------------


	//------------------------------------------------------------------------------------------------------------------
	// Get parameters from database
	//------------------------------------------------------------------------------------------------------------------
	try
	{
		if($params['errorMessage'] == "")
		{
			// Connect to SQL Server
			$pdo = DBConnector::getConnection();

			$sqlStr = "SELECT pt.patient_id, st.study_instance_uid, sr.series_instance_uid,"
					. " pt.patient_name, sr.image_width, sr.image_height, pt.sex, st.age,"
					. " st.study_id, st.study_date, sr.series_number, sr.series_date, sr.modality,"
					. " sr.series_description, el.plugin_id, pm.plugin_name, pm.version, sm.path, sm.apache_alias"
					. " FROM patient_list pt, study_list st, series_list sr, storage_master sm,"
					. " executed_plugin_list el, executed_series_list es, plugin_master pm"
			        . " WHERE el.job_id=? AND es.job_id=el.job_id AND es.series_id=0"
			        . " AND sr.sid=es.series_sid"
			        . " AND st.study_instance_uid=sr.study_instance_uid"
			        . " AND pt.patient_id=st.patient_id"
					. " AND pm.plugin_id=el.plugin_id"
			        . " AND sr.storage_id=sm.storage_id;";

			$result = DBConnector::query($sqlStr, $params['jobID'], 'ARRAY_NUM');

			$params['patientID']         = $result[0];
			$params['studyInstanceUID']  = $result[1];
			$params['seriesInstanceUID'] = $result[2];
			$params['patientName']       = $result[3];
			$params['orgWidth']          = $result[4];
			$params['orgHeight']         = $result[5];
			$params['sex']               = $result[6];
			$params['age']               = $result[7];
			$params['studyID']           = $result[8];
			$params['studyDate']         = $result[9];
			$params['seriesID']          = $result[10];
			$params['seriesDate']        = $result[11];
			$params['modality']          = $result[12];
			$params['seriesDescription'] = $result[13];
			$params['pluginID']          = $result[14];
			$params['cadName']           = $result[15];
			$params['version']           = $result[16];

			$params['seriesDir'] = $result[17] . $DIR_SEPARATOR . $params['patientID']
								 . $DIR_SEPARATOR . $params['studyInstanceUID']
								 . $DIR_SEPARATOR . $params['seriesInstanceUID'];

			$params['seriesDirWeb'] = $result[18] . $params['patientID']
								    . $DIR_SEPARATOR_WEB . $params['studyInstanceUID']
								    . $DIR_SEPARATOR_WEB . $params['seriesInstanceUID'];

			$params['encryptedPatientID'] = PinfoScramble::encrypt($params['patientID'], $_SESSION['key']);

			$params['dispWidth']  = $params['orgWidth'];
			$params['dispHeight'] = $params['orgHeight'];

			if($params['dispWidth'] >= $params['dispHeight'] && $params['dispWidth'] > 256)
			{
				$params['dispWidth']  = 256;
				$params['dispHeight'] = (int)((float)$params['orgHeight'] * (float)$params['dispWidth']/(float)$params['orgWidth']);
			}
			else if($params['dispHeight'] > 256)
			{
				$params['dispHeight'] = 256;
				$params['dispWidth']  = (int)((float)$params['orgWidth'] * (float)$params['dispHeight']/(float)$params['orgHeight']);
			}

			$params['dispWidth']  = (int)($params['dispWidth']  * $RESCALE_RATIO);
			$params['dispHeight'] = (int)($params['dispHeight'] * $RESCALE_RATIO);

			$params['windowLevel']  = $params['windowWidth'] = 0;
			$params['presetName']   = "";
			$params['grayscaleStr'] = "";

			//----------------------------------------------------------------------------------------------------
			// Retrieve preset grayscales
			//----------------------------------------------------------------------------------------------------
			$stmt = $pdo->prepare("SELECT * FROM grayscale_preset WHERE modality=? ORDER BY priolity ASC");
			$stmt->bindParam(1, $params['modality']);
			$stmt->execute();

			$grayscaleArray = array();
			$detailParams['presetArr'] = array();

			while($result = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				if($result['priolity'] == 1)
				{
					$params['windowLevel'] = $result['window_level'];
					$params['windowWidth'] = $result['window_width'];
					$params['presetName']  = $result['preset_name'];
				}

				$grayscaleArray[] = $result['preset_name'];
				$grayscaleArray[] = $result['window_level'];
				$grayscaleArray[] = $result['window_width'];

				$params['presetArr'][] = array($result['preset_name'],
													 $result['window_level'],
													 $result['window_width']);
			}

			$params['grayscaleStr'] = implode('^', $grayscaleArray);
			$params['presetNum'] = count($detailParams['presetArr']);
			//----------------------------------------------------------------------------------------------------

			//----------------------------------------------------------------------------------------------------
			// Retrieve slice origin, slice pitch, slice offset
			//----------------------------------------------------------------------------------------------------
			$stmt = $pdo->prepare("SELECT key, value FROM executed_plugin_attributes WHERE job_id=?");
			$stmt->bindParam(1, $params['jobID']);
			$stmt->execute();
			$result = array();

			foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $item)
			{
				$result[$item['key']] = $item['value'];
			}

			$params['sliceOrigin'] = $result['slice_location_origin'];
			$params['slicePitch']  = $result['slice_location_pitch'];
			$params['sliceOffset'] = $result['slice_offset'];
			//----------------------------------------------------------------------------------------------------

			//----------------------------------------------------------------------------------------------------
			// Retrieve locations of lesion candidate
			//----------------------------------------------------------------------------------------------------
			$sqlStr = "SELECT result_table FROM plugin_cad_master WHERE plugin_id=?";
			$tableName = DBConnector::query($sqlStr, $params['pluginID'], 'SCALAR');

			$sqlStr  = 'SELECT sub_id, location_x, location_y, location_z'
			         . ' FROM "' . $tableName . '" WHERE job_id=? ORDER BY sub_id ASC';

			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindParam(1, $params['jobID']);
			$stmt->execute();
			$lesionNum = $stmt->rowCount();

			$candPos = array();

			while($result = $stmt->fetch(PDO::FETCH_NUM))
			{
				$candPos[] = array('id' => $result[0],
								   'x'  => $result[1],
				                   'y'  => $result[2],
								   'z'  => $result[3]);
			}
			//----------------------------------------------------------------------------------------------------

			//----------------------------------------------------------------------------------------------------
			// Retrieve entered FN locations
			//----------------------------------------------------------------------------------------------------
			$consensualFlg = ($params['feedbackMode'] == "consensual") ? 't' : 'f';
			$sqlParams = array();

			$sqlStr = "SELECT fn.fn_num, fn.status, fl.registered_at, fl.entered_by"
					. " FROM feedback_list fl, fn_count fn"
					. " WHERE fl.job_id=? AND fn.fb_id=fl.fb_id AND fl.is_consensual=?";

			if($params['feedbackMode'] == "personal")  $sqlStr .= " AND fl.entered_by=?";

			$sqlParams[] = $params['jobID'];
			$sqlParams[] = $consensualFlg;
			if($params['feedbackMode'] == "personal")  $sqlParams[] = $params['userID'];

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);

			if($stmt->rowCount() == 1)
			{
				$result = $stmt->fetch(PDO::FETCH_NUM);
				$params['enteredFnNum'] = $result[0];
				$params['status']       = $result[1];
				$params['registTime']   = $result[2];
				$params['enteredBy']    = $result[3];
			}

			$sqlStr = "SELECT * FROM feedback_list fl, fn_location fn"
					. " WHERE fl.job_id=? AND fn.fb_id=fl.fb_id AND fl.is_consensual=?";
			if($params['feedbackMode'] == "personal")  $sqlStr .= " AND fl.entered_by=?";
			$sqlStr .= " ORDER BY fn.location_z ASC, fn.location_y ASC, fn.location_x ASC";

			$result = DBConnector::query($sqlStr, $sqlParams, 'ALL_ASSOC');

			$fnPosArray = array();

			if(count($result) >= 1)
			{
				foreach($result as $item)
				{
					$fnPosArray[] = $item['location_x'];
					$fnPosArray[] = $item['location_y'];
					$fnPosArray[] = $item['location_z'];

					if($item['nearest_lesion_id'] > 0)
					{
						$sqlStr = 'SELECT location_x, location_y, location_z FROM "' . $tableName . '"'
								. ' WHERE job_id=? AND sub_id=?';
						$result2 = DBConnector::query($sqlStr,
						                       array($params['jobID'], $item['nearest_lesion_id']),
											   'ARRAY_NUM');

						$dist = (($item['location_x']-$result2[0])*($item['location_x']-$result2[0])
						      + ($item['location_y']-$result2[1])*($item['location_y']-$result2[1]))
						      + ($item['location_z']-$result2[2])*($item['location_z']-$result2[2]);

						$fnPosArray[] = sprintf("%d / %.2f", $item['nearest_lesion_id'],  sqrt($dist));
					}
					else
					{
						$fnPosArray[] = '- / -';
					}

					$fnPosArray[] = $item['entered_by'];
					$fnPosArray[] = $item['location_id'];
				}

				$params['userStr'] = $params['enteredBy'] . "^0";

				//$sqlStr = "SELECT COUNT(*) FROM fn_location WHERE job_id=?"
				//		. " AND entered_by=? AND interrupted='t'";
				//
				//if($params['feedbackMode'] == "personal")	$sqlStr .= " AND is_consensual='f'";
				//else										$sqlStr .= " AND is_consensual='t'";
				//
				//if(DBConnector::query($sqlStr, array($params['jobID'], $params['userID']), 'SCALAR') > 0)
				//{
				//	$params['registTime'] ="";
				//}
			}
			else if($params['feedbackMode'] == "consensual")
			{
				$sqlStr = "SELECT fn.fn_num, fn.status, fl.registered_at, fl.entered_by"
						. " FROM feedback_location fl, fn_count fn"
						. " WHERE fl.job_id=? AND fl.fb_id=fn.fb_id"
						. " AND fl.is_consensual='t' AND fn.status=2";

				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindValue(1, $params['jobID']);
				$stmt->execute();

				if($stmt->rowCount() == 1)
				{
					$result = $stmt->fetch(PDO::FETCH_NUM);
					$params['enteredFnNum'] = $result[0];
					$params['status']       = $result[1];
					$params['registTime']   = $result[2];
					$params['userStr']      = $result[3] . "^0";
					$params['enteredBy']    = $result[3];
				}
				else
				{
					$params['userStr'] = $params['userID'] . "^0";
					$params['registTime'] = "";

					$sqlStr = "SELECT * FROM feedback_list fl, fn_location fn"
							. " WHERE fl.job_id=? AND fl.fb_id=fn.fb_id"
					        . " AND fl.is_consensual='f' AND fl.status=1"
							. " ORDER BY fn.location_z ASC, fn.location_y ASC, fn.location_x ASC";

					$stmt = $pdo->prepare($sqlStr);
					$stmt->bindValue(1, $params['jobID']);
					$stmt->execute();

					$params['enteredFnNum'] = $stmt->rowCount();

					if($params['enteredFnNum'] >= 1)
					{
						$nearestLesionCnt = 0;
						$nearestLesionArr = array();

						while($result = $stmt->fetch(PDO::FETCH_ASSOC))
						{

							if($result['nearest_lesion_id'] > 0) // in the case of undisplayed candidate is exist
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
											. ' WHERE job_id=? AND sub_id=?';

									$stmt2 = $pdo->prepare($sqlStr);
									$stmt2->execute(array($params['jobID'], $result['nearest_lesion_id']));
									$result2 = $stmt2->fetch(PDO::FETCH_NUM);

									$fnPosArray[] = $result2[0];
									$fnPosArray[] = $result2[1];
									$fnPosArray[] = $result2[2];
									$fnPosArray[] = sprintf("%d / 0.00", $result['nearest_lesion_id']);
									$fnPosArray[] = $result['entered_by'];
									$fnPosArray[] = $result['location_id'];

									$nearestLesionArr[$nearestLesionCnt] = $result['nearest_lesion_id'];
									$nearestLesionCnt++;
								}
								else // 近傍病変候補が既にposArrに含まれている場合（自動統合）
								{
									$fnPosArray[$dupePos * $DEFAULT_COL_NUM + 4] .= ', ' . $result['entered_by'];
									$fnPosArray[$dupePos * $DEFAULT_COL_NUM + 5] .= ', ' . $result['location_id'];
									$params['enteredFnNum']--; // 重複分を減算
								}
							}
							else // 近傍病変候補がない場合
							{
								//----------------------------------------------------------------------------
								// 同一座標の有無をチェック
								//----------------------------------------------------------------------------
								$posCnt = count($fnPosArray) / $DEFAULT_COL_NUM;

								$dupePos = -1;

								for($i=0; $i<$posCnt; $i++)
								{
									if($fnPosArray[$i * $DEFAULT_COL_NUM] == $result['location_x']
									   && $fnPosArray[$i * $DEFAULT_COL_NUM + 1] == $result['location_y']
									   && $fnPosArray[$i * $DEFAULT_COL_NUM + 2] == $result['location_z'])
									{
										$dupePos = $i;
										break;
									}
								}
								//----------------------------------------------------------------------------

								if($dupePos == -1) // 重複がない場合
								{
									$fnPosArray[] = $result['location_x'];
									$fnPosArray[] = $result['location_y'];
									$fnPosArray[] = $result['location_z'];

									if($result['nearest_lesion_id'] == -1)  $fnPosArray[] = 'BT';
									else                                    $fnPosArray[] = '- / -';

									$fnPosArray[] = $result['entered_by'];
									$fnPosArray[] = $result['location_id'];
								}
								else // 重複がある場合（自動統合）
								{
									$fnPosArray[$dupePos * $DEFAULT_COL_NUM + 4] .= ', ' . $result['entered_by'];
									$fnPosArray[$dupePos * $DEFAULT_COL_NUM + 5] .= ', ' . $result['location_id'];
									$params['enteredFnNum']--; // 重複分を減算
								}
							}
						}

						$sqlStr = "SELECT DISTINCT fl.entered_by"
								. " FROM feedback_list fl, fn_location fn"
						        . " WHERE fl.job_id=? AND fn.fb_id=fl.fb_id"
								. " AND fl.is_consensual='f' ORDER BY fl.entered_by ASC";

						$stmt = $pdo->prepare($sqlStr);
						$stmt->bindValue(1, $params['jobID']);
						$stmt->execute();

						$userCnt = 0;

						while($result = $stmt->fetch(PDO::FETCH_NUM))
						{
							if($result[0] != $params['userID'])
							{
								$userCnt = (++$userCnt % 4);
								$params['userStr'] .= "^" . $result[0] . "^" . $userCnt;
							}
						}
					}
				}
			}

			$params['posStr'] = implode('^', $fnPosArray);

			$tmpArr = explode('^', $params['userStr']);
			$params['userArr'] = array();

			for($i = 0; $i < count($tmpArr)/2; $i++)
			{
				$params['userArr'][$tmpArr[$i*2]] = $tmpArr[$i*2+1];
			}
			//--------------------------------------------------------------------------------------------------------

			//--------------------------------------------------------------------------------------------------------
			// Make one-time ticket
			//--------------------------------------------------------------------------------------------------------
			$_SESSION['ticket'] = md5(uniqid().mt_rand());
			//--------------------------------------------------------------------------------------------------------

			//--------------------------------------------------------------------------------------------------------
			// FN入力画面の2D画像ファイル名の生成
			//--------------------------------------------------------------------------------------------------------
			$flist = array();
			$flist = GetDicomFileListInPath($params['seriesDir']);
			$params['imgNum'] = 1;
			$params['fNum'] = count($flist);

			$subDir = $params['seriesDir'] . $DIR_SEPARATOR . $SUBDIR_JPEG;
			if(!is_dir($subDir))	mkdir($subDir);

			$tmpFname = $flist[$params['imgNum']+$params['sliceOffset']-1];

			$srcFname  = $params['seriesDir'] . $DIR_SEPARATOR . $tmpFname;

			// For compresed DICOM file
			$tmpFname = str_ireplace("c_", "_", $tmpFname);
			$tmpFname = substr($tmpFname, 0, strlen($tmpFname)-4);

			$dstFname .= $subDir . $DIR_SEPARATOR . $tmpFname;
			$dstBase = $dstFname;
			$params['dstFnameWeb'] .= $params['seriesDirWeb'] . $DIR_SEPARATOR_WEB . $SUBDIR_JPEG
								   .  $DIR_SEPARATOR_WEB . $tmpFname;

			$dumpFname = $dstFname . ".txt";
			if($params['presetName'] != "" && $params['presetName'] != "Auto")
			{
				$dstFname .= "_" . $params['presetName'];
				$params['dstFnameWeb'] .= "_" . $params['presetName'];
			}
			$dstFname .= '.jpg';
			$params['dstFnameWeb'] .= '.jpg';

			if(!is_file($dstFname))
			{
				DcmExport::createThumbnailJpg($srcFname, $dstBase, $params['presetName'], $JPEG_QUALITY,
		        		                      1, $params['windowLevel'], $params['windowWidth']);
			}

			$fp = fopen($dumpFname, "r");

			$params['sliceLocation'] = 0;

			if($fp != NULL)
			{
				while($str = fgets($fp))
				{
					$dumpTitle   = strtok($str,":");
					$dumpContent = strtok("\r\n");

					switch($dumpTitle)
					{
						case 'Slice location':
							$params['sliceLocation'] = sprintf("%.2f", $dumpContent);
							break;
					}
				}
				fclose($fp);
			}
			//--------------------------------------------------------------------------------------------------------

			//--------------------------------------------------------------------------------------------------------
			// Location list
			//--------------------------------------------------------------------------------------------------------
			$fnData = array();

			for($j=0; $j<$params['enteredFnNum']; $j++)
			{
				// Position for label
				$colorSet = 0;
				if($params['feedbackMode'] == "consensual" && $params['registTime'] == "")
				{
					$tmpUserID = strtok($fnPosArray[$j * $DEFAULT_COL_NUM + 4], ',');
					$colorSet = $params['userArr'][$tmpUserID];
				}

				$fnData[] = array("x"         => $fnPosArray[$j*$DEFAULT_COL_NUM],		// posX
				                  "y"         => $fnPosArray[$j*$DEFAULT_COL_NUM+1],	// posY
								  "z"         => $fnPosArray[$j*$DEFAULT_COL_NUM+2],	// posZ
								  "rank"      => $fnPosArray[$j*$DEFAULT_COL_NUM+3],
								  "enteredBy" => $fnPosArray[$j*$DEFAULT_COL_NUM+4],	// entered by
								  "idStr"     => $fnPosArray[$j*$DEFAULT_COL_NUM+5],
								  "colorSet"  => $colorSet);
			}
		}

		//--------------------------------------------------------------------------------------------------------------
		// Write action log table (personal feedback only)
		//--------------------------------------------------------------------------------------------------------------
		if($params['feedbackMode'] == "personal" && ($params['registTime'] == "" || $params['status'] != 2))
		{
			$sqlStr = "INSERT INTO feedback_action_log (job_id, user_id, act_time, action, options)"
					. " VALUES (?,?,?,'open','FN input')";
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindValue(1, $params['jobID']);
			$stmt->bindValue(2, $params['userID']);
			$stmt->bindValue(3, date('Y-m-d H:i:s'));
			$stmt->execute();

			//$tmp = $stmt->errorInfo();
			//echo $tmp[2];
		}
		//--------------------------------------------------------------------------------------------------------------


		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		$smarty = new SmartyEx();

		if($_SESSION['anonymizeFlg'] == 1)
		{
			$params['patientID']   = $params['encryptedPatientID'];
			$params['patientName'] = PinfoScramble::scramblePtName();
		}

		$smarty->assign('params',   $params);

		$smarty->assign('fnData',     $fnData);
		$smarty->assign('candPos',    $candPos);

		$smarty->assign('colorList',  $colorList);

		$smarty->assign('ticket',   $_SESSION['ticket']);

		if($params['dispWidth'] >=256)
		{
			$smarty->assign('widthOfPlusButton', (int)(($params['dispWidth']-256)/2));
		}

		$smarty->display('cad_results/fn_input.tpl');
		//--------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;

?>
