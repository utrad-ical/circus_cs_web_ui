<?php

/**
 * Command line script to update CIRCUS CS.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */

require_once(__DIR__ . '/../../pub/common.php');
set_time_limit(0); // no time limit

$log = fopen(__DIR__ . '/updater.log', 'w');

try {
	$updater = new CircusUpdater();
	$updater->run();
} catch (Exception $e) {
	print "Error : " . $e->getMessage() . "\n";
	fwrite($log, "\nStack Trace:\n" . $e->getTraceAsString());
}

fclose($log);

// END

/**
 * CIRCUS CS Updater main program class.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class CircusUpdater
{
	/**
	 * @var ZipArchive
	 */
	protected $_zip;

	protected $_oldRevesion = null;

	protected $_newVersion = null;
	protected $_newRevision = null;

	public function run()
	{
		global $CIRCUS_CS_VERSION; // current version before update
		global $BASE_DIR, $argc, $argv;

		$this->introduction();

		// Check if the archive exists.
		$archiveName = null;
		for ($i = 1; $i < $argc; $i++)
		{
			if (preg_match('/\.zip$/i', $argv[$i]))
			{
				if (is_file($argv[$i]))
				{
					$archiveName = $argv[$i];
					break;
				}
				else
				{
					throw new Exception('Specified ZIP archive does not exist.');
				}
			}
		}

		if (!is_null($archiveName))
		{
			$this->fileUpdate($archiveName);
		}


		// Update complete.
		// Migration is done in another process, so that it can be updated
		// just before the migration.
		$migrator = __DIR__ . '/circus-migrate.php';
		system("php $migrator");
	}

	protected function introduction()
	{
		global $CIRCUS_CS_VERSION;
		try {
			$this->_oldRevision = intval(ServerParam::getVal('revision'));
		} catch (Exception $e) {
			throw new Exception("Failed to get the current DB revision. \n" .
				'Manually update CIRCUS CS to ver. 3.4 or later.');
		}
		$this->log("Welcome to CIRCUS CS Updater.");
		$this->log("-----------------------------");
		$this->log("Current Version:     $CIRCUS_CS_VERSION");
		$this->log("Current DB Revision: " . $this->_oldRevision . "\n");
	}

	protected function fileUpdate($archiveName)
	{
		global $CIRCUS_CS_VERSION, $BASE_DIR;
		// Open ZIP file and check if the archive is corerct CIRCUS CS archive
		$this->log("Checking ZIP archive...");
		$this->_zip = new ZipArchive();
		$zip = $this->_zip;
		$err = $zip->open($archiveName);
		if ($err !== true)
		{
			throw new Exception('Failed to open ZIP archive.');
		}
		$num = $zip->numFiles;
		for ($i = 0; $i < $num; $i++)
		{
			$name = $zip->getNameIndex($i);
			if ($name == 'web_ui/version.json')
			{
				$v = json_decode($zip->getFromIndex($i), true);
				$this->_newVersion = $v['CIRCUS_CS_VERSION'];
				$this->_newRevision = $v['REVISION'];
				$this->log("CIRCUS CS update file found.", 1);
				$this->log("New version={$this->_newVersion}, New revision={$this->_newRevision}", 1);
				break;
			}
		}
		if (!$this->_newVersion || !$this->_newRevision)
		{
			throw new Exception('This ZIP file does not seem to be a valid CIRCUS CS archive.');
		}

		// Check if the archive is too old.
		if (version_compare($this->_newVersion, $CIRCUS_CS_VERSION) <= 0)
		{
			throw new Exception(
				"Archive version ({$this->_newVersion}) is no newer than " .
				"current CIRCUS CS ver. $CIRCUS_CS_VERSION."
			);
		}

		// Final check to execute update.
		$this->log('');
		$this->log("This will update CIRCUS CS ver. $CIRCUS_CS_VERSION to {$this->_newVersion}.");
		$this->log("+--- WARNING -------------------------------------------------+");
		$this->log("|  Before proceeding, make sure that your data are backed up! |");
		$this->log("+-------------------------------------------------------------+");
		print "Continue? ";
		if (!$this->inputYesNo()) exit();

		$this->log("Extracting ZIP archive...");
		for ($i = 0; $i < $num; $i++)
		{
			$name = $zip->getNameIndex($i);
			$this->log("Extracting file $name", 1);
			$this->_zip->extractTo($BASE_DIR, $name);
		}
		$this->Log("Extraction finished.");
	}

	protected function log($message, $depth = 0)
	{
		global $log;
		$indent = str_repeat('  ', $depth);
		print "$indent$message\n";
		fwrite($log, "[" . date('Y-m-d H:i:s') . "] $indent$message\n");
	}

	protected function inputYesNo()
	{
		print "[Y/n] ";
		$char = fgets(STDIN);
		if (preg_match('/\s*Y(es)?\s*/i', $char))
			return true;
		return false;
	}
}

