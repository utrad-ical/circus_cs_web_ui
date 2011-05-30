<?php
function count_images($api_request)
{
	$params = $api_request['params'];
	$action = $api_request['action'];
	
	$seriesUIDs = $params['seriesInstanceUID'];
	$studyUIDs = $params['studyInstanceUID'];
	
	if(check_params($params) == FALSE)
	{
		$res = new ApiResponse();
		$res->setError($action, ApiResponse::STATUS_ERR_OPE, "Invalid parameter.");
		return $res;
	}
	
	try
	{
		$result = array();
		if(isset($seriesUIDs))
		{
			$result = get_series_counts($seriesUIDs);
		}
		else if(isset($studyUIDs))
		{
			$result = get_study_counts($studyUIDs);
		}
		
		$res = new ApiResponse();
		$res->setResult($action, $result);
		return $res;
	}
	catch (Exception $e)
	{
		$res = new ApiResponse();
		$res->setError($action, ApiResponse::STATUS_ERR_SYS, "Database connection error.");
		return $res;
	}
}

function check_params($params)
{
	$seriesUIDs = $params['seriesInstanceUID'];
	$studyUIDs = $params['studyInstanceUID'];
	
	if((isset($seriesUIDs) && isset($studyUIDs))
		|| (!isset($seriesUIDs) && !isset($studyUIDs)))
	{
		return FALSE;
	}
	return TRUE;
}

function get_series_counts($UIDs)
{
	$result = array();
	try
	{
		foreach ($UIDs as $id)
		{
			$series = new Series($id);
			array_push(
				$result,
				array(
					"studyInstanceUID" => $series->study_instance_uid,
					"seriesInstanceUID" => $id,
					"number" => $series->image_number
				)
			);
		}
	}
	catch (PDOException $e)
	{
		throw $e;
	}
	
	return $result;
}

function get_study_counts($UIDs)
{
	$result = array();
	try
	{
		foreach ($studyUIDs as $id)
		{
			$series = new Series();
			$studies = $series->find(array('study_instance_uid' => $id));
			foreach ($studies as $s)
			{
				array_push(
					$result,
					array(
						"studyInstanceUID" => $s,
						"seriesInstanceUID" => $s->series_instance_uid,
						"number" => $s->image_number
					)
				);
			}
		}
	}
	catch (PDOException $e)
	{
		throw $e;
	}
	
	return $result;
}
