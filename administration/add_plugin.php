<?php
	//session_cache_limiter('none');
	session_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> 
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title></title>
<link rel="stylesheet" type="text/css" href="../css/base_style.css">

<script type="text/javascript" src="../js/jquery/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="../js/list_function.js"></script>

<script language="Javascript">;
<!--

function UploadPlugin()
{
	if(confirm('Do you upload the plug-in package to CIRCUS-CS server?'))
	{
		document.form1.mode.value = 'upload';
		
		document.form1.method = 'POST';
		document.form1.target = '_self';
		document.form1.action = 'add_plugin.php';
		document.form1.submit();
	}
}


-->
</script>
</head>
<body bgcolor=#ffffff>

<?php

	include_once("../common.php");
	
	function DropTableIfExists($tableName, $dbConn)
	{
		if($tableName != "")
		{
			// e[ȗ݊mF
			$sqlStr = "SELECT count(*) FROM pg_class c"
			        . " WHERE c.relkind = 'r'"
					. " AND c.relname = '" . $tableName . "';";
							
			$res = pg_query($dbConn, $sqlStr);
	
			if(pg_fetch_result($res, 0, 0) == 1)
			{
				$sqlStr = 'SELECT COUNT(*) FROM "' . $tableName . '"';
				$res = pg_query($dbConn, $sqlStr);
			
				if(pg_fetch_result($res, 0, 0) == 0)
				{
					$sqlStr = 'DROP TABLE IF EXISTS "' . $tableName . '"';
					$res = pg_query($dbConn, $sqlStr);
				}
			}
		}	
	}	

	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//--------------------------------------------------------------------------------------------------------
	$mode         = (isset($_REQUEST['mode']))         ? $_REQUEST['mode']         : "";
	$enteredFname = (isset($_REQUEST['enteredFname'])) ? $_REQUEST['enteredFname'] : "";
	$uploadFile = $_FILES["upfile"]["name"];
	//--------------------------------------------------------------------------------------------------------

	echo '<form id="form1" name="form1" enctype="multipart/form-data">';
	echo '<input type="hidden" id="mode" name="mode" value="">';
	echo '<div class="listTitle">Add plug-in from pakaged file</div>';
	echo '<div style="font-size:5px;">&nbsp</div>';
	echo '<div style="font-size:16px;">File name:&nbsp;';
	echo '<input id="uploadFname" type="file" name="upfile" size="50">&nbsp;';
	echo '<input type="button" value="upload" onClick=UploadPlugin()>';
	echo '</div></form>';
	echo '<hr>';

	$baseName = substr($uploadFile, 0, strlen($uploadFile)-4);
	$errorFlg = 0;

	//--------------------------------------------------------------------------------------------------------
	// Connect to SQL server
	//--------------------------------------------------------------------------------------------------------
	if($errorFlg == 0)
	{
		if(($dbConn = pg_connect($connStr)) == FALSE)
		{
			echo '<font color=#ff0000>Fail to connect DB server.</font><br>';
			flush();
			DeleteDirRecursively($PLUGIN_DIR . $DIR_SEPARATOR . $baseName, $DIR_SEPARATOR);
			$errorFlg = 1;
		}
	}
	//--------------------------------------------------------------------------------------------------------

	if($mode == 'upload')
	{
		$dstPath = $PLUGIN_DIR . $DIR_SEPARATOR;
		
		$installDate = date("Y-m-d H:i:s");
	
		//----------------------------------------------------------------------------------------------------
		//  unzip the pakcage file
		//----------------------------------------------------------------------------------------------------
		if($errorFlg == 0 && move_uploaded_file($_FILES['upfile']['tmp_name'], $dstPath.$uploadFile) == TRUE)
		{
			$pos = strpos($baseName, "_v.");
			$cadName = substr($baseName, 0, $pos);
			$version = substr($baseName, $pos+3, strlen($baseName)-$pos-3);

			$sqlStr = "SELECT COUNT(*) FROM executed_cad_list "
			        . " WHERE cad_name='" . $cadName . "' AND version='" . $version . "';";
			
			$res = pg_query($dbConn, $sqlStr);
			$executedNum = pg_fetch_result($res, 0, 0);

			if($executedNum == 0)
			{
				$zipData = new ZipArchive;
				if ($zipData->open($dstPath.$uploadFile) === TRUE)
				{
					$zipData->extractTo($PLUGIN_DIR);
    				$zipData->close();
				}
				else
				{
					echo  "<font color=#ff0000>Error: " . $uploadFile . " is not ZIP file!!";
					$errorFlg = 1;
				}
			}
			else
			{
				echo  "<font color=#ff0000>Error: " . $baseName . " was already executed in this server!!";
				$errorFlg = 1;
			}
			unlink($dstPath.$uploadFile);
		}
		//----------------------------------------------------------------------------------------------------
		
		//----------------------------------------------------------------------------------------------------
		// Load xml file and DB setting
		//----------------------------------------------------------------------------------------------------
		if($errorFlg == 0)
		{
			echo '<div style="font-size:14px;"><b>Uploaded file name:</b>&nbsp;' . $_FILES['upfile']['name'] . '</div>';
			echo '<div style="font-size:14px;"><b>Size:&nbsp;</b>';
			echo $_FILES['upfile']['size'] . ' bytes<br></div>';
			flush();

			$base_name = substr($uploadFile, 0, strlen($uploadFile)-4);
			$xmlFname = $dstPath . $baseName . $DIR_SEPARATOR . $baseName . ".xml";
			
			if(!is_file($xmlFname) || ($xml = simplexml_load_file($xmlFname)) == FALSE)
			{
				echo '<font color=#ff0000>Fail to load xml file (' . $xmlFname . ')</font><br>';
				flush();				
				DeleteDirRecursively($PLUGIN_DIR . $DIR_SEPARATOR . $baseName, $DIR_SEPARATOR);
				$errorFlg = 1;
			}
			else
			{
				echo '<div style="font-size:14px;">Load xml file (' . $baseName . '.xml)</div>';
				flush();

				//--------------------------------------------------------------------------------------------
				// Get parameters from xml file
				//--------------------------------------------------------------------------------------------
				$resultDBDefine   = $xml->ResultDBDefine[0];
				$scoreDBDefine    = $xml->ScoreDBDefine[0];
				$pluginDefine     = $xml->PluginDefine[0];
				$seriesDefine     = $xml->SeriesDefine[0];
				$classifierDefine = $xml->ClassifierDefine[0];
				
				$resultTableName = $resultDBDefine->ResultTableName[0];
				$scoreTableName  = $scoreDBDefine->ScoreTableName[0];
				
				$cadName          = $pluginDefine->PluginName[0];
				$version          = $pluginDefine->Version[0];
				$inputType        = $pluginDefine->InputType[0];
				$resultType       = $pluginDefine->ResultType[0];
				$presentType      = $pluginDefine->PresentType[0];
				$exportType       = $pluginDefine->ExportType[0];
				$timeLimit        = $pluginDefine->TimeLimit[0];
				$defaultSortKey   = $pluginDefine->DefaultSortKey[0];
				$defaultSortOrder = ($pluginDefine->DefaultSortOrder[0] == 1) ? "'t'" : "'f'";
				$maxDispNum       = $pluginDefine->MaxDispNum[0];
				$confidenceTh     = $pluginDefine->ConfidenceTh[0];
				$yellowCircleTh   = $pluginDefine->YellowCircleTh[0];
				$doubleCircleTh   = $pluginDefine->DoubleCircleTh[0];
				$windowLevel      = $pluginDefine->WindowLevel[0];
				$windowWidth      = $pluginDefine->WindowWidth[0];
				$description      = addslashes($pluginDefine->Description[0]);

				$mainModality   = "";

				$cnt = 0;
				$cadSeriesSqlStr = "";
				$cadDicomTagSqlStr = "";
		
				foreach($seriesDefine->SeriesItem as $item)
				{	
					if($cnt = 0 || $item->SeriesID[0] == 1)  $mainModality = $item->Modality[0];

					$cadSeriesSqlStr .= "INSERT INTO cad_series (cad_name, version, series_id,"
                                     .  " series_description, modality, min_slice, max_slice,"
									 .  " isotropic_type, start_img_num, end_img_num, export_series_number)"
									 .  " VALUES ('" . $cadName . "','" . $version . "'," . $item->SeriesID[0] . ","
									 .  "'(default)','" . $item->Modality[0] . "',"
									 . $item->MinSlice[0] . "," . $item->MaxSlice[0] . ","
									 . $item->IsotropicType[0] . "," . $item->StartImageNum[0] . ","
									 . $item->EndImageNum[0] . "," . $item->ExportSeriesNum[0] . ");";		
					$cnt++;
				}
				
				$classifierName = array();
				foreach($classifierDefine->ClassifierItem as $item)
				{				
					//echo $item->FileName[0];
					array_push($classifierName, $item->FileName[0]);
				}
				//--------------------------------------------------------------------------------------------
				
				//--------------------------------------------------------------------------------------------
				// Create .conf file
				//--------------------------------------------------------------------------------------------
				$confFname = $dstPath . $baseName . $DIR_SEPARATOR . $baseName . ".conf";
				
				if(($fp = fopen($confFname,"w")) == FALSE)
				{
					echo '<font color=#ff0000>Fail to create configure file (' . $confFname . ')</font><br>';
					flush();
					DeleteDirRecursively($PLUGIN_DIR . $DIR_SEPARATOR . $baseName, $DIR_SEPARATOR);
					$errorFlg = 1;
				}
				else
				{
					fprintf($fp, "%d;\r\n", count($classifierName));
					
					for($i=0; $i<count($classifierName); $i++)
					{
						if($i > 0)  fprintf($fp, ",");
						fprintf($fp, "%s", $classifierName[$i]);
					}
					fprintf($fp, ";\r\n");
					fprintf($fp, "%d;\r\n", $maxDispNum);
					fprintf($fp, "%d;\r\n", $windowLevel);
					fprintf($fp, "%d;\r\n", $windowWidth);
					fclose($fp);
				}
				//--------------------------------------------------------------------------------------------
				
				//--------------------------------------------------------------------------------------------
				// Add plug-in information to cad_master, cad_series and cad_dicom_tag table
				//--------------------------------------------------------------------------------------------
				if($errorFlg == 0)
				{
					echo '<div style="font-size:14px;">';
					echo 'Rsgistration of plug-in information...</div>';
					flush();
				
					$sqlStr = "SELECT * FROM cad_master WHERE cad_name='" . $cadName . "'"
					        .  " AND version='" . $version . "';";
				
					pg_send_query($dbConn, $sqlStr);
					$res = pg_get_result($dbConn);
				
					if(pg_num_rows($res) == 1)
					{
						$row = pg_fetch_assoc($res);
						
						$tableName = $row['result_table'];
						
						DropTableIfExists($row['result_table'], $dbConn);
						DropTableIfExists($row['score_table'], $dbConn);
						
						$sqlStr = "DELETE FROM cad_master WHERE cad_name='" . $cadName . "'"
						        . " AND version='" . $version . "';"
						        . "DELETE FROM cad_series WHERE cad_name='" . $cadName . "'"
						        . " AND version='" . $version . "';";

						$res = pg_query($dbConn, $sqlStr);
					}

					$sqlStr = "SELECT MAX(cm.label_order) FROM cad_master cm, cad_series cs"
							. " WHERE cm.cad_name=cs.cad_name AND cm.version=cs.version"
							. " AND cm.exec_flg='t' AND cs.series_id=1"
							. " AND cs.modality='" . $mainModality . "';";
		
					$res = pg_query($dbConn, $sqlStr);
					$maxLabelOrder = pg_fetch_result($res, 0, 0);
		
					$sqlStr = "INSERT INTO cad_master (cad_name, version, exec_flg, label_order, input_type,"
							. " result_type, present_type, export_type, time_limit, default_sort_key, default_sort_order,"
							. " max_disp_num, confidence_threshold, yellow_circle_th, double_circle_th,"
							. " window_level, window_width, result_table, score_table, description, install_dt) VALUES ("
							. "'" . $cadName . "','" . $version . "','t'," . ($maxLabelOrder+1) . ","
							. $inputType . "," . $resultType . "," . $presentType . "," . $exportType . ","
							. $timeLimit . "," . $defaultSortKey . "," . $defaultSortOrder . "," . $maxDispNum . ","
							. $confidenceTh . "," . $yellowCircleTh . "," . $doubleCircleTh . ","
							. $windowLevel . "," . $windowWidth . ",'" . $resultTableName . "',"
							. "'" . $scoreTableName . "','" . $description . "','" . $installDate . "');";
							
					$sqlStr .= $cadSeriesSqlStr;

					//echo $sqlStr;

					pg_send_query($dbConn, $sqlStr);
					$res = pg_get_result($dbConn);
					$msg = pg_result_error($res);

					if($msg != "")
					{
						echo '<font color=#ff0000>' . $msg . '</font>';
						flush();

						$sqlStr = "DELETE FROM cad_master WHERE cad_name='" . $cadName . "'"
						        .  " AND version='" . $version . "';";
						$res = pg_query($dbConn, $sqlStr);

						DeleteDirRecursively($PLUGIN_DIR . $DIR_SEPARATOR . $baseName, $DIR_SEPARATOR);
						$errorFlg = 1;
					}
				}
				
				//------------------------------------------------------------------------------------------------
				// Create result table
				//------------------------------------------------------------------------------------------------
				if($errorFlg == 0 && $resultTableName != "")
				{
					echo '<div style="font-size:14px;">';
					echo 'Create table for feature value (table name:&nbsp;' . $resultTableName . ')...</div>';
					flush();

					DropTableIfExists($resultTableName, $dbConn);

					$sqlStr = 'CREATE TABLE "' . $resultTableName . '"('
							. 'exec_id        INT NOT NULL,'
							. 'sub_id         SMALLINT NOT NULL,';

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
	
					$sqlStr .= ' CONSTRAINT "' . $resultTableName . '_pkey"'
							.  ' PRIMARY KEY (exec_id, sub_id),'
							.  ' CONSTRAINT key_exec_id FOREIGN KEY (exec_id)'
							.  ' REFERENCES executed_cad_list (exec_id) MATCH SIMPLE'
							.  ' ON UPDATE RESTRICT ON DELETE CASCADE);';
	
					pg_send_query($dbConn, $sqlStr);
					$res = pg_get_result($dbConn);
					$msg = pg_result_error($res);

					if($msg != "")
					{
						echo '<font color=#ff0000>' . $msg . '</font>';
						flush();
						$sqlStr .= "DELETE FROM cad_master WHERE cad_name='" . $cadName . "'"
						        .  " AND version='" . $version . "';";
						$res = pg_query($dbConn, $sqlStr);

						DeleteDirRecursively($PLUGIN_DIR . $DIR_SEPARATOR . $baseName, $DIR_SEPARATOR);
					}
					else
					{
						$sqlStr = 'ALTER TABLE "' . $resultTableName . '" OWNER TO ' . $dbAccessUser . ';';
						$res = pg_query($dbConn, $sqlStr);
					}					
					
				} // end if($errorFlg == 0 && $resultTableName != "")
				//-----------------------------------------------------------------------------------------------
										
				//------------------------------------------------------------------------------------------------
				// Create score table
				//------------------------------------------------------------------------------------------------
				if($errorFlg == 0 && $scoreTableName != "")
				{
					echo '<div style="font-size:14px;">';
					echo 'Create table for visual scoring (table name:&nbsp;' . $scoreTableName . ')...</div>';
					flush();
					
					DropTableIfExists($scoreTableName, $dbConn);					

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
							.  ' REFERENCES executed_cad_list (exec_id) MATCH SIMPLE'
							.  ' ON UPDATE RESTRICT ON DELETE CASCADE);';
	
					pg_send_query($dbConn, $sqlStr);
					$res = pg_get_result($dbConn);
					$msg = pg_result_error($res);

					if($msg != "")
					{
						echo '<font color=#ff0000>' . $msg . '</font>';
						flush();
						$sqlStr .= "DELETE FROM cad_master WHERE cad_name='" . $cadName . "'"
						        .  " AND version='" . $version . "';";
						$res = pg_query($dbConn, $sqlStr);
						
						DropTableIfExists($resultTableName, $dbConn);
						DeleteDirRecursively($PLUGIN_DIR . $DIR_SEPARATOR . $baseName, $DIR_SEPARATOR);
					}
					else
					{
						$sqlStr = 'ALTER TABLE "' . $scoreTableName . '" OWNER TO ' . $dbAccessUser . ';';
						$res = pg_query($dbConn, $sqlStr);
					}					
					
				} // end if($errorFlg == 0 && $scoreTableName != "")
				//-----------------------------------------------------------------------------------------------

				if($errorFlg == 0)
				{
					echo '<font color=#0000ff>';
					echo $cadName . ' ver.' . $version . ' is successfully registered.</font>';
				}

			}
		}
		//----------------------------------------------------------------------------------------------------
	}

	

?>

</body>
</html>