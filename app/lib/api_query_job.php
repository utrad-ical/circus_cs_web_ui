<?php
function query_job($api_request)
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
			$result = get_series_jobs($seriesUIDs);
		}
		else if(isset($studyUIDs))
		{
			$result = get_study_jobs($studyUIDs);
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

function get_series_jobs($UIDs)
{
	
}

function get_study_jobs($UIDs)
{
	
}
?>
