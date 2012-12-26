<?php

class InspectJobDirectoryAction extends ApiActionBase
{
	/**
	 * @var CadResult
	 */
	private $_cad_result = null;
	private $_options = null;

	protected static $rules = array(
		'jobID' => array('type' => 'int', 'required' => true)
	);

	protected function execute($params)
	{
		// check that current user has access to job directory
		$job_id = $params['jobID'];
		$this->_cad_result = new CadResult($job_id);

		$extensions = $this->_cad_result->Plugin->presentation()->extensions();
		foreach ($extensions as $item)
		{
			if ($item instanceof CadDownloaderExtension)
			{
				$this->_options = $item->getParameter();
				break;
			}
		}
		if (!is_array($this->_options) || !$this->_options['enableUpload'])
			new ApiOperationException('This pluguin does not enable file uploads.');

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
		$pattern = $this->_options['filesMatch'];
		while ($it->valid())
		{
			if ($it->current()->isDir())
			{
				$it->next();
				continue;
			}
			$entry = $it->getSubPathname();
			if (is_string($pattern) && !preg_match($pattern, $entry))
			{
				$it->next();
				continue;
			}

			$link = $entry;
			if (is_array($this->_options['substitutes']))
			{
				foreach ($this->_options['substitutes'] as $sub)
				{
					$link = preg_replace($sub[0], $sub[1], $link);
				}
			}
			$result[] = array(
				'file' => $entry,
				'url' => "$wpath/$entry",
				'download' => "$wpath/$entry?dl=1",
				'size' => $it->getSize(),
				'link' => $link,
			);
			$it->next();
		}
		return $result;
	}
}
