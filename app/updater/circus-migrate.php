<?php

/**
 * Command line script to migrate CIRCUS CS Database Schema and other tasks.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */

require_once(__DIR__ . '/../../pub/common.php');
set_time_limit(0); // no time limit

$log = fopen(__DIR__ . '/migrator.log', 'w');

try {
	$migrator = new CircusMigrator();
	$migrator->run();
} catch (Exception $e) {
	print "Error : " . $e->getMessage() . "\n";
	fwrite($log, "\nStack Trace:\n" . $e->getTraceAsString());
}

fclose($log);

// END

/**
 * Migrator.
 */
class CircusMigrator
{
	protected $_oldRevision = null;
	protected $_classes = null;

	const SQL = 1;
	const PHP = 2;

	public function run()
	{
		ServerParam::setVal('hoge', 3);
		global $CIRCUS_REVISION;

		print "\n";
		$this->log("Welcome to CIRCUS CS DB Migrator.");
		$this->log("---------------------------------");

		// version check
		$this->log('Checking for the DB revisions...');
		try {
			$this->_oldRevision = intval(ServerParam::getVal('revision'));
		} catch (Exception $e) {
			throw new Exception("Failed to get the current DB revision. \n" .
						'Manually update CIRCUS CS to ver. 3.4 or later.');
		}
		$this->log('Current DB Revision: ' . $this->_oldRevision, 1);
		$this->log('Target Revision    : ' . $CIRCUS_REVISION, 1);

		if ($this->_oldRevision == $CIRCUS_REVISION)
		{
			$this->log('The database structure is up to date.');
			return;
		}

		$queue = $this->survey();
		$this->log("-> " . count($queue) . " file(s) to update.", 1);

		$db = DBConnector::getConnection();
		foreach ($queue as $rev => $migration)
		{
			$this->log("Updating to revision $rev...");
			if (isset($migration[self::SQL]))
			{
				$this->log("Executing SQL update for revision $rev", 1);
				$file = __DIR__ . "/revisions/" . $migration[self::SQL];
				$sql = file_get_contents($file);
				$db->exec($sql);
			}
			if (isset($migration[self::PHP]))
			{
				$this->log("Executing update script for revision $rev", 1);
				$file = __DIR__ . "/revisions/" . $migration[self::PHP];
				require($file);
				$class = "MigrationRev$rev";
				$obj = new $class();
				$obj->run();
			}
			ServerParam::setVal('revision', $rev);
			$this->log("Updated to revision $rev");
		}

		$this->log("CIRCUS CS DB Struture is now up to date.");
	}

	protected function survey()
	{
		global $CIRCUS_REVISION;
		// search for revision directory and enumerate migrations
		$this->log("Searching for the available migration files...");
		$diff_dir = __DIR__ . '/revisions';
		$dp = opendir(__DIR__ . '/revisions');
		$list = array();
		while ($item = readdir($dp))
		{
			if (!is_file(__DIR__ . "/revisions/$item")) continue;
			if (preg_match('/^rev(\d+)/', $item, $m))
			{
				$rev = intval($m[1]);
				if ($rev <= $this->_oldRevision || $rev > $CIRCUS_REVISION)
				{
					continue;
				}
				if (preg_match('/\.sql$/', $item))
				{
					$list[$rev][self::SQL] = $item;
				}
				elseif (preg_match('/\.php$/', $item))
				{
					$list[$rev][self::PHP] = $item;
				}
			}
		}
		closedir($dp);
		ksort($list, SORT_NUMERIC);
		return $list;
	}

	protected function log($message, $depth = 0)
	{
		global $log;
		$indent = str_repeat('  ', $depth);
		print "$indent$message\n";
		fwrite($log, "[" . date('Y-m-d H:i:s') . "] $indent$message\n");
	}
}

/**
* Migration base class.
*/
abstract class Migration
{
	public function run() {
		//
	}
}

