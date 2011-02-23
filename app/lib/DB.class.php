<?php

/**
 * Provides singleton PDO connection to the CIRCUS CS database.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */

class DB
{
	private static $_conn;

	/**
	 * Returns the singleton PDO connection object.
	 * When called at the first time, the method will begin a new connection
	 * and returns the handle.
	 * If called for more than one time, this just returns the existing
	 * connection.
	 * PDOException thrown by the PDO will not be caught here.
	 * @return PDO The connection handle.
	 */
	public static function getConnection()
	{
		if (DB::$_conn)
		{
			return DB::$_conn;
		} else {
			$dsn = $GLOBALS['connStrPDO'];
			$h = new PDO($dsn);
			if ($h)
			{
				DB::$_conn = $h;
				return $h;
			}
		}
	}

	public function __construct()
	{
		throw new Exception('Do not instantiate this object');
	}
}

?>