<?php

/**
 * Script for plug-in job automatic execution
 * @author Yukihiro Nomura <nomuray-tky@umin.ac.jp>
 */

include_once('../../pub/common.php');
$executedBy = "server_service";

function appendLog($fileName, $date, $message)
{
	$fp = fopen($fileName, "a");

	if($fp)  fprintf($fp, "[%s] %s\r\n", $date->format('Y-m-d H:i:s'), $message);

	fclose($fp);
}

try
{
	$pdo = DBConnector::getConnection();

	$nowDateTime = new DateTime();

	//----------------------------------------------------------------------------------------------
	// Load configuration file (JSON format)
	//----------------------------------------------------------------------------------------------
	$jsonFileName = $argv[1];

	if(!file_exists($jsonFileName))
	{
		printf("ERROR: %s does not exist.\r\n", $jsonFileName);
	}
	$paramData = json_decode(file_get_contents($jsonFileName), TRUE);
	//----------------------------------------------------------------------------------------------

	foreach($paramData["pluginDefinition"] as $params)
	{
		//------------------------------------------------------------------------------------------
		// Check plugin exists
		//------------------------------------------------------------------------------------------
		$plugin = Plugin::selectOne(array('plugin_name' => $params['pluginName'],
										 'version' => $params['version']));
		if (!$plugin)
		throw new Exception($params['pluginName'].' ver.'.$params['version'].' is not installed.');
		//------------------------------------------------------------------------------------------

		//------------------------------------------------------------------------------------------
		// Check plugin executable time
		//------------------------------------------------------------------------------------------
		$timeRangeFlg = 0;

		foreach($params['executableTime']['ranges'] as $timeArr)
		{
			$fromTime = clone $nowDateTime;
			$toTime   = clone $nowDateTime;

			$fromArr = explode(":", $timeArr['from']);
			$toArr   = explode(":", $timeArr['to']);

			$fromTime->setTime($fromArr[0], $fromArr[1], $fromArr[2]);
			$toTime->setTime($toArr[0], $toArr[1], $toArr[2]);

			if($fromTime <= $nowDateTime && $nowDateTime <= $toTime)
			{
				$timeRangeFlg = 1;
				break;
			}
		}
		if($timeRangeFlg == 0) continue;
		//var_dump($params['executableTime']);
		//------------------------------------------------------------------------------------------

		//------------------------------------------------------------------------------------------
		// Select series candidates by 'last_recieved_at'
		//------------------------------------------------------------------------------------------
		$minLastRecieveTime = clone $nowDateTime;
		$minLastRecieveTime->sub(new DateInterval('PT' . $params['minLastRecieveTime'] . 'M'));

		$maxLastRecieveTime = clone $nowDateTime;
		$maxLastRecieveTime->sub(new DateInterval('PT' . $params['maxLastRecieveTime'] . 'M'));

		$seriesList = Series::select(array('last_received_at>=' => $maxLastRecieveTime->format('Y-m-d H:i:s'),
										   'last_received_at<=' => $minLastRecieveTime->format('Y-m-d H:i:s')),
									 array('order' => array('sid ASC')));
		//------------------------------------------------------------------------------------------

		foreach($seriesList as $series)
		{
			$availSeries = Job::findExecutableSeries($plugin, $series->series_instance_uid);

			if (array_product(array_map("count", $availSeries)) == 1)
			{
				$seriesUidArr = array();
				$j = new QueryJobAction($executedBy);

				foreach($availSeries as $item)
				{
					$seriesUidArr[] = $item[0]->series_instance_uid;
				}
				//var_dump($seriesUidArr);

				$result = $j->query_job_series(array('seriesUID' => $seriesUidArr));
				//var_dump($result[0]);

				try
				{
					$pdo->beginTransaction();

					// Check number of execution failure (Failed/Invalidated/Aborted)
					$ps = $seriesUidArr[0]; // series UID for the primary series
					$s = Series::selectOne(array('series_instance_uid' => $ps));

					$eqn = array();
					$binds = array();

					foreach($seriesUidArr as $vid => $series_uid)
					{
						$eqn[] = 'es.volume_id = ? AND sl.series_instance_uid = ?';
						$binds[] = $vid;
						$binds[] = $series_uid;
					}
					$or_clause = implode(' OR ', $eqn);

					// Find exactly the same job (same plugin, same combination of series),
					// which is marked as 'failed', 'invalidated' or 'aborted'.
					$sqlStr = <<<EOT
SELECT el.job_id AS job_id
FROM executed_plugin_list AS el
  JOIN executed_series_list AS es
    ON el.job_id = es.job_id
  JOIN series_list AS sl
    ON es.series_sid = sl.sid
WHERE
  el.plugin_id = ? AND el.status < 0
  AND ($or_clause)
GROUP BY el.job_id
HAVING COUNT(*) = ?
EOT;
					array_unshift($binds, $plugin->plugin_id);
					array_push($binds, count($seriesUidArr));

					$failedJobIdList = DBConnector::query($sqlStr, $binds, 'ALL_COLUMN');
					//printf("%d/%d\r\n", count($failedJobIdList), $params['maxRetryNum']);

					if($params['maxRetryNum'] < count($failedJobIdList))
					{
						$message = 'The number of exection failure was exceeded'
									. '(Plug-in:'.$params['pluginName'].' ver.'.$params['version']
									. ', UID of 1st series:'.$seriesUidArr[0].')';
						throw new Exception($message);
					}

					$jobID = Job::registerNewJob( $plugin,
												  $seriesUidArr,
												  $executedBy,
												  $params['priority'],
												  $params['resultPolicy']);

					$pdo->commit();
				}
				catch (Exception $e)
				{
					$pdo->rollBack();

					if($paramData['verbose'])
					{
						$buffer = sprintf("Error: %s", $e->getMessage());
						appendLog($paramData['errLogFileName'], $nowDateTime, $buffer);
					}
					continue;
				}

				$result = $j->query_job(array($jobID));

				$buffer = sprintf("Registered (JobID: %d, Plug-in: %s ver.%s, SeriesUID: %s)",
									$result[0]['jobID'],
									$result[0]['pluginName'],
									$result[0]['pluginVersion'],
									$result[0]["seriesUID"]);

				appendLog($paramData['logFileName'], $nowDateTime, $buffer);
				//var_dump($result[0]);

			}
			//if(count($availSeries[0]) > 0)  var_dump($availSeries);
		}
	}
}
catch (PDOException $e)
{
	var_dump($e->getMessage());
}

