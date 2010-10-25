<?php

	session_start();
	
	include("../common.php");
	include("auto_logout_administration.php");
		
	if($_SESSION['serverOperationFlg']==1 || $_SESSION['serverSettingsFlg']==1)
	{
		$params = array('toTopDir' => "../",
		                'message'  => "&nbsp;");	
	
		//--------------------------------------------------------------------------------------------------------------
		// Import $_REQUEST variables
		//--------------------------------------------------------------------------------------------------------------
		$mode = (isset($_REQUEST['mode']) && ($_SESSION['ticket'] == $_REQUEST['ticket'])) ? $_REQUEST['mode'] : "";
		$oldUserID         = (isset($_REQUEST['oldUserID']))        ? $_REQUEST['oldUserID']        : "";
		$newUserID         = (isset($_REQUEST['newUserID']))        ? $_REQUEST['newUserID']        : "";
		$newUserName       = (isset($_REQUEST['newUserName']))      ? $_REQUEST['newUserName']      : "";
		$newPassword       = (isset($_REQUEST['newPassword']))      ? $_REQUEST['newPassword']      : "";
		$newGroupID        = (isset($_REQUEST['newGroupID']))       ? $_REQUEST['newGroupID']       : "";
		$newTodayDisp      = (isset($_REQUEST['newTodayDisp']))     ? $_REQUEST['newTodayDisp']     : "";
		$newDarkroomFlg    = (isset($_REQUEST['newDarkroomFlg']))   ? $_REQUEST['newDarkroomFlg']   : "";
		$newAnonymizeFlg   = (isset($_REQUEST['newAnonymizeFlg']))  ? $_REQUEST['newAnonymizeFlg']  : "";
		$newLatestResults  = (isset($_REQUEST['newLatestResults'])) ? $_REQUEST['newLatestResults'] : "";
		//--------------------------------------------------------------------------------------------------------------

		$longinUser = $_SESSION['userID'];

		try
		{	
			// Connect to SQL Server
			$pdo = new PDO($connStrPDO);

			//----------------------------------------------------------------------------------------------------
			// Add / Update / Delete user
			//----------------------------------------------------------------------------------------------------
			$sqlStr = "";
			$sqlParams = array();
			
			if(($mode == 'delete' && $newUserID != $longinUser) || $mode ='update')	// delete user
			{
				$sqlStr = "DELETE FROM users WHERE user_id=?;";
				$sqlParams[] = $newUserID;
			}

			if(($mode == 'add' && $newUserID != "" && $newPassword != "" ) || $mode == 'update')
			{
				$sqlStr  .= "INSERT INTO users(user_id, user_name, passcode, group_id, today_disp, darkroom_flg, "
				         .  " anonymize_flg, latest_results) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
						 
				$sqlParams[] = $newUserID;
				$sqlParams[] = $newUserName;
				$sqlParams[] = md5($newPassword);
				$sqlParams[] = $newGroupID;
				$sqlParams[] = $newTodayDisp;
				$sqlParams[] = $newDarkroomFlg;
				$sqlParams[] = $newAnonymizeFlg;
				$sqlParams[] = $newLatestResults;
			}

			if($mode ='update' && $oldUserID == $longinUser && $newUserID != $oldUserID)
			{
				$sqlStr = "";
				$params['message'] = "You can't change own user ID (" . $longinUser . " -> " . $newUserID . ")";
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
						case 'add'    :  $params['message'] .= '"' . $newUserID . '" was successfully added.'; break;
						case 'update' :  $params['message'] .= '"' . $oldUserID . '" was successfully updated.'; break;
						case 'delete' :  $params['message'] .= '"' . $newUserID . '" was successfully deleted.'; break;
					}
					$params['message'] .= '</span>';
				}
				else
				{
					$params['message'] = '<span style="color:#ff0000;">Fail to ' . $mode . '"';
					
					if($mode == 'update')
					{
						$params['message'] .= $oldUserID;
					}
					else
					{
						$params['message'] .= $newUserID;
					}
				}
			}
			else $params['message'] = '<span style="color: #ff0000;">' . $params['message'] . '</span>';
		
			//------------------------------------------------------------------------------------------------

			//------------------------------------------------------------------------------------------------
			// Make one-time ticket
			//------------------------------------------------------------------------------------------------
			$_SESSION['ticket'] = md5(uniqid().mt_rand());
			//------------------------------------------------------------------------------------------------

			//------------------------------------------------------------------------------------------------
			// Retrieve user lists
			//------------------------------------------------------------------------------------------------
			$sqlStr = "SELECT user_id, user_name, group_id, today_disp, darkroom_flg, anonymize_flg,"
					. " latest_results, passcode FROM users ORDER BY user_id ASC";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute();
			
			$userList = $stmt->fetchAll(PDO::FETCH_NUM);
			//------------------------------------------------------------------------------------------------

			//------------------------------------------------------------------------------------------------
			// Retrieve group lists
			//------------------------------------------------------------------------------------------------
			$sqlStr = "SELECT group_id FROM groups ORDER BY group_id ASC";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute();
			
			$groupList = $stmt->fetchAll(PDO::FETCH_NUM);
			//------------------------------------------------------------------------------------------------

			//------------------------------------------------------------------------------------------------
			// Settings for Smarty
			//------------------------------------------------------------------------------------------------
			require_once('../smarty/SmartyEx.class.php');
			$smarty = new SmartyEx();	

			$smarty->assign('params',    $params);
			$smarty->assign('userList',  $userList);
			$smarty->assign('groupList', $groupList);
			
			$smarty->assign('ticket',    $_SESSION['ticket']);
	
			$smarty->display('administration/user_config.tpl');
			//------------------------------------------------------------------------------------------------
		}
		catch (PDOException $e)
		{
			var_dump($e->getMessage());
		}

		$pdo = null;
	}

?>