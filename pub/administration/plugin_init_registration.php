<?php
include_once("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(Auth::SERVER_SETTINGS);

function DropTableIfExists($pdo, $tableName)
{
	if($tableName != "")
	{
		$sqlStr = "SELECT count(*) FROM pg_class c"
				. " WHERE c.relkind = 'r' AND c.relname = ?";

		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindValue(1, $tableName);
		$stmt->execute();

		if($stmt->fetchColumn() == 1)
		{
			$sqlStr = 'SELECT COUNT(*) FROM "' . $tableName . '"';
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute();

			if($stmt->fetchColumn() == 0)
			{
				$sqlStr = 'DROP TABLE IF EXISTS "' . $tableName . '"';
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute();
			}
		}
	}
}

$message = "";

//----------------------------------------------------------------------------------------------------
// Import $_REQUEST variables
//----------------------------------------------------------------------------------------------------
$uploadFile = $_FILES["upfile"]["name"];
//----------------------------------------------------------------------------------------------------

$baseName = "";
$errorFlg = 0;

// Connect to SQL Server
$pdo = DBConnector::getConnection();

$pluginPath = $PLUGIN_DIR . $DIR_SEPARATOR;
$installDate = date("Y-m-d H:i:s");

//----------------------------------------------------------------------------------------------------
//  unzip the pakcage file
//----------------------------------------------------------------------------------------------------
$tmpPath = $pluginPath . uniqid();

if(move_uploaded_file($_FILES['upfile']['tmp_name'], $pluginPath.$uploadFile))
{
	$zipData = new ZipArchive;

	if ($zipData->open($pluginPath.$uploadFile) === TRUE)
	{
		$zipData->extractTo($tmpPath);
		$zipData->close();
	}
	else
	{
		$message = '<span style="color:red;">[ERROR] '
		. $uploadFile . ' is not ZIP file.</span>';
		$errorFlg = 1;
	}
	unlink($pluginPath.$uploadFile);
}
else
{
	$message = '<span style="color:red;">[ERROR] Failed to upload file ('
	. $uploadFile . ')</span>';
	$errorFlg = 1;
}

//----------------------------------------------------------------------------------------------------

//----------------------------------------------------------------------------------------------------
// Load plugin.json
//----------------------------------------------------------------------------------------------------
if(!$errorFlg)
{
	$message = '<span style="font-weight:bold;">Uploaded file name:</span>&nbsp;'
	. $_FILES['upfile']['name']
	. ' (File size:&nbsp;' . $_FILES['upfile']['size'] . ' bytes)<br/>';

	$jsonFileName = $tmpPath . $DIR_SEPARATOR . "plugin.json";

	if(!is_file($jsonFileName) || ($file = file_get_contents($jsonFileName)) == false)
	{
		$message .= '<span style="color:red;">Failed to load plugin.json</span><br/>';
		DeleteDirRecursively($tmpPath);
		$errorFlg = 1;
	}
	else
	{
		$data = json_decode($file, true);
		if(json_last_error() != JSON_ERROR_NONE)
		{
			$message .= '<span style="color:red;">Failed to decode JSON format</span><br/>';
			DeleteDirRecursively($tmpPath);
			$errorFlg = 1;
		}
		else
		{
			$message .= '<span style="font-weight:bold;">Plug-in name:</span> '
			. $data['pluginName'] . '</br>'
			. '<span style="font-weight:bold;">Version:</span> '
			. $data['version'] . '</br>';
			$baseName = $data['pluginName'] . '_v.' . $data['version'];
		}
	}
}
//----------------------------------------------------------------------------------------------------

//----------------------------------------------------------------------------------------------------
// Check the plug-in was already excuted
//----------------------------------------------------------------------------------------------------
if(!$errorFlg)
{
	$sqlStr = "SELECT COUNT(*) FROM executed_plugin_list el, plugin_master pm"
			. " WHERE pm.plugin_id=el.plugin_id"
			. " AND pm.plugin_name=?"
			. " AND pm.version=?"
			. " AND el.status>=0";
	try
	{
		if(DBConnector::query($sqlStr, array($data['pluginName'], $data['version']), 'SCALAR') != 0)
		{
			$message = '<span style="color:red;">[ERROR] '
			. $baseName . ' was already executed in this server.</span>';
			$errorFlg = 1;
			DeleteDirRecursively($tmpPath);
		}
	}
	catch (PDOException $e)
	{
		$message .= '<span style="color:red;">[ERROR] Failed to check plug-in execution ('
		. $baseName . ')<br />'
		.  '(' . $e->getMessage() . ')</span>';
		$errorFlg = 1;
	}
}
//----------------------------------------------------------------------------------------------------

//----------------------------------------------------------------------------------------------------
// Change directory name
//----------------------------------------------------------------------------------------------------
if(!$errorFlg)
{
	$pluginPath .= $baseName;

	if(file_exists($pluginPath))
	{
		DeleteDirRecursively($pluginPath);
	}

	if(!rename($tmpPath, $pluginPath))
	{
		$message = '<span style="color:red;">[ERROR] '
		. ' Failed to rename directory name ('
		. $tmpPath . ' => '
		. $pluginPath . ')</span>';
		$errorFlg = 1;
		DeleteDirRecursively($tmpPath);
	}
}
//----------------------------------------------------------------------------------------------------

//----------------------------------------------------------------------------------------------------
// Register database
//----------------------------------------------------------------------------------------------------
if(!$errorFlg)
{
	try
	{
		$message .= 'Database registration<br/>';

		// Begin transaction
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->beginTransaction();	// Begin transaction

		// Get new plugin_id
		$sqlStr = "SELECT plugin_id FROM plugin_master WHERE plugin_name=? AND version=?";
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute(array($data['pluginName'], $data['version']));

		if($stmt->rowCount() == 1)
		{
			$pluginID = $stmt->fetchColumn();
			$sqlStr = "DELETE FROM plugin_master WHERE plugin_id=?";
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($pluginID));
		}
		else
		{
			$sqlStr= "SELECT nextval('plugin_master_plugin_id_seq')";
			$pluginID =  DBConnector::query($sqlStr, NULL, 'SCALAR');
		}

		// Set plugin_master
		$sqlStr = 'INSERT INTO plugin_master(plugin_id, plugin_name, "version", "type",'
				. 'description, install_dt) VALUES (?, ?, ?, ?, ?, ?)';
		$sqlParams = array($pluginID, $data['pluginName'], $data['version'],
							$data['pluginType'], $data['description'],
							date("Y-m-d H:i:s", time()));
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($sqlParams);

		$resultTableName = (isset($data['resultTable']['tableName'])) ? $data['resultTable']['tableName'] : "";

		$sqlStr = "SELECT COUNT(*) FROM plugin_master WHERE type=? AND exec_enabled='t'";
		$maxLabelOrder = DBConnector::query($sqlStr, array($data['pluginType']), 'SCALAR') + 1;

		if($data['pluginType'] == 1) // CAD plug-in
		{
			// Set plugin_cad_master
			$sqlStr = "INSERT INTO plugin_cad_master(plugin_id, label_order,"
					. " input_type, result_type, time_limit, result_table)"
					. " VALUES (?, ?, ?, ?, ?, ?)";
			$sqlParams = array($pluginID, $maxLabelOrder,
								$data['cadDefinition']['inputType'],
								$data['cadDefinition']['resultType'],
								$data['cadDefinition']['timeLimit'],
								$resultTableName);
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);

			// Set plugin_cad_series
			if(isset($data['seriesDefinition']))
			{
				$sqlParams = array($pluginID);

				foreach($data['seriesDefinition'] as $item)
				{
					$sqlStr = "INSERT INTO plugin_cad_series(plugin_id, volume_id, volume_label, ruleset)"
							. " VALUES (?, ?, ?, ?)";
					$sqlParams = array($pluginID, $item['volumeID']);
					$sqlParams[2] = isset($item['label']) ? $item['label'] : "";
					$sqlParams[3] = is_array($item['ruleset']) ? json_encode($item['ruleset']) : "[]";
					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute($sqlParams);
				}
			}
		}
		else // Research plug-in
		{
			$sqlStr = "INSERT INTO plugin_research_master(plugin_id, label_order,"
					. " research_type, target_plugin_name, target_version_min, target_version_max,"
					. " time_limit, result_table) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
			$sqlParams = array($pluginID, $maxLabelOrder,
								$data['researchDefinition']['researchType'],
								$data['researchDefinition']['targetPluginName'],
								$data['researchDefinition']['targetVersionMin'],
								$data['researchDefinition']['targetVersionMax'],
								$data['researchDefinition']['timeLimit'],
								$resultTableName);
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);
		}

		//----------------------------------------------------------------------------------------------------
		// Create result table
		//----------------------------------------------------------------------------------------------------
		if(isset($data['resultTable']))
		{
			$message .= 'Create result table<br/>';

			DropTableIfExists($pdo, $resultTableName);

			$sqlStr = 'CREATE TABLE "' . $resultTableName . '"('
					. 'job_id INT NOT NULL,'
					. 'sub_id SMALLINT NOT NULL,';

			foreach($data['resultTable']['column'] as $item)
			{
				switch($item['type'])
				{
					case 'int':
					case 'integer':
						$sqlStr .= $item['name'] . ' INT NOT NULL,';
						break;

					case 'smallint':
						$sqlStr .= $item['name'] . ' SMALLINT NOT NULL,';
						break;

					case 'text':
						$colSize = (isset($item['size'])) ? $item['size'] : 0;
						if($colSize > 255 || $colSize <= 0)
						{
							$sqlStr .= $item['name'] . ' TEXT NOT NULL,';
						}
						else
						{
							$sqlStr .= $item['name'] . ' CHARACTER VARYING(' . $colSize . ') NOT NULL,';
						}
						break;

					case 'boolean':
						$sqlStr .= $item['name'] . ' BOOLEAN NOT NULL,';
						break;

					default: // case 'real'
						$sqlStr .= $item['name'] . ' REAL NOT NULL,';
						break;
				} // end switch
			}

			$sqlStr .= 'sid SERIAL NOT NULL,'
					.  ' CONSTRAINT "' . $resultTableName . '_pkey" PRIMARY KEY (sid),'
					.  ' CONSTRAINT "' . $resultTableName . '_ukey" UNIQUE (job_id, sub_id),'
					.  ' CONSTRAINT key_job_id FOREIGN KEY (job_id)'
					.  ' REFERENCES executed_plugin_list (job_id) MATCH SIMPLE'
					.  ' ON UPDATE RESTRICT ON DELETE CASCADE)';
			$stmt =$pdo->prepare($sqlStr);
			$stmt->execute();
		}
		//----------------------------------------------------------------------------------------------------

		//----------------------------------------------------------------------------------------------------
		// Create score table
		//--------------------------------------------------------------------------------------------------
		if(isset($data['scoreTable']))
		{
			$message .= 'Create score table<br/>';

			DropTableIfExists($pdo, $scoreTableName);

			$sqlStr = 'CREATE TABLE "' . $scoreTableName . '"('
					. 'fb_id  INT NULL,'
					. 'sub_id SMALLINT NOT NULL,';

			foreach($data['scoreTable']['column'] as $item)
			{
				switch($item['type'])
				{
					case 'int':
					case 'integer':
						$sqlStr .= $item['name'] . ' INT NOT NULL,';
						break;

					case 'smallint':
						$sqlStr .= $item['name'] . ' SMALLINT NOT NULL,';
						break;

					case 'text':
						$colSize = (isset($item['size'])) ? $item['size'] : 0;
						if($colSize > 255 || $colSize <= 0)
						{
							$sqlStr .= $item['name'] . ' TEXT NOT NULL,';
						}
						else
						{
							$sqlStr .= $item['name'] . ' CHARACTER VARYING(' . $colSize . ') NOT NULL,';
						}
						break;

					case 'boolean':
						$sqlStr .= $item['name'] . ' BOOLEAN NOT NULL,';
						break;

					default: // case 'real'
						$sqlStr .= $item['name'] . ' REAL NOT NULL,';
						break;
				} // end switch
			}

			$sqlStr .= 'sid SERIAL NOT NULL,'
					.  ' CONSTRAINT "' . $scoreTableName . '_pkey" PRIMARY KEY (sid),'
					.  ' CONSTRAINT "' . $scoreTableName . '_ukey" UNIQUE (fb_id, sub_id),'
					.  ' CONSTRAINT key_fb_id FOREIGN KEY (job_id)'
					.  ' REFERENCES feedback_list (fb_id) MATCH SIMPLE'
					.  ' ON UPDATE RESTRICT ON DELETE CASCADE)';
			$stmt =$pdo->prepare($sqlStr);
			$stmt->execute();
		}
		//----------------------------------------------------------------------------------------------------

		//----------------------------------------------------------------------------------------------------
		// Set process_machine_installed_plugins
		//----------------------------------------------------------------------------------------------------
		$message .= 'Set process_machine_installed_plugins<br/>';

		$sqlStr = "SELECT * FROM process_machine_list WHERE plugin_job_manager>=2";
		$processMachineList = DBConnector::query($sqlStr, NULL, 'ALL_ASSOC');

		foreach($processMachineList as $item)
		{
			if($item['architecture'] != 'x86')
			{
				$sqlStr = "INSERT INTO process_machine_installed_plugins"
						. "(pm_id, plugin_id, exec_enabled, priority)"
						. " VALUES (?, ?, 't', 1)";
				$sqlParams = array($item['pm_id'], $pluginID);
				$stmt =$pdo->prepare($sqlStr);
				$stmt->execute($sqlParams);
			}
		}
		//----------------------------------------------------------------------------------------------------

		//----------------------------------------------------------------------------------------------------
		// Copy webconfig/webpub files
		//----------------------------------------------------------------------------------------------------
		$webConfigDir = $pluginPath . $DIR_SEPARATOR . 'webconfig';
		$webPubDir    = $pluginPath . $DIR_SEPARATOR . 'webpub';

		if(is_dir($webConfigDir))
		{
			$message .= 'Copy presentation files<br/>';
			$dstDir = $WEB_UI_ROOT . $DIR_SEPARATOR . 'plugin' . $DIR_SEPARATOR . $baseName;
			CopyDirRecursively($webConfigDir, $dstDir);
		}

		if(is_dir($webPubDir))
		{
			$message .= 'Copy resource files<br/>';
			$dstDir = $WEB_UI_ROOT . $DIR_SEPARATOR . 'pub' . $DIR_SEPARATOR
					. 'plugin' . $DIR_SEPARATOR . $baseName;
			CopyDirRecursively($webPubDir, $dstDir);
		}
		//--------------------------------------------------------------------------------------------------

		// Commit transaction
		$pdo->commit();

		$message .= '<span style="color:blue;">Succeeded to register plug-in (' . $baseName . ')</span>';
	}
	catch (PDOException $e)
	{
		$pdo->rollBack();
		DeleteDirRecursively($pluginPath);
		$message .= '<span style="color:red;">[ERROR] Failed to register plug-in (' . $baseName . ')<br />'
				 .  '(' . $e->getMessage() . ')</span>';
	}
}

$pdo = null;
echo $message;




/**
 * Recursively copy a directory that is not empty.
 * @param string $srcDir The path of source directory.
 * @param string $dstDir The path of destination directory
 */
function CopyDirRecursively($srcDir, $dstDir)
{
	global $DIR_SEPARATOR;

	if(is_dir($srcDir))
	{
		if(!is_dir($dstDir))  mkdir($dstDir);

		$objects = scandir($srcDir);

		foreach( $objects as $file )
		{
			if( $file == "." || $file == ".." )  continue;

			if( is_dir($srcDir.$DIR_SEPARATOR.$file) )
			{
				CopyDirRecursively($srcDir.$DIR_SEPARATOR.$file, $dstDir.$DIR_SEPARATOR.$file);
			}
			else
			{
				copy($srcDir.$DIR_SEPARATOR.$file, $dstDir.$DIR_SEPARATOR.$file);
			}
		}
	}
	return TRUE;
}