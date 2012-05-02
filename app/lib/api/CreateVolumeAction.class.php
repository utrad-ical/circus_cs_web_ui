<?php

class CreateVolumeAction extends ApiActionBase
{
	protected static $required_privileges = array(
		Auth::VOLUME_DOWNLOAD
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

		$archiver = new VolumeArchiver($dst, $series_uid, $series_uid, $params['requiredPrivateTags']);
		$cnt = $params['endImgNum'] - $params['startImgNum'] + 1;

		$archiver->archiveFromSeries($series_uid, $params['startImgNum'], $params['imageDelta'], $cnt);

		$url = "storage/$storage_id/$dir/$series_uid.zip";
		return array(
			'location' => $url
		);
	}
}