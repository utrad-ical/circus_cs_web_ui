<?php
function count_images($api_request)
{
	$params = $api_request['params'];
	
	$seriesUIDs = $params['seriesInstanceUID'];
	$studyUIDs = $params['studyInstanceUID'];
	
	if((isset($seriesUIDs) && isset($studyUIDs))
		|| (!isset($seriesUIDs) && !isset($studyUIDs)))
	{
		throw new ApiException("OperationError", "Invalid parameters.");
	}
	
	try
	{
		if(isset($seriesUIDs))
		{
			return get_series_counts($seriesUIDs);
		}
		else if(isset($studyUIDs))
		{
			return get_study_counts($studyUIDs);
		}
	}
	catch (Exception $e)
	{
		throw new ApiException("SystemError", "Database connection error.");
	}
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
