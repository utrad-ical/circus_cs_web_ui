<?php
	session_cache_limiter('nocache');
	session_start();

	include("common.php");
	require_once('class/PersonalInfoScramble.class.php');	
	
	//------------------------------------------------------------------------------------------------------------------
	// Auto logout (session timeout)
	//------------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'])  header('location: index.php?mode=timeout');
	else	$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables and set $param array	
	//------------------------------------------------------------------------------------------------------------------
	$param = array('mode'                => (isset($_REQUEST['mode'])) ? $_REQUEST['mode'] : "",
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
				   
	if($param['filterSex'] != "M" && $param['filterSex'] != "F")  $param['filterSex'] = "all";
	if($param['showing'] != "all" && $param['showing'] < 10)  $param['showing'] = 10;
	//------------------------------------------------------------------------------------------------------------------
	
	//------------------------------------------------------------------------------------------------------------------
	// Retrieve mode of display order (Default: ascending order of series number)
	//------------------------------------------------------------------------------------------------------------------
	$orderColStr = "";
	
	switch($param['orderCol'])
	{
		case "Patient ID":	$orderColStr = 'pt.patient_id '         . $param['orderMode'];  break;	
		case "Name":		$orderColStr = 'pt.patient_name '       . $param['orderMode'];  break;	
		case "Age":			$orderColStr = 'st.age '                . $param['orderMode'];  break;
		case "Sex":			$orderColStr = 'pt.sex '                . $param['orderMode'];  break;
		case "ID":          $orderColStr = 'sr.series_number '      . $param['orderMode'];  break;
		case "Modality":    $orderColStr = 'sr.modality '           . $param['orderMode'];  break;
		case "Img.":        $orderColStr = 'sr.image_number '       . $param['orderMode'];  break;
		case "Desc.":       $orderColStr = 'sr.series_description ' . $param['orderMode'];  break;
		default: // Date
			$orderColStr = 'sr.series_date ' . $param['orderMode'] . ', sr.series_time ' . $param['orderMode'];
			$param['orderCol'] = ($param['mode'] == 'today') ? 'Time' : 'Date';
			break;
	}
	//------------------------------------------------------------------------------------------------------------------

	$colParam = array( array('colName' => 'Patient ID', 'align' => 'right'),
		               array('colName' => 'Name',       'align' => 'left'),
		               array('colName' => 'Age',        'align' => 'left'),
		               array('colName' => 'Sex',        'align' => 'left'),
		               array('colName' => 'Date',       'align' => 'center'),
		               array('colName' => 'Time',       'align' => 'center'),
		               array('colName' => 'ID',         'align' => 'left'),
		               array('colName' => 'Modality',   'align' => 'center'),
		               array('colName' => 'Img.',       'align' => 'right'),
		               array('colName' => 'Desc.',      'align' => 'left'),
		               array('colName' => 'Detail',     'align' => 'center'),
		               array('colName' => 'CAD',        'align' => 'left'));
	
	$data = array();
	$PinfoScramble = new PinfoScramble();
	
	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		//--------------------------------------------------------------------------------------------------------------
		// Create WHERE statement of SQL
		//--------------------------------------------------------------------------------------------------------------
		$optionNum = 0;
		$condArr = array();
		
		$sqlCond = " WHERE";

		if($param['mode'] == 'today')
		{
			$param['showing'] = "all";  // for HIMEDIC
		
			$today = date("Y-m-d");
			$param['srDateFrom'] = $today;
			$param['srDateTo']   = $today;
			
			$sqlCond .= " sr.series_date=?";
			array_push($condArr, $param['srDateFrom']);
			$param['pageAddress'] .= 'mode=today';
			$optionNum++;
		}
		
		if($param['mode']== "study")
		{
			$sqlStr = "SELECT pt.patient_id, pt.patient_name, pt.sex, st.study_id, st.study_date, st.age"
					. " FROM patient_list pt, study_list st WHERE st.study_instance_uid=? AND pt.patient_id=st.patient_id";
		
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindParam(1, $param['studyInstanceUID']);
			$stmt->execute();
			
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if($_SESSION['anonymizeFlg'] == 1)
			{		
				$param['filterPtID'] = $PinfoScramble->Encrypt($result['patient_id'], $_SESSION['key']);
				$param['filterPtName'] = "";
			}
			else
			{
				$param['filterPtID'] = $result['patient_id'];
				$param['filterPtName'] = $result['patient_name'];
			}
			
			$param['filterStudyID'] = $result['study_id'];
			$param['filterStudyDate'] = $result['study_date'];
			$param['filterAgeMin'] = $param['filterAgeMax'] = $result['age'];
			$param['filterSex'] = $result['sex'];

			if($param['filterSex'] != "M" && $param['filterSex'] != "F")  $param['filterSex'] = "all";
			
			$sqlCond .= " sr.study_instance_uid=?";
			array_push($condArr, $param['studyInstanceUID']);
			$param['pageAddress'] .= 'mode=study&studyInstanceUID=' . $param['studyInstanceUID'];
			$optionNum++;
		}
		else		
		{
			if($param['filterPtID'] != "")
			{
				$patientID = $param['filterPtID'];
				if($_SESSION['anonymizeFlg'] == 1)  $patientID = $PinfoScramble->Decrypt($param['filterPtID'], $_SESSION['key']);

				if(0<$optionNum)
				{
					$sqlCond .= " AND";
					$param['pageAddress'] .= "&";
				}
	
				// Search by regular expression
				$sqlCond .= " pt.patient_id~*?";
				array_push($condArr, $patientID);
				$param['pageAddress'] .= 'filterPtID=' . $param['filterPtID'];
				$optionNum++;
			}
	
			if($param['filterPtName'] != "")
			{
				if(0<$optionNum)	$sqlCond .= " AND";

				// Search by regular expression 
				$sqlCond .= " pt.patient_name~*?";
				array_push($condArr, $param['filterPtName']);
				$param['pageAddress'] .= 'filterPtName=' . $param['filterPtName'];
				$optionNum++;
			}
			
			if($param['filterSex'] == "M" || $param['filterSex'] == "F")
			{
				if(0<$optionNum)
				{
					$sqlCond .= " AND";
					$param['pageAddress'] .= "&";
				}
				
				$sqlCond .= " pt.sex=?";
				array_push($condArr, $param['filterSex']);
				$param['pageAddress'] .= 'filterSex=' . $param['filterSex'];
				$optionNum++;
				
			}
			
			if($param['filterAgeMin'] != "" && $param['filterAgeMax'] != "" && $param['filterAgeMin'] == $param['filterAgeMax'])
			{
				if(0<$optionNum)
				{
					$sqlCond .= " AND";
					$param['pageAddress'] .= "&";
				}
				
				$sqlCond .= " st.age=?";
				array_push($condArr, $param['filterAgeMin']);
				$param['pageAddress'] .= 'filterAgeMin=' . $param['filterAgeMin'] . '&filterAgeMax=' . $param['filterAgeMax'];
				$optionNum++;
			}
			else
			{
				if($param['filterAgeMin'] != "")
				{
					if(0<$optionNum)
					{
						$sqlCond .= " AND";
						$param['pageAddress'] .= "&";
					}
					
					$sqlCond .= " ?<=st.age";
					array_push($condArr, $param['filterAgeMin']);
					$param['pageAddress'] .= 'filterAgeMin=' . $param['filterAgeMin'];
					$optionNum++;
				}
		
				if($param['filterAgeMax'] != "")
				{
					if(0<$optionNum)
					{
						$sqlCond .= " AND";
						$param['pageAddress'] .= "&";
					}
					
					$sqlCond .= " st.age<=?";
					array_push($condArr, $param['filterAgeMax']);
					$param['pageAddress'] .= 'filterAgeMax=' . $param['filterAgeMax'];
					$optionNum++;
				}
			}		
		}
		
		if($param['mode'] != 'today')
		{
			if($param['srDateFrom'] != "" && $param['srDateTo'] != "" && $param['srDateFrom'] == $param['srDateTo'])
			{
				if(0<$optionNum)
				{
					$sqlCond .= " AND";
					$param['pageAddress'] .= "&";
				}
				$sqlCond .= " sr.series_date=?";
				array_push($condArr, $param['srDateFrom']);
				$param['pageAddress'] .= 'srDateFrom=' . $param['srDateFrom'] . '&srDateTo=' . $param['srDateTo'];
				$optionNum++;
			}
			else
			{
				if($param['srDateFrom'] != "")
				{
					if(0<$optionNum)
					{
						$sqlCond .= " AND";
						$param['pageAddress'] .= "&";
					}
					
					$sqlCond .= " ?<=sr.series_date";
					array_push($condArr, $param['srDateFrom']);
					$param['pageAddress'] .= 'srDateFrom=' . $param['srDateFrom'];
					$optionNum++;
				}
		
				if($param['srDateTo'] != "")
				{
					if(0<$optionNum)
					{
						$sqlCond .= " AND";
						$param['pageAddress'] .= "&";
					}
					
					if($param['srTimeTo'] != "")
					{
						$sqlCond .= " (sr.series_date<? OR (sr.series_date=? AND sr.series_time<=?))";
						array_push($condArr, $param['srDateTo']);
						array_push($condArr, $param['srDateTo']);
						array_push($condArr, $param['srTimeTo']);
						$param['pageAddress'] .= 'srDateTo=' . $param['srDateTo'] . '&srTimeTo=' . $param['srTimeTo'];
					}
					else
					{
						$sqlCond .= " sr.series_date<=?";
						array_push($condArr, $param['srDateTo']);
						$param['pageAddress'] .= 'srDateTo=' . $param['srDateTo'];
					}
					$optionNum++;
				}
			}
		}
		
		if($param['filterModality'] != "" && $param['filterModality'] != "all")
		{
			if(0<$optionNum)
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}
			$sqlCond .= " sr.modality=?";
			array_push($condArr, $param['filterModality']);
			$param['pageAddress'] .= 'filterModality=' . $param['filterModality'];
			$optionNum++;
		}
		
		if($param['filterSrDescription'] != "")
		{
			if(0<$optionNum)
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}
			
			// Search by regular expression
			$sqlCond .= " sr.series_description~*?";
			array_push($condArr, $param['filterSrDescription']);
			$param['pageAddress'] .= 'filterSrDescription=' . $param['filterSrDescription'];
			$optionNum++;
		}
		
		if($param['filterTag'] != "")
		{		
			if(0<$optionNum) 
			{
				$sqlCond .= " AND";
				$param['pageAddress'] .= "&";
			}

		 	$sqlCond .= " sr.series_instance_uid IN (SELECT DISTINCT series_instance_uid FROM series_tag WHERE tag~*?)";
			array_push($condArr, $param['filterTag']);
			$param['pageAddress'] .= 'filterTag=' . $param['filterTag'];
			$optionNum++;
		}			
			
		if(0<$optionNum)
		{
			$sqlCond .= " AND";
			$param['pageAddress'] .= "&";
		}
		$sqlCond .= " st.study_instance_uid=sr.study_instance_uid "
		         .  " AND pt.patient_id=st.patient_id";
		$param['pageAddress'] .= 'orderCol=' . $param['orderCol'] . '&orderMode=' .  $param['orderMode']
		                      .  '&showing=' . $param['showing'];
							  
		$_SESSION['listAddress'] = $param['pageAddress'];
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// count total number
		//--------------------------------------------------------------------------------------------------------------
		$stmt = $pdo->prepare("SELECT COUNT(*) FROM patient_list pt, study_list st, series_list sr " . $sqlCond);
		$stmt->execute($condArr);		
		
		$param['totalNum'] = $stmt->fetchColumn();
		$param['maxPageNum'] = ($param['showing'] == "all") ? 1 : ceil($param['totalNum'] / $param['showing']);
		$param['startPageNum'] = max($param['pageNum'] - $PAGER_DELTA, 1);
		$param['endPageNum']   = min($param['pageNum'] + $PAGER_DELTA, $param['maxPageNum']);		
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// Set $data array
		//--------------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT st.study_instance_uid, sr.series_instance_uid, sr.series_number, pt.patient_id,"
				. " pt.patient_name, pt.sex, st.age, sr.series_date, sr.series_time, sr.modality,"
				. " sr.image_number, sr.series_description"
				. " FROM patient_list pt, study_list st, series_list sr "
				. $sqlCond . " ORDER BY " . $orderColStr;
				
		if($param['showing'] != "all")
		{
			$sqlStr .= " LIMIT ? OFFSET ?";
			array_push($condArr, $param['showing']);
			array_push($condArr, $param['showing'] * ($param['pageNum']-1));
		}

		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($condArr);

		//var_dump($stmt);
		//var_dump($condArr);
		
		$rowNum = $stmt->rowCount();
		$param['startNum'] = ($rowNum == 0) ? 0 : $param['showing'] * ($param['pageNum']-1) + 1;
		$param['endNum']   = ($rowNum == 0) ? 0 : $param['startNum'] + $rowNum - 1;	
		
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
		
			//var_dump($stmtCADMaster);
			//echo $result['modality'] . $result['series_description'] . $imgNum;
		
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
				$ptID   = $PinfoScramble->Encrypt($result['patient_id'], $_SESSION['key']);
				$ptName = $PinfoScramble->ScramblePtName();
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
									$PinfoScramble->Encrypt($result['patient_id'], $_SESSION['key']),
									$PinfoScramble->Encrypt($result['patient_name'], $_SESSION['key'])));
		}
		//var_dump($data);
		//--------------------------------------------------------------------------------------------------------------
		
		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		require_once('smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
		
		$smarty->assign('param', $param);
		$smarty->assign('colParam', $colParam);
		$smarty->assign('data', $data);
		
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