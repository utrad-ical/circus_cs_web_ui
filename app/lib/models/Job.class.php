<?php

/**
 * Job model class represents CAD plug-in jobs.
 * You can create a new job using this class.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class Job extends Model
{
	protected static $_table = 'job_queue';
	protected static $_primaryKey = 'job_id';
	protected static $_belongsTo = array(
		'Plugin' => array('key' => 'plugin_id')
	);
	protected static $_hasAndBelongsToMany = array(
		'Series' => array(
			'joinTable' => 'job_queue_series',
			'foreignKey' => 'job_id',
			'associationForeignKey' => 'series_sid',
			'foreignPrimaryKey' => 'sid'
		)
	);

	/**
	 * Indicates the job has failed while processing.
	 */
	const JOB_FAILED        = -1;

	/**
	 * Indicates the job is in the queue, but not allocated to any process
	 * machine.
	 */
	const JOB_NOT_ALLOCATED =  1;

	/**
	 * Indicates the job is in the queue, allocated to a process machine,
	 * and waiting for the process machine to begin the job.
	 */
	const JOB_ALLOCATED     =  2;

	/**
	 * Indicates the job is being processed.
	 */
	const JOB_PROCESSING    =  3;

	/**
	 * Indicated the job has finished processing and the results are ready.
	 */
	const JOB_SUCCEEDED     =  4;

	/**
	 * Utility method to register a new job.
	 *
	 * You are recommended to use this method to insert new job,
	 * rather than directly using the Model::save() method.
	 * This method does not handle DB transatcion; you should manually
	 * handle transaction before/after calling this method. This is because
	 * you may want to call this function multiple times as bulk registration.
	 * @return int The job ID for the registered job.
	 * @throws InvalidArgumentException
	 */
	public static function registerNewJob(Plugin $plugin, array $series,
		$user_id, $priority = 1, $resultPolicy = PluginResultPolicy::DEFAULT_POLICY)
	{
		if (!($plugin instanceof Plugin))
			throw new InvalidArgumentException('Plugin not defined');
		if (!$plugin->exec_enabled || $plugin->type != Plugin::CAD_PLUGIN)
			throw new InvalidArgumentException('Plugin is not executable.');
		if (!is_array($series) || count($series) < 1)
			throw new InvalidArgumentException('Target series not defined.');
		if (!$resultPolicy)
			throw new InvalidArgumentException('Invalid result policy.');
		$policy = PluginResultPolicy::selectOne(array('policy_name' => $resultPolicy));
		if (!$policy)
			throw new InvalidArgumentException('Invalid result policy.');
		$policy_id = $policy->policy_id;
		$priority = is_numeric($priority) ? (int)$priority : 1;

		$volumes = $plugin->PluginCadSeries;

		// Confirm all series match the defined series filters.
		$filter = new SeriesFilter();

		$series_queue = array();
		foreach ($volumes as $vol)
		{
			$vid = $vol->volume_id;
			if (!isset($series[$vid]))
			{
				$errors[] = "Target series for volume ID $vid is not specified.";
				continue;
			}
			$s = SeriesJoin::selectOne(array('series_instance_uid' => $series[$vid]));
			if (!$s)
			{
				$errors[] = "Target series for volume ID $vid is not found in series list.";
				continue;
			}
			$ruleSets = json_decode($vol->ruleset, true);
			$data = $s->getData();
			$matched = $filter->processRuleSets($data, $ruleSets);
			if ($matched === false)
			{
				$errors[] = "Target series for volume ID $vid can not be processed (filter unmatch).";
				continue;
			}
			$series_queue[$vid] = array(
				'series_uid' => $series[$vid],
				'series_sid' => $s->series_sid,
				'matched_rule' => $matched
			);
		}
		if (count($volumes) != count($series)) // unknown series specified
			$errors[] = 'Wrong number of series (' .
				count($volumes) . ' required, ' . count($series). ' given).';
		if ($errors)
			throw new InvalidArgumentException(implode(' ', $errors));

		// Detect job duplication.
		$dupe_job = Job::detectDuplicatedJob($plugin, $series);
		if ($dupe_job !== false)
			throw new InvalidArgumentException(
				"This job is already registered (Job ID: $dupe_job).");

		//////////
		// All check passed. Actually insert a new job.
		//////////

		// All exceptions passed to the caller
		return Job::doRegisterJob($plugin, $series_queue, $user_id,
			$priority, $policy_id);
	}

	protected static function doRegisterJob(Plugin $plugin,
		array $series_queue, $user_id, $priority, $policy_id)
	{
		// Get new job ID
		$sqlStr= "SELECT nextval('executed_plugin_list_job_id_seq')";
		$job_id =  DBConnector::query($sqlStr, null, 'SCALAR');

		// Get current storage ID for CAD result storage
		$store = Storage::selectOne(array('type' => 2, 'current_use' => 't'));
		if (!$store)
			throw new Exception('Storage ID invalid');
		$storage_id = $store->storage_id;

		$now = date("Y-m-d H:i:s");

		// Determine environment
		$env_items = array();
		foreach ($series_queue as $item)
		{
			$e = $item['matched_rule']['environment'];
			if (strlen($e) > 0) $env_items[$e] = true;
		}
		$environment = $env_items ? implode(',', array_keys($env_items)) : null;

		// Register into "execxuted_plugin_list"
		$sqlStr = "INSERT INTO executed_plugin_list"
			. " (job_id, plugin_id, storage_id, policy_id, status, exec_user,"
			. " registered_at, executed_at, started_at, environment)"
			. " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$sqlParams = array(
			$job_id, $plugin->plugin_id, $storage_id, $policy_id,
			Job::JOB_NOT_ALLOCATED, $user_id, $now, $now, $now, $environment
		);
		DBConnector::query($sqlStr, $sqlParams, 'SCALAR');

		// Register into "job_queue"
		$sqlStr = "INSERT INTO job_queue"
			. " (job_id, plugin_id, priority, status, exec_user, registered_at, updated_at)"
			. " VALUES (?, ?, ?, 1, ?, ?, ?)";
		$sqlParams = array(
			$job_id, $plugin->plugin_id, $priority, $user_id, $now, $now
		);
		DBConnector::query($sqlStr, $sqlParams, 'SCALAR');

		// Register into "executed_series_list" and "job_queue_series"
		$pdo = DBConnector::getConnection();
		$stmt1 = $pdo->prepare(
			"INSERT INTO executed_series_list(job_id, volume_id, series_sid) " .
			"VALUES (?, ?, ?)"
		);
		$stmt2 = $pdo->prepare(
			"INSERT INTO job_queue_series" .
			"(job_id, volume_id, series_sid, start_img_num, end_img_num, required_private_tags, image_delta)" .
			" VALUES (?, ?, ?, ?, ?, ?, ?)"
		);
		foreach ($series_queue as $vid => $item)
		{
			$stmt1->execute(array($job_id, $vid, $item['series_sid']));
			$m = $item['matched_rule'];
			$stmt2->execute(array($job_id, $vid, $item['series_sid'],
				$m['start_img_num'], $m['end_img_num'],
				$m['required_private_tags'], $m['image_delta'] ?: 0));
		}
		return $job_id;
	}

	/**
	 * Detects job duplication.
	 * A duplicated job is a job with the same plugin and excactly the same
	 * combination of target series. Such job (theoretically) gives the
	 * same result, so such job registration is denied.
	 * @param Plugin $plugin
	 * @param array $series
	 * @return mixed If exactly the same job is found, return the job ID of
	 * the existing job. Otherwise return false.
	 */
	protected static function detectDuplicatedJob(Plugin $plugin, array $series)
	{
		$ps = $series[0]; // series UID for the primary series
		$s = Series::selectOne(array('series_instance_uid' => $ps));

		foreach($series as $vid => $series_uid)
		{
			$eqn[] = 'es.volume_id = ? AND sl.series_instance_uid = ?';
			$binds[] = $vid;
			$binds[] = $series_uid;
		}
		$or_clause = implode(' OR ', $eqn);

		// Find exactly the same job (same plugin, same combination of series),
		// which is not marked as 'failed.'
		$sql = <<<EOT
SELECT el.job_id AS job_id
FROM executed_plugin_list AS el
  JOIN executed_series_list AS es
    ON el.job_id = es.job_id
  JOIN series_list AS sl
    ON es.series_sid = sl.sid
WHERE
  el.plugin_id = ? AND el.status > 0
  AND ($or_clause)
GROUP BY el.job_id
HAVING COUNT(*) = ?
EOT;
		array_unshift($binds, $plugin->plugin_id);
		array_push($binds, count($series));
		$result = DBConnector::query($sql, $binds, 'SCALAR');
		if ($result)
			return $result;
		else
			return false;

	}
}
