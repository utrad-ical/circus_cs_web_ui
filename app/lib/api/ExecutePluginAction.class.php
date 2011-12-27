<?php

class ExecutePluginAction extends ApiAction
{
	protected $rule;

	protected static $required_privileges = array(
		Auth::API_EXEC,
		Auth::CAD_EXEC
	);


	function requiredPrivileges()
	{
		return self::$required_privileges;
	}


	function execute($api_request)
	{
		$action = $api_request['action'];
		$params = $api_request['params'];

		try {
			$pdo = DBConnector::getConnection();
			$pdo->beginTransaction();
			$t = true;
			$dum = new Plugin();
			$plugin = $dum->find(array(
				'plugin_name' => $params['pluginName'],
				'version' => $params['pluginVersion']
			));
			if (count($plugin) != 1)
				throw new Exception('Plugin not found');
			$plugin = $plugin[0];

			$job_id = Job::registerNewJob(
				$plugin,
				$params['seriesUID'],
				ApiExec::currentUser()->user_id,
				$params['priority'],
				$params['resultPolicy']
			);
			$pdo->commit();
		} catch (Exception $e) {
			if ($t) $pdo->rollBack();
			throw new ApiException($e->getMessage(), ApiResponse::STATUS_ERR_OPE);
		}

		$res = new ApiResponse();
		$result = QueryJobAction::query_job(array($job_id));
		$res->setResult($action, $result[0]);
		return $res;
	}
}

?>
