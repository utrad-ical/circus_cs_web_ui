<?php
	session_cache_limiter('nocache');
	session_start();

	include_once('common.php');
	include_once("auto_logout.php");
	require_once('class/PersonalInfoScramble.class.php');	
	
	//------------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables and set $params array	
	//------------------------------------------------------------------------------------------------------------------
	$params = array('mode'                => (isset($_REQUEST['mode'])) ? $_REQUEST['mode'] : "",
	               'filterPtID'          => (isset($_REQUEST['filterPtID'])) ? $_REQUEST['filterPtID'] : "",
				   'filterPtName'        => (isset($_REQUEST['filterPtName'])) ? $_REQUEST['filterPtName'] : "",
				   'filterSex'           => (isset($_REQUEST['filterSex'])) ? $_REQUEST['filterSex'] : "all",
				   'filterAgeMin'        => (isset($_REQUEST['filterAgeMin'])) ? $_REQUEST['filterAgeMin'] : "",
				   'filterAgeMax'        => (isset($_REQUEST['filterAgeMax'])) ? $_REQUEST['filterAgeMax'] : "",
				   'filterModality'      => (isset($_REQUEST['filterModality'])) ? $_REQUEST['filterModality'] : "all",
				   'filterSrDescription' => (isset($_REQUEST['filterSrDescription'])) ? $_REQUEST['filterSrDescription'] : "",
				   'filterTag'           => (isset($_REQUEST['filterTag'])) ? $_REQUEST['filterTag'] : "",				   
				   'srDateFrom'          => (isset($_REQUEST['srDateFrom'])) ? $_REQUEST['srDateFrom'] : "",
				   'srDateTo'            => (isset($_REQUEST['srDateTo'])) ? $_REQUEST['srDateTo'] : "",
				   //'srTimeFrom'          => (isset($_REQUEST['srTimeFrom'])) ? $_REQUEST['srTimeFrom'] : "00:00:00",
				   'srTimeTo'            => (isset($_REQUEST['stTimeTo'])) ? $_REQUEST['stTimeTo'] : "",
				   'studyInstanceUID'    => (isset($_REQUEST['studyInstanceUID']))   ? $_REQUEST['studyInstanceUID'] : "",
				   'orderCol'            => (isset($_REQUEST['orderCol'])) ? $_REQUEST['orderCol'] : "Study date",
				   'orderMode'           => ($_REQUEST['orderMode'] === "ASC") ? "ASC" : "DESC",
				   'totalNum'            => (isset($_REQUEST['totalNum']))  ? $_REQUEST['totalNum'] : 0,
				   'pageNum'             => (isset($_REQUEST['pageNum']))   ? $_REQUEST['pageNum']  : 1,
				   'showing'             => (isset($_REQUEST['showing'])) ? $_REQUEST['showing'] : 10,
				   'startNum'            => 1,
				   'endNum'              => 10,
				   'maxPageNum'          => 1,
				   'pageAddress'         => 'series_list.php?');
				   
	if($params['filterSex'] != "M" && $params['filterSex'] != "F")  $params['filterSex'] = "all";
	if($params['showing'] != "all" && $params['showing'] < 10)  $params['showing'] = 10;
	//------------------------------------------------------------------------------------------------------------------
	
	$data = array();
	
	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		//--------------------------------------------------------------------------------------------------------------
		// Create WHERE statement of SQL
		//--------------------------------------------------------------------------------------------------------------
		$sqlCondArray = array();
		$sqlParams = array();
		$sqlCond = "";
		$pageAddressParams = array();
		
		$sqlCond = " WHERE ";

		if($params['mode'] == 'today')
		{
			$params['showing'] = "all";  // for HIMEDIC
		
			$today = date("Y-m-d");
			$params['srDateFrom'] = $today;
			$params['srDateTo']   = $today;
			
			$sqlCondArray[] = "sr.series_date=?";
			$sqlParams[] = $params['srDateFrom'];
			$pageAddressParams['mode'] = 'today';
		}
		
		if($params['mode']== "study")
		{
			$sqlStr = "SELECT pt.patient_id, pt.patient_name, pt.sex, st.study_id, st.study_date, st.age"
					. " FROM patient_list pt, study_list st WHERE st.study_instance_uid=? AND pt.patient_id=st.patient_id";
		
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
			$pageAddressParams['mode'] = 'study';
			$pageAddressParams['studyInstanceUID'] = $params['studyInstanceUID'];
		}
		else		
		{
			if($params['filterPtID'] != "")
			{
				$patientID = $params['filterPtID'];
				if($_SESSION['anonymizeFlg'] == 1)  $patientID = PinfoScramble::decrypt($params['filterPtID'], $_SESSION['key']);

				// Search by regular expression
				$sqlCondArray[] = "pt.patient_id~*?";
				$sqlParams[] = $patientID;
				$pageAddressParams['filterPtID'] = $params['filterPtID'];
			}
	
			if($params['filterPtName'] != "")
			{
				// Search by regular expression 
				$sqlCondArray[] = "pt.patient_name~*?";
				$sqlParams[] = $params['filterPtName'];
				$pageAddressParams['filterPtName'] = $params['filterPtName'];
			}
			
			if($params['filterSex'] == "M" || $params['filterSex'] == "F")
			{
				$sqlCondArray[] = "pt.sex=?";
				$sqlParams[] = $params['filterSex'];
				$pageAddressParams['filterSex'] = $params['filterSex'];
			}
			
			if($params['filterAgeMin'] != "" && $params['filterAgeMax'] != "" && $params['filterAgeMin'] == $params['filterAgeMax'])
			{
				$sqlCondArray[] = "st.age=?";
				$sqlParams[] = $params['filterAgeMin'];
				$pageAddressParams['filterAgeMin'] = $params['filterAgeMin'];
				$pageAddressParams['filterAgeMax'] = $params['filterAgeMax'];
			}
			else
			{
				if($params['filterAgeMin'] != "")
				{
					$sqlCondArray[] = "?<=st.age";
					$sqlParams[] = $params['filterAgeMin'];
					$pageAddressParams['filterAgeMin'] = $params['filterAgeMin'];
				}
		
				if($params['filterAgeMax'] != "")
				{
					$sqlCondArray[] = "st.age<=?";
					$sqlParams[] = $params['filterAgeMax'];
					$pageAddressParams['filterAgeMax'] = $params['filterAgeMax'];
				}
			}		
		}
		
		if($params['mode'] != 'today')
		{
			if($params['srDateFrom'] != "" && $params['srDateTo'] != "" && $params['srDateFrom'] == $params['srDateTo'])
			{
				$sqlCondArray[] = "sr.series_date=?";
				$sqlParams[] = $params['srDateFrom'];
				$pageAddressParams['srDateFrom'] = $params['srDateFrom'];
				$pageAddressParams['srDateTo'] = $params['srDateTo'];
			}
			else
			{
				if($params['srDateFrom'] != "")
				{
					$sqlCondArray[] = "?<=sr.series_date";
					$sqlParams[] = $params['srDateFrom'];
					$pageAddressParams['srDateFrom'] = $params['srDateFrom'];
				}
		
				if($params['srDateTo'] != "")
				{
					$sqlParams[] = $params['srDateTo'];
					$pageAddressParams['srDateTo'] = $params['srDateTo'];

					if($params['srTimeTo'] != "")
					{
						$sqlCondArray[] = "(sr.series_date<? OR (sr.series_date=? AND sr.series_time<=?))";
						$sqlParams[] = $params['srDateTo'];
						$sqlParams[] = $params['srTimeTo'];
						$pageAddressParams['srTimeTo'] = $params['srTimeTo'];
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
			$pageAddressParams['filterModality'] = $params['filterModality'];
		}
		
		if($params['filterSrDescription'] != "")
		{
			// Search by regular expression
			$sqlCondArray[] = "sr.series_description~*?";
			$sqlParams[] = $params['filterSrDescription'];
			$pageAddressParams['filterSrDescription'] = $params['filterSrDescription'];
		}
		
		if($params['filterTag'] != "")
		{		
		 	$sqlCond .= "sr.series_instance_uid IN (SELECT DISTINCT series_instance_uid FROM series_tag WHERE tag~*?)";
			$sqlParams[] = $params['filterTag'];
			$pageAddressParams['filterTag'] = $params['filterTag'];
		}			
			
		$sqlCondArray[] = "st.study_instance_uid=sr.study_instance_uid";
		$sqlCondArray[] = "pt.patient_id=st.patient_id";
		$sqlCond = sprintf(" WHERE %s", implode(' AND ', $sqlCondArray));
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// Retrieve mode of display order (Default: ascending order of series number)
		//--------------------------------------------------------------------------------------------------------------	
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
				 
		$pageAddressParams['orderCol']  = $params['orderCol'];
		$pageAddressParams['orderMode'] = $params['orderMode'];
		$pageAddressParams['showing']   = $params['showing'];
		//--------------------------------------------------------------------------------------------------------------

		$params['pageAddress'] = implode('&', array_map(UrlKeyValPair, array_keys($pageAddressParams), array_values($pageAddressParams)));

		//--------------------------------------------------------------------------------------------------------------
		// count total number
		//--------------------------------------------------------------------------------------------------------------
		$stmt = $pdo->prepare("SELECT COUNT(*) FROM patient_list pt, study_list st, series_list sr " . $sqlCond);
		$stmt->execute($sqlParams);		
		
		$params['totalNum'] = $stmt->fetchColumn();
		$params['maxPageNum'] = ($params['showing'] == "all") ? 1 : ceil($params['totalNum'] / $params['showing']);
		$params['startPageNum'] = max($params['pageNum'] - $PAGER_DELTA, 1);
		$params['endPageNum']   = min($params['pageNum'] + $PAGER_DELTA, $params['maxPageNum']);		
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// Set $data array
		//--------------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT st.study_instance_uid, sr.series_instance_uid, sr.series_number, pt.patient_id,"
				. " pt.patient_name, pt.sex, st.age, sr.series_date, sr.series_time, sr.modality,"
				. " sr.image_number, sr.series_description"
				. " FROM patient_list pt, study_list st, series_list sr "
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
		//var_dump($condArr);
		
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

			//----------------------------------------------------------------------------------------------------------
			// Setting of "CAD" column (pull-downmenu, [Exec] button, and [Result] button)
			//----------------------------------------------------------------------------------------------------------
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

			array_push($data, array($result['study_instance_uid'],
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
		//--------------------------------------------------------------------------------------------------------------
		
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