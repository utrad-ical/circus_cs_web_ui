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
		$mode = (isset($_REQUEST['mode']) && ($_SESSION['ticket'] == $_REQUEST['ticket'])) ? $_REQUEST['mode'] : "";
		$oldUserID         = (isset($_REQUEST['oldUserID']))        ? $_REQUEST['oldUserID']        : "";
		$oldUserName       = (isset($_REQUEST['oldUserName']))      ? $_REQUEST['oldUserName']      : "";
		$oldPassword       = (isset($_REQUEST['oldPassword']))      ? $_REQUEST['oldPassword']      : "";
		$oldGroupID        = (isset($_REQUEST['oldGroupID']))       ? $_REQUEST['oldGroupID']       : "";
		$oldTodayDisp      = (isset($_REQUEST['oldTodayDisp']))     ? $_REQUEST['oldTodayDisp']     : "";
		$oldDarkroomFlg    = (isset($_REQUEST['oldDarkroomFlg']))   ? $_REQUEST['oldDarkroomFlg']   : "";
		$oldAnonymizeFlg   = (isset($_REQUEST['oldAnonymizeFlg']))  ? $_REQUEST['oldAnonymizeFlg']  : "";
		$oldLatestResults  = (isset($_REQUEST['oldLatestResults'])) ? $_REQUEST['oldLatestResults'] : "";
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
			$message = "&nbsp;";
			$sqlStr = "";
			$sqlParams = array();
		
			if($mode == 'add')
			{
				$sqlStr  = "INSERT INTO users(user_id, user_name, passcode, group_id, today_disp, darkroom_flg, "
				         . " anonymize_flg, latest_results) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
						 
				$sqlParams[0] = $newUserID;
				$sqlParams[1] = $newUserName;
				$sqlParams[2] = md5($newPassword);
				$sqlParams[3] = $newGroupID;
				$sqlParams[4] = $newTodayDisp;
				$sqlParams[5] = $newDarkroomFlg;
				$sqlParams[6] = $newAnonymizeFlg;
				$sqlParams[7] = $newLatestResults;
			
				if($newUserID == "" || $newPassword == "")  $sqlStr = "";
			}
			else if($mode == 'update') // update user config
			{
				$updateCnt = 0;
			
				$sqlStr = 'UPDATE users SET ';
				if($oldUserID == $longinUser && $newUserID != $oldUserID)
				{
					$msg = "You can't change your user ID (" . $longinUser . " -> " . $newUserID . ")";
				}
				else if($newUserID != $oldUserID)
				{
					$sqlStr .= "user_id=?";
					$sqlParams[$updateCnt] = $newUserID;
					$updateCnt++;
				}

				if($msg == "")
				{
					if($oldUserName != $newUserName)
					{
						if($updateCnt > 0)	$sqlStr .= ",";
						$sqlStr .= "user_name=?";
						$sqlParams[$updateCnt] = $newUserName;
						$updateCnt++;
					}

					if($oldPassword != $newPassword && $oldPassword != md5($newPassword))
					{
						if($updateCnt > 0)	$sqlStr .= ",";
						$sqlStr .= "passcode=?";
						$sqlParams[$updateCnt] = md5($newPassword);
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
						$sqlParams[$updateCnt] = $newGroupID;
						$updateCnt++;
					}

					if($oldTodayDisp != $newTodayDisp)
					{
						if($updateCnt > 0)	$sqlStr .= ",";
						$sqlStr .= "today_disp=?";
						$sqlParams[$updateCnt] = $newTodayDisp;
						$updateCnt++;
					}
					
					if($oldDarkroomFlg != $newDarkroomFlg)
					{
						if($updateCnt > 0)	$sqlStr .= ",";
						$sqlStr .= "darkroom_flg=?";
						$sqlParams[$updateCnt] = $newDarkroomFlg;
						$updateCnt++;
					}

					if($oldAnonymizeFlg != $newAnonymizeFlg)
					{
						if($updateCnt > 0)	$sqlStr .= ",";
						$sqlStr .= "anonymize_flg=?";
						$sqlParams[$updateCnt] = $newAnonymizeFlg;
						$updateCnt++;
					}

					if($oldLatestResults != $newLatestResults)
					{
						if($updateCnt > 0)	$sqlStr .= ",";
						$sqlStr .= "latest_results=?";
						$sqlParams[$updateCnt] = $newLatestResults;
						$updateCnt++;
					}
				
					$sqlStr .= " WHERE user_id=?";
					$sqlParams[$updateCnt] = $oldUserID;

					if($updateCnt == 0)  $sqlStr  = "";
				}
			}
			else if($mode == 'delete')	// delete user
			{
				if($newUserID == $longinUser)
				{
					$message = "You can't delete own user ID (" . $longinUser . ")";
				}
				else if($newUserID != "")
				{
					$sqlStr = "DELETE FROM users WHERE user_id=?";
					$sqlParams[0] = $newUserID;
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
					$message = '<span style="color: #0000ff;" >';
					
					switch($mode)
					{
						case 'add'    :  $message .= '"' . $newUserID . '" was successfully added.'; break;
						case 'update' :  $message .= '"' . $oldUserID . '" was successfully updated.'; break;
						case 'delete' :  $message .= '"' . $newUserID . '" was successfully deleted.'; break;
					}
					$message .= '</span>';
				}
				else $message = '<span style="color: #ff0000;">' . $message . '</span>';
			}
			else $message = '<span style="color: #ff0000;">' . $message . '</span>';
		
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

			$smarty->assign('param',     $param);
			$smarty->assign('message',   $message);
			$smarty->assign('userList',  $userList);
			$smarty->assign('groupList', $groupList);
			
			$smarty->assign('ticket',   htmlspecialchars($_SESSION['ticket'], ENT_QUOTES));
	
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