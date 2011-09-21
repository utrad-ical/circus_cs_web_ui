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

$baseName = substr($uploadFile, 0, strlen($uploadFile)-4);
$errorFlg = 0;

// Connect to SQL Server
$pdo = DBConnector::getConnection();

$pluginPath = $PLUGIN_DIR . $DIR_SEPARATOR;
$installDate = date("Y-m-d H:i:s");

//----------------------------------------------------------------------------------------------------
//  unzip the pakcage file
//----------------------------------------------------------------------------------------------------
if(move_uploaded_file($_FILES['upfile']['tmp_name'], $pluginPath.$uploadFile))
{
	$pos = strpos($baseName, "_v.");
	$pluginName = substr($baseName, 0, $pos);
	$version = substr($baseName, $pos+3, strlen($baseName)-$pos-3);

	$sqlStr = "SELECT COUNT(*) FROM executed_plugin_list el, plugin_master pm"
			. " WHERE pm.plugin_id=el.plugin_id"
			. " AND pm.plugin_name=?"
			. " AND pm.version=?"
			. " AND el.status>=0";

	if(DBConnector::query($sqlStr, array($pluginName, $version), 'SCALAR') == 0)
	{
		$zipData = new ZipArchive;
		if ($zipData->open($pluginPath.$uploadFile) === TRUE)
		{
			$zipData->extractTo($PLUGIN_DIR);
  				$zipData->close();
		}
		else
		{
			$message = '<span style="color:red;">[ERROR] '
					 . $uploadFile . ' is not ZIP file.</span>';
			$errorFlg = 1;
		}
	}
	else
	{
		$message = '<span style="color:red;">[ERROR] '
				 . $baseName . ' was already executed in this server.</span>';
		$errorFlg = 1;
	}
	unlink($pluginPath.$uploadFile);
	
	$pluginPath .= $baseName;
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

	$jsonFname = $pluginPath . $DIR_SEPARATOR . "plugin.json";

	if(!is_file($jsonFname) || ($file = file_get_contents($jsonFname)) == false)
	{
		$message .= '<span style="color:red;">Fail to load plugin.json ' . $jsonFname .'</span><br/>';
		DeleteDirRecursively($pluginPath);
		$errorFlg = 1;
	}
	else
	{
		$data = json_decode($file, true);
		if(json_last_error() != JSON_ERROR_NONE)
		{
			$message .= '<span style="color:red;">Fail to load plugin.json ' . $jsonFname .'</span><br/>';
			DeleteDirRecursively($pluginPath);
			$errorFlg = 1;
		}
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
		$stmt->execute(array($pluginName, $version));
		
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
		$sqlParams = array($pluginID, $pluginName, $version,
							$data['PluginType'], $data['Description'],
							date("Y-m-d H:i:s", time()));
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($sqlParams);
		
		$resultTableName = (isset($data['ResultTable']['TableName'])) ? $data['ResultTable']['TableName'] : "";
		$scoreTableName  = (isset($data['ScoreTable']['TableName'])) ? $data['ScoreTable']['TableName'] : "";

		$sqlStr = "SELECT COUNT(*) FROM plugin_master WHERE type=? AND exec_enabled='t'";
		$maxLabelOrder = DBConnector::query($sqlStr, array($data['PluginType']), 'SCALAR') + 1;

		if($data['PluginType'] == 1) // CAD plug-in
		{
			// Set plugin_cad_master
			$sqlStr = "INSERT INTO plugin_cad_master(plugin_id, label_order,"
					. " input_type, result_type, time_limit, result_table, score_table)"
					. " VALUES (?, ?, ?, ?, ?, ?, ?)";
			$sqlParams = array($pluginID, $maxLabelOrder,
								$data['CADDefinition']['InputType'],
								$data['CADDefinition']['ResultType'],
								$data['CADDefinition']['TimeLimit'],
								$resultTableName,
								$scoreTableName);
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);
			
			// Set plugin_cad_series
			if(isset($data['SeriesDefinition']))
			{
				$sqlParams = array($pluginID);

				foreach($data['SeriesDefinition'] as $item)
				{
					$sqlStr = "INSERT INTO plugin_cad_series(plugin_id, volume_id, ruleset, modality)"
							. " VALUES (?, ?, ?, ?)";
					$sqlParams = array($pluginID, $DEFAULT_CAD_PREF_USER);
					$sqlParams[1] = $item['volumeID'];
					$sqlParams[2] = json_encode($item['ruleset']);
					$sqlParams[3] = $item['modality'];
					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute($sqlParams);
				}
			}

			// Set plugin_user_preference
			if(isset($data['DefalutUserPreference']))
			{
				$sqlParams = array($pluginID, $DEFAULT_CAD_PREF_USER);

				foreach($data['DefalutUserPreference'] as $item)
				{
					$sqlStr = 'INSERT INTO plugin_user_preference(plugin_id, user_id, "key", "value")'
							. 'VALUES (?, ?, ?, ?)';
					$sqlParams[2] = $item['key'];
					$sqlParams[3] = $item['value'];
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
								$data['ResearchDefinition']['ResearchType'],
								$data['ResearchDefinition']['TargetPluginName'],
								$data['ResearchDefinition']['TargetVersionMin'],
								$data['ResearchDefinition']['TargetVersionMax'],
								$data['ResearchDefinition']['TimeLimit'],
								$resultTableName);
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);
		}
		
		//----------------------------------------------------------------------------------------------------
		// Create result table
		//----------------------------------------------------------------------------------------------------
		if(isset($data['ResultTable']))
		{
			$message .= 'Create result table<br/>';
			
			DropTableIfExists($pdo, $resultTableName);

			$sqlStr = 'CREATE TABLE "' . $resultTableName . '"('
					. 'job_id INT NOT NULL,'
					. 'sub_id SMALLINT NOT NULL,';

			foreach($data['ResultTable']['column'] as $item)
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
		if(isset($data['ScoreTable']))
		{
			$message .= 'Create score table<br/>';

			DropTableIfExists($pdo, $scoreTableName);

			$sqlStr = 'CREATE TABLE "' . $scoreTableName . '"('
					. 'fb_id  INT NULL,'
					. 'sub_id SMALLINT NOT NULL,';

			foreach($data['ScoreTable']['column'] as $item)
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
		// Set process_machine_installed_plugins and copy files for execution
		//----------------------------------------------------------------------------------------------------
		$message .= 'Set process_machine_installed_plugins and copy files for execution<br/>';

		$binDir = $pluginPath . $DIR_SEPARATOR . 'bin' . $DIR_SEPARATOR;

		$sqlStr = "SELECT * FROM process_machine_list WHERE plugin_job_manager>=2";
		$processMachineList = DBConnector::query($sqlStr, NULL, 'ALL_ASSOC'); 

		foreach($processMachineList as $item)
		{
			if(!($item['architecture'] == 'x86' && $data['Architecture'] == 'x64'))
			{
				$sqlStr = "INSERT INTO process_machine_installed_plugins"
						. "(pm_id, plugin_id, exec_enabled, priority)"
						. " VALUES (?, ?, 't', 1)";
				$sqlParams = array($item['pm_id'], $pluginID);
				$stmt =$pdo->prepare($sqlStr);
				$stmt->execute($sqlParams);
				
				$dstPath = $pluginPath;
				
				if(!($item['host_name'] == '127.0.0.1' || $item['host_name'] == 'localhost'))
				{
					$dstPath = '\\\\' . $item['host_name'] . 'CIRCUS-CS' . $DIR_SEPARATOR
							. 'plugins' . $DIR_SEPARATOR;
				}

				if($item['architecture'] == 'x64' && is_dir($binDir.'x64'))
				{
					CopyDirRecursively($binDir.'x64', $dstPath);
				}
				else
				{
					CopyDirRecursively($binDir.'x86', $dstPath);
				}
			}
		}
		//----------------------------------------------------------------------------------------------------

		//----------------------------------------------------------------------------------------------------
		// Copy presentation/resource files
		//----------------------------------------------------------------------------------------------------
		$presentationDir = $pluginPath . $DIR_SEPARATOR . 'presentation';
		$resourceDir     = $pluginPath . $DIR_SEPARATOR . 'resource';
		
		if(is_dir($presentationDir))
		{
			$message .= 'Copy presentation files<br/>';
			$dstDir = $WEB_UI_ROOT . $DIR_SEPARATOR . 'plugin' . $DIR_SEPARATOR . $baseName;
			CopyDirRecursively($presentationDir, $dstDir);
		}
		
		if(is_dir($resourceDir))
		{
			$message .= 'Copy resource files<br/>';
			$dstDir = $WEB_UI_ROOT . $DIR_SEPARATOR . 'pub' . $DIR_SEPARATOR
					. 'plugin' . $DIR_SEPARATOR . $baseName;
			CopyDirRecursively($resourceDir, $dstDir);
		}
		//--------------------------------------------------------------------------------------------------

		// Commit transaction
		$pdo->commit();

		$message .= '<span style="color:blue;">Success to register plug-in (' . $baseName . ')</span>';
	}
	catch (PDOException $e)
	{
		$pdo->rollBack();
		DeleteDirRecursively($pluginPath);
		$message .= '<span style="color:red;">[ERROR] Fail to register plug-in (' . $baseName . ')<br />'
				 .  '(' . $e->getMessage() . ')</span>';
	}
}

$pdo = null;	
echo $message;

?>
