<?php

class QueryJobAction extends ApiAction
{
	const studyUID  = "studyUID";
	const seriesUID = "seriesUID";
	const jobID     = "jobID";
	const show      = "show";
	
	static $param_strings = array(
		studyUID,
		seriesUID,
		jobID,
		show	// "queue_list" or "error_list"
	);
	
	
	function execute($api_request)
	{
		$action = $api_request['action'];
		$params = $api_request['params'];
		$show = $params['show'];
		
		if(self::check_params($params) == FALSE) {
			throw new ApiException("Invalid parameter.", ApiResponse::STATUS_ERR_OPE);
		}
		
		$cond = $params[1];
		switch ($cond)
		{
			case studyUID:
				break;
				
			case seriesUID:
				break;
				
			case jobID:
				break;
				
			case show:
				if ("queue_list") {
					
				} elseif ("error_list") {
					
				}
				throw new ApiException("Invalid parameter.", ApiResponse::STATUS_ERR_OPE);
				break;
				
			default:
				throw new ApiException("Invalid parameter.", ApiResponse::STATUS_ERR_OPE);
				break;
		}
		
		$result = array();
		
		$res = new ApiResponse();
		$res->setResult($action, $result);
		return $res;
	}
	
	
	private function check_params($params)
	{
		if(count($params) != 1) {
			return FALSE;
		}
		
		$p = $params[1];
		if(!array_search($p, self::$param_strings)) {
			return FALSE;
		}
		
		return TRUE;
	}
	
	
	function query_job($jobIDlist)
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		$sqlStr = "select"
		. "   job.*,"
		. "   count(jq.job_id) as waiting"
		. " from"
		. "   job_queue jq,"
		. " ("
		. "   select"
		. "     sl.study_instance_uid as studyUID,"
		. "     sl.series_instance_uid as seriesUID,"
		. "     jq.job_id as jobID,"
		. "     pm.plugin_name as pluginName,"
		. "     pm.version as pluginVersion,"
		. "     rp.policy_name as resultPolicy,"
		. "     jq.registered_at as registeredAt,"
		. "     jq.status,"
		. "     jq.priority"
		. "   from"
		. "     job_queue jq,"
		. "     job_queue_series qs,"
		. "     series_list sl,"
		. "     plugin_master pm,"
		. "     plugin_result_policy rp,"
		. "     executed_plugin_list el"
		. "   where jq.plugin_id = pm.plugin_id"
		. "     and jq.job_id = qs.job_id"
		. "     and qs.series_sid = sl.sid"
		. "     and jq.job_id = el.job_id"
		. "     and el.policy_id = rp.policy_id"
		. "     and jq.job_id = ?"
		. " ) job"
		. " where"
		. "   jq.priority >= job.priority"
		. " and"
		. "   jq.registered_at <= job.registeredAt"
		. " group by"
		. "   job.studyUID,"
		. "   job.seriesUID,"
		. "   job.jobID,"
		. "   job.pluginName,"
		. "   job.pluginVersion,"
		. "   job.resultPolicy,"
		. "   job.registeredAt,"
		. "   job.status,"
		. "   job.priority";
		
		$result = DBConnector::query($sqlStr, $jobIDlist, 'ALL_ASSOC');
		
		$pdo = null;
		
		return $result;
	}
}

?>
