<?php
function query_job($api_request)
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

function get_series_jobs($UIDs)
{
	
}

function get_study_jobs($UIDs)
{
	
}
?>
