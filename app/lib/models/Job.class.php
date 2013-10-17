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
	protected static $_consts;

	/**
	 * Indicates the job has failed while processing.
	 */
	const JOB_FAILED        = -1;

	/**
	 * Indicates the job is invalidated.
	 */
	const JOB_INVALIDATED   = -2;

	/**
	 * Indicates the job is aborted.
	 */
	const JOB_ABORTED       = -3;	
	
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
	 * Builds the list of executable series for each volume ID,
	 * which matches the ruleset of the specified CAD plugin.
	 * @param Plugin $plugin
	 * @param str $primary_series_uid
	 * @return array An array of executable series, indexed by volume ID.
	 * Each value holds an array of Series object which
	 * matched the corresponding ruleset for that volume ID.
	 */
	public static function findExecutableSeries(Plugin $plugin,
		$primary_series_uid)
	{
		if (!($plugin instanceof Plugin))
			throw new Exception('Plugin is not correctly specified.');
		if ($plugin->type != 1)
			throw new Exception($plugin->fullName() . ' is not CAD plug-in.');
		if (!$plugin->exec_enabled)
			throw new Exception($plugin->fullName() . ' is not allowed to execute.');
		$cad_master = $plugin->CadPlugin[0];
		if (!($cad_master instanceof CadPlugin))
			throw new Exception('Critical CAD master error. (Broken installation)');
		$input_type = $cad_master->input_type;
		if ($input_type < 0 || 2 < $input_type)
			throw new Exception('Input type is incorrect (' . $plugin->fullName() . ')');

		$primarySeries = Series::selectOne(array('series_instance_uid' => $primary_series_uid));
		if (!($primarySeries instanceof Series))
			throw new Exception('Target primary series does not exist.');

		$vols = $plugin->PluginCadSeries;
		$rules = array();
		foreach ($vols as $v) $rules[$v->volume_id] = json_decode($v->ruleset, true);

		$result = array();

		switch ($input_type)
		{
			case 0: // primary series only
				$where = array('series_instance_uid', $primarySeries->series_instance_uid);
				break;
			case 1: // find secondary and subsequent volumes from the same study
				$where = array('study_instance_uid', $primarySeries->Study->study_instance_uid);
				break;
			case 2: // find secondary and subsequent volumes from the same patient
				$where = array('patient_id', $primarySeries->Study->Patient->patient_id);
				break;
		}
		$candidates = DBConnector::query(
			"SELECT * FROM series_join_list WHERE {$where[0]}=? " .
			"ORDER BY study_date DESC, series_number ASC",
			$where[1],
			'ALL_ASSOC'
		);

		$fp = new SeriesFilter();
		foreach ($vols as $v)
		{
			$vid = $v->volume_id;
			$targets = array();
			foreach ($candidates as $s)
			{
				$uid = $s['series_instance_uid'];
				if ($vid == 0 && $uid != $primarySeries->series_instance_uid)
					continue;
				if ($vid > 0 && $uid == $primarySeries->series_instance_uid)
					continue;
				if ($fp->processRuleSets($s, $rules[$vid]))
					$targets[] = new Series($s['series_sid']);
			}
			$result[$vid] = $targets;
		}
		ksort($result, SORT_NUMERIC);

		return $result;
	}

	/*
	 * Returns job status string from status code.
	 * @param int $code Status code.
	 * @return string Status string such as 'SUCCEEDED', 'FAILED'.
	 */
	public static function codeToStatusName($code)
	{
		if (!self::$_consts)
		{
			$ref = new ReflectionClass(__CLASS__);
			self::$_consts = array_flip($ref->getConstants());
		}
		return str_replace('JOB_', '', self::$_consts[$code]);
	}

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

			if ($matched['continuous'])
			{
				if ($s->image_number != $s->max_image_number - $s->min_image_number + 1)
				{
					$errors[] = "Target series has discontinuous image number.";
					continue;
				}
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
			. " (job_id, plugin_id, priority, status, exec_user,"
			. " registered_at, updated_at, environment)"
			. " VALUES (?, ?, ?, 1, ?, ?, ?, ?)";
		$sqlParams = array(
			$job_id, $plugin->plugin_id, $priority, $user_id, $now, $now, $environment
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
		// which is not marked as 'failed' nor 'invalidated'.
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
	
	public static function abortJob($job_id)
	{
		// Update "job_queue"
		$sqlStr = "UPDATE job_queue"
		. " SET status=?"
		. " WHERE job_id=?";
		$sqlParams = array(self::JOB_ABORTED, $job_id);
		DBConnector::query($sqlStr, $sqlParams);
	
		// Update "executed_plugin_list"
		$sqlStr = "UPDATE executed_plugin_list"
		. " SET status=?"
		. " WHERE job_id=?";
		DBConnector::query($sqlStr, $sqlParams);
	
		return true;
	}
}
