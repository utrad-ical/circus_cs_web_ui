<?php

class InspectJobDirectoryAction extends ApiActionBase
{
	/**
	 * @var CadResult
	 */
	private $_cad_result = null;
	private $_params;

	protected static $rules = array(
		'jobID' => array('type' => 'int', 'required' => true),
		'filesMatch' => array('type' => 'string'),
		'substitutes' => array('type' => 'array')
	);

	protected function execute($params)
	{
		$this->_params = $params;

		// check that current user has access to job directory
		$job_id = $params['jobID'];
		$this->_cad_result = new CadResult($job_id);
		$user = $this->currentUser;
		if (!$this->_cad_result->checkCadResultAvailability($user->Group))
			new ApiOperationException('You do not have privilege to see this CAD result.');

		// recursively read the current directory.
		$result = $this->readJobDirContents();
		return $result;
	}

	protected function readJobDirContents()
	{
		$path = $this->_cad_result->pathOfCadResult();
		$wpath = $this->_cad_result->webPathOfCadResult();
		$flags = FilesystemIterator::SKIP_DOTS |
			FilesystemIterator::UNIX_PATHS |
			FilesystemIterator::CURRENT_AS_FILEINFO;
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, $flags));
		$result = array();
		$pattern = $this->_params['filesMatch'];
		while ($it->valid())
		{
			$entry = $it->getSubPathname();
			if (is_string($pattern) && !preg_match($pattern, $entry))
			{
				$it->next();
				continue;
			}

			$link = $entry;
			if (is_array($this->_params['substitutes']))
			{
				foreach ($this->_params['substitutes'] as $sub)
				{
					$link = preg_replace($sub[0], $sub[1], $link);
				}
			}
			$result[] = array(
				'file' => $entry,
				'url' => "$wpath/$entry",
				'size' => $it->getSize(),
				'link' => $link,
			);
			$it->next();
		}
		return $result;
	}
}
