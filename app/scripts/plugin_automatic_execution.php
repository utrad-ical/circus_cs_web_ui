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
		printf("ERROR: %s is not exist.\r\n", $jsonFileName);
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

