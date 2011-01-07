<?php
	session_cache_limiter('nocache');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include_once("auto_logout_research_exec.php");	
	require_once('../class/PersonalInfoScramble.class.php');	
	require_once('../class/validator.class.php');		

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
	
		$userID = $_SESSION['userID'];
		
		//-----------------------------------------------------------------------------------------------------------------
		// Import $_POST variables and validation
		//-----------------------------------------------------------------------------------------------------------------
		$params = array();
	
		PgValidator::$conn = $pdo;
		$validator = new FormValidator();
		$validator->registerValidator('pgregex', 'PgRegexValidator');
	
		$validator->addRules(array(
			"cadName" => array(
				"type" => "cadname",
				"errorMes" => "'CAD' is invalid."),
			"version" => array(
				"type" => "version",
				"errorMes" => "'Version' is invalid."),
			"filterSex" => array(
				"type" => "select",
				"options" => array('M', 'F', 'all'),
				"default" => "all",
				"otherwise" => "all"),
			"filterAgeMin" => array(
				"type" => "int", 
				"min" => "0",
				"errorMes" => "'Age' is invalid."),
			"filterAgeMax" => array(
				"type" => "int", 
				"min" => "0",
				"errorMes" => "'Age' is invalid."),
			"srDateFrom" => array(
				"type" => "date",
				"errorMes" => "'Series date' is invalid."),
			"srDateTo" => array(
				"type" => "date",
				"errorMes" => "'Series date' is invalid."),
			"srTimeTo" => array(
				"type" => "time",
				"errorMes" => "'Series time' is invalid."),
			"cadDateFrom" => array(
				"type" => "date",
				"errorMes" => "'CAD date' is invalid."),
			"cadDateTo" => array(
				"type" => "date",
				"errorMes" => "'CAD date' is invalid."),
			"cadTimeTo" => array(
				"type" => "time",
				"errorMes" => "'CAD time' is invalid."),
			"filterTag"=> array(
				"type" => "pgregex",
				"errorMes" => "'Tag' is invalid."),
			));
		
		if($validator->validate($_POST))
		{
			$params = $validator->output;
			$params['errorMessage'] = "";
			
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
		
		$params['toTopDir'] = "../";		
		//--------------------------------------------------------------------------------------------------------------
		
		//--------------------------------------------------------------------------------------------------------------
		// Create SQL queries 
		//--------------------------------------------------------------------------------------------------------------

		if($params['errorMessage'] == "")
		{
			$sqlStr = "SELECT result_type FROM cad_master WHERE cad_name=? AND version=?";
			$condArr = array($params['cadName'], $params['version']);
		
			$resultType = PdoQueryOne($pdo, $sqlStr, $condArr, 'SCALAR');
		
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
		
			if($params['cadDateFrom'] != "" && $params['cadDateTo'] != ""
			   && $params['cadDateFrom'] == $params['cadDateTo'])
			{
				$sqlStr .= " AND el.executed_at>=? AND el.executed_at<=?";
				$condArr[] = $params['cadDateFrom'] . ' 00:00:00';
				$condArr[] = $params['cadDateFrom'] . ' 23:59:59';
			}
			else
			{
				if($params['cadDateFrom'] != "")
				{
					$sqlStr .= " AND ?<=el.executed_at";
					$condArr[] = $params['cadDateFrom'] .' 00:00:00';
				}
		
				if($params['cadDateTo'] != "")
				{
					$sqlStr .= " AND el.executed_at<=?";
		
					if($params['cadTimeTo'] != "")
					{
						$condArr[] = $params['cadDateTo'] . ' ' . $params['cadTimeTo'];
					}
					else
					{
						$condArr[] = $params['cadDateTo'] . ' 23:59:59';
					}
				}
			}

			if($params['srDateFrom'] != "" && $params['srDateTo'] != ""
			   && $params['srDateFrom'] == $params['srDateTo'])
			{
				$sqlStr .= " AND sr.series_date=?";
				$condArr[] = $params['srDateFrom'];
			}
			else
			{
				if($params['srDateFrom'] != "")
				{
					$sqlStr .= " AND ?<=sr.series_date";
					$condArr[] = $params['srDateFrom'];
				}
		
				if($params['srDateTo'] != "")
				{
					$condArr[] = $params['srDateTo'];

					if($params['srTimeTo'] != "")
					{
						$sqlStr .= " AND (sr.series_date<? OR (sr.series_date=? AND sr.series_date<=?))";
						$condArr[] = $params['srDateTo'];
						$condArr[] = $params['srTimeTo'];
					}
					else
					{
						$sqlStr .= " AND sr.series_date<=?";
					}
				}
			}
		
			if($params['filterSex'] == "M" || $params['filterSex'] == "F")
			{
				$sqlStr .= " AND pt.sex=?";
				$condArr[] = $params['filterSex'];
			}

			if($params['filterAgeMin'] != "" && $params['filterAgeMax'] != "" && $params['filterAgeMin'] == $params['filterAgeMax'])
			{
				$sqlStr .= " AND st.age=?";
				$condArr[] = $params['filterAgeMin'];
			}
			else
			{
				if($params['filterAgeMin'] != "")
				{
					$sqlStr .= " AND ?<=st.age";
					$condArr[] = $params['filterAgeMin'];
				}
			
				if($params['filterAgeMax'] != "")
				{
					$sqlStr .= " AND st.age<=?";
					$condArr[] = $params['filterAgeMax'];
				}
			}

			if($params['filterTag'] != "")
			{
				$sqlStr .= " AND el.exec_id IN (SELECT DISTINCT reference_id FROM tag_list WHERE category=4 AND tag~*?)";
				$condArr[] = $params['filterTag'];
			}
		
			$sqlStr .= " GROUP BY el.exec_id, pt.patient_id, pt.patient_name, st.age, pt.sex,"
					.  " sr.series_date, sr.series_time, el.executed_at ORDER BY el.exec_id ASC";
			//echo $sqlStr;

			$cadList =  PdoQueryOne($pdo, $sqlStr, $condArr, 'ALL_ASSOC');

			echo json_encode($cadList);
		}
		else echo null;
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
?>
