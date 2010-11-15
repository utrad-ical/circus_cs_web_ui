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
		$mode = (isset($_REQUEST['mode']) && ($_SESSION['ticket'] == $_GET['ticket'])) ? $_GET['mode'] : "";
		$oldUserID         = (isset($_GET['oldUserID']))        ? $_GET['oldUserID']        : "";
		$oldUserName       = (isset($_GET['oldUserName']))      ? $_GET['oldUserName']      : "";
		$oldPassword       = (isset($_GET['oldPassword']))      ? $_GET['oldPassword']      : "";
		$oldGroupID        = (isset($_GET['oldGroupID']))       ? $_GET['oldGroupID']       : "";
		$oldTodayDisp      = (isset($_GET['oldTodayDisp']))     ? $_GET['oldTodayDisp']     : "";
		$oldDarkroomFlg    = (isset($_GET['oldDarkroomFlg']))   ? $_GET['oldDarkroomFlg']   : "";
		$oldAnonymizeFlg   = (isset($_GET['oldAnonymizeFlg']))  ? $_GET['oldAnonymizeFlg']  : "";
		$oldLatestResults  = (isset($_GET['oldLatestResults'])) ? $_GET['oldLatestResults'] : "";
		$newUserID         = (isset($_GET['newUserID']))        ? $_GET['newUserID']        : "";
		$newUserName       = (isset($_GET['newUserName']))      ? $_GET['newUserName']      : "";
		$newPassword       = (isset($_GET['newPassword']))      ? $_GET['newPassword']      : "";
		$newGroupID        = (isset($_GET['newGroupID']))       ? $_GET['newGroupID']       : "";
		$newTodayDisp      = (isset($_GET['newTodayDisp']))     ? $_GET['newTodayDisp']     : "";
		$newDarkroomFlg    = (isset($_GET['newDarkroomFlg']))   ? $_GET['newDarkroomFlg']   : "";
		$newAnonymizeFlg   = (isset($_GET['newAnonymizeFlg']))  ? $_GET['newAnonymizeFlg']  : "";
		$newLatestResults  = (isset($_GET['newLatestResults'])) ? $_GET['newLatestResults'] : "";
		//--------------------------------------------------------------------------------------------------------------

		$longinUser = $_SESSION['userID'];

		try
		{	
			// Connect to SQL Server
			$pdo = new PDO($connStrPDO);

			//----------------------------------------------------------------------------------------------------
			// Add / Update / Delete user
			//----------------------------------------------------------------------------------------------------
			if($mode != "")
			{
				$sqlStr = "";
				$sqlParams = array();
				
				if($mode == 'add')
				{
					$sqlStr  = "INSERT INTO users(user_id, user_name, passcode, group_id, today_disp, darkroom_flg, "
					         . " anonymize_flg, latest_results) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
						 
					$sqlParams[] = $newUserID;
					$sqlParams[] = $newUserName;
					$sqlParams[] = md5($newPassword);
					$sqlParams[] = $newGroupID;
					$sqlParams[] = $newTodayDisp;
					$sqlParams[] = $newDarkroomFlg;
					$sqlParams[] = $newAnonymizeFlg;
					$sqlParams[] = $newLatestResults;
			
					if($newUserID == "" || $newPassword == "")  $sqlStr = "";
				}
				else if($mode == 'update') // update user config
				{
					$updateCnt = 0;
				
					$sqlStr = 'UPDATE users SET ';
					if($oldUserID == $longinUser && $newUserID != $oldUserID)
					{
						$sqlStr = "";
						$params['message'] = "You can't change own user ID (" . $longinUser . " -> " . $newUserID . ")";
					}
					else if($newUserID != $oldUserID)
					{
						$sqlStr .= "user_id=?";
						$sqlParams[] = $newUserID;
						$updateCnt++;
					}

					if($params['message'] == "&nbsp;")
					{
						if($oldUserName != $newUserName)
						{
							if($updateCnt > 0)	$sqlStr .= ",";
							$sqlStr .= "user_name=?";
							$sqlParams[] = $newUserName;
							$updateCnt++;
						}

						if($oldPassword != $newPassword && $oldPassword != md5($newPassword))
						{
							if($updateCnt > 0)	$sqlStr .= ",";
							$sqlStr .= "passcode=?";
							$sqlParams[] = md5($newPassword);
							$updateCnt++;
						}
	
						if($oldUserID == $longinUser && $oldGroupID != $newGroupID)
						{
							$msg = "You can't change your group ID (" . $oldGroupID . " -> " . $newGroupID . ")";
						}
						else if($oldGroupID != $newGroupID)
						{
							if($updateCnt > 0)	$sqlStr .= ",";
							$sqlStr .= "group_id=?";
							$sqlParams[] = $newGroupID;
							$updateCnt++;
						}
	
						if($oldTodayDisp != $newTodayDisp)
						{
							if($updateCnt > 0)	$sqlStr .= ",";
							$sqlStr .= "today_disp=?";
							$sqlParams[] = $newTodayDisp;
							$updateCnt++;
						}
						
						if($oldDarkroomFlg != $newDarkroomFlg)
						{
							if($updateCnt > 0)	$sqlStr .= ",";
							$sqlStr .= "darkroom_flg=?";
							$sqlParams[] = $newDarkroomFlg;
							$updateCnt++;
						}
	
						if($oldAnonymizeFlg != $newAnonymizeFlg)
						{
							if($updateCnt > 0)	$sqlStr .= ",";
							$sqlStr .= "anonymize_flg=?";
							$sqlParams[] = $newAnonymizeFlg;
							$updateCnt++;
						}
	
						if($oldLatestResults != $newLatestResults)
						{
							if($updateCnt > 0)	$sqlStr .= ",";
							$sqlStr .= "latest_results=?";
							$sqlParams[] = $newLatestResults;
							$updateCnt++;
						}
					
						$sqlStr .= " WHERE user_id=?";
						$sqlParams[] = $oldUserID;
	
						if($updateCnt == 0)  $sqlStr  = "";
					}
				}
				else if($mode == 'delete')	// delete user
				{
					if($newUserID == $longinUser)
					{
						$params['message'] = "You can't delete own user ID (" . $longinUser . ")";
					}
					else if($newUserID != "")
					{
						$sqlStr = "DELETE FROM users WHERE user_id=?";
						$sqlParams[0] = $newUserID;
					}
				}
		
				if($params['message'] == "&nbsp;" && $sqlStr != "")
				{
					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute($sqlParams);
				
					$tmp = $stmt->errorInfo();
					$message = $tmp[2];

					if($stmt->errorCode() == '00000')
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
						$params['message'] = '<span style="color: #ff0000;">Fail to ' . $mode
						                   . 'user (userID:' . (($mode=='update') ? $oldUserID : $newUserID)
										   . '</span>';
					}
				}
				else $params['message'] = '<span style="color: #ff0000;">' . $params['message'] . '</span>';
			}
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