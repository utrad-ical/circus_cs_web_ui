<?php

	session_start();
	include("../common.php");

	//------------------------------------------------------------------------------------------------------------------
	// Auto logout
	//------------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'] || $_SESSION['superUserFlg'] == 0)
	{
		header('location: ../index.php?mode=timeout');
	}
	else
	{
		$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	}
	//------------------------------------------------------------------------------------------------------------------

	if($_SESSION['superUserFlg'])
	{
		$param = array('toTopDir' => "../");
	
		//--------------------------------------------------------------------------------------------------------------
		// Import $_REQUEST variables 
		//--------------------------------------------------------------------------------------------------------------
		$mode             = (isset($_REQUEST['mode']))             ? $_REQUEST['mode']             : "";
		$oldGroupID       = (isset($_REQUEST['oldGroupID']))       ? $_REQUEST['oldGroupID']       : "";
		$oldColorSet      = (isset($_REQUEST['oldColorSet']))      ? $_REQUEST['oldColorSet']      : "";
		$oldExecCAD       = (isset($_REQUEST['oldExecCAD']))       ? $_REQUEST['oldExecCAD']       : "";
		$oldPersonalFB    = (isset($_REQUEST['oldPersonalFB']))    ? $_REQUEST['oldPersonalFB']    : "";
		$oldConsensualFB  = (isset($_REQUEST['oldConsensualFB']))  ? $_REQUEST['oldConsensualFB']  : "";
		$oldAllStatistics = (isset($_REQUEST['oldAllStatistics'])) ? $_REQUEST['oldAllStatistics'] : "";
		$oldResearch      = (isset($_REQUEST['oldResearch']))      ? $_REQUEST['oldResearch']      : "";
		$oldVolumeDL      = (isset($_REQUEST['oldVolumeDL']))      ? $_REQUEST['oldVolumeDL']      : "";
		$oldAnonymizeFlg  = (isset($_REQUEST['oldAnonymizeFlg']))  ? $_REQUEST['oldAnonymizeFlg']  : "";
		$oldSuFlg         = (isset($_REQUEST['oldSuFlg']))         ? $_REQUEST['oldSuFlg']         : "";
		$newGroupID       = (isset($_REQUEST['newGroupID']))       ? $_REQUEST['newGroupID']       : "";
		$newColorSet      = (isset($_REQUEST['newColorSet']))      ? $_REQUEST['newColorSet']      : "";
		$newExecCAD       = (isset($_REQUEST['newExecCAD']))       ? $_REQUEST['newExecCAD']       : "";
		$newPersonalFB    = (isset($_REQUEST['newPersonalFB']))    ? $_REQUEST['newPersonalFB']    : "";
		$newConsensualFB  = (isset($_REQUEST['newConsensualFB']))  ? $_REQUEST['newConsensualFB']  : "";
		$newAllStatistics = (isset($_REQUEST['newAllStatistics'])) ? $_REQUEST['newAllStatistics'] : "";
		$newResearch      = (isset($_REQUEST['newResearch']))      ? $_REQUEST['newResearch']      : "";
		$newVolumeDL      = (isset($_REQUEST['newVolumeDL']))      ? $_REQUEST['newVolumeDL']      : "";
		$newAnonymizeFlg  = (isset($_REQUEST['newAnonymizeFlg']))  ? $_REQUEST['newAnonymizeFlg']  : "";
		$newSuFlg         = (isset($_REQUEST['newSuFlg']))         ? $_REQUEST['newSuFlg']         : "";
		//--------------------------------------------------------------------------------------------------------------

		try
		{	
			// Connect to SQL Server
			$pdo = new PDO($connStrPDO);

			//----------------------------------------------------------------------------------------------------
			// Add / Update / Delete group
			//----------------------------------------------------------------------------------------------------
			$message = "&nbsp;";
			$sqlStr = "";
			$sqlParams = array();
		
			if($mode == 'add' && $newGroupID != "")
			{
				$sqlStr = 'INSERT INTO groups(group_id, color_set, exec_cad, personal_feedback, consensual_feedback,'
						. ' view_all_statistics, research, volume_download, anonymize_flg, super_user)'
						. ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
						
				$sqlParams[0]  = $newGroupID;
				$sqlParams[1]  = $newColorSet;
				$sqlParams[2]  = $newExecCAD;
				$sqlParams[3]  = $newPersonalFB;
				$sqlParams[4]  = $newConsensualFB;
				$sqlParams[5]  = $newAllStatistics;
				$sqlParams[6]  = $newResearch;
				$sqlParams[7]  = $newVolumeDL;
				$sqlParams[8]  = $newAnonymizeFlg;
				$sqlParams[9]  = $newSuFlg;
			}
			else if($mode == 'update') // Update group
			{
				$updateCnt = 0;
				
				if($oldGroupID == "admin")	$msg = "You can't change setting of <b>admin</b> group.";
			
				if($msg == "")
				{
					$sqlStr = 'UPDATE groups SET ';
					if($newGroupID != $oldGroupID)
					{
						$sqlStr .= "group_id=?";
						$sqlParams[$updateCnt] = $newGroupID;
						$updateCnt++;
					}
	
					if($newColorSet != $oldColorSet)
					{
						if($updateCnt > 0)	$sqlStr .= ",";
						$sqlStr .= "color_set=?";
						$sqlParams[$updateCnt] = $newColorSet;
						$updateCnt++;
					}

					if($newExecCAD != $oldExecCAD)
					{
						if($updateCnt > 0)	$sqlStr .= ",";
						$sqlStr .= "exec_cad=?";
						$sqlParams[$updateCnt] = $newExecCAD;
						$updateCnt++;
					}
	
					if($newPersonalFB != $oldPersonalFB)
					{
						if($updateCnt > 0)	$sqlStr .= ",";
						$sqlStr .= "personal_feedback=?";
						$sqlParams[$updateCnt] = $newPersonalFB;
						$updateCnt++;
					}
	
					if($newConsensualFB != $oldConsensualFB)
					{
						if($updateCnt > 0)	$sqlStr .= ",";
						$sqlStr .= "consensual_feedback=?";
						$sqlParams[$updateCnt] = $newConsensualFB;
						$updateCnt++;
					}
	
					if($newAllStatistics != $oldAllStatistics)
					{
						if($updateCnt > 0)	$sqlStr .= ",";
						$sqlStr .= "view_all_statistics=?";
						$sqlParams[$updateCnt] = $newAllStatistics;
						$updateCnt++;
					}

					if($newResearch != $oldResearch)
					{
						if($updateCnt > 0)	$sqlStr .= ",";
						$sqlStr .= "research=?";
						$sqlParams[$updateCnt] = $newResearch;
						$updateCnt++;
					}
	
					if($newVolumeDL != $oldVolumeDL)
					{
						if($updateCnt > 0)	$sqlStr .= ",";
						$sqlStr .= "volume_download=?";
						$sqlParams[$updateCnt] = $newVolumeDL;
						$updateCnt++;
					}

					if($newAnonymizeFlg != $oldAnonymizeFlg)
					{
						if($updateCnt > 0)	$sqlStr .= ",";
						$sqlStr .= "anonymize_flg=?";
						$sqlParams[$updateCnt] = $newAnonymizeFlg;
						$updateCnt++;
					}

					if($newSuFlg != $oldSuFlg)
					{
						if($updateCnt > 0)	$sqlStr .= ",";
						$sqlStr .= "super_user=?";
						$sqlParams[$updateCnt] = $newSuFlg;
						$updateCnt++;
					}
	
					if($updateCnt > 0)
					{
						$sqlStr .= " WHERE group_id=?";
						$sqlParams[$updateCnt] = $oldGroupID;
					}
					else  $sqlStr = "";
				}
			}
			else if($mode == 'delete' && $newGroupID != "admin")	// Delete group
			{
				$sqlStr = "DELETE FROM groups WHERE group_id=?";
				$sqlParams[0] = $newGroupID;
			}
			
			if($message == "&nbsp;" && $sqlStr != "")
			{
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($sqlParams);
				
				$tmp = $stmt->errorInfo();
				$message = $tmp[2];			
				
				if($message == "")
				{
					$message = '<span style="color: #0000ff;" >';
				
					switch($mode)
					{
						case 'add'    :  $message .= '"' . $newGroupID . '" was successfully added.'; break;
						case 'update' :  $message .= '"' . $oldGroupID . '" was successfully updated.'; break;
						case 'delete' :  $message .= '"' . $newGroupID . '" was successfully deleted.'; break;
					}
					$message .= '</span>';
				}
				else $message = '<span style="color:#ff0000;">' . $message . '</span>';
			}
			else $message = '<span style="color:#ff0000;">' . $message . '</span>';
			
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
					. 'view_all_statistics, research, volume_download, anonymize_flg, super_user'
					. ' FROM groups ORDER BY group_id ASC';

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute();
			
			$groupList = $stmt->fetchAll(PDO::FETCH_NUM);
			//------------------------------------------------------------------------------------------------	
	
			//------------------------------------------------------------------------------------------------
			// Settings for Smarty
			//------------------------------------------------------------------------------------------------
			require_once('../smarty/SmartyEx.class.php');
			$smarty = new SmartyEx();	

			$smarty->assign('param',     $param);
			$smarty->assign('message',   $message);
			$smarty->assign('groupList', $groupList);
			
			$smarty->assign('ticket',   htmlspecialchars($_SESSION['ticket'], ENT_QUOTES));
	
			$smarty->display('administration/group_config.tpl');
			//------------------------------------------------------------------------------------------------

		}
		catch (PDOException $e)
		{
			var_dump($e->getMessage());
		}

		$pdo = null;
	}


?>


