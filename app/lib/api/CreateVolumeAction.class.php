<?php

/**
 * CreateVolume action create zip archive of DICOM series for download.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class CreateVolumeAction extends ApiActionBase
{
	protected static $required_privileges = array(
		Auth::VOLUME_DOWNLOAD
	);

	protected static $rules = array(
		'mode' => array('type' => 'select', 'options' => array('job', 'series')),
		'seriesInstanceUID' => array('type' => 'string'),
		'jobID' => array('type' => 'int'),
		'volumeID' => array('type' => 'int'),
		'startImgNum' => array('type' => 'int', 'min' => 1),
		'endImgNum' => array('type' => 'int', 'min' => 1),
		'imageDelta' => array('type' => 'int'),
		'requiredPrivateTags' => array(
			'type' => 'string',
			'regex' => '/^(\d\d\d\d,\d\d\d\d;)*(\d\d\d\d,\d\d\d\d)?$/'
		)
	);

	protected function execute($params)
	{
		global $DIR_SEPARATOR;

		$series_uid = $params['seriesInstanceUID'];

		// craete random directory in cache directory
		$storage = Storage::getCurrentStorage(Storage::WEB_CACHE);
		$storage_id = $storage->storage_id;
		$path = $storage->path;
		$dir =  uniqid();
		$dst = $path . $DIR_SEPARATOR . $dir;

		if (!$path)
			throw new ApiSystemException('Fetal error: web cache directory is not configured.');
		$r = mkdir($dst);
		if (!$r)
			throw new ApiSystemException('Failed to create temporary directory in web cache area.');



		if ($params['mode'] == 'series')
		{
			$filename = series_uid;
			$archiver = new VolumeArchiver($dst, $filename, $series_uid, $params['requiredPrivateTags']);
			$cnt = $params['endImgNum'] - $params['startImgNum'] + 1;
			$ret = $archiver->archiveFromSeries($series_uid, $params['startImgNum'], $params['imageDelta'], $cnt);
		}
		else
		{
			$job_id = $params['jobID'];
			$volume_id = $params['volumeID'];
			$filename = "job$job_id";
			$archiver = new VolumeArchiver($dst, $filename, $volume_id, $params['requiredPrivateTags']);
			$ret = $archiver->archiveFromJobID($job_id, $volume_id);
		}
		if (!ret)
			throw new ApiSystemException('Unknown error occurred while creating volume archive file.');

		$url = "storage/$storage_id/$dir/$filename.zip";
		return array(
			'location' => $url
		);
	}
}