<?php

class QueryJobAction extends ApiAction
{
	const studyUID  = "studyuid";
	const seriesUID = "seriesuid";
	const jobID     = "jobid";
	const show      = "show";
	
	static $param_strings = array(
		self::studyUID,
		self::seriesUID,
		self::jobID,
		self::show	// "queue_list" or "error_list"
	);
	
	protected static $required_privileges = array(
		Auth::API_EXEC
	);
	
	
	function requiredPrivileges()
	{
		return self::$required_privileges;
	}
	
	
	function execute($api_request)
	{
		$action = $api_request['action'];
		$params = $api_request['params'];
		$show = $params['show'];
		
		if(self::check_params($params) == FALSE) {
			throw new ApiException("Invalid parameter.", ApiResponse::STATUS_ERR_OPE);
		}
		
		$result = array();
		
		$cond = strtolower(key($params));
		switch ($cond)
		{
			case self::studyUID:
				$result = self::query_job_study($params['studyUID']);
				break;
				
			case self::seriesUID:
				$result = self::query_job_series($params['seriesUID']);
				break;
				
			case self::jobID:
				$result = self::query_job($params['jobID']);
				break;
				
			case self::show:
				if ($params['show'] == "queue_list")
				{
					$result = self::queue_list();
				}
				elseif ($params['show'] == "error_list")
				{
					$result = self::error_list();
				}
				else
				{
					throw new ApiException("Invalid parameter.", ApiResponse::STATUS_ERR_OPE);
				}
				break;
				
			default:
				throw new ApiException("Invalid parameter.", ApiResponse::STATUS_ERR_OPE);
				break;
		}
		
		$res = new ApiResponse();
		$res->setResult($action, null);
		if(count($result) > 0) {
			$res->setResult($action, $result);
		}
		
		return $res;
	}
	
	
	private function check_params($params)
	{
		if(count($params) < 1) {
			return false;
		}
		
		return true;
	}
	
	
	function queue_list()
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();
		
		$sqlStr = 'select'
		. '      sl.study_instance_uid  as "studyUID",'
		. '      sl.series_instance_uid as "seriesUID",'
		. '      jq.job_id              as "jobID",'
		. '      pm.plugin_name         as "pluginName",'
		. '      pm.version             as "pluginVersion",'
		. '      rp.policy_name         as "resultPolicy",'
		. '      jq.registered_at       as "registeredAt",'
		. '      pl.status              as "status",'
		. '      jq.priority            as "priority"'
		. ' from job_queue            jq,'
		. '      job_queue_series     qs,'
		. '      series_list          sl,'
		. '      plugin_master        pm,'
		. '      executed_plugin_list pl,'
		. '      plugin_result_policy rp'
		. ' where jq.job_id     = qs.job_id'
		. ' and   qs.series_sid = sl.sid'
		. ' and   jq.plugin_id  = pm.plugin_id'
		. ' and   jq.job_id     = pl.job_id'
		. ' and   pl.policy_id  = rp.policy_id';
		
		$result = DBConnector::query($sqlStr, array(), 'ALL_ASSOC');
		
		$pdo = null;
		
		return $result;
	}
	
	
	function error_list()
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();
		
		$sqlStr = 'select'
		. ' sl.study_instance_uid  as "studyUID",'
		. ' sl.series_instance_uid as "seriesUID",'
		. ' el.job_id              as "jobID",'
		. ' pm.plugin_name         as "pluginName",'
		. ' pm.version             as "pluginVersion",'
		. ' rp.policy_name         as "resultPolicy",'
		. ' el.executed_at         as "executedAt",'
		. ' \'error\'              as "status"'
		. ' from'
		. ' executed_plugin_list el,'
		. ' series_list          sl,'
		. ' plugin_master        pm,'
		. ' plugin_result_policy rp,'
		. ' executed_series_list esl'
		. ' where el.plugin_id = pm.plugin_id'
		. ' and   el.job_id = esl.job_id'
		. ' and   esl.series_sid = sl.sid'
		. ' and   el.policy_id = rp.policy_id'
		. ' and   el.status = -1';
		
		$result = DBConnector::query($sqlStr, array(), 'ALL_ASSOC');
		
		$pdo = null;
		
		return $result;
	}
	
	
	function query_job($jobIDArr)
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();
		
		$ret = array();
		foreach ($jobIDArr as $id)
		{
			$sqlStr = 'select'
			. ' sl.study_instance_uid  as "studyUID",'
			. ' sl.series_instance_uid as "seriesUID",'
			. ' el.job_id              as "jobID",'
			. ' pm.plugin_name         as "pluginName",'
			. ' pm.version             as "pluginVersion",'
			. ' rp.policy_name         as "resultPolicy",'
			. ' jq.registered_at       as "registeredAt",'
			. ' el.executed_at         as "executedAt",'
			. ' el.status              as "status",'
			. ' jq.priority            as "priority"'
			. ' from executed_plugin_list el'
			. ' left join'
			. ' job_queue jq'
			. ' on	el.job_id = jq.job_id'
			. ' left join'
			. '	executed_series_list es'
			. ' on	el.job_id     = es.job_id'
			. ' left join'
			. '	series_list sl'
			. ' on	es.series_sid = sl.sid'
			. ' left join'
			. '	plugin_master pm'
			. ' on	el.plugin_id  = pm.plugin_id'
			. ' left join'
			. '	plugin_result_policy rp'
			. ' on	el.policy_id  = rp.policy_id'
			. ' where el.job_id = ?';
			
			$result = DBConnector::query($sqlStr, array($id), 'ALL_ASSOC');
			
			// Set status
			if(isset($result[0]['status'])) {
				$result[0]['status'] = self::get_status($result[0]['status']);
			}
			
			// Set waiting
			$waiting = self::get_waiting($result[0][registeredAt], $result[0]['priority']);
			if ($waiting >= 0) {
				$result[0]['waiting'] = $waiting;
			}
			
			if($result) {
				array_push($ret, $result);
			}
		}
		
		$pdo = null;
		
		return $ret;
	}
	
	
	function query_job_study($studyArr)
	{
		throw new ApiException("Not implemented.", ApiResponse::STATUS_ERR_SYS);
	}
	
	
	function query_job_series($seriesArr)
	{
		throw new ApiException("Not implemented.", ApiResponse::STATUS_ERR_SYS);
	}
	
	
	private function get_status($stat)
	{
		switch ($stat)
		{
			case -1:
				return "error";
				break;
			case 1:
				return "in_queue";
				break;
			case 2:
				return "processing";
				break;
			case 3:
				return "processing";
				break;
			case 4:
				return "finished";
				break;
			default:
				break;
		}
		
		return $stat;
	}
	
	private function get_waiting($reg, $pri)
	{
		// Count waiting
		$sqlStr = 'select count(*) cnt'
		. ' from job_queue'
		. ' where priority > ?'
		. ' or (registered_at <= ? and priority = ?)';
		
		$waiting = DBConnector::query($sqlStr, array($pri, $reg, $pri),'ALL_ASSOC');
		
		return ($waiting[0]['cnt'] - 1);
	}
}

?>
