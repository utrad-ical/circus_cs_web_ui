<?php
	session_cache_limiter('nocache');
	session_start();

	include_once('common.php');
	include_once("auto_logout.php");
	require_once('class/PersonalInfoScramble.class.php');
	require_once('class/validator.class.php');

	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		//-----------------------------------------------------------------------------------------------------------------
		// Import $_GET variables and validation
		//-----------------------------------------------------------------------------------------------------------------
		$mode = (isset($_GET['mode']) && ($_GET['mode']=='today' || $_GET['mode']=='study')) ? $_GET['mode'] : "";
		$params = array();
		
		PgValidator::$conn = $pdo;
		$validator = new FormValidator();
		$validator->registerValidator('pgregex', 'PgRegexValidator');		
		
		if($mode == 'study')
		{
			$validator->addRules(array(
				"studyInstanceUID" => array(
					"type" => "uid",
					"required" => true,
					"errorMes" => "URL is incorrect.")));
		}
		else
		{
			$validator->addRules(array(
				"filterPtID" => array(
					"type" => "pgregex",
					"errorMes" => "'Patient ID' is invalid."),
				"filterPtName" => array(
					"type" => "pgregex",
					"errorMes" => "'Patient name' is invalid."),
				"filterSex" => array(
					"type" => "select",
					"options" => array('M', 'F', 'all'),
					"default" => "all",
					"otherwise" => "all"),
				"filterAgeMin" => array(
					'type' => 'int', 
					'min' => '0',
					'errorMes' => "'Age' is invalid."),
				"filterAgeMax" => array(
					'type' => 'int', 
					'min' => '0',
					'errorMes' => "'Age' is invalid.")
				));				
		}
	
		if($mode != 'today')
		{
			$validator->addRules(array(
				"srDateFrom" => array(
					"type" => "date",
					"errorMes" => "'Series date' is invalid."),
				"srDateTo" => array(
					"type" => "date",
					"errorMes" => "'Series date' is invalid."),
				"srTimeTo" => array(
					"type" => "time",
					"errorMes" => "'Series time' is invalid.")
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
				"errorMes" => "'Series description' is invalid."),
			"filterTag"=> array(
				"type" => "pgregex",
				"errorMes" => "'Tag' is invalid."),
			"orderCol" => array(
				"type" => "select",
				"options" => array('Patient ID','Name','Age','Sex','ID','Modality','Img.','Desc.','Date','Time'),
				"default" => 'Date'),
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
				$params['errorMessage'] = "Range of 'Age' is invalid."; 
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
				
				$sqlCondArray[] = "sr.series_date=?";
				$sqlParams[] = $params['srDateFrom'];
				$addressParams['mode'] = 'today';
			}
			
			if($params['mode']== "study")
			{
				$sqlStr = "SELECT pt.patient_id, pt.patient_name, pt.sex, st.study_id, st.study_date, st.age"
						. " FROM patient_list pt, study_list st"
						. " WHERE st.study_instance_uid=? AND pt.patient_id=st.patient_id";
			
				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindParam(1, $params['studyInstanceUID']);
				$stmt->execute();
				
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				
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
				
				$sqlCondArray[] = "sr.study_instance_uid=?";
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
					$sqlCondArray[] = "pt.patient_id~*?";
					$sqlParams[] = $patientID;
					$addressParams['filterPtID'] = $params['filterPtID'];
				}
		
				if($params['filterPtName'] != "")
				{
					// Search by regular expression 
					$sqlCondArray[] = "pt.patient_name~*?";
					$sqlParams[] = $params['filterPtName'];
					$addressParams['filterPtName'] = $params['filterPtName'];
				}
				
				if($params['filterSex'] == "M" || $params['filterSex'] == "F")
				{
					$sqlCondArray[] = "pt.sex=?";
					$sqlParams[] = $params['filterSex'];
					$addressParams['filterSex'] = $params['filterSex'];
				}
				
				if($params['filterAgeMin'] != "" && $params['filterAgeMax'] != ""
				   && $params['filterAgeMin'] == $params['filterAgeMax'])
				{
					$sqlCondArray[] = "st.age=?";
					$sqlParams[] = $params['filterAgeMin'];
					$addressParams['filterAgeMin'] = $params['filterAgeMin'];
					$addressParams['filterAgeMax'] = $params['filterAgeMax'];
				}
				else
				{
					if($params['filterAgeMin'] != "")
					{
						$sqlCondArray[] = "?<=st.age";
						$sqlParams[] = $params['filterAgeMin'];
						$addressParams['filterAgeMin'] = $params['filterAgeMin'];
					}
			
					if($params['filterAgeMax'] != "")
					{
						$sqlCondArray[] = "st.age<=?";
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
					$sqlCondArray[] = "sr.series_date=?";
					$sqlParams[] = $params['srDateFrom'];
					$addressParams['srDateFrom'] = $params['srDateFrom'];
					$addressParams['srDateTo'] = $params['srDateTo'];
				}
				else
				{
					if($params['srDateFrom'] != "")
					{
						$sqlCondArray[] = "?<=sr.series_date";
						$sqlParams[] = $params['srDateFrom'];
						$addressParams['srDateFrom'] = $params['srDateFrom'];
					}
			
					if($params['srDateTo'] != "")
					{
						$sqlParams[] = $params['srDateTo'];
						$addressParams['srDateTo'] = $params['srDateTo'];
	
						if($params['srTimeTo'] != "")
						{
							$sqlCondArray[] = "(sr.series_date<? OR (sr.series_date=? AND sr.series_time<=?))";
							$sqlParams[] = $params['srDateTo'];
							$sqlParams[] = $params['srTimeTo'];
							$addressParams['srTimeTo'] = $params['srTimeTo'];
						}
						else
						{
							$sqlCondArray[] = "sr.series_date<=?";
						}
					}
				}
			}
			
			if($params['filterModality'] != "" && $params['filterModality'] != "all")
			{
				$sqlCondArray[] = "sr.modality=?";
				$sqlParams[] = $params['filterModality'];
				$addressParams['filterModality'] = $params['filterModality'];
			}
			
			if($params['filterSrDescription'] != "")
			{
				// Search by regular expression
				$sqlCondArray[] = "sr.series_description~*?";
				$sqlParams[] = $params['filterSrDescription'];
				$addressParams['filterSrDescription'] = $params['filterSrDescription'];
			}
			
			if($params['filterTag'] != "")
			{		
			 	$sqlCond .= "sr.series_instance_uid IN (SELECT DISTINCT series_instance_uid FROM series_tag WHERE tag~*?)";
				$sqlParams[] = $params['filterTag'];
				$addressParams['filterTag'] = $params['filterTag'];
			}			
				
			$sqlCondArray[] = "st.study_instance_uid=sr.study_instance_uid";
			$sqlCondArray[] = "pt.patient_id=st.patient_id";
			$sqlCond = sprintf(" WHERE %s", implode(' AND ', $sqlCondArray));
			//----------------------------------------------------------------------------------------------------------
	
			//----------------------------------------------------------------------------------------------------------
			// Retrieve mode of display order (Default: ascending order of series number)
			//----------------------------------------------------------------------------------------------------------	
			$orderColStr = "";
		
			switch($params['orderCol'])
			{
				case "Patient ID":	$orderColStr = 'pt.patient_id '         . $params['orderMode'];  break;	
				case "Name":		$orderColStr = 'pt.patient_name '       . $params['orderMode'];  break;	
				case "Age":			$orderColStr = 'st.age '                . $params['orderMode'];  break;
				case "Sex":			$orderColStr = 'pt.sex '                . $params['orderMode'];  break;
				case "ID":          $orderColStr = 'sr.series_number '      . $params['orderMode'];  break;
				case "Modality":    $orderColStr = 'sr.modality '           . $params['orderMode'];  break;
				case "Img.":        $orderColStr = 'sr.image_number '       . $params['orderMode'];  break;
				case "Desc.":       $orderColStr = 'sr.series_description ' . $params['orderMode'];  break;
				default: // Date
					$orderColStr = 'sr.series_date ' . $params['orderMode'] . ', sr.series_time ' . $params['orderMode'];
					$params['orderCol'] = ($params['mode'] == 'today') ? 'Time' : 'Date';
					break;
			}
					 
			$addressParams['orderCol']  = $params['orderCol'];
			$addressParams['orderMode'] = $params['orderMode'];
			$addressParams['showing']   = $params['showing'];
			//----------------------------------------------------------------------------------------------------------
	
			$params['pageAddress'] = sprintf('series_list.php?%s',
			                         implode('&', array_map(UrlKeyValPair, array_keys($addressParams), array_values($addressParams))));
			$_SESSION['listAddress'] = $params['pageAddress'];

			//----------------------------------------------------------------------------------------------------------
			// count total number
			//----------------------------------------------------------------------------------------------------------
			$sqlStr = "SELECT COUNT(*) FROM patient_list pt, study_list st, series_list sr " . $sqlCond;
			$params['totalNum']     = PdoQueryOne($pdo, $sqlStr, $sqlParams, 'SCALAR');	
			$params['maxPageNum'] = ($params['showing'] == "all") ? 1 : ceil($params['totalNum'] / $params['showing']);
			$params['startPageNum'] = max($params['pageNum'] - $PAGER_DELTA, 1);
			$params['endPageNum']   = min($params['pageNum'] + $PAGER_DELTA, $params['maxPageNum']);		
			//----------------------------------------------------------------------------------------------------------
	
			//----------------------------------------------------------------------------------------------------------
			// Set $data array
			//----------------------------------------------------------------------------------------------------------
			$sqlStr = 'SELECT sr.sid, st.study_instance_uid, sr.series_instance_uid, sr.series_number,'
					. ' pt.patient_id, pt.patient_name, pt.sex, st.age, sr.series_date, sr.series_time,'
					. ' sr.modality, sr.image_number, sr.series_description'
					. ' FROM patient_list pt, study_list st, series_list sr '
					. $sqlCond . " ORDER BY " . $orderColStr;
					
			if($params['showing'] != "all")
			{
				$sqlStr .= " LIMIT ? OFFSET ?";
				$sqlParams[] = $params['showing'];
				$sqlParams[] = $params['showing'] * ($params['pageNum']-1);
			}
	
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);
	
			//var_dump($stmt);
			//var_dump($sqlParams);
			
			$rowNum = $stmt->rowCount();
			$params['startNum'] = ($rowNum == 0) ? 0 : $params['showing'] * ($params['pageNum']-1) + 1;
			$params['endNum']   = ($rowNum == 0) ? 0 : $params['startNum'] + $rowNum - 1;	
			
			// Search executable or executed CAD software
			$sqlStr = "SELECT cm.cad_name, cm.version, cm.exec_flg, max(cs.series_description)"
					. " FROM cad_master cm, cad_series cs"
					. " WHERE cm.cad_name=cs.cad_name"
					. " AND cm.version=cs.version"
					. " AND cs.series_id=1"
					. " AND cs.modality=?"
					. " AND ((cs.series_description=?)"
					. " OR (cs.series_description='(default)' AND cs.min_slice<=? AND cs.max_slice>=?))"
					. " GROUP BY cm.cad_name, cm.version, cm.exec_flg, cm.label_order"
					. " ORDER BY cm.label_order ASC";
						
			$stmtCADMaster = $pdo->prepare($sqlStr);
	
			$sqlStr = "SELECT executed_at FROM executed_plugin_list el, executed_series_list esr, cad_master cm"
			        . " WHERE cm.cad_name=? AND cm.version=?"
			        . " AND cm.cad_name=el.plugin_name"
			        . " AND cm.version=el.version"
					. " AND el.exec_id=esr.exec_id"
			        . " AND esr.series_id=1"
			        . " AND esr.study_instance_uid=?"
			        . " AND esr.series_instance_uid=?";
	
			$stmtCADExec = $pdo->prepare($sqlStr);
	
			$sqlStr = "SELECT COUNT(*) FROM plugin_job_list pjob, job_series_list jsr, cad_master cm"
			        . " WHERE cm.cad_name=? AND cm.version=?"
			        . " AND cm.cad_name=pjob.plugin_name"
			        . " AND cm.version=pjob.version"
					. " AND pjob.job_id = jsr.job_id"
					. " AND jsr.series_id=1"
					. " AND jsr.study_instance_uid=?"
					. " AND jsr.series_instance_uid=?";
	
			$stmtCADJob = $pdo->prepare($sqlStr);
	
			while ($result = $stmt->fetch(PDO::FETCH_ASSOC))
			{
			
				$imgNum = $result['image_number'];
	
				//------------------------------------------------------------------------------------------------------
				// Setting of "CAD" column (pull-downmenu, [Exec] button, and [Result] button)
				//------------------------------------------------------------------------------------------------------
				$stmtCADMaster->execute(array($result['modality'], $result['series_description'], $imgNum, $imgNum));
			
				$cadNum = 0;
				$cadColSettings = array();
	
				while($resultCADMaster = $stmtCADMaster->fetch(PDO::FETCH_NUM))
				{
					$cadCondArr = array($resultCADMaster[0], $resultCADMaster[1],
					                    $result['study_instance_uid'], $result['series_instance_uid']);
				
					$cadColSettings[$cadNum][0] = $resultCADMaster[0];
					$cadColSettings[$cadNum][1] = $resultCADMaster[1];
					$cadColSettings[$cadNum][2] = ($resultCADMaster[2]=='t') ? 1 : 0;
					$cadColSettings[$cadNum][3] = 0;					// flg for plug-in execution
					$cadColSettings[$cadNum][4] = 0;					// queue flg
					$cadColSettings[$cadNum][5] = '';
					$cadColSettings[$cadNum][6] = $resultCADMaster[3];
									
					$stmtCADExec->execute($cadCondArr);						
			
					if($stmtCADExec->rowCount() == 1) // PostgreSQL以外に適用する場合は要動作確認(特にMySQL)
					{
						$cadColSettings[$cadNum][3] = 1;
						$cadColSettings[$cadNum][5] = substr($stmtCADExec->fetchColumn(), 0, 10);
					}
					else
					{
						$cadColSettings[$cadNum][3] = 0;
						$stmtCADJob->execute($cadCondArr);
						if($stmtCADJob->fetchColumn() > 0)  $cadColSettings[$cadNum][4] = 1;
					}
					
					$cadNum++;
					
				} // end while
	
				if($_SESSION['anonymizeFlg'] == 1)
				{
					$ptID   = PinfoScramble::encrypt($result['patient_id'], $_SESSION['key']);
					$ptName = PinfoScramble::scramblePtName();
				}
				else
				{
					$ptID   = $result['patient_id'];
					$ptName = $result['patient_name'];
				}			
	
				array_push($data, array($result['sid'],
										$result['study_instance_uid'],
				                        $result['series_instance_uid'],
										$ptID,
										$ptName,
										$result['age'],
										$result['sex'],
										$result['series_date'],
										$result['series_time'],
				                        $result['series_number'],
										$result['modality'],
										$result['image_number'],
										$result['series_description'],
										$cadNum,
										$cadColSettings,
										PinfoScramble::encrypt($result['patient_id'], $_SESSION['key']),
										PinfoScramble::encrypt($result['patient_name'], $_SESSION['key'])));
			}
			//var_dump($data);
			//----------------------------------------------------------------------------------------------------------
		}
		
		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		require_once('smarty/SmartyEx.class.php');
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