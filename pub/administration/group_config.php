<?php
	include("../common.php");
	Auth::checkSession();
	Auth::purgeUnlessGranted(AUTH::SERVER_SETTINGS);

	if($_SESSION['serverSettingsFlg']==1)
	{
		$params = array('toTopDir' => "../",
		                'message'  => "&nbsp;");

		//--------------------------------------------------------------------------------------------------------------
		// Import $_REQUEST variables
		//--------------------------------------------------------------------------------------------------------------
		$mode                = (isset($_REQUEST['mode']))                ? $_REQUEST['mode']                : "";
		$oldGroupID          = (isset($_REQUEST['oldGroupID']))          ? $_REQUEST['oldGroupID']          : "";
		$newGroupID          = (isset($_REQUEST['newGroupID']))          ? $_REQUEST['newGroupID']          : "";
		$newColorSet         = (isset($_REQUEST['newColorSet']))         ? $_REQUEST['newColorSet']         : "";
		$newExecCAD          = (isset($_REQUEST['newExecCAD']))          ? $_REQUEST['newExecCAD']          : "";
		$newPersonalFB       = (isset($_REQUEST['newPersonalFB']))       ? $_REQUEST['newPersonalFB']       : "";
		$newConsensualFB     = (isset($_REQUEST['newConsensualFB']))     ? $_REQUEST['newConsensualFB']     : "";
		$newModifyConsensual = (isset($_REQUEST['newModifyConsensual'])) ? $_REQUEST['newModifyConsensual'] : "";
		$newAllStatistics    = (isset($_REQUEST['newAllStatistics']))    ? $_REQUEST['newAllStatistics']    : "";
		$newResearchShow     = (isset($_REQUEST['newResearchShow']))     ? $_REQUEST['newResearchShow']     : "";
		$newResearchExec     = (isset($_REQUEST['newResearchExec']))     ? $_REQUEST['newResearchExec']     : "";
		$newVolumeDL         = (isset($_REQUEST['newVolumeDL']))         ? $_REQUEST['newVolumeDL']         : "";
		$newAnonymized       = (isset($_REQUEST['newAnonymized']))       ? $_REQUEST['newAnonymized']       : "";
		$newDataDelete       = (isset($_REQUEST['newDataDelete']))       ? $_REQUEST['newDataDelete']       : "";
		$newServerOperation  = (isset($_REQUEST['newServerOperation']))  ? $_REQUEST['newServerOperation']  : "";
		$newServerSettings   = (isset($_REQUEST['newServerSettings']))   ? $_REQUEST['newServerSettings']   : "";
		//--------------------------------------------------------------------------------------------------------------

		try
		{
			// Connect to SQL Server
			$pdo = DBConnector::getConnection();

			//---------------------------------------------------------------------------------------------------------
			// Add / Update / Delete group
			//---------------------------------------------------------------------------------------------------------
			$sqlStr = "";
			$sqlParams = array();

			if($mode == 'delete')
			{
				if($oldGroupID == "admin")
				{
					$params['message'] = '<span style="color:#ff0000;">You can\'t delete <b>admin</b> group.</span>';
				}
				else
				{
					$sqlStr = "DELETE FROM groups WHERE group_id=?;";
					$sqlParams[] = $oldGroupID;
				}
			}
			else if(($mode == 'add' || $mode == 'update') && $newGroupID != "")
			{
				$sqlParams[]  = $newGroupID;
				$sqlParams[]  = $newColorSet;
				$sqlParams[]  = $newExecCAD;
				$sqlParams[]  = $newPersonalFB;
				$sqlParams[]  = $newConsensualFB;
				$sqlParams[]  = $newModifyConsensual;
				$sqlParams[]  = $newAllStatistics;
				$sqlParams[]  = $newResearchShow;
				$sqlParams[]  = $newResearchExec;
				$sqlParams[]  = $newVolumeDL;
				$sqlParams[]  = $newAnonymized;
				$sqlParams[]  = $newDataDelete;
				$sqlParams[]  = $newServerOperation;
				$sqlParams[]  = $newServerSettings;

				if($mode == 'add')
				{
					$sqlStr .= 'INSERT INTO groups(group_id, color_set, exec_cad, personal_feedback, consensual_feedback,'
							.  ' modify_consensual, view_all_statistics, research_show, research_exec, volume_download,'
							.  ' anonymized, data_delete, server_operation, server_settings)'
							.  ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
				}
				else if($mode == 'update')
				{
					if($oldGroupID == "admin")
					{
						$params['message'] = '<span style="color:#ff0000;">You can\'t change setting of '
										   .  '<b>admin</b> group.</span>';
					}
					else
					{
						$sqlStr = 'UPDATE groups SET group_id=?, color_set=?, exec_cad=?, personal_feedback=?,'
								. ' consensual_feedback=?, modify_consensual=?, view_all_statistics=?,'
								. ' research_show=?, research_exec=?, volume_download=?, anonymized=?,'
								. ' data_delete=?, server_operation=?, server_settings=? WHERE group_id=?';
						$sqlParams[]  = $oldGroupID;
					}
				}
			}

			if($params['message'] == "&nbsp;" && $sqlStr != "")
			{
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($sqlParams);
				$errorMessage = $stmt->errorInfo();

				if($errorMessage[2] == "")
				{
					$params['message'] = '<span style="color: #0000ff;" >';

					switch($mode)
					{
						case 'add'    :  $params['message'] .= '"' . $newGroupID . '" was successfully added.'; break;
						case 'update' :  $params['message'] .= '"' . $oldGroupID . '" was successfully updated.'; break;
						case 'delete' :  $params['message'] .= '"' . $newGroupID . '" was successfully deleted.'; break;
					}
					$params['message'] .= '</span>';
				}
				else
				{
					$params['message'] = '<span style="color:#ff0000;">Fail to ' . $mode . '"';

					if($mode == 'update')
					{
						$params['message'] .= $oldGroupID;
					}
					else
					{
						$params['message'] .= $newGroupID;
					}
					$params['message'] .= '"</span>';
				}
			}

			//----------------------------------------------------------------------------------------------------------

			//----------------------------------------------------------------------------------------------------------
			// Make one-time ticket
			//----------------------------------------------------------------------------------------------------------
			$_SESSION['ticket'] = md5(uniqid().mt_rand());
			//----------------------------------------------------------------------------------------------------------

			//----------------------------------------------------------------------------------------------------------
			// Retrieve group lists
			//----------------------------------------------------------------------------------------------------------
			$sqlStr = 'SELECT group_id, color_set, exec_cad, personal_feedback, consensual_feedback,'
					. ' modify_consensual, view_all_statistics, research_show, research_exec, volume_download,'
					. ' anonymized, data_delete, server_operation, server_settings, '
					. 'cast(exec_cad as integer)+cast(personal_feedback as integer)+cast(consensual_feedback as integer)'
					. '+cast(modify_consensual as integer)+cast(view_all_statistics as integer)'
					. '+cast(research_show as integer)+cast(research_exec as integer)+cast(volume_download as integer)'
					. '+cast(anonymized as integer)+cast(data_delete as integer)'
					. '+cast(server_operation as integer)+cast(server_settings as integer) as true_cnt'
					. ' FROM groups ORDER BY true_cnt DESC';

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute();

			$groupList = $stmt->fetchAll(PDO::FETCH_NUM);
			//----------------------------------------------------------------------------------------------------------

			//----------------------------------------------------------------------------------------------------------
			// Settings for Smarty
			//----------------------------------------------------------------------------------------------------------
			$smarty = new SmartyEx();

			$smarty->assign('params',    $params);
			$smarty->assign('groupList', $groupList);
			$smarty->assign('ticket',    $_SESSION['ticket']);

			$smarty->display('administration/group_config.tpl');
			//----------------------------------------------------------------------------------------------------------
		}
		catch (PDOException $e)
		{
			var_dump($e->getMessage());
		}

		$pdo = null;
	}
?>
