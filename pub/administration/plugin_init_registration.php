<?php
	//session_cache_limiter('none');
	session_start();

	include_once("../common.php");

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

	try
	{
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

		$dstPath = $PLUGIN_DIR . $DIR_SEPARATOR;

		$installDate = date("Y-m-d H:i:s");

		//----------------------------------------------------------------------------------------------------
		//  unzip the pakcage file
		//----------------------------------------------------------------------------------------------------
		if(move_uploaded_file($_FILES['upfile']['tmp_name'], $dstPath.$uploadFile))
		{
			$pos = strpos($baseName, "_v.");
			$pluginName = substr($baseName, 0, $pos);
			$version = substr($baseName, $pos+3, strlen($baseName)-$pos-3);

			$sqlStr = "SELECT COUNT(*) FROM executed_plugin_list WHERE plugin_name=? AND version=?";

			if(DBConnector::query($sqlStr, array($pluginName, $version), 'SCALAR') == 0)
			{
				$zipData = new ZipArchive;
				if ($zipData->open($dstPath.$uploadFile) === TRUE)
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
			unlink($dstPath.$uploadFile);
		}

		//----------------------------------------------------------------------------------------------------

		//----------------------------------------------------------------------------------------------------
		// Load xml file and DB setting
		//----------------------------------------------------------------------------------------------------
		if(!$errorFlg)
		{
			$message = '<span style="font-weight:bold;">Uploaded file name:</span>&nbsp;'
					 . $_FILES['upfile']['name']
					 . ' (File size:&nbsp;' . $_FILES['upfile']['size'] . ' bytes)<br/>';

			$base_name = substr($uploadFile, 0, strlen($uploadFile)-4);
			$xmlFname = $dstPath . $baseName . $DIR_SEPARATOR . $baseName . ".xml";

			if(!is_file($xmlFname) || ($xml = simplexml_load_file($xmlFname)) == false)
			{
				$message .= '<span style="color:red;">Fail to load xml file (' . $xmlFname . ')</span><br/>';
				DeleteDirRecursively($PLUGIN_DIR . $DIR_SEPARATOR . $baseName);
				$errorFlg = 1;
			}
			else
			{
				//------------------------------------------------------------------------------------------------------
				// Retrieve common parameters from xml file
				//------------------------------------------------------------------------------------------------------
				$pluginDefinition        = $xml->PluginDefinition[0];
				$cadDefinition           = $xml->CadDefinition[0];
				$researchDefinition      = $xml->ResearchDefinition[0];
				$groupResearchDefinition = $xml->GroupResearchDefinition[0];

				$resultDBDefinition = $xml->ResultDBDefinition[0];
				$scoreDBDefinition  = $xml->ScoreDBDefinition[0];
				$seriesDefinition   = $xml->SeriesDefinition[0];

				$resultTableName = (string)$resultDBDefinition->ResultTableName[0];
				$scoreTableName  = (string)$scoreDBDefinition->ScoreTableName[0];

				$pluginName       = (string)$pluginDefinition->PluginName[0];
				$version          = (string)$pluginDefinition->Version[0];
				$pluginType       = (string)$pluginDefinition->PluginType[0];
				$description      = addslashes((string)$pluginDefinition->Description[0]);
				//------------------------------------------------------------------------------------------------------

				if($pluginType == 1)    // for CAD
				{
					$inputType        = (string)$cadDefinition->InputType[0];
					$resultType       = (string)$cadDefinition->ResultType[0];
					$presentType      = (string)$cadDefinition->PresentType[0];
					$exportType       = (string)$cadDefinition->ExportType[0];
					$timeLimit        = (string)$cadDefinition->TimeLimit[0];
					$defaultSortKey   = (string)$cadDefinition->DefaultSortKey[0];
					$defaultSortOrder = ((string)$cadDefinition->DefaultSortOrder[0] == "1") ? 't' : 'f';
					$maxDispNum       = (string)$cadDefinition->MaxDispNum[0];
					$confidenceTh     = (string)$cadDefinition->ConfidenceTh[0];
					$yellowCircleTh   = (string)$cadDefinition->YellowCircleTh[0];
					$doubleCircleTh   = (string)$cadDefinition->DoubleCircleTh[0];
					$windowLevel      = (string)$cadDefinition->WindowLevel[0];
					$windowWidth      = (string)$cadDefinition->WindowWidth[0];

					$mainModality   = "";

					$cnt = 0;
					$cadSeriesSqlStr = "";
					$cadSeriesSqlParams = array();

					foreach($seriesDefinition->SeriesItem as $item)
					{
						if($cnt = 0 || (string)$item->SeriesID[0] == 1)  $mainModality = (string)$item->Modality[0];

						$cadSeriesSqlStr .= "INSERT INTO cad_series (plugin_name, version, series_id,"
										 .  " series_description, manufacturer, model_name, station_name,"
										 .  " modality, min_slice, max_slice, isotropic_type, start_img_num,"
										 .  " end_img_num, export_series_number)"
										 .  " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";

						$cadSeriesSqlParams[] = $pluginName;
						$cadSeriesSqlParams[] = $version;
						$cadSeriesSqlParams[] = (string)$item->SeriesID[0];

						$tmpStr = (string)$item->DefaultSeriesDescription[0];
						$cadSeriesSqlParams[] = ($tmpStr == "") ? $tmpStr : '(default)';

						$cadSeriesSqlParams[] = (string)$item->Manufacturer[0];
						$cadSeriesSqlParams[] = (string)$item->ModelName[0];
						$cadSeriesSqlParams[] = (string)$item->StationName[0];
						$cadSeriesSqlParams[] = (string)$item->Modality[0];
						$cadSeriesSqlParams[] = (string)$item->MinSlice[0];
						$cadSeriesSqlParams[] = (string)$item->MaxSlice[0];
						$cadSeriesSqlParams[] = (string)$item->IsotropicType[0];
						$cadSeriesSqlParams[] = (string)$item->StartImgNum[0];
						$cadSeriesSqlParams[] = (string)$item->EndImgNum[0];
						$cadSeriesSqlParams[] = (string)$item->ExportSeriesNum[0];

						$cnt++;
					}

					//--------------------------------------------------------------------------------------------------
					// Add plug-in information to plugin_master, cad_master, and cad_series table
					//--------------------------------------------------------------------------------------------------
					if(!$errorFlg)
					{
						$sqlStr = "SELECT COUNT(*) FROM plugin_master WHERE plugin_name=? AND version=?";

						if(DBConnector::query($sqlStr, array($pluginName, $version), 'SCALAR') == 1)
						{
							$sqlStr = "SELECT result_table, score_table FROM cad_master"
									. " WHERE plugin_name=? AND version=?";
							$result = DBConnector::query($sqlStr, array($pluginName, $version), 'ARRAY_ASSOC');

							if($result[0] != "")	DropTableIfExists($pdo, $esult[0]);
							if($result[1] != "")	DropTableIfExists($pdo, $esult[1]);

							$sqlStr = "DELETE FROM plugin_master WHERE plugin_name=? AND version=?";
							$stmt = $pdo->prepare($sqlStr);
							$stmt->execute(array($pluginName, $version));
						}

						$sqlStr = "SELECT MAX(cm.label_order) FROM cad_master cm, cad_series cs"
								. " WHERE cm.plugin_name=cs.plugin_name AND cm.version=cs.version"
								. " AND cm.exec_flg='t' AND cs.series_id=0 AND cs.modality=?";
						$maxLabelOrder = DBConnector::query($sqlStr, $mainModality, 'SCALAR');

						$sqlParams = array();

						$sqlStr = "INSERT INTO plugin_master (plugin_name, version, type, exec_flg,"
								. " description, install_dt) VALUES (?, ?, 1, 't', ?, ?);";
						$sqlParams[] = $pluginName;
						$sqlParams[] = $version;
						$sqlParams[] = $description;
						$sqlParams[] = $installDate;

						$sqlStr .= "INSERT INTO cad_master (plugin_name, version, exec_flg, label_order,"
								.  " input_type, result_type, present_type, export_type, time_limit,"
								.  " default_sort_key, default_sort_order, max_disp_num,"
								.  " confidence_threshold, yellow_circle_th, double_circle_th,"
								.  " window_level, window_width, result_table, score_table,"
								.  " description) VALUES "
								.  "(?,?,'t',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";

						$sqlParams[] = $pluginName;
						$sqlParams[] = $version;
						$sqlParams[] = $maxLabelOrder+1;
						$sqlParams[] = $inputType;
						$sqlParams[] = $resultType;
						$sqlParams[] = $presentType;
						$sqlParams[] = $exportType;
						$sqlParams[] = $timeLimit;
						$sqlParams[] = $defaultSortKey;
						$sqlParams[] = $defaultSortOrder;
						$sqlParams[] = $maxDispNum;
						$sqlParams[] = $confidenceTh;
						$sqlParams[] = $yellowCircleTh;
						$sqlParams[] = $doubleCircleTh;
						$sqlParams[] = $windowLevel;
						$sqlParams[] = $windowWidth;
						$sqlParams[] = $resultTableName;
						$sqlParams[] = $scoreTableName;
						$sqlParams[] = $description;

						$sqlStr .= $cadSeriesSqlStr;

						$stmt = $pdo->prepare($sqlStr);
						$stmt->execute(array_merge($sqlParams, $cadSeriesSqlParams));

						//echo $sqlStr;
						//var_dump(array_merge($sqlParams, $cadSeriesSqlParams));


						if($stmt->errorCode() != '00000')
						{
							$message .= '<span style="color:red;">[ERROR] Fail to register plugin table.</span><br/>';
							$errorMessage = $stmt->errorInfo();
							$message .= $errorMessage[2] . '<br/>';

							$sqlStr = "DELETE FROM plugin_master WHERE plugin_name=? AND version=?";
							$stmt = $pdo->prepare($sqlStr);
							$stmt->execute(array($pluginName, $version));

							DeleteDirRecursively($PLUGIN_DIR . $DIR_SEPARATOR . $baseName);
							$errorFlg = 1;
						}
					}
					//--------------------------------------------------------------------------------------------------

					//--------------------------------------------------------------------------------------------------
					// Create result table
					//--------------------------------------------------------------------------------------------------
					if(!$errorFlg && $resultTableName != "")
					{
						DropTableIfExists($pdo, $resultTableName);

						$sqlStr = 'CREATE TABLE "' . $resultTableName . '"('
								. 'exec_id    INT NOT NULL,'
								. 'sub_id     SMALLINT NOT NULL,';

						foreach($resultDBDefinition->DBItem as $item)
						{
							$colName = sprintf("%s", (string)$item->DBColumnName[0]);

							switch((string)$item->DBColumnType[0])
							{
								case 'int':
									$sqlStr .= $colName . ' INT NOT NULL,';
									break;

								case 'smallint':
									$sqlStr .= $colName . ' SMALLINT NOT NULL,';
									break;

								case 'text':
									$colSize = (int)$item->DBColumnSize[0];
									if($colSize > 255)
									{
										$sqlStr .= $colName . ' TEXT NOT NULL,';
									}
									else
									{
										$sqlStr .= $colName . ' CHARACTER VARYING(' . $colSize . ') NOT NULL,';
									}
									break;

								case 'boolean':
									$sqlStr .= $colName . ' BOOLEAN NOT NULL,';
									break;

								default: // case 'real'
									$sqlStr .= $colName . ' REAL NOT NULL,';
									break;
							} // end switch
						}

						$sqlStr .= 'sid SERIAL NOT NULL,'
								.  ' CONSTRAINT "' . $resultTableName . '_pkey" PRIMARY KEY (sid),'
								.  ' CONSTRAINT "' . $resultTableName . '_ukey" UNIQUE (exec_id, sub_id),'
								.  ' CONSTRAINT key_exec_id FOREIGN KEY (exec_id)'
								.  ' REFERENCES executed_plugin_list (exec_id) MATCH SIMPLE'
								.  ' ON UPDATE RESTRICT ON DELETE CASCADE)';

						$stmt =$pdo->prepare($sqlStr);
						$stmt->execute();

						if($stmt->errorCode() != '00000')
						{
							$message .= '<span style="color:red;">[ERROR] Fail to create result table.</span><br/>';
							//$errorMessage = $stmt->errorInfo();
							//$message .= $errorMessage[2] . '<br/>';

							$sqlStr = "DELETE FROM plugin_master WHERE plugin_name=? AND version=?";
							$stmt = $pdo->prepare($sqlStr);
							$stmt->execute(array($pluginName, $version));

							DeleteDirRecursively($PLUGIN_DIR . $DIR_SEPARATOR . $baseName);
							$errorFlg = 1;
						}

					} // end if($errorFlg == 0 && $resultTableName != "")
					//--------------------------------------------------------------------------------------------------

					//--------------------------------------------------------------------------------------------------
					// Create score table
					//--------------------------------------------------------------------------------------------------
					if(!$errorFlg && $scoreTableName != "")
					{
						DropTableIfExists($pdo, $scoreTableName);

						$sqlStr = 'CREATE TABLE "' . $scoreTableName . '"('
								. 'exec_id        INT NOT NULL,'
								. 'entered_by     CHARACTER VARYING(32) NOT NULL,'
								. 'consensual_flg BOOLEAN NOT NULL DEFAULT false,'
								. 'interrupt_flg BOOLEAN NOT NULL DEFAULT false,';

						foreach($scoreDBDefinition->DBItem as $item)
						{
							$colName = sprintf("%s", (string)$item->DBColumnName[0]);

							switch((string)$item->DBColumnType[0])
							{
								case 'int':
									$sqlStr .= $colName . ' INT NOT NULL,';
									break;

								case 'smallint':
									$sqlStr .= $colName . ' SMALLINT NOT NULL,';
									break;

								case 'text':
									$colSize = (int)$item->DBColumnSize[0];
									if($colSize > 255)
									{
										$sqlStr .= $colName . ' TEXT NOT NULL,';
									}
									else
									{
										$sqlStr .= $colName . ' CHARACTER VARYING(' . $colSize . ') NOT NULL,';
									}
									break;

								case 'boolean':
									$sqlStr .= $colName . ' BOOLEAN NOT NULL,';
									break;

								default: // case 'real'
									$sqlStr .= $colName . ' REAL NOT NULL,';
									break;
							} // end switch
						}

						$sqlStr .= ' registered_at timestamp without time zone NOT NULL,'
								.  ' CONSTRAINT "' . $scoreTableName . '_pkey"'
								.  ' PRIMARY KEY (exec_id, entered_by, consensual_flg),'
								.  ' CONSTRAINT key_exec_id FOREIGN KEY (exec_id)'
								.  ' REFERENCES executed_plugin_list (exec_id) MATCH SIMPLE'
								.  ' ON UPDATE RESTRICT ON DELETE CASCADE);';

						$stmt =$pdo->prepare($sqlStr);
						$stmt->execute();

						if($stmt->errorCode() != '00000')
						{
							$message .= '<span style="color:red;">[ERROR] Fail to create score table.</span><br/>';
							$errorMessage = $stmt->errorInfo();
							$message .= $errorMessage[2] . '<br/>';

							if($resultTableName[0] != "")  DropTableIfExists($pdo, $resultTableName);

							$sqlStr = "DELETE FROM plugin_master WHERE plugin_name=? AND version=?";
							$stmt = $pdo->prepare($sqlStr);
							$stmt->execute(array($pluginName, $version));

							DeleteDirRecursively($PLUGIN_DIR . $DIR_SEPARATOR . $baseName);
							$errorFlg = 1;
						}
					} // end if($errorFlg == 0 && $scoreTableName != "")
					//-------------------------------------------------------------------------------------------

					if(!$errorFlg)
					{
						$message .= '<span style="color:blue;">'
								 .  $pluginName . ' ver.' . $version . ' is successfully registered.</span>';
					}
				} //end of CAD
				else if($pluginType==2)  // for research
				{

				}

				else if($pluginType==3)  // for group research
				{

				}
			}
		}
		//----------------------------------------------------------------------------------------------------

		echo $message;

	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;

?>
