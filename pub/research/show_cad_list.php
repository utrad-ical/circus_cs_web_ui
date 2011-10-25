<?php
$params = array('toTopDir' => "../");
include_once("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(Auth::RESEARCH_EXEC);

try
{
	$currentUser = Auth::currentUser();
	
	// Connect to SQL Server
	$pdo = DBConnector::getConnection();

	$userID = $_SESSION['userID'];

	//-----------------------------------------------------------------------------------------------------------------
	// Import $_POST variables and validation
	//-----------------------------------------------------------------------------------------------------------------
	$params = array();

	$validator = new FormValidator();

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

	// including bug in SQL statement (2011.05.19)

	if($params['errorMessage'] == "")
	{
		$sqlStr = "SELECT cm.result_type FROM plugin_master pm, plugin_cad_master cm"
				. " WHERE cm.plugin_id=pm.plugin_id AND pm.plugin_name=? AND pm.version=?";
		$condArr = array($params['cadName'], $params['version']);

		$resultType = DBConnector::query($sqlStr, $condArr, 'SCALAR');

		if($resultType == 1)
		{
			$sqlStr = "SELECT el.job_id, sr.patient_id, sr.patient_name, sr.age, sr.sex,"
					. " sr.series_date, sr.series_time, el.executed_at"
					. " FROM series_join_list sr, executed_plugin_list el,"
					. " executed_series_list es, feedback_list fl, plugin_master pm"
					. " WHERE pm.plugin_id=el.plugin_id"
					. " AND pm.plugin_name=? AND pm.version=?"
					. " AND es.job_id=el.job_id"
					. " AND es.volume_id=0"
					. " AND sr.series_sid=es.series_sid"
					. " AND fl.job_id=el.job_id"
					. " AND fl.is_consensual='t' AND fl.status=1";
		}
		else
		{
			$sqlStr = "SELECT el.job_id, sr.patient_id, sr.patient_name, sr.age, sr.sex,"
					. " sr.series_date, sr.series_time, el.executed_at"
					. " FROM series_join_list sr, plugin_master pm,"
					. " executed_plugin_list el, executed_series_list es"
					. " WHERE pm.plugin_id=el.plugin_id"
					. " AND pm.plugin_name=? AND pm.version=?"
					. " AND el.job_id=es.job_id AND es.volume_id=0"
					. " AND sr.series_sid = es.series_sid";
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
			$sqlStr .= " AND sr.sex=?";
			$condArr[] = $params['filterSex'];
		}

		if($params['filterAgeMin'] != "" && $params['filterAgeMax'] != "" && $params['filterAgeMin'] == $params['filterAgeMax'])
		{
			$sqlStr .= " AND sr.age=?";
			$condArr[] = $params['filterAgeMin'];
		}
		else
		{
			if($params['filterAgeMin'] != "")
			{
				$sqlStr .= " AND ?<=sr.age";
				$condArr[] = $params['filterAgeMin'];
			}

			if($params['filterAgeMax'] != "")
			{
				$sqlStr .= " AND sr.age<=?";
				$condArr[] = $params['filterAgeMax'];
			}
		}

		if($params['filterTag'] != "")
		{
			$sqlStr .= " AND el.job_id IN (SELECT DISTINCT reference_id FROM tag_list WHERE category=4 AND tag~*?)";
			$condArr[] = $params['filterTag'];
		}

		$sqlStr .= " GROUP BY el.job_id, sr.patient_id, sr.patient_name, sr.age, sr.sex,"
				.  " sr.series_date, sr.series_time, el.executed_at ORDER BY el.job_id ASC";
		//echo $sqlStr;

		$cadList =  DBConnector::query($sqlStr, $condArr, 'ALL_ASSOC');
		
		// Enter anonymization mode
		if ($currentUser->anonymized || !$currentUser->hasPrivilege(Auth::PERSONAL_INFO_VIEW))
		{
			for($i = 0; $i < count($cadList); $i++)
			{
				$cadList[$i]['patient_id'] = PinfoScramble::encrypt($cadList[$i]['patient_id'] , $_SESSION['key']);
				$cadList[$i]['patient_name'] = PinfoScramble::scramblePtName();;
			}
		}

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
