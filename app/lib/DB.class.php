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

	/**
	 * Utility function to do one-liner database access.
	 * This function prepares, executes and returns results in various styles.
	 * @param string $sqlStr SQL statements with binnding placeholders ('?').
	 * @param array $bindValues The list of binding parameters.
	 * @param string $outputType Format of the return type.
	 * @return Result of the query in the specified format.
	 */
	static function query($sqlStr, $bindValues, $outputType)
	{
		$pdo = self::getConnection();
		$stmt = $pdo->prepare($sqlStr);

		if(is_array($bindValues))
		{
			$stmt->execute($bindValues);
		}
		else
		{
			if(strlen($bindValues) > 0)  $stmt->bindValue(1, $bindValues);
			$stmt->execute();
		}

		if($stmt->errorCode()=='00000')
		{
			switch($outputType)
			{
				case 'SCALAR':
					return $stmt->fetchColumn();
					break;
				case 'ARRAY_ASSOC':
					return $stmt->fetch(PDO::FETCH_ASSOC);
					break;
				case 'ARRAY_NUM':
					return $stmt->fetch(PDO::FETCH_NUM);
					break;
				case 'ALL_ASSOC':
					return $stmt->fetchAll(PDO::FETCH_ASSOC);
					break;
				case 'ALL_NUM':
					return $stmt->fetchAll(PDO::FETCH_NUM);
					break;
				case 'ALL_COLUMN':
					return $stmt->fetchAll(PDO::FETCH_COLUMN);
					break;
				default:
					return null;
			}
		}
		else	return null;
	}

	public function __construct()
	{
		throw new Exception('Do not instantiate this object');
	}
}

?>