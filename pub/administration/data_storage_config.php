<?php
	include("../common.php");
	Auth::checkSession();
	Auth::purgeUnlessGranted(Auth::SERVER_OPERATION);

	if($_SESSION['serverSettingsFlg']==1)
	{
		//--------------------------------------------------------------------------------------------------------------
		// Import $_REQUEST variables
		//--------------------------------------------------------------------------------------------------------------
		$mode = (isset($_REQUEST['mode']) && ($_SESSION['ticket'] == $_REQUEST['ticket'])) ? $_REQUEST['mode'] : "";
		$newStorageID  = (isset($_REQUEST['newStorageID'])) ? $_REQUEST['newStorageID'] : "";
		$newPath       = (isset($_REQUEST['newPath']))      ? $_REQUEST['newPath']      : "";
		$newType       = (isset($_REQUEST['newType']))      ? $_REQUEST['newType']      : "";

		$oldDicomID  = (isset($_REQUEST['oldDicomID']) && is_numeric($_REQUEST['oldDicomID'])) ? $_REQUEST['oldDicomID'] : 0;
		$oldResultID = (isset($_REQUEST['oldResultID']) && is_numeric($_REQUEST['oldResultID'])) ? $_REQUEST['oldResultID'] : 0;
		$newDicomID  = (isset($_REQUEST['newDicomID']) && is_numeric($_REQUEST['newDicomID'])) ? $_REQUEST['newDicomID'] : 0;
		$newResultID = (isset($_REQUEST['newResultID']) && is_numeric($_REQUEST['newResultID'])) ? $_REQUEST['newResultID'] : 0;
		$oldCacheID = (isset($_REQUEST['oldCacheID']) && is_numeric($_REQUEST['oldCacheID'])) ? $_REQUEST['oldCacheID'] : 0;
		$newCacheID = (isset($_REQUEST['newCacheID']) && is_numeric($_REQUEST['newCacheID'])) ? $_REQUEST['newCacheID'] : 0;
		//--------------------------------------------------------------------------------------------------------------

		$params = array('toTopDir' => "../");

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

					//echo $newStorageID;
					$sqlStr = "INSERT INTO storage_master(storage_id, path, current_use, type)"
							. " VALUES (currval('storage_master_storage_id_seq'), ?, ?, ?)";

					$sqlParams[] = $newPath;
					$sqlParams[] = $currentUse;
					$sqlParams[] = $newType;
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
					$sqlStr = "SELECT COUNT(*) FROM executed_plugin_list WHERE status > 0 AND storage_id=?";
				}

				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindParam(1, $newStorageID);
				$stmt->execute();

				if($stmt->fetchColumn()>0)
				{
					$message = '<span style="color:#ff0000;">Storage ' . $newStorageID . ' was already stored.</span>';
				}
				else
				{
					$stmt = $pdo->prepare("SELECT current_use FROM storage_master WHERE storage_id=?");
					$stmt->bindParam(1, $newStorageID);
					$stmt->execute();

					if($stmt->fetchColumn() == 't')
					{
						$message = '<span style="color:#ff0000;"> Error: storage ID ' . $newStorageID
								 . ' is set as current use.</span>';
					}
					else
					{
						$sqlStr = "DELETE FROM storage_master WHERE storage_id=?";
						$sqlParams[] = $newStorageID;
					}
				}
			}
			else if($mode == 'changeCurrent')
			{
				if($oldDicomID != 0 && $newDicomID != 0 && $oldDicomID != $newDicomID)
				{
					$stmt = $pdo->prepare("UPDATE storage_master SET current_use='f' WHERE type=1");
					$stmt->execute();

					$sqlStr = "UPDATE storage_master SET current_use='t' WHERE storage_id=?";
					$sqlParams[] = $newDicomID;

				}

				if($oldResultID != 0 && $newResultID != 0 && $oldResultID != $newResultID)
				{
					$stmt = $pdo->prepare("UPDATE storage_master SET current_use='f' WHERE type=2");
					$stmt->execute();

					$sqlStr = "UPDATE storage_master SET current_use='t' WHERE storage_id=?";
					$sqlParams[] = $newResultID;
				}

				if($oldCacheID != 0 && $newCacheID != 0 && $oldCacheID != $newCacheID)
				{
					$stmt = $pdo->prepare("UPDATE storage_master SET current_use='f' WHERE type=3");
					$stmt->execute();

					$sqlStr = "UPDATE storage_master SET current_use='t' WHERE storage_id=?";
					$sqlParams[] = $newCacheID;
				}
			}

			if($mode == 'add')
			{
				$newPath = (realpath($newPath) == "") ? $newPath : realpath($newPath);

				if(dirname($newPath) != "." && !is_dir($newPath))
				{
					if(mkdir($newPath) == FALSE)
					{
						$message = '<span style="color:#ff0000;"> Fail to create directory: ' . $newPath . '</span>';
					}

					if($newType == 1 && $message == "&nbsp;")
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
					$message = '<span style="color:#0000ff;">';

					switch($mode)
					{
						case 'add':
							$message .= 'New setting was successfully added.';
							break;

						case 'delete':
							$message .= 'The selected setting (ID=' . $newStorageID . ') was successfully deleted.';
							break;

						case 'changeCurrent':
							$message .= 'The current storage was successfully changed.';
							break;
					}
					$message .= '</span>';

					//--------------------------------------------------------------------------------------------
					// Modify storage.json
					//--------------------------------------------------------------------------------------------
					if($mode == 'add' || $mode == 'delete')
					{
						$sqlStr = "SELECT storage_id, path FROM storage_master ORDER BY storage_id ASC";
						$tmpList = DBConnector::query($sqlStr, null, 'ALL_NUM');

						$storageList = array();

						foreach($tmpList as $item)
						{
							$storageList[$item[0]] = $item[1];
						}

						file_put_contents("../../config/storage.json", json_encode($storageList));
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
			$sqlStr = "SELECT storage_id, path, type, current_use"
					. " FROM storage_master ORDER BY storage_id ASC;";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute();

			$storageList = $stmt->fetchAll(PDO::FETCH_NUM);

			$oldDicomID = 0;
			$oldResultID = 0;

			foreach($storageList as $item)
			{
				if($item[3]==true)
				{
					if($item[2] ==1)      $oldDicomID  = $item[0];
					else if($item[2] ==2) $oldResultID = $item[0];
					else if($item[2] ==3) $oldCacheID  = $item[0];
				}
			}
			//---------------------------------------------------------------------------------------------------

			//------------------------------------------------------------------------------------------------
			// Settings for Smarty
			//------------------------------------------------------------------------------------------------
			$smarty = new SmartyEx();

			$smarty->assign('params',       $params);
			$smarty->assign('message',      $message);
			$smarty->assign('storageList',  $storageList);
			$smarty->assign('oldDicomID',   $oldDicomID);
			$smarty->assign('oldResultID',  $oldResultID);
			$smarty->assign('oldCacheID',   $oldCacheID);

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
