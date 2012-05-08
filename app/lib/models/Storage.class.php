<?php
/**
 * Model class for data storage area.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class Storage extends Model
{
	/**
	 * DICOM Storage area.
	 * @var int
	 */
	const DICOM_STORAGE = 1;

	/**
	 * Plugin result area.
	 * @var int
	 */
	const PLUGIN_RESULT = 2;

	/**
	 * Web cache area.
	 * @var int
	 */
	const WEB_CACHE = 3;

	protected static $_table = 'storage_master';
	protected static $_primaryKey = 'storage_id';

	/**
	 * Converts the storage type (integer) to string.
	 */
	public function storageTypeString()
	{
		switch ($this->type)
		{
			case self::DICOM_STORAGE:
				return 'DICOM Storage';
			case self::PLUGIN_RESULT:
				return 'Plugin Result';
			case self::WEB_CACHE:
				return 'Web Cache';
		}
		return 'Unknown';
	}

	/**
	 * Creates a new Storage instance of the specified type which is currently
	 * active.
	 * @param int $type Storage type.
	 */
	public static function getCurrentStorage($type)
	{
		return Storage::selectOne(array('current_use' => 't', 'type' => $type));
	}
}
