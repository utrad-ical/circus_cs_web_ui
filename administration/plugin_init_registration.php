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
		$pdo = new PDO($connStrPDO);	
	
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
					
			if(PdoQueryOne($pdo, $sqlStr, array($pluginName, $version), 'SCALAR') == 0)
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
				//DeleteDirRecursively($PLUGIN_DIR . $DIR_SEPARATOR . $baseName, $DIR_SEPARATOR);
				$errorFlg = 1;
			}
			else
			{
				//------------------------------------------------------------------------------------------------------
				// Retrieve common parameters from xml file
				//------------------------------------------------------------------------------------------------------
				$pluginDefine        = $xml->PluginDefine[0];
				$cadDefine           = $xml->CadDefine[0];
				$researchDefine      = $xml->ResearchDefine[0];
				$groupResearchDefine = $xml->GroupResearchDefine[0];

				$resultDBDefine   = $xml->ResultDBDefine[0];
				$scoreDBDefine    = $xml->ScoreDBDefine[0];
				$seriesDefine     = $xml->SeriesDefine[0];
				$classifierDefine = $xml->ClassifierDefine[0];
				
				$resultTableName = $resultDBDefine->ResultTableName[0];
				$scoreTableName  = $scoreDBDefine->ScoreTableName[0];
				
				$pluginName       = $pluginDefine->PluginName[0];
				$version          = $pluginDefine->Version[0];
				$pluginType       = $pluginDefine->PluginType[0];				
				$description      = addslashes($pluginDefine->Description[0]);
				//------------------------------------------------------------------------------------------------------
				
				if($pluginType == 1)    // for CAD
				{
					$inputType        = $cadDefine->InputType[0];
					$resultType       = $cadDefine->ResultType[0];
					$presentType      = $cadDefine->PresentType[0];
					$exportType       = $cadDefine->ExportType[0];
					$timeLimit        = $cadDefine->TimeLimit[0];
					$defaultSortKey   = $cadDefine->DefaultSortKey[0];
					$defaultSortOrder = ($cadDefine->DefaultSortOrder[0] == 1) ? 't' : 'f';
					$maxDispNum       = $cadDefine->MaxDispNum[0];
					$confidenceTh     = $cadDefine->ConfidenceTh[0];
					$yellowCircleTh   = $cadDefine->YellowCircleTh[0];
					$doubleCircleTh   = $cadDefine->DoubleCircleTh[0];
					$windowLevel      = $cadDefine->WindowLevel[0];
					$windowWidth      = $cadDefine->WindowWidth[0];
				
					$mainModality   = "";

					$cnt = 0;
					$cadSeriesSqlStr = "";
					$cadSeriesSqlParams = array();
		
					foreach($seriesDefine->SeriesItem as $item)
					{	
						if($cnt = 0 || $item->SeriesID[0] == 1)  $mainModality = $item->Modality[0];

						$cadSeriesSqlStr .= "INSERT INTO cad_series (cad_name, version, series_id,"
										 .  " series_description, manufacturer, model_name, station_name,"
										 .  " modality, min_slice, max_slice, isotropic_type, start_img_num,"
										 .  " end_img_num, export_series_number)"
										 .  " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
										 
						$cadSeriesSqlParams[] = $pluginName;
						$cadSeriesSqlParams[] = $version;
						$cadSeriesSqlParams[] = $item->SeriesID[0];

						$tmpStr = $item->DefaultSeriesDescription[0];
						$cadSeriesSqlParams[] = ($tmpStr == "") ? $tmpStr : '(default)';

						$cadSeriesSqlParams[] = $item->Manufacturer[0];
						$cadSeriesSqlParams[] = $item->ModelName[0];
						$cadSeriesSqlParams[] = $item->StationName[0];
						$cadSeriesSqlParams[] = $item->Modality[0];
						$cadSeriesSqlParams[] = $item->MinSlice[0];
						$cadSeriesSqlParams[] = $item->MaxSlice[0];
						$cadSeriesSqlParams[] = $item->IsotropicType[0];
						$cadSeriesSqlParams[] = $item->StartImgNum[0]; 
						$cadSeriesSqlParams[] = $item->EndImgNum[0];
						$cadSeriesSqlParams[] = $item->ExportSeriesNum[0];
										 
						$cnt++;
					}
				
					$classifierName = array();
					foreach($classifierDefine->ClassifierItem as $item)
					{				
						//echo $item->FileName[0];
						$classifierName[] = $item->FileName[0];
					}
				
					//--------------------------------------------------------------------------------------------------
					// Create configure file
					//--------------------------------------------------------------------------------------------------
					$confFname = $dstPath . $baseName . $DIR_SEPARATOR . $baseName . ".conf";
				
					if(($fp = fopen($confFname,"w")) == FALSE)
					{
						$message = '<span style="color:red;">[ERROR] '
								 . 'Fail to create configure file (' . $confFname . ').</span>';
						$errorFlg = 1;
					}
					else
					{
						fprintf($fp, "%d;\r\n", count($classifierName));
						fprintf($fp, "%s;\r\n", implode(",", $classifierName));
						fprintf($fp, "%d;\r\n", $maxDispNum);
						fprintf($fp, "%d;\r\n", $windowLevel);
						fprintf($fp, "%d;\r\n", $windowWidth);
						fclose($fp);
					}
					//--------------------------------------------------------------------------------------------------
				
					//--------------------------------------------------------------------------------------------------
					// Add plug-in information to plugin_master, cad_master, and cad_series table
					//--------------------------------------------------------------------------------------------------
					if(!$errorFlg)
					{
						$sqlStr = "SELECT COUNT(*) FROM plugin_master WHERE plugin_name=? AND version=?";
					
						if(PdoQueryOne($pdo, $sqlStr, array($pluginName, $version), 'SCALAR') == 1)
						{
							$sqlStr = "SELECT result_table, score_table FROM cad_master"
									. " WHERE plugin_name=? AND version=?";
							$result = PdoQueryOne($pdo, $sqlStr, array($pluginName, $version), 'ARRAY_ASSOC');
						
							if($result[0] != "")	DropTableIfExists($pdo, $esult[0]);
							if($result[1] != "")	DropTableIfExists($pdo, $esult[1]);
						
							$sqlStr = "DELETE FROM plugin_master WHERE plugin_name=? AND version=?";
							$stmt = $pdo->prepare($sqlStr);
							$stmt->execute(array($pluginName, $version));
						}

						$sqlStr = "SELECT MAX(cm.label_order) FROM cad_master cm, cad_series cs"
								. " WHERE cm.cad_name=cs.cad_name AND cm.version=cs.version"
								. " AND cm.exec_flg='t' AND cs.series_id=1 AND cs.modality=?";
						$maxLabelOrder = PdoQueryOne($pdo, $sqlStr, $mainModality, 'SCALAR');

						$sqlParams = array();

						$sqlStr = "INSERT INTO plugin_master (plugin_name, version, type, exec_flg,"
								. " description, install_dt) VALUES (?, ?, 1, 't', ?, ?);";
						$sqlParams[] = $pluginName;
						$sqlParams[] = $version;
						$sqlParams[] = $description;
						$sqlParams[] = $installDate;
						
						$sqlStr .= "INSERT INTO cad_master (cad_name, version, exec_flg, label_order,"
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

						if($stmt->errorCode() != '00000')
						{
							$message .= '<span style="color:red;">[ERROR] Fail to register plugin table.</span><br/>';
							//$errorMessage = $stmt->errorInfo();
							//$message .= $errorMessage[2] . '<br/>';
							
							$sqlStr = "DELETE FROM plugin_master WHERE plugin_name=? AND version=?";
							$stmt = $pdo->prepare($sqlStr);
							$stmt->execute(array($pluginName, $version));
	
							//DeleteDirRecursively($PLUGIN_DIR . $DIR_SEPARATOR . $baseName, $DIR_SEPARATOR);
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
								. 'sid        SERIAL NOT NULL,'
								. 'exec_id    INT NOT NULL,'
								. 'sub_id     SMALLINT NOT NULL,';
	
						foreach($resultDBDefine->DBItem as $item)
						{
							$colName = sprintf("%s", (string)$item->DBColumnName[0]);
					
							//echo $item->DBColumnName[0] . " " . $item->DBColumnType[0] . "<br>";
					
							switch($item->DBColumnType[0])
							{
								case 'int':      
									$sqlStr .= $colName . ' INT NOT NULL,';
									break;
					
								case 'smallint':
									$sqlStr .= $colName . ' SMALLINT NOT NULL,';
									break;
					
								case 'text':      
									$colSize = $item->DBColumnSize[0];
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
	
						$sqlStr .= ' CONSTRAINT "' . $resultTableName . '_pkey" PRIMARY KEY (sid),'
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
	
							//DeleteDirRecursively($PLUGIN_DIR . $DIR_SEPARATOR . $baseName, $DIR_SEPARATOR);
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
					
						foreach($scoreDBDefine->DBItem as $item)
						{
							$colName = sprintf("%s", (string)$item->DBColumnName[0]);
				
							switch($item->DBColumnType[0])
							{
								case 'int':      
									$sqlStr .= $colName . ' INT NOT NULL,';
									break;
				
								case 'smallint':
									$sqlStr .= $colName . ' SMALLINT NOT NULL,';
									break;
				
								case 'text':      
									$colSize = $item->DBColumnSize[0];
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
	
							//DeleteDirRecursively($PLUGIN_DIR . $DIR_SEPARATOR . $baseName, $DIR_SEPARATOR);
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
