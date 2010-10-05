<?php
	session_cache_limiter('nocache');
	session_start();

	include_once("../common.php");
	
	//------------------------------------------------------------------------------------------------------------------
	// Auto logout (session timeout)
	//------------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'])  header('location: index.php?mode=timeout');
	else	$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	//------------------------------------------------------------------------------------------------------------------
	
	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables 
	//------------------------------------------------------------------------------------------------------------------
	$cadName = $_POST['cadName'];
	$version = $_POST['version'];

	$param = array('cadName'      => (isset($_POST['cadName'])) ? $_POST['cadName'] : "",
	               'version'      => (isset($_POST['version'])) ? $_POST['version'] : "",
				   'filterSex'    => (isset($_POST['filterSex']) && $_POST['filterSex'] != "undefined") ? $_POST['filterSex'] : "all",
				   'filterAgeMin' => (isset($_POST['filterAgeMin']) && $_POST['filterAgeMin'] != "undefined") ? $_POST['filterAgeMin'] : "",
				   'filterAgeMax' => (isset($_POST['filterAgeMax']) && $_POST['filterAgeMax'] != "undefined") ? $_POST['filterAgeMax'] : "",
				   'filterTag'    => (isset($_POST['filterTag']) && $_POST['filterTag'] != "undefined") ? $_POST['filterTag'] : "",
				   'srDateFrom'   => (isset($_POST['srDateFrom']) && $_POST['srDateFrom'] != "undefined") ? $_POST['srDateFrom'] : "",
				   'srDateTo'     => (isset($_POST['srDateTo']) && $_POST['srDateTo'] != "undefined") ? $_POST['srDateTo'] : "",
				   'srTimeTo'     => (isset($_POST['stTimeTo']) && $_POST['stTimeTo'] != "undefined") ? $_POST['stTimeTo'] : "",
				   'cadDateFrom'  => (isset($_POST['cadDateFrom']) && $_POST['cadDateFrom'] != "undefined") ? $_POST['cadDateFrom'] : "",
				   'cadDateTo'    => (isset($_POST['cadDateTo']) && $_POST['cadDateTo'] != "undefined") ? $_POST['cadDateTo'] : "",
				   'cadTimeTo'    => (isset($_POST['cadTimeTo']) && $_POST['cadTimeTo'] != "undefined") ? $_POST['cadTimeTo'] : "");

	//------------------------------------------------------------------------------------------------------------------

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
		
		//--------------------------------------------------------------------------------------------------------------
		// Create SQL queries 
		//--------------------------------------------------------------------------------------------------------------
		$condArr = array();	

		$stmt = $pdo->prepare("SELECT result_type FROM cad_master WHERE cad_name=? AND version=?");
		
		array_push($condArr, $param['cadName']);
		array_push($condArr, $param['version']);
				
		$stmt->execute($condArr);
		
		$resultType = $stmt->fetchColumn();
		
		if($resultType == 1)
		{
			$sqlStr = "SELECT el.exec_id, pt.patient_id, pt.patient_name, st.age, pt.sex,"
					. " sr.series_date, sr.series_time, el.executed_at"
					. " FROM patient_list pt JOIN (study_list st JOIN series_list sr"
					. " ON (st.study_instance_uid = sr.study_instance_uid)) ON (pt.patient_id=st.patient_id)"
					. " JOIN (executed_series_list es JOIN executed_plugin_list el"
					. " ON (es.exec_id=el.exec_id AND es.series_id=1 AND el.plugin_type=1))"
					. " ON (sr.series_instance_uid = es.series_instance_uid)"
					. " LEFT JOIN lesion_feedback lf ON (es.exec_id=lf.exec_id AND lf.interrupt_flg='f')"
					. " WHERE el.plugin_name=? AND el.version=? AND lf.consensual_flg='t'";
		}
		else
		{
			$sqlStr = "SELECT el.exec_id, pt.patient_id, pt.patient_name, st.age, pt.sex,"
					. " sr.series_date, sr.series_time, el.executed_at"
					. " FROM patient_list pt, study_list st, series_list sr, "
					. " executed_plugin_list el, executed_series_list es"
					. " WHERE el.plugin_name=? AND el.version=?"
					. " AND el.exec_id=es.exec_id AND es.series_id=1"
					. " AND sr.series_instance_uid = es.series_instance_uid"
					. " AND st.study_instance_uid = sr.study_instance_uid"
					. " AND pt.patient_id=st.patient_id";
		}
		
		if($param['cadDateFrom'] != "" && $param['cadDateTo'] != "" && $param['cadDateFrom'] == $param['cadDateTo'])
		{
			$sqlStr .= " AND el.executed_at>=? AND el.executed_at<=?";
			array_push($condArr, $param['cadDateFrom'] . ' 00:00:00');
			array_push($condArr, $param['cadDateFrom'] . ' 23:59:59');
		}
		else
		{
			if($param['cadDateFrom'] != "")
			{
				$sqlStr .= " AND ?<=el.executed_at";
				array_push($condArr, $param['cadDateFrom'].' 00:00:00');
				$optionNum++;
			}
		
			if($param['cadDateTo'] != "")
			{
				$sqlStr .= " AND el.executed_at<=?";
		
				if($param['cadTimeTo'] != "")
				{
					array_push($condArr, $param['cadDateTo'] . ' ' . $param['cadTimeTo']);
				}
				else
				{
					array_push($condArr, $param['cadDateTo'] . '23:59:59');
				}
			}
		}

	
		if($param['srDateFrom'] != "" && $param['srDateTo'] != "" && $param['srDateFrom'] == $param['srDateTo'])
		{
			$sqlStr .= " AND sr.series_date=?";
			array_push($condArr, $param['srDateFrom']);
		}
		else
		{
			if($param['srDateFrom'] != "")
			{
				$sqlStr .= " AND ?<=sr.series_date";
				array_push($condArr, $param['srDateFrom']);
			}
		
			if($param['srDateTo'] != "")
			{
				if($param['srTimeTo'] != "")
				{
					$sqlStr .= " AND (sr.series_date<? OR (sr.series_date=? AND sr.series_date<=?))";
					array_push($condArr, $param['srDateTo']);
					array_push($condArr, $param['srDateTo']);
					array_push($condArr, $param['srTimeTo']);
				}
				else
				{
					$sqlStr .= " AND sr.series_date<=?";
					array_push($condArr, $param['srDateTo']);
				}
			}
		}
		
		if($param['filterSex'] == "M" || $param['filterSex'] == "F")
		{
			$sqlStr .= " AND pt.sex=?";
			array_push($condArr, $param['filterSex']);
			$optionNum++;
		}

		if($param['filterAgeMin'] != "" && $param['filterAgeMax'] != "" && $param['filterAgeMin'] == $param['filterAgeMax'])
		{
			$sqlStr .= " AND st.age=?";
			array_push($condArr, $param['filterAgeMin']);
		}
		else
		{
			if($param['filterAgeMin'] != "")
			{
				$sqlStr .= " AND ?<=st.age";
				array_push($condArr, $param['filterAgeMin']);
			}
		
			if($param['filterAgeMax'] != "")
			{
				$sqlStr .= " AND st.age<=?";
				array_push($condArr, $param['filterAgeMax']);
			}
		}

		if($param['filterTag'] != "")
		{
			$sqlStr .= " AND el.exec_id IN (SELECT DISTINCT exec_id FROM executed_plugin_tag WHERE tag~*?)";
			array_push($condArr, $param['filterTag']);
		}
		
		$sqlStr .= " GROUP BY el.exec_id, pt.patient_id, pt.patient_name, st.age, pt.sex,"
				.  " sr.series_date, sr.series_time, el.executed_at ORDER BY el.exec_id ASC";
		
		//echo $sqlStr;

		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($condArr);
		
		$cadList = array();
		
		while($result = $stmt->fetch(PDO::FETCH_ASSOC)) 
		{
			array_push($cadList, $result);
		}

		echo json_encode($cadList);
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
