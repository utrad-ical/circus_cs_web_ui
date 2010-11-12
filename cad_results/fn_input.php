<?php
	session_cache_limiter('none');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include_once("../auto_logout.php");	
	include_once("fn_input_private.php");
	require_once('../class/PersonalInfoScramble.class.php');
	require_once('../class/DcmExport.class.php');	
	require_once('../class/validator.class.php');	
		
	//------------------------------------------------------------------------------------------------------------------
	// Import $_GET variables and validation
	//------------------------------------------------------------------------------------------------------------------
	$params = array();
	$validator = new FormValidator();
	
	$validator->addRules(array(
		"execID" => array(
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
			$pdo = new PDO($connStrPDO);

			$sqlStr = "SELECT pt.patient_id, st.study_instance_uid, sr.series_instance_uid,"
					. " pt.patient_name, sr.image_width, sr.image_height, pt.sex, st.age,"
					. " st.study_id, st.study_date, sr.series_number, sr.series_date, sr.modality,"
					. " sr.series_description, el.plugin_name, el.version, sm.path, sm.apache_alias"
					. " FROM patient_list pt, study_list st, series_list sr, storage_master sm,"
					. " executed_plugin_list el, executed_series_list es" 
			        . " WHERE el.exec_id=? AND es.exec_id=el.exec_id AND es.series_id=1" 
			        . " AND sr.series_instance_uid=es.series_instance_uid" 
			        . " AND st.study_instance_uid=es.study_instance_uid" 
			        . " AND pt.patient_id=st.patient_id" 
			        . " AND sr.storage_id=sm.storage_id;";
					

			$result = PdoQueryOne($pdo, $sqlStr, $params['execID'], 'ARRAY_NUM');
			
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
			$params['cadName']           = $result[14];
			$params['version']           = $result[15];
			
			$params['seriesDir'] = $result[16] . $DIR_SEPARATOR . $params['patientID'] 
								 . $DIR_SEPARATOR . $params['studyInstanceUID']
								 . $DIR_SEPARATOR . $params['seriesInstanceUID'];
					   
			$params['seriesDirWeb'] = $result[17] . $params['patientID'] 
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
			$stmt = $pdo->prepare("SELECT * FROM param_set where exec_id=?");
			$stmt->bindValue(1, $params['execID']);
			$stmt->execute();
			
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
		
			$params['sliceOrigin'] = $result['slice_location_origin'];
			$params['slicePitch']  = $result['slice_location_pitch'];
			$params['sliceOffset'] = $result['slice_offset'];
			//----------------------------------------------------------------------------------------------------
	
			//----------------------------------------------------------------------------------------------------
			// Retrieve locations of lesion candidate	
			//----------------------------------------------------------------------------------------------------
			$stmt = $pdo->prepare("SELECT result_table FROM cad_master WHERE cad_name=? AND version=?");
			$stmt->bindValue(1, $params['cadName']);
			$stmt->bindValue(2, $params['version']);
			$stmt->execute();
			$tableName = $stmt->fetchColumn();
			
			$sqlStr  = 'SELECT sub_id, location_x, location_y, location_z'
			         . ' FROM "' . $tableName . '" WHERE exec_id=? ORDER BY sub_id ASC';
						 
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindParam(1, $params['execID']);
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

			$sqlStr = "SELECT * FROM false_negative_count WHERE exec_id=? AND consensual_flg=?";
	
			if($params['feedbackMode'] == "personal")  $sqlStr .= " AND entered_by=?";

			$sqlParams[] = $params['execID'];
			$sqlParams[] = $consensualFlg;
			if($params['feedbackMode'] == "personal")  $sqlParams[] = $params['userID'];
			
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);
			
			if($stmt->rowCount() == 1)
			{
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				$params['enteredFnNum'] = $result['false_negative_num'];
				$params['status'] = $result['status'];
				$params['registTime'] = $result['registered_at'];
				$params['enteredBy']  = $result['entered_by'];
			}
			
			$sqlStr = "SELECT * FROM false_negative_location"
					. " WHERE exec_id=? AND consensual_flg=?";
			if($params['feedbackMode'] == "personal")  $sqlStr .= " AND entered_by=?";
			$sqlStr .= " ORDER BY location_z ASC, location_y ASC, location_x ASC";

			$result = PdoQueryOne($pdo, $sqlStr, $sqlParams, 'ALL_ASSOC');

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
								. ' WHERE exec_id=? AND sub_id=?';
						$result2 = PdoQueryOne($pdo, $sqlStr,
						                       array($params['execID'], $item['nearest_lesion_id']),
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
					
				//$sqlStr = "SELECT COUNT(*) FROM false_negative_location WHERE exec_id=?"
				//		. " AND entered_by=? AND interrupt_flg='t'";	
				//
				//if($params['feedbackMode'] == "personal")	$sqlStr .= " AND consensual_flg='f'";
				//else										$sqlStr .= " AND consensual_flg='t'";
				//		
				//if(PdoQueryOne($pdo, $sqlStr, array($params['execID'], $params['userID']), 'SCALAR') > 0)
				//{
				//	$params['registTime'] ="";
				//}
			}
			else if($params['feedbackMode'] == "consensual")
			{
				$sqlStr = "SELECT * FROM false_negative_count WHERE exec_id=?"
						. " AND consensual_flg='t' AND status=2";	
								
				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindValue(1, $params['execID']);
				$stmt->execute();
				
				if($stmt->rowCount() == 1)
				{
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					$params['enteredFnNum'] = $result['false_negative_num'];
					$params['status'] = $result['status'];					
					$params['registTime'] = $result['registered_at'];
					$params['userStr'] = $result['entered_by'] . "^0";
					$params['enteredBy'] = $result['entered_by'];
				}
				else
				{
					$params['userStr'] = $params['userID'] . "^0";
					$params['registTime'] = "";
				
					$sqlStr = "SELECT * FROM false_negative_location WHERE exec_id=?"
					        . " AND consensual_flg='f' AND interrupt_flg='f'"
							. " ORDER BY location_z ASC, location_y ASC, location_x ASC";
		
					$stmt = $pdo->prepare($sqlStr);
					$stmt->bindValue(1, $params['execID']);
					$stmt->execute();
					
					$params['enteredFnNum'] = $stmt->rowCount();
					
					if($params['enteredFnNum'] >= 1)
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
									$stmt2->execute(array($params['execID'], $result['nearest_lesion_id']));
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
						
						$sqlStr = "SELECT DISTINCT entered_by FROM false_negative_location"
						        . " WHERE exec_id=? AND consensual_flg='f' ORDER BY entered_by ASC";
			
						$stmt = $pdo->prepare($sqlStr);
						$stmt->bindValue(1, $params['execID']);
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
							
						//$sqlStr = "SELECT COUNT(*) FROM false_negative_location WHERE exec_id=?"
						//		. " AND consensual_flg='t'" . " AND interrupt_flg='t'";	
						//			
						//$stmt = $pdo->prepare($sqlStr);
						//$stmt->bindValue(1, $params['execID']);
						//$stmt->execute();
						//
						//if($stmt->fetchColumn()>0)	$params['registTime'] ="";
					}
				}
			}
			
			$params['posStr'] = implode('^', $fnPosArray);
		
			$tmpArr = explode('^', $params['userStr']);
			$params['userArr'] = array();
			
			for($i=0; $i<count($tmpArr)/2; $i++)
			{
				$params['userArr'][$tmpArr[$i*2]] = $tmpArr[$i*2+1];
			}
			//--------------------------------------------------------------------------------------------------------
		
			//--------------------------------------------------------------------------------------------------------
			// Make one-time ticket
			//--------------------------------------------------------------------------------------------------------
			//$_SESSION['ticket'] = md5(uniqid().mt_rand());
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
				$fontColor = "black";
				
				if($params['feedbackMode'] == "consensual")
				{
					if($params['registTime'] != "")
					{
						$fontColor = "#ff00ff";
					}
					else
					{
						$tmpUserID = strtok($fnPosArray[$j * $DEFAULT_COL_NUM + 4], ',');
						$fontColor = $colorList[$params['userArr'][$tmpUserID]];	
					}
				}
				
				// Position for label
				$colorSet = 0;
				if($params['feedbackMode'] == "consensual")
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

		//--------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------
		require_once('../smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();

		if($_SESSION['anonymizeFlg'] == 1)
		{
			$params['patientID']   = $params['encryptedPtID'];
			$params['patientName'] = PinfoScramble::scramblePtName();
		}
	
		$smarty->assign('params',   $params);

		$smarty->assign('fnData',   $fnData);
		$smarty->assign('candPos',  $candPos);
		
	//	$smarty->assign('presetArr', $presetArr);
	//	$smarty->assign('presetNum', $presetNum);

		$smarty->assign('ticket',   $_SESSION['ticket']);
	
		if($params['dispWidth'] >=256)
		{
			$smarty->assign('widthOfPlusButton', (int)(($params['dispWidth']-256)/2));	
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
