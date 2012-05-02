<?php

/**
 * VolumeArchiver class makes zipped archive file from DICOM file
 * for download.
 * There are several ways to create archive file.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class VolumeArchiver
{
	private $_dst = null;
	private $_filename = null;
	private $_contentname = null;
	private $_required_private_tags = null;

	/**
	 * Constructor.
	 * @param string $dst The destination directory where the zip file goes.
	 * This directory must be writable. Don't add the trailing backslash.
	 * @param string $filename The file name, without suffix ('.zip') of
	 * the zip archive.
	 * @param string $contentname The file name, without extension, of the
	 * contents of the zip archive ("*.raw", "*.txt") The default value is the
	 * same as $filename.
	 * @param string $required_private_tags The private tag to include in the
	 * DICOM tag dump file. Specify like '0001,0012;0015,0001'.
	 */
	public function VolumeArchiver($dst, $filename, $contentname = null,
		$required_private_tags = null)
	{
		$this->_dst = $dst;
		if ($contentname === null) $contentname = $filename;
		$this->_filename = $filename;
		$this->_contentname = $contentname;
		$this->_required_private_tags = $required_private_tags;
	}

	protected function doArchiveFromSeries($input_path,
		$start_img_num, $image_delta, $image_count)
	{
		global $cmdForProcess, $cmdDcmToVolume, $DIR_SEPARATOR;

		$cmd = $cmdDcmToVolume . " " . escapeshellarg($input_path) . " " .
			escapeshellarg($this->_dst) . " ". escapeshellarg($this->_contentname) . " " .
			intval($start_img_num) . " " . intval($image_delta) . " " .
			intval($image_count);
		if ($this->_required_private_tags)
			$cmd .= " " . escapeshellarg($this->_required_private_tags);

		$wrap = $cmdForProcess . " " . escapeshellarg($cmd);
		$result = shell_exec($wrap);

		if (!preg_match('/Succeeded/', $result))
			throw new Exception("A volume converter error occurred.");

		$f = $this->_contentname !== null ? $this->_contentname : $this->_filename;
		$files = array("$f.raw", "$f.txt", "$f.mha");
		$zipfile = $this->_dst . $DIR_SEPARATOR . $this->_filename . '.zip';

		try
		{
			// Create ZIP archive file
			$zip = new ZipArchive();
			if ($zip->open($zipfile, ZipArchive::OVERWRITE) !== true)
				throw new Exception('Failed to create zip archive.');

			foreach ($files as $item)
			{
				$target_file = $this->_dst . $DIR_SEPARATOR . $item;
				if ($zip->addFile($target_file, $item) !== true)
				{
					throw new Exception("$item could not be included for archive.");
				}
			}
			$zip->close();
		}
		catch (Exception $e)
		{
			foreach ($files as $item)
			{
				$item = $this->_dst . $DIR_SEPARATOR . $item;
				if (is_file($item)) unlink("$item");
			}
			return false;
		}
		foreach ($files as $item)
		{
			$item = $this->_dst . $DIR_SEPARATOR . $item;
			if (is_file($item)) unlink("$item");
		}
		return true;
	}

	/**
	 * Create volume archive from a specified series.
	 *
	 * @param string $series_uid The series instance UID.
	 * @param int $image_delta Image direction.
	 * @throws Exception
	 */
	public function archiveFromWholeSeries($series_uid, $image_delta)
	{
		global $DIR_SEPARATOR;

		$series = Series::selectOne(array('series_instance_uid' => $series_uid));
		if (!($series instanceof Series))
			throw new Exception('Specified series does not exist');
		$storage = new Storage($series->storage_id);
		$input_path = $storage->path . $DIR_SEPARATOR .
			$series->Study->Patient->patient_id . $DIR_SEPARATOR .
			$series->Study->study_instance_uid . $DIR_SEPARATOR . $series_uid;

		$cnt = $series->max_image_number - $series->min_image_number + 1;
		return $this->doArchiveFromSeries($input_path, $series->min_image_number, $image_delta, $cnt);
	}

	/**
	 * Create volume archive file from a single series.
	 * This function may take very long time according to the volume size.
	 * Supress timeout before calling this function, if necessary.
	 * @param string $series_uid The series instance UID.
	 * @param int $start_img_num The image number from which the volume is created.
	 * @param int $image_delta Volume creation direction.
	 * @param int $image_count The number of images to process from $start_img_num.
	 */
	public function archiveFromSeries(
		$series_uid, $start_img_num, $image_delta, $image_count)
	{
		global $DIR_SEPARATOR;

		$series = Series::selectOne(array('series_instance_uid' => $series_uid));
		if (!($series instanceof Series))
			throw new Exception('Specified series does not exist');
		$storage = new Storage($series->storage_id);
		$input_path = $storage->path . $DIR_SEPARATOR .
			$series->Study->Patient->patient_id . $DIR_SEPARATOR .
			$series->Study->study_instance_uid . $DIR_SEPARATOR . $series_uid;
		return $this->doArchiveFromSeries($input_path, $start_img_num, $image_delta, $image_count);
	}

	/**
	 * Recreate volume data from a specified job and volume ID.
	 * @param int $job_id The job ID
	 * @param int $vol_id The volume ID
	 */
	public function archiveFromJobID($job_id, $vol_id)
	{
		$job = new CadResult(intval($job_id));
		if (!isset($job->job_id))
		{
			throw new Exception('Specified job not found');
		}
		$es_list = $job->ExecutedSeries;
		$ss = null;
		foreach ($es_list as $s)
		{
			if ($s->volume_id == $vol_id)
			{
				$es = $s;
				$series = $es->Series;
				break;
			}
		}
		if (is_null($es) || is_null($es->z_org_img_num) ||
			is_null($es->image_delta) || is_null($es->image_count))
		{
			// If the current job is not yet executed, z_org_img_num, etc.,
			// may not be determined.
			throw new Exception('Specified volume not found or not processed');
		}

		if (!$this->_filename) $this->_filename = $series->series_instance_uid;
		return $this->archiveFromSeries($series->series_instance_uid,
			$es->z_org_img_num, $es->image_delta, $es->image_count
		);
	}
}