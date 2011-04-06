<?php
	session_start();

	include("../common.php");
	include("auto_logout_administration.php");

	if($_SESSION['serverSettingsFlg']==1)
	{
		//--------------------------------------------------------------------------------------------------------------
		// Import $_REQUEST variables
		//--------------------------------------------------------------------------------------------------------------
		$mode = (isset($_REQUEST['mode']) && ($_SESSION['ticket'] == $_REQUEST['ticket'])) ? $_REQUEST['mode'] : "";
		$newStorageID  = (isset($_REQUEST['newStorageID'])) ? $_REQUEST['newStorageID'] : "";
		$newPath       = (isset($_REQUEST['newPath']))      ? $_REQUEST['newPath']      : "";
		$newType       = (isset($_REQUEST['newType']))      ? $_REQUEST['newType']      : "";

		$oldDicomID    = (isset($_REQUEST['oldDicomID']) && is_numeric($_REQUEST['oldDicomID'])) ? $_REQUEST['oldDicomID'] : 0;
		$oldResearchID = (isset($_REQUEST['oldResearchID']) && is_numeric($_REQUEST['oldResearchID'])) ? $_REQUEST['oldResearchID'] : 0;
		$newDicomID    = (isset($_REQUEST['newDicomID']) && is_numeric($_REQUEST['newDicomID'])) ? $_REQUEST['newDicomID'] : 0;
		$newResearchID = (isset($_REQUEST['newResearchID']) && is_numeric($_REQUEST['newResearchID'])) ? $_REQUEST['newResearchID'] : 0;
		//--------------------------------------------------------------------------------------------------------------

		$params = array('toTopDir' => "../");
		$restartButtonFlg = 0;

		try
		{
			// Connect to SQL Server
			$pdo = DBConnector::getConnection();

			//----------------------------------------------------------------------------------------------------------
			// Registration of storage settings
			//----------------------------------------------------------------------------------------------------------
			$message = "&nbsp;";
			$sqlStr = "";
			$sqlParams = array();

			if($mode == 'add')
			{
				$sqlStr = "SELECT COUNT(*) FROM storage_master WHERE path=?";

				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindParam(1, $newPath);
				$stmt->execute();

				if($stmt->fetchColumn()==1)
				{
					$message = '<span style="color:#ff0000;">[ERROR] Entered path (' . $newPath . ') was already exist.</span>';
				}
				else
				{
					// Set current_use
					$stmt = $pdo->prepare("SELECT COUNT(*) FROM storage_master WHERE type=?");
					$stmt->bindParam(1, $newType);
					$stmt->execute();
					$currentUse = ($stmt->fetchColumn() == 0) ? 't' : 'f';

					// Get new storage ID
					$stmt = $pdo->prepare("SELECT nextval('storage_master_storage_id_seq')");
					$stmt->execute();
					$newStorageID = $stmt->fetchColumn();
					$newAlias = sprintf("store%d/", $newStorageID);

					//echo $newStorageID;

					$sqlStr = "INSERT INTO storage_master(storage_id, path, apache_alias, current_use, type)"
							. " VALUES (currval('storage_master_storage_id_seq'), ?, ?, ?, ?)";

					$sqlParams[] = $newPath;
					$sqlParams[] = $newAlias;
					$sqlParams[] = $currentUse;
					$sqlParams[] = $newType;
				}
			}
			else if($mode == 'changeCurrent')
			{
				if($oldDicomID != 0 && $newDicomID != 0 && $oldDicomID != $newDicomID)
				{
					$sqlStr = "UPDATE storage_master SET current_use='f' WHERE storage_id=?;"
					        . "UPDATE storage_master SET current_use='t' WHERE storage_id=?;";
					$sqlParams[] = $oldDicomID;
					$sqlParams[] = $newDicomID;
				}

				if($oldResearchID != 0 && $newResearchID != 0 && $oldResearchID != $newResearchID)
				{
					$sqlStr .= "UPDATE storage_master SET current_use='f' WHERE storage_id=?;"
					        .  "UPDATE storage_master SET current_use='t' WHERE storage_id=?;";
					$sqlParams[] = $oldResearchID;
					$sqlParams[] = $newResearchID;
				}
			}
			else if($mode == 'delete')
			{
				if($newType == 1)
				{
					$sqlStr = "SELECT COUNT(*) FROM series_list WHERE storage_id=?";
				}
				else if($newType == 2)
				{
					$sqlStr = "SELECT COUNT(*) FROM executed_plugin_list WHERE plugin_type>=2 AND storage_id=?";
				}

				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindParam(1, $newStorageID);
				$stmt->execute();

				if($stmt->fetchColumn()>0)
				{
					$message = "<font color=#ff0000>Storage " . $newStorageID . " was already stored.</font>";
				}
				else
				{
					$sqlStr = "DELETE FROM storage_master WHERE storage_id=?";
					$sqlParams[] = $newStorageID;
				}
			}
			else if($mode == 'restart')
			{
				echo 'DICOM storage server and HTTP server are restarting. Please relogin later.<br>';
				flush();

				win32_stop_service($DICOM_STORAGE_SERVICE);
				win32_start_service($DICOM_STORAGE_SERVICE);

				echo '<script language="Javascript">';
				echo "top.location.replace('../index.php?mode=restartApache');";
				echo '</script>';
				flush();
			}

			if($mode == 'add')
			{
				$newPath = (realpath($newPath) == "") ? $newPath : realpath($newPath);

				if(substr_count($newPath, $APACHE_DOCUMENT_ROOT)==0 && dirname($newPath) != "." && !is_dir($newPath))
				{
					if(mkdir($newPath) == FALSE)
					{
						$message = '<span style="color:#ff0000;"> Fail to create directory: ' . $newPath . '</span>';
					}

					if($message == "&nbsp;")
					{
						if(mkdir($newPath.$DIR_SEPARATOR."tmp") == FALSE)
						{
							rmdir($newPath);
							$message = '<span style="color:#ff0000;"> Fail to create directory: ' . $newPath . $DIR_SEPARATOR . 'tmp</span>';
						}
					}
				}
				else
				{
					$message = '<span style="color:#ff0000;"> Error: Illegal path (' . $newPath . ')</span>';
				}
			}

			if($message == "&nbsp;" && $sqlStr != "")
			{
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($sqlParams);

				$tmp = $stmt->errorInfo();
				$message = $tmp[2];

				if($message == "")
				{
					$message = '<span style="color:#ff0000;">';

					switch($mode)
					{
						case 'add'          : $message .= 'New setting was successfully added.'; break;
						case 'changeCurrent': $message .= 'The current storage was successfully changed.'; break;
						case 'delete'       : $message .= 'The selected setting (ID=' . $newStorageID . ') was successfully deleted.'; break;
					}
					$message .= '</span>';

					if($mode == 'add' || $mode =='changeCurrent')
					{
						$restartButtonFlg = 1;
					}

					//--------------------------------------------------------------------------------------------
					// Modify httpd-aliases.conf
					//--------------------------------------------------------------------------------------------
					if($mode == 'add')
					{
						$newPath = str_replace("\\", "/", stripslashes($newPath));

						$fp = fopen($apacheAliasFname, "a");

						fprintf($fp, "\r\nAlias /CIRCUS-CS/%s \"%s/\"\r\n\r\n", $newAlias, $newPath);
						fprintf($fp, "<Directory \"%s/\">\r\n", $newPath);
						fprintf($fp, "\tOptions Indexes MultiViews\r\n");
						fprintf($fp, "\tAllowOverride None\r\n");
						fprintf($fp, "\tOrder allow,deny\r\n");
						fprintf($fp, "\tAllow from all\r\n");
						fprintf($fp, "</Directory>\r\n");

						fclose($fp);
					}
					else if($mode == 'delete')
					{
						$srcData = file($apacheAliasFname);
						$dstData = array();

						$alias = "/CIRCUS-CS/store" . $newStorageID . "/";

						for($i = 0; $i < count($srcData); $i++)
						{
							if(substr_count($srcData[$i], $alias)>=1)
							{
								$i += 8;
							}
							else
							{
								array_push($dstData, $srcData[$i]);
								$count++;
							}
						}

						file_put_contents($apacheAliasFname, $dstData);

						unset($srcData);
						unset($dstData);
					}
					//--------------------------------------------------------------------------------------------
				}
				else $message = '<span style="color:#ff0000;">' . $message . '</span>';
			}

			//----------------------------------------------------------------------------------------------------

			//----------------------------------------------------------------------------------------------------
			// Make one-time ticket
			//----------------------------------------------------------------------------------------------------
			$_SESSION['ticket'] = md5(uniqid().mt_rand());
			//----------------------------------------------------------------------------------------------------

			//----------------------------------------------------------------------------------------------------
			// Retrieve storage list
			//----------------------------------------------------------------------------------------------------
			$sqlStr = "SELECT storage_id, path, apache_alias, type, current_use"
					. " FROM storage_master ORDER BY storage_id ASC;";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute();

			$storageList = $stmt->fetchAll(PDO::FETCH_NUM);

			$oldDicomID = 0;
			$oldResearchID = 0;

			foreach($storageList as $item)
			{
				if($item[4]==true)
				{
					if($item[3] ==1)      $oldDicomID    = $item[0];
					else if($item[3] ==2) $oldResearchID = $item[0];
				}
			}
			//---------------------------------------------------------------------------------------------------

			//------------------------------------------------------------------------------------------------
			// Settings for Smarty
			//------------------------------------------------------------------------------------------------
			$smarty = new SmartyEx();

			$smarty->assign('params',        $params);
			$smarty->assign('message',       $message);
			$smarty->assign('storageList',   $storageList);
			$smarty->assign('oldDicomID',    $oldDicomID);
			$smarty->assign('oldResearchID', $oldResearchID);

			$smarty->assign('restartButtonFlg', $restartButtonFlg);

			$smarty->assign('ticket',  rawurlencode($_SESSION['ticket']));

			$smarty->display('administration/data_storage_config.tpl');
			//------------------------------------------------------------------------------------------------
		}
		catch (PDOException $e)
		{
			var_dump($e->getMessage());
		}

		$pdo = null;
	}
?>
