<?php

/**
 * Model class for server-level parameters.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class ServerParam extends Model
{
	protected static $_table = 'server_params';
	protected static $_primaryKey = 'key';

	public static function getVal($key)
	{
		$obj = static::selectOne(array('key' => $key));
		if ($obj)
		{
			return $obj->value;
		} else {
			return null;
		}
	}

	public static function setVal($key, $value)
	{
		$dum = new ServerParam();
		$dum->save(array(__CLASS__ => array(
			'key' => $key,
			'value' => $value
		)), true); // upsert
	}

	public static function getArray($key)
	{
		return unserialize(self::getVal);
	}

	public static function setArray($key, $val)
	{
		self::setVal($val, serialize($val));
	}
}