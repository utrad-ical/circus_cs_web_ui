<?php

/**
 * Defines public web API "getCadJob".
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class GetCadJobAction extends ApiActionBase
{
	protected static $public = true;

	protected static $rules = array(
		'jobID' => array('type' => 'int', 'required' => true),
		'withDisplays' => array('type' => 'boolean'),
		'withFeedback' => array('type' => 'boolean'),
		'withFiles' => array('type' => 'boolean'),
		'withSeries' => array('type' => 'boolean'),
		'withAttributes' => array('type' => 'boolean')
	);

	private $_cad_result = null;

	protected function execute($params)
	{
		$jobID  = $params['jobID'];

		// Retrieve the CAD Result
		$cr = new CadResult($jobID);
		$this->_cad_result = $cr;
		if (!isset($cr->job_id))
			throw new ApiOperationException('Target job not found.');

		// Authentication
		if (!$cr->checkCadResultAvailability($this->currentUser->Group))
			throw new ApiAuthException('Access denied by plugin result policy.');

		// Create basic data
		$result = array(
			'jobID' => $cr->job_id,
			'pluginName' => $cr->Plugin->plugin_name,
			'pluginVersion' => $cr->Plugin->version,
			'resultPolicy' => $cr->ResultPolicy->policy_name,
			'registeredAt' => $cr->registered_at,
			'executedAt' => $cr->executed_at,
			'status' => QueryJobAction::getJobStatus($cr->status)
		);

		if ($cr->status != Job::JOB_SUCCEEDED)
			return $result;

		if ($params['withDisplays'] || $params['withFeedback'])
		{
			set_include_path(get_include_path() . PATH_SEPARATOR . $cr->Plugin->configurationPath());
		}

		// Display
		if ($params['withDisplays'])
		{
			$result['displays'] = $cr->getDisplays();
		}

		// Feedback
		if ($params['withFeedback'])
		{
			$fbs = array();
			$feedback = $cr->queryFeedback('all');

			foreach ($feedback as $f) {
				$f->loadFeedback();
				$item = array(
					'enteredBy'    => $f->entered_by,
					'registeredAt' => $f->registered_at,
					'isConsensual' => $f->is_consensual,
					'feedback' => array(
						'blockFeedback' => $f->blockFeedback,
						'additionalFeedback' => $f->additionalFeedback
					)
				);
				array_push($fbs, $item);
			}
			$result['feedback'] = $fbs;
		}

		// Attributes
		if ($params['withAttributes'])
		{
			$attrs = array();
			foreach ($cr->PluginAttribute as $a)
			{
				$attrs[$a->key] = $a->value;
			}
			$result['attributes'] = $attrs;
		}

		// Files
		if ($params['withFiles'])
		{
			$result['files'] = $this->readJobDirContents();
		}

		// Series
		if ($params['withSeries'])
		{
			$result['series'] = array();
			foreach ($cr->ExecutedSeries as $es)
			{
				$s = $es->Series;
				$result['series'][$es->volume_id] = array(
					'modality' => $s->modality,
					'seriesNumber' => $s->series_number,
					'seriesDescription' => $s->series_description,
					'seriesDate' => $s->series_date . " " . $s->series_time,
					'minImageNumber' => $s->min_image_number,
					'maxImageNumber' => $s->max_image_number,
				);
			}
		}

		return $result;
	}

	protected function readJobDirContents()
	{
		$cr = $this->_cad_result;
		$path = $cr->pathOfCadResult();
		$wpath = $cr->webPathOfCadResult(true);
		$flags = FilesystemIterator::SKIP_DOTS |
			FilesystemIterator::UNIX_PATHS |
			FilesystemIterator::CURRENT_AS_FILEINFO;
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, $flags));

		$result = array();
		while ($it->valid())
		{
			if ($it->current()->isDir())
			{
				$it->next();
				continue;
			}
			$entry = $it->getSubPathname();
			$result[] = array(
					'file' => $entry,
					'url' => "$wpath/$entry",
					'size' => $it->getSize()
			);
			$it->next();
		}
		return $result;
	}
}