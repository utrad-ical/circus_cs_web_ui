<?php

	include_once('common.php');
	Auth::checkSession();

	try
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		//-----------------------------------------------------------------------------------------------------------------
		// Import $_GET variables and validation
		//-----------------------------------------------------------------------------------------------------------------
		$mode = (isset($_GET['mode']) && ($_GET['mode']=='today' || $_GET['mode']=='study')) ? $_GET['mode'] : "";
		$params = array();

		$validator = new FormValidator();

		if($mode == 'study')
		{
			$validator->addRules(array(
				"studyInstanceUID" => array(
					"type" => "uid",
					"required" => true)
				));
		}
		else
		{
			$validator->addRules(array(
				"filterPtID" => array(
					"type" => "pgregex",
					"label" => 'Patient ID'),
				"filterPtName" => array(
					"type" => "pgregex",
					"label" => 'Patient name'),
				"filterSex" => array(
					"type" => "select",
					"options" => array('M', 'F', 'all'),
					"default" => "all",
					"otherwise" => "all"),
				"filterAgeMin" => array(
					'type' => 'int',
					'min' => '0',
					'label' => 'Age (min)'),
				"filterAgeMax" => array(
					'type' => 'int',
					'min' => '0',
					'label' => 'Age (max)')
				));
		}

		if($mode != 'today')
		{
			$validator->addRules(array(
				"srDateFrom" => array(
					"type" => "date",
					"label" => 'Series date'),
				"srDateTo" => array(
					"type" => "date",
					"label" => 'Series date'),
				"srTimeTo" => array(
					"type" => "time",
					"label" => 'Series time')
				));
		}

		$validator->addRules(array(
			"filterModality" => array(
				'type' => 'select',
				"options" => $modalityList,
				"default" => 'all',
				"otherwise" => "all"),
			"filterSrDescription"=> array(
				"type" => "pgregex",
				"label" => 'Series description'),
			"filterTag"=> array(
				"type" => "pgregex",
				"label" => 'Tag'),
			"orderCol" => array(
				"type" => "select",
				"options" => array('PatientID','Name','Age','Sex','SeriesID',
								   'Modality','ImgNum','SeriesDesc','Date','Time'),
				"default" => 'Date',
				"otherwise" => 'Date'),
			"orderMode" => array(
				"type" => "select",
				"options" => array('DESC', 'ASC'),
				"default" => 'DESC',
				"otherwise" => 'DESC'),
			"showing" => array(
				"type" => "select",
				"options" => array('10', '25', '50', 'all'),
				"default" => '10',
				"otherwise" => '10')
			));

		if($validator->validate($_GET))
		{
			$params = $validator->output;
			$params['errorMessage'] = "&nbsp;";

			$params['pageNum']  = (isset($_GET['pageNum']) && ctype_digit($_GET['pageNum'])) ? $_GET['pageNum'] : 1;
			$params['startNum'] = 0;
			$params['endNum'] = 0;
			$params['totalNum'] = 0;
			$params['maxPageNum'] = 1;

			if(isset($params['filterAgeMin']) && isset($params['filterAgeMax'])
			   && $params['filterAgeMin'] > $params['filterAgeMax'])
			{
				$tmp = $params['filterAgeMin'];
				$params['filterAgeMin'] = $params['filterAgeMax'];
				$params['filterAgeMax'] = $tmp;
			}
		}
		else
		{
			$params = $validator->output;
			$params['errorMessage'] = implode('<br/>', $validator->errors);
		}
		$params['mode'] = $mode;
		//-----------------------------------------------------------------------------------------------------------------

		$data = array();

		if($params['errorMessage'] == "&nbsp;")
		{
			//----------------------------------------------------------------------------------------------------------
			// Create WHERE statement of SQL
			//----------------------------------------------------------------------------------------------------------
			$sqlCondArray = array();
			$sqlParams = array();
			$sqlCond = "";
			$addressParams = array();

			$sqlCond = " WHERE ";

			if($params['mode'] == 'today')
			{
				$params['showing'] = "all";  // for HIMEDIC

				$today = date("Y-m-d");
				$params['srDateFrom'] = $today;
				$params['srDateTo']   = $today;

				$sqlCondArray[] = "series_date=?";
				$sqlParams[] = $params['srDateFrom'];
				$addressParams['mode'] = 'today';
			}

			if($params['mode']== "study")
			{
				$sqlStr = "SELECT pt.patient_id, pt.patient_name, pt.sex, st.study_id, st.study_date, st.age"
						. " FROM patient_list pt, study_list st"
						. " WHERE st.study_instance_uid=? AND pt.patient_id=st.patient_id";

				$result = DBConnector::query($sqlStr, array($params['studyInstanceUID']), 'ARRAY_ASSOC');

				if($_SESSION['anonymizeFlg'] == 1)
				{
					$params['filterPtID'] = PinfoScramble::encrypt($result['patient_id'], $_SESSION['key']);
					$params['filterPtName'] = "";
				}
				else
				{
					$params['filterPtID'] = $result['patient_id'];
					$params['filterPtName'] = $result['patient_name'];
				}

				$params['filterStudyID'] = $result['study_id'];
				$params['filterStudyDate'] = $result['study_date'];
				$params['filterAgeMin'] = $params['filterAgeMax'] = $result['age'];
				$params['filterSex'] = $result['sex'];

				if($params['filterSex'] != "M" && $params['filterSex'] != "F")  $params['filterSex'] = "all";

				$sqlCondArray[] = "study_instance_uid=?";
				$sqlParams[] = $params['studyInstanceUID'];
				$addressParams['mode'] = 'study';
				$addressParams['studyInstanceUID'] = $params['studyInstanceUID'];
			}
			else
			{
				if($params['filterPtID'] != "")
				{
					$patientID = $params['filterPtID'];
					if($_SESSION['anonymizeFlg'] == 1)
					{
						$patientID = PinfoScramble::decrypt($params['filterPtID'], $_SESSION['key']);
					}

					// Search by regular expression
					$sqlCondArray[] = "patient_id~*?";
					$sqlParams[] = $patientID;
					$addressParams['filterPtID'] = $params['filterPtID'];
				}

				if($params['filterPtName'] != "")
				{
					// Search by regular expression
					$sqlCondArray[] = "patient_name~*?";
					$sqlParams[] = $params['filterPtName'];
					$addressParams['filterPtName'] = $params['filterPtName'];
				}

				if($params['filterSex'] == "M" || $params['filterSex'] == "F")
				{
					$sqlCondArray[] = "sex=?";
					$sqlParams[] = $params['filterSex'];
					$addressParams['filterSex'] = $params['filterSex'];
				}

				if($params['filterAgeMin'] != "" && $params['filterAgeMax'] != ""
				   && $params['filterAgeMin'] == $params['filterAgeMax'])
				{
					$sqlCondArray[] = "age=?";
					$sqlParams[] = $params['filterAgeMin'];
					$addressParams['filterAgeMin'] = $params['filterAgeMin'];
					$addressParams['filterAgeMax'] = $params['filterAgeMax'];
				}
				else
				{
					if($params['filterAgeMin'] != "")
					{
						$sqlCondArray[] = "?<=age";
						$sqlParams[] = $params['filterAgeMin'];
						$addressParams['filterAgeMin'] = $params['filterAgeMin'];
					}

					if($params['filterAgeMax'] != "")
					{
						$sqlCondArray[] = "age<=?";
						$sqlParams[] = $params['filterAgeMax'];
						$addressParams['filterAgeMax'] = $params['filterAgeMax'];
					}
				}
			}

			if($params['mode'] != 'today')
			{
				if($params['srDateFrom'] != "" && $params['srDateTo'] != ""
				   && $params['srDateFrom'] == $params['srDateTo'])
				{
					$sqlCondArray[] = "series_date=?";
					$sqlParams[] = $params['srDateFrom'];
					$addressParams['srDateFrom'] = $params['srDateFrom'];
					$addressParams['srDateTo'] = $params['srDateTo'];
				}
				else
				{
					if($params['srDateFrom'] != "")
					{
						$sqlCondArray[] = "?<=series_date";
						$sqlParams[] = $params['srDateFrom'];
						$addressParams['srDateFrom'] = $params['srDateFrom'];
					}

					if($params['srDateTo'] != "")
					{
						$sqlParams[] = $params['srDateTo'];
						$addressParams['srDateTo'] = $params['srDateTo'];

						if($params['srTimeTo'] != "")
						{
							$sqlCondArray[] = "(series_date<? OR (series_date=? AND series_time<=?))";
							$sqlParams[] = $params['srDateTo'];
							$sqlParams[] = $params['srTimeTo'];
							$addressParams['srTimeTo'] = $params['srTimeTo'];
						}
						else
						{
							$sqlCondArray[] = "series_date<=?";
						}
					}
				}
			}

			if($params['filterModality'] != "" && $params['filterModality'] != "all")
			{
				$sqlCondArray[] = "modality=?";
				$sqlParams[] = $params['filterModality'];
				$addressParams['filterModality'] = $params['filterModality'];
			}

			if($params['filterSrDescription'] != "")
			{
				// Search by regular expression
				$sqlCondArray[] = "series_description~*?";
				$sqlParams[] = $params['filterSrDescription'];
				$addressParams['filterSrDescription'] = $params['filterSrDescription'];
			}

			if($params['filterTag'] != "")
			{
			 	$sqlCondArray[] = "series_sid IN (SELECT DISTINCT reference_id FROM tag_list WHERE category=3 AND tag~*?)";
				$sqlParams[] = $params['filterTag'];
				$addressParams['filterTag'] = $params['filterTag'];
			}

			$sqlCond = (count($sqlCondArray) > 0) ? sprintf(" WHERE %s", implode(' AND ', $sqlCondArray)) : "";
			//----------------------------------------------------------------------------------------------------------

			//----------------------------------------------------------------------------------------------------------
			// Retrieve mode of display order (Default: ascending order of series number)
			//----------------------------------------------------------------------------------------------------------
			$orderColStr = "";

			switch($params['orderCol'])
			{
				case "PatientID":	$orderColStr = 'patient_id '         . $params['orderMode'];  break;
				case "Name":		$orderColStr = 'patient_name '       . $params['orderMode'];  break;
				case "Age":			$orderColStr = 'age '                . $params['orderMode'];  break;
				case "Sex":			$orderColStr = 'sex '                . $params['orderMode'];  break;
				case "SeriesID":	$orderColStr = 'series_number '      . $params['orderMode'];  break;
				case "Modality":	$orderColStr = 'modality '           . $params['orderMode'];  break;
				case "ImgNum":		$orderColStr = 'image_number '       . $params['orderMode'];  break;
				case "SeriesDesc":	$orderColStr = 'series_description ' . $params['orderMode'];  break;
				default: // Date
					$orderColStr = 'series_date ' . $params['orderMode'] . ', series_time ' . $params['orderMode'];
					$params['orderCol'] = ($params['mode'] == 'today') ? 'Time' : 'Date';
					break;
			}

			$addressParams['orderCol']  = $params['orderCol'];
			$addressParams['orderMode'] = $params['orderMode'];
			$addressParams['showing']   = $params['showing'];
			//----------------------------------------------------------------------------------------------------------

			$params['pageAddress'] = sprintf('series_list.php?%s',
			                         implode('&', array_map('UrlKeyValPair', array_keys($addressParams), array_values($addressParams))));
			$_SESSION['listAddress'] = $params['pageAddress'];

			//----------------------------------------------------------------------------------------------------------
			// count total number
			//----------------------------------------------------------------------------------------------------------
			$sqlStr = "SELECT COUNT(*) FROM series_join_list" . $sqlCond;
			$params['totalNum']     = DBConnector::query($sqlStr, $sqlParams, 'SCALAR');
			$params['maxPageNum'] = ($params['showing'] == "all") ? 1 : ceil($params['totalNum'] / $params['showing']);
			$params['startPageNum'] = max($params['pageNum'] - $PAGER_DELTA, 1);
			$params['endPageNum']   = min($params['pageNum'] + $PAGER_DELTA, $params['maxPageNum']);
			//----------------------------------------------------------------------------------------------------------

			//----------------------------------------------------------------------------------------------------------
			// Set $data array
			//----------------------------------------------------------------------------------------------------------
			$sqlStr = 'SELECT series_sid FROM series_join_list'
					. $sqlCond . " ORDER BY " . $orderColStr;

			if($params['showing'] != "all")
			{
				$sqlStr .= " LIMIT ? OFFSET ?";
				$sqlParams[] = $params['showing'];
				$sqlParams[] = $params['showing'] * ($params['pageNum']-1);
			}
			$sidList = DBConnector::query($sqlStr, $sqlParams, 'ALL_COLUMN');

			$rowNum = count($sidList);
			$params['startNum'] = ($rowNum == 0) ? 0 : $params['showing'] * ($params['pageNum']-1) + 1;
			$params['endNum']   = ($rowNum == 0) ? 0 : $params['startNum'] + $rowNum - 1;

			// Get rulesets
			$sqlStr = "SELECT pm.plugin_name, pm.version, pm.exec_enabled, cs.ruleset"
					. " FROM plugin_master pm, plugin_cad_master cm, plugin_cad_series cs"
					. " WHERE cm.plugin_id=pm.plugin_id"
					. " AND cs.plugin_id=cm.plugin_id"
					. " AND cs.volume_id=0"
					. " ORDER BY cm.label_order ASC";
			$ruleList = DBConnector::query($sqlStr, NULL, 'ALL_ASSOC');

			// SQL statement to search executed CAD software
			$sqlStr = "SELECT el.job_id, el.status, el.executed_at"
					. " FROM executed_plugin_list el, executed_series_list es, plugin_master pm"
					. " WHERE pm.plugin_name=? AND pm.version=?"
					. " AND pm.plugin_id=el.plugin_id"
					. " AND el.job_id=es.job_id"
					. " AND es.volume_id=0"
					. " AND es.series_sid=?"
					. " ORDER BY el.job_id DESC";
			$stmtCADExec = $pdo->prepare($sqlStr);

			$seriesFilter = new SeriesFilter();
			
			foreach($sidList as $sid)
			{
				$s = new SeriesJoin();
				$sdata = $s->find(array("series_sid" => $sid));
				$seriesData = $sdata[0]->getData();

				$cadNum = 0;
				$cadColSettings = array();
				
				foreach($ruleList as $result)
				{
					$ruleSet = json_decode($result['ruleset'], true);

					foreach($ruleSet as $rules)
					{
						$matchFlg = 1;
						
						$ruleFilterGroup = $rules['filter']['group'];
						$ruleFilterMembers = $rules['filter']['members'];

						foreach($ruleFilterMembers as $ruleFilter)
						{
							if($ruleFilter['key'] == 'modality'
								&& $ruleFilter['value'] != $seriesData['modality'])
							{
								$matchFlg = 0;
							}
						}
					}
					
					if($matchFlg == 1
						&& $ret = $seriesFilter->processRuleSets($seriesData, $ruleSet))
					{
						$cadColSettings[$cadNum][0] = $result['plugin_name'];
						$cadColSettings[$cadNum][1] = $result['version'];
						$cadColSettings[$cadNum][2] = ($result['exec_enabled']=='t') ? 1 : 0;
						$cadColSettings[$cadNum][3] = 0;					// status of plugin-job job
						$cadColSettings[$cadNum][4] = '';
						$cadColSettings[$cadNum][5] = 0;

						$cadCondArr = array($result['plugin_name'], $result['version'], $sid);
						$stmtCADExec->execute($cadCondArr);

						if($stmtCADExec->rowCount() >= 1)
						{
							$execResult = $stmtCADExec->fetch(PDO::FETCH_NUM);

							$cadColSettings[$cadNum][5] = $execResult[0];  // jobID
							$cadColSettings[$cadNum][3] = $execResult[1];  // status
							$tmpDate = $execResult[2];

							// Set executed date or time (if successed)
							if($cadColSettings[$cadNum][3] == $PLUGIN_SUCESSED)
							{
								if($mode == 'today' && substr($tmpDate, 0, 10) == date('Y-m-d'))
								{
									$cadColSettings[$cadNum][4] = substr($tmpDate, 11);
								}
								else
								{
									$cadColSettings[$cadNum][4] = substr($tmpDate, 0, 10);
								}
							}
						}
						$cadNum++;
					}
				}
				
				if($_SESSION['anonymizeFlg'] == 1)
				{
					$ptID   = PinfoScramble::encrypt($seriesData['patient_id'], $_SESSION['key']);
					$ptName = PinfoScramble::scramblePtName();
				}
				else
				{
					$ptID   = $seriesData['patient_id'];
					$ptName = $seriesData['patient_name'];
				}

				array_push($data, array($seriesData['series_sid'],
										$seriesData['study_instance_uid'],
										$seriesData['series_instance_uid'],
										$ptID,
										$ptName,
										$seriesData['age'],
										$seriesData['sex'],
										$seriesData['series_date'],
										$seriesData['series_time'],
										$seriesData['series_number'],
										$seriesData['modality'],
										$seriesData['image_number'],
										$seriesData['series_description'],
										$cadNum,
										$cadColSettings,
										PinfoScramble::encrypt($seriesData['patient_id'], $_SESSION['key']),
										PinfoScramble::encrypt($seriesData['patient_name'], $_SESSION['key'])));
			}
			//var_dump($data);
			//----------------------------------------------------------------------------------------------------------

			//----------------------------------------------------------------------------------------------------------
			// Generate one-time ticket to delete selected series
			//----------------------------------------------------------------------------------------------------------
			if($_SESSION['dataDeleteFlg'])
			{
				$_SESSION['ticket'] = md5(uniqid().mt_rand());
				$params['ticket'] = $_SESSION['ticket'];
			}
			//----------------------------------------------------------------------------------------------------------
		}

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		$smarty = new SmartyEx();

		$smarty->assign('params', $params);
		$smarty->assign('data',   $data);

		$smarty->assign('modalityList', $modalityList);

		$smarty->display('series_list.tpl');
		//--------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;
?>