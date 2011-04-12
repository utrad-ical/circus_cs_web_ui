<?php
	session_cache_limiter('nocache');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include_once("../auto_logout.php");

	$reqSeriesList = array();

	$studyUIDArr = array();
	$seriesUIDArr = array();
	$modalityArr = array();
	$descriptionNumArr = array();
	$seriesDescriptionArr = array();
	$minSliceArr = array();
	$maxSliceArr = array();

	//------------------------------------------------------------------------------------------------------------------
	// Import $_GET variables and validation
	//------------------------------------------------------------------------------------------------------------------
	$params = array();
	$validator = new FormValidator();

	$validator->addRules(array(
		"cadName" => array(
			"type" => "cadname",
			"required" => true,
			"errorMes" => "[ERROR] 'CAD name' is invalid."),
		"version" => array(
			"type" => "version",
			"required" => true,
			"errorMes" => "[ERROR] 'Version' is invalid."),
		"studyInstanceUID" => array(
			"type" => "uid",
			"required" => true,
			"errorMes" => "[ERROR] 'Study instance UID' is invalid."),
		"seriesInstanceUID" => array(
			"type" => "uid",
			"required" => true,
			"errorMes" => "[ERROR] 'Series instance UID' is invalid."),
		"srcList" => array(
			"type" => "select",
			"options" => array("todaysSeries", "series"),
			'default'  => "series",
			'oterwise' => "series")
		));

	if($validator->validate($_GET))
	{
		$params = $validator->output;
		$params['errorMessage'] = "";

		$studyUIDArr[] = $params['studyInstanceUID'];
		$seriesUIDArr[] = $params['seriesInstanceUID'];

	}
	else
	{
		$params = $validator->output;
		$params['errorMessage'] = implode('<br/>', $validator->errors);
	}

	$params['toTopDir'] = "../";
	$params['mode'] ='';
	$params['inputType'] = 0;

	if($params['srcList'] == 'todaysSeries')	$params['listTabTitle'] = "Today's series";
	else										$params['listTabTitle'] = "Series list";

	$userID = $_SESSION['userID'];
	//------------------------------------------------------------------------------------------------------------------

	try
	{
		if($params['errorMessage'] == "")
		{
			// Connect to SQL Server
			$pdo = DBConnector::getConnection();

			//----------------------------------------------------------------------------------------------------------
			// Get initial value from database
			//----------------------------------------------------------------------------------------------------------
			$sqlStr = "SELECT * FROM patient_list pt, study_list st WHERE st.study_instance_uid=?"
					. " AND pt.patient_id=st.patient_id";

			$result = DBConnector::query($sqlStr, $studyUIDArr[0], 'ARRAY_ASSOC');

			$params['patientID']   = $result['patient_id'];
			$params['patientName'] = $result['patient_name'];

			$encryptedPatientID   = PinfoScramble::encrypt($params['patientID'] , $_SESSION['key']);

			$sqlStr = "SELECT cm.input_type FROM plugin_master pm, plugin_cad_master cm"
					. " WHERE cm.plugin_id=pm.plugin_id AND pm.plugin_name=? AND pm.version=?";
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($params['cadName'], $params['version']));

			if($stmt->rowCount() == 1)
			{
				$params['inputType'] = $stmt->fetchColumn();
			}
			else
			{
				$params['errorMessage'] = $params['cadName'] . ' ver.' . $params['version'] . ' is not defined.';
			}
		}

		$seriesNum = 0;

		if($params['errorMessage'] == "")
		{
			// Get plugin ID
			$sqlStr = "SELECT plugin_id FROM plugin_master WHERE plugin_name=? AND version=?";
			$params['pluginID'] = DBConnector::query($sqlStr, array($params['cadName'], $params['version']), 'SCALAR');

			// Set series array
			$sqlStr = "SELECT DISTINCT series_id, modality FROM plugin_cad_series"
	    			. " WHERE plugin_id=? ORDER BY series_id ASC;";
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindValue(1, $params['pluginID']);
			$stmt->execute();

			$seriesNum = $stmt->rowCount();

			$cnt = 0;

			for($j=0; $j<$seriesNum; $j++)
			{
				$seriesIdRes = $stmt->fetch(PDO::FETCH_NUM);

				$modalityArr[$j] = $seriesIdRes[1];		// modality

				$sqlStr = "SELECT series_description, min_slice, max_slice FROM plugin_cad_series"
						. " WHERE plugin_id=? AND series_id=? ORDER BY series_description DESC";

				$stmtDesc = $pdo->prepare($sqlStr);
				$stmtDesc->execute(array($params['pluginID'], $seriesIdRes[0]));

				$tmp = $stmtDesc->rowCount();  // No. of description
				$descriptionNumArr[$j] = $tmp;

				for($i=0; $i<$tmp; $i++)
				{
					$descriptionRes = $stmtDesc->fetch(PDO::FETCH_NUM);

					if(!($descriptionRes[0] == '(default)' && $descriptionRes[1] == 0 && $descriptionRes[2] == 0))
					{
						$seriesDescriptionArr[$cnt] = $descriptionRes[0];
						$minSliceArr[$cnt]          = $descriptionRes[1];
						$maxSliceArr[$cnt]          = $descriptionRes[2];
						$cnt++;
					}
					else $descriptionNumArr[$j]--;
					//echo $seriesIdRes[0]."(".$seriesIdRes[1]."): ".$descriptionRes[0].",".$descriptionRes[1].",".$descriptionRes[2]."<br>";
				}
			}
			//------------------------------------------------------------------------------------------------------

			//------------------------------------------------------------------------------------------------------
			// Main roop
			//------------------------------------------------------------------------------------------------------
			$cnt = $descriptionNumArr[0];

			for($j=1; $j<$seriesNum; $j++)
			{
				$colArr = array();
				$sqlStr = "";

				//----------------------------------------------------------------------------------------------------------
				// Create SQL state
				//----------------------------------------------------------------------------------------------------------
				if($params['inputType'] == 1) // multi series in same study
				{
					$sqlStr = "SELECT study_instance_uid, series_instance_uid FROM series_list"
					        . " WHERE study_instance_uid=? AND modality=?"
							. " AND (";

					$colArr[] = $studyUIDArr[0];
					$colArr[] = $modalityArr[$j];

					for($i=0; $i<$descriptionNumArr[$j]; $i++)
					{
						if($i > 0)  $sqlStr .= " OR ";

						if($seriesDescriptionArr[$cnt+$i] == '(default)')
						{
							$sqlStr .= "(image_number>=? AND image_number<=?)";
							$colArr[] = $minSliceArr[$cnt+$i];
							$colArr[] = $maxSliceArr[$cnt+$i];
						}
						else
						{
							$sqlStr .= "series_description=?";
							$colArr[] = $seriesDescriptionArr[$cnt+$i];
						}
					}

					$sqlStr .= ")";
					$cnt += $descriptionNumArr[$j];

					for($i=0; $i<$j; $i++)
					{
						if($modalityArr[$i] == $modalityArr[$j])
						{
							$sqlStr .= " AND series_instance_uid!=?";
							$colArr[] = $seriesUIDArr[$i];
						}
					}
					$sqlStr .= " ORDER BY series_date ASC, series_time ASC";
				}
				else if($params['inputType'] == 2) // multi series in mulit studies
				{
					$sqlStr = "SELECT st.study_instance_uid, sr.series_instance_uid"
							. " FROM study_list st, series_list sr"
							. " WHERE st.patient_id=?"
							. " AND st.study_instance_uid=sr.study_instance_uid"
							. " AND sr.modality=?"
							. " AND (";

					$colArr[] = $params['patientID'];
					$colArr[] = $modalityArr[$j];

					for($i=0; $i<$descriptionNumArr[$j]; $i++)
					{
						if($i > 0)  $sqlStr .= " OR ";

						if($seriesDescriptionArr[$cnt+$i] == '(default)')
						{
							$sqlStr .= "(image_number>=? AND image_number<=?)";
							$colArr[] = $minSliceArr[$cnt+$i];
							$colArr[] = $maxSliceArr[$cnt+$i];
						}
						else
						{
							$sqlStr .= "series_description=?";
							$colArr[] = $seriesDescriptionArr[$cnt+$i];
						}
					}

					$sqlStr .= ")";
					$cnt += $descriptionNumArr[$j];

					for($i=0; $i<$j; $i++)
					{
						if($modalityArr[$i] == $modalityArr[$j])
						{
							$sqlStr .= " AND NOT (st.study_instance_uid=? AND sr.series_instance_uid=?)";
							array_push($colArr, $studyUIDArr[$i]);
							array_push($colArr, $seriesUIDArr[$i]);
						}
					}
					$sqlStr .= " ORDER BY sr.series_date ASC, sr.series_time ASC";
				}
				//--------------------------------------------------------------------------------------------------

				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($colArr);
				$rowNum = $stmt->rowCount();

				if ($rowNum <= 0)
				{
					$params['mode'] ='error';
					break;
				}
				else
				{
					if(count($modalityArr)==2)
					{
						if($rowNum == 1)
						{
							$result = $stmt->fetch(PDO::FETCH_NUM);
							array_push($studyUIDArr,  $result[0]);
							array_push($seriesUIDArr, $result[1]);
						}
						else
						{
							$params['mode'] = 'select';
						}
					}
					else if(count($modalityArr)>3)
					{
						$result = $stmt->fetch(PDO::FETCH_NUM);
						array_push($studyUIDArr,  $result[0]);
						array_push($seriesUIDArr, $result[1]);
					}
				}
			} // end for
			//----------------------------------------------------------------------------------------------------------

			if($params['mode'] == "")
			{
				if(count($modalityArr)<=2)
				{
					$params['mode'] = 'confirm';
				}
				else
				{
					$params['mode'] = 'select';
				}
			}

			$selectedSeriesArr = array_fill(0, count($modalityArr)+1, 0);

			$defaultSelectedSeriesArr = array();
			$seriesList = array();

			$studyUIDStr = "";
			$seriesUIDStr = "";

			if($params['mode'] == 'confirm')
			{
				for($j = 0; $j < count($seriesUIDArr); $j++)
				{
					$sqlStr = "SELECT pt.patient_id, pt.patient_name, st.study_id, sr.series_number, "
							. " sr.series_date, sr.series_time, sr.modality, sr.image_number, sr.series_description"
							. " FROM patient_list pt, study_list st, series_list sr"
							. " WHERE sr.series_instance_uid=? AND st.study_instance_uid=?"
							. " AND st.study_instance_uid=sr.study_instance_uid"
							. " AND pt.patient_id=st.patient_id;";

					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute(array($seriesUIDArr[$j], $studyUIDArr[$j]));

					$result = $stmt->fetch(PDO::FETCH_NUM);

					if($j==0)
					{
						$params['patientID']   = $result[0];
						$params['patientName'] = $result[1];
					}

					for($i=0; $i<7; $i++)
					{
						$seriesList[$j][$i] = $result[$i+2];
					}

					if($j==0)
					{
						$studyUIDStr = $studyUIDArr[$j];
						$seriesUIDStr = $seriesUIDArr[$j];
					}
					else
					{
						$studyUIDStr  .= "^" . $studyUIDArr[$j];
						$seriesUIDStr .= "^" . $seriesUIDArr[$j];
					}
				}
			}
			else if($params['mode'] == 'select')
			{
				$cnt = 0;

				for($k = 0; $k < count($modalityArr); $k++)
				{
					//--------------------------------------------------------------------------------------------------
					// 1st series of 1st modality
					//--------------------------------------------------------------------------------------------------
					if($k==0)
					{
						$sqlStr = "SELECT st.study_id, sr.series_number, sr.series_date, sr.series_time, "
								. " sr.image_number, sr.series_description"
								. " FROM study_list st, series_list sr"
								. " WHERE sr.series_instance_uid=?"
								. " AND st.study_instance_uid=?"
								. " AND st.study_instance_uid=sr.study_instance_uid";

						$stmt = $pdo->prepare($sqlStr);
						$stmt->execute(array($seriesUIDArr[0], $studyUIDArr[0]));

						$result = $stmt->fetch(PDO::FETCH_NUM);

						$seriesList[0][0][0] = $studyUIDArr[0] . "^" . $seriesUIDArr[0];
						$seriesNumArr[0] = 1;

						for($i=1; $i < 7; $i++)
						{
							$seriesList[0][0][$i] = $result[$i-1];
						}
						$studyUIDStr = $studyUIDArr[0];
						$seriesUIDStr = $seriesUIDArr[0];
					}
					else
					{
						//----------------------------------------------------------------------------------------------
						// Create SQL statement
						//----------------------------------------------------------------------------------------------
						$colArr = array();

						if($params['inputType'] == 1)
						{
							$sqlStr = "SELECT st.study_instance_uid, sr.series_instance_uid,"
							        . " st.study_id, sr.series_number, sr.series_date, sr.series_time, "
									. " sr.image_number, sr.series_description"
									. " FROM study_list st, series_list sr"
									. " WHERE st.study_instance_uid=?"
									. " AND st.study_instance_uid=sr.study_instance_uid"
									. " AND sr.modality=?"
									. " AND (";

							$colArr[] = $studyUIDArr[0];
							$colArr[] = $modalityArr[$k];

							for($j = 0; $j < $descriptionNumArr[$k]; $j++)
							{
								if($j > 0)  $sqlStr .= " OR ";

								if($seriesDescriptionArr[$cnt+$j] == '(default)')
								{
									$sqlStr .= "(sr.image_number>=? AND sr.image_number<=?)";
									$colArr[] = $minSliceArr[$cnt+$j];
									$colArr[] = $maxSliceArr[$cnt+$j];
								}
								else
								{
									$sqlStr .= "sr.series_description=?";
									$colArr[] = $seriesDescriptionArr[$cnt+$j];
								}
							}

							$sqlStr .= ")";

							if($modalityArr[$k] == $modalityArr[0])
							{
								$sqlStr .= " AND sr.series_instance_uid!=?";
								$colArr[] = $seriesUIDArr[0];
							}

							//echo $sqlStr . "<br>";
						}
						else if($params['inputType'] == 2)
						{
							$sqlStr = "SELECT st.study_instance_uid, sr.series_instance_uid,"
						   		    . " st.study_id, sr.series_number, sr.series_date, sr.series_time, "
									. " sr.image_number, sr.series_description"
									. " FROM study_list st, series_list sr"
									. " WHERE st.patient_id=?"
									. " AND st.study_instance_uid=sr.study_instance_uid"
									. " AND sr.modality=?"
									. " AND (";

							$colArr[] = $params['patientID'];
							$colArr[] = $modalityArr[$k];

							for($j = 0; $j < $descriptionNumArr[$k]; $j++)
							{
								if($j > 0)  $sqlStr .= " OR ";

								if($seriesDescriptionArr[$cnt+$i] == '(default)')
								{
									$sqlStr .= "(sr.image_number>=? AND sr.image_number<=?)";
									$colArr[] = $minSliceArr[$cnt+$j];
									$colArr[] = $maxSliceArr[$cnt+$j];
								}
								else
								{
									$sqlStr .= "sr.series_description=?";
									$colArr[] = $seriesDescriptionArr[$cnt+$j];
								}
							}

							if($modalityArr[$k] == $modalityArr[0])
							{
								$sqlStr .= " AND NOT (st.study_instance_uid=? AND sr.series_instance_uid=?)";
								$colArr[] = $studyUIDArr[0];
								$colArr[] = $seriesUIDArr[0];
							}
						}
						$sqlStr .= " ORDER BY sr.series_date DESC, sr.series_time DESC";

						$stmt = $pdo->prepare($sqlStr);
						$stmt->execute($colArr);

						$rowNum = $stmt->rowCount();
						$selectedSeriesArr[$k+1] = $rowNum;

						for($j = 0; $j < $rowNum; $j++)
						{
							$result = $stmt->fetch(PDO::FETCH_NUM);

							$seriesList[$k][$j][0] = $result[0] . '^' . $result[1];

							if($result[7] == $defaultSeriesDescriptionArr[$k])
							{
								$tmpStr = ($k+1) . '_' . $seriesList[$k][$j][0];
								$defaultSelectedSeriesArr[] = $tmpStr;
							}

							for($i=1; $i<7; $i++)
							{
								$seriesList[$k][$j][$i] = $result[$i+1];
							}
						}
					}

					$cnt += $descriptionNumArr[$k];

				} // end for : k

				$defaultSelectedSeriesList = array();

				if(count($defaultSelectedSeries) > 0)
				{
					for($i = 0; $i < count($defaultSelectedSeries); $i++)
					{
						$tmpArr = explode('_', $defaultSelectedSeries[$i]);
						$defaultSelectedSeriesList[$i][0] = $tmpArr[0];
						$defaultSelectedSeriesList[$i][1] = $tmpArr[1];
					}
				}
			}

			$selectedSeriesStr = implode('^', $selectedSeriesArr);
		}
		else
		{
			$params['mode'] = 'error';
		}

		if($_SESSION['anonymizeFlg'] == 1)
		{
			$params['patientID']   = PinfoScramble::encrypt($params['patientID'], $_SESSION['key']);
			$params['patientName'] = PinfoScramble::scramblePtName();
		}

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		$smarty = new SmartyEx();

		$smarty->assign('params',     $params);
		$smarty->assign('seriesList', $seriesList);

		$smarty->assign('seriesNum',            $seriesNum);
		$smarty->assign('modalityArr',          $modalityArr);
		$smarty->assign('descriptionNumArr',    $descriptionNumArr);
		$smarty->assign('minSliceArr',          $minSliceArr);
		$smarty->assign('maxSliceArr',          $maxSliceArr);
		$smarty->assign('seriesDescriptionArr', $seriesDescriptionArr);
		$smarty->assign('seriesNum',            count($modalityArr));

		$smarty->assign('selectedSeriesArr',     $selectedSeriesArr);

		$smarty->assign('studyUIDStr',          $studyUIDStr);
		$smarty->assign('seriesUIDStr',         $seriesUIDStr);

		$smarty->assign('selectedSeriesStr',         $selectedSeriesStr);
		$smarty->assign('defaultSelectedSeriesNum',  count($defaultSelectedSeries));
		$smarty->assign('defaultSelectedSeriesList', $defaultSelectedSeriesList);

		$smarty->display('cad_job/cad_execution.tpl');
		//--------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
