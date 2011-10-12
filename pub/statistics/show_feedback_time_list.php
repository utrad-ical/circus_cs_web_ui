<?php
session_start();

include("../common.php");

//------------------------------------------------------------------------------------------------------------
// Import $_POST variables and validation
//------------------------------------------------------------------------------------------------------------
$mode = (isset($_GET['mode']) && $_GET['mode'] == 'redraw') ? 'redraw' : "";

$params = array();
$validator = new FormValidator();

$validator->addRules(array(
	"dateFrom" => array(
		"type" => "date",
		"label" => "Series date"),
	"dateTo" => array(
		"type" => "date",
		"label" => "Series date"),
	"cadName" => array(
		"type" => "cadname",
		"otherwise"=> "all",
		"label" => "CAD name"),
	"version" => array(
		"type" => "version",
		"otherwise" => "all"),
	"evalUser" => array(
		"type" => "string")
	));

if($validator->validate($_POST))
{
	$params = $validator->output;
	$params['errorMessage'] = "&nbsp;";
}
else
{
	$params = $validator->output;
	$params['errorMessage'] = implode('<br/>', $validator->errors);
}

if($_SESSION['allStatFlg'])		$userID = $params['evalUser'];
else							$userID = $_SESSION['userID'];
//------------------------------------------------------------------------------------------------------------

$dstData = array('errorMessage' => $params['errorMessage'],
				 'tblHtml'      => "");

if($params['errorMessage'] == "&nbsp;")
{
	try
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		//----------------------------------------------------------------------------------------------------
		// Create tblHtml
		//----------------------------------------------------------------------------------------------------
		$sqlParams = array();

		$sqlStr = "SELECT DISTINCT(fa.job_id)"
				. " FROM executed_plugin_list el, executed_series_list es, plugin_master pm,"
				. " feedback_action_log fa, series_list sr"
				. " WHERE pm.plugin_id=el.plugin_id AND pm.plugin_name=?";

		$sqlParams[] = $params['cadName'];

		if($params['version'] != "all")
		{
			$sqlStr .= " AND pm.version=?";
			$sqlParams[] = $params['version'];
		}

		$sqlStr .= " AND es.job_id=el.job_id AND fa.job_id=el.job_id AND es.volume_id=0"
				.  " AND sr.sid = es.series_sid";

		if($params['dateFrom'] != "")
		{
			$sqlStr .= " AND sr.series_date>=?";
			$sqlParams[] = $params['dateFrom'];
		}

		if($params['dateTo'] != "")
		{
			$sqlStr .= " AND sr.series_date<=?";
			$sqlParams[] = $params['dateTo'];
		}

		$sqlStr .= " AND fa.user_id=? ORDER BY fa.job_id ASC";
		$sqlParams[] = $userID;

		$jobIDList = DBConnector::query($sqlStr, $sqlParams, 'ALL_COLUMN');

		for($j = 0; $j < count($jobIDList); $j++)
		{
			$sqlStr = "SELECT sr.patient_id, sr.series_date, sr.series_time,"
					. " pm.plugin_name, pm.version, el.executed_at,"
					. " fa.action, fa.options, fa.act_time"
					. " FROM executed_plugin_list el, executed_series_list es, plugin_master pm,"
					. " feedback_action_log fa, series_join_list sr"
					. " WHERE el.job_id=?"
					. " AND fa.user_id=?"
					. " AND pm.plugin_id=el.plugin_id"
					. " AND fa.job_id=el.job_id"
					. " AND es.job_id=el.job_id AND es.volume_id=0"
					. " AND sr.series_sid = es.series_sid"
					. " ORDER BY fa.sid ASC";

			$results = DBConnector::query($sqlStr, array($jobIDList[$j], $userID), 'ALL_NUM');

			$startFlg = 0;
			$fnInputFlg = 0;
			$transFlg = 0;
			$totalStartTime = "";
			$totalEndTime = "";
			$fnStartTime = "";
			$fnEndTime = "";
			$transStartTime = "";
			$transEndTime = "";
			$fnTime = 0;
			$transTime = 0;
			$tmpStr = "";

			for($i = 0; $i < count($results); $i++)
			{
				if($i==0)
				{
					$tmpStr = '<tr';
					if($j%2==1) $tmpStr .= ' class="column"';

					$tmpStr .= '>'
					        .  '<td>' . $jobIDList[$j] . '</td>'
							.  '<td>' . $results[$i][0] . '</td>'
							.  '<td>' . $results[$i][1] . '</td>'
							.  '<td>' . $results[$i][2] . '</td>'
							.  '<td>' . $results[$i][3] . ' v.' . $results[$i][4] . '</td>'
							.  '<td>' . $results[$i][5] . '</td>';
				}

				if($startFlg == 0)
				{
					if($results[$i][6] == 'open' && $results[$i][7] == 'CAD result')
					{
						$totalStartTime = $results[$i][8];
					}
				}

				if($startFlg == 0
				   && ($results[$i][6] != 'open' || ($results[$i][6] == 'open' &&  $results[$i][7] == 'FN input')))
				{
					$startFlg=1;
				}

				if($fnInputFlg == 0 && $results[$i][6] == 'open' &&  $results[$i][7] == 'FN input')
				{
					$fnInputFlg = 1;
					$fnStartTime = $results[$i][8];
				}

				if($fnInputFlg == 1 && $results[$i][6] == 'save' &&  $results[$i][7] == 'FN input')
				{
					$fnInputFlg = 0;
					$fnEndTime = $results[$i][8];

					$fnTime += (strtotime($fnEndTime)-strtotime($fnStartTime));
				}

				if($startFlg == 1 &&  $results[$i][6] == 'save')
				{
					$transFlg = 1;
					$transStartTime = $results[$i][8];
				}

				if($transFlg == 1 &&  $results[$i][6] == 'open')
				{
					$transFlg = 0;
					$transEndTime = $results[$i][8];

					$transTime += (strtotime($transEndTime)-strtotime($transStartTime));
				}

				if($results[$i][6] == 'register')
				{
					$totalEndTime = $results[$i][8];
					break;
				}
			} // end for: $i
			
			if($totalStartTime != "" && $totalEndTime != "")
			{
				//------------------------------------------------------------------------------------
				// For TP and FN column
				//------------------------------------------------------------------------------------
				$sqlStr = "SELECT COUNT(*) FROM feedback_list fl, candidate_classification cc"
						. " WHERE fl.job_id=? AND fl.entered_by=? AND cc.fb_id=fl.fb_id"
						. " AND fl.is_consensual='f' AND fl.status=1 AND cc.candidate_id>0";
				$dispCandNum = DBConnector::query($sqlStr, array($jobIDList[$j], $userID), 'SCALAR');

				$sqlStr = "SELECT fn_num FROM feedback_list fl, fn_count fn"
						. " WHERE fl.job_id=? AND fl.entered_by=? AND fn.fb_id=fl.fb_id"
						. " AND fl.is_consensual='f' AND fl.status=1";
				$enterFnNum = DBConnector::query($sqlStr, array($jobIDList[$j], $userID), 'SCALAR');

				$sqlStr = "SELECT COUNT(*) FROM feedback_list fl, candidate_classification cc"
						. " WHERE fl.job_id=? AND cc.fb_id=fl.fb_id"
						. " AND fl.is_consensual=? AND status=1 AND evaluation>=1";
				$stmtTP = $pdo->prepare($sqlStr);

				// SQL statement for count No. of FN
				$sqlStr = "SELECT fn_num FROM feedback_list fl, fn_count fn"
						. " WHERE fl.job_id=? AND fn.fb_id=fl.fb_id"
						. " AND fl.is_consensual=? AND fl.status=1 AND fn.fn_num>0";
				$stmtFN = $pdo->prepare($sqlStr);

				$tpColStr = "-";
				$fnColStr = "-";

				$stmtTP->bindValue(1, $jobIDList[$j]);
				$stmtTP->bindValue(2, 't', PDO::PARAM_BOOL);
				$stmtTP->execute();

				if($stmtTP->fetchColumn() > 0)	$tpColStr = '<span style="font-weight:bold;">+</span>';
				else
				{
					$stmtTP->bindValue(2, 'f', PDO::PARAM_BOOL);
					$stmtTP->execute();
					if($stmtTP->fetchColumn() > 0) $tpColStr = '<span style="font-weight:bold;">!</span>';
				}

				$stmtFN->bindValue(1, $jobIDList[$j]);
				$stmtFN->bindValue(2, 't', PDO::PARAM_BOOL);
				$stmtFN->execute();

				if($stmtFN->fetchColumn() > 0)  $fnColStr = '<span style="font-weight:bold;">+</span>';
				else
				{
					$stmtFN->bindValue(2, 'f', PDO::PARAM_BOOL);
					$stmtFN->execute();
					if($stmtFN->fetchColumn() > 0)  $fnColStr = '<span style="font-weight:bold;">!</span>';
				}
				//------------------------------------------------------------------------------------

				$tmpStr .= '<td>' . (strtotime($totalEndTime)-strtotime($totalStartTime)-$fnTime-$transTime) . '</td>'
						.  '<td>' . $fnTime . '</td>'
						.  '<td>' . $dispCandNum . '</td>'
						.  '<td>' . $enterFnNum . '</td>'
					//	.  '<td>' . $tpColStr . '</td>'
					//	.  '<td>' . $fnColStr . '</td>'
						.  '</tr>';

				//$tmpStr .= '<td>' . $totalStartTime  . ' - ' . $totalEndTime . '='
				//        . (strtotime($totalEndTime)-strtotime($totalStartTime)) . '</td></tr>';
			}
			else $tmpStr = "";

			$dstData['tblHtml'] .= $tmpStr;
		} // end for: $j
		//----------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;
}
echo json_encode($dstData);

?>
