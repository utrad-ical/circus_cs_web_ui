<?php
	include("../common.php");
	Auth::checkSession();
	Auth::purgeUnlessGranted(Auth::SERVER_OPERATION);

	if($_SESSION['serverOperationFlg']==1 || $_SESSION['serverSettingsFlg']==1)
	{
		$params = array('toTopDir' => "../",
		                'message'  => "&nbsp;");

		//--------------------------------------------------------------------------------------------------------------
		// Import $_REQUEST variables
		//--------------------------------------------------------------------------------------------------------------
		$mode = (isset($_REQUEST['mode']) && ($_SESSION['ticket'] == $_GET['ticket'])) ? $_GET['mode'] : "";
		$oldUserID     = (isset($_GET['oldUserID']))     ? $_GET['oldUserID']     : "";
		$oldUserName   = (isset($_GET['oldUserName']))   ? $_GET['oldUserName']   : "";
		$oldPassword   = (isset($_GET['oldPassword']))   ? $_GET['oldPassword']   : "";
		$oldTodayDisp  = (isset($_GET['oldTodayDisp']))  ? $_GET['oldTodayDisp']  : "";
		$oldDarkroom   = (isset($_GET['oldDarkroom']))   ? $_GET['oldDarkroom']   : "";
		$oldAnonymized = (isset($_GET['oldAnonymized'])) ? $_GET['oldAnonymized'] : "";
		$oldShowMissed = (isset($_GET['oldShowMissed'])) ? $_GET['oldShowMissed'] : "";
		$newUserID     = (isset($_GET['newUserID']))     ? $_GET['newUserID']     : "";
		$newUserName   = (isset($_GET['newUserName']))   ? $_GET['newUserName']   : "";
		$newPassword   = (isset($_GET['newPassword']))   ? $_GET['newPassword']   : "";
		$newGroupID    = (isset($_GET['newGroupID']))    ? $_GET['newGroupID']    : "";
		$newTodayDisp  = (isset($_GET['newTodayDisp']))  ? $_GET['newTodayDisp']  : "";
		$newDarkroom   = (isset($_GET['newDarkroom']))   ? $_GET['newDarkroom']   : "";
		$newAnonymized = (isset($_GET['newAnonymized'])) ? $_GET['newAnonymized'] : "";
		$newShowMissed = (isset($_GET['newShowMissed'])) ? $_GET['newShowMissed'] : "";
		//--------------------------------------------------------------------------------------------------------------

		$longinUser = $_SESSION['userID'];

		try
		{
			// Connect to SQL Server
			$pdo = DBConnector::getConnection();

			//----------------------------------------------------------------------------------------------------
			// Add / Update / Delete user
			//----------------------------------------------------------------------------------------------------
			if($mode!="" && $oldUserID!=$DEFAULT_CAD_PREF_USER && $newUsewrID!=$DEFAULT_CAD_PREF_USER)
			{
				$sqlStr = "";
				$sqlParams = array();

				if($mode == 'add')
				{
					$sqlStr  = "INSERT INTO users(user_id, user_name, passcode, today_disp, darkroom, "
					         . " anonymized, show_missed) VALUES (?, ?, ?, ?, ?, ?, ?)";

					$sqlParams[] = $newUserID;
					$sqlParams[] = $newUserName;
					$sqlParams[] = md5($newPassword);
					$sqlParams[] = $newTodayDisp;
					$sqlParams[] = $newDarkroom;
					$sqlParams[] = $newAnonymized;
					$sqlParams[] = $newShowMissed;

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

						if($oldDarkroomFlg != $newDarkroom)
						{
							if($updateCnt > 0)	$sqlStr .= ",";
							$sqlStr .= "darkroom=?";
							$sqlParams[] = $newDarkroom;
							$updateCnt++;
						}

						if($oldAnonymizeFlg != $newAnonymizeFlg)
						{
							if($updateCnt > 0)	$sqlStr .= ",";
							$sqlStr .= "anonymized=?";
							$sqlParams[] = $newAnonymized;
							$updateCnt++;
						}

						if($oldLatestResults != $newLatestResults)
						{
							if($updateCnt > 0)	$sqlStr .= ",";
							$sqlStr .= "show_missed=?";
							$sqlParams[] = $newShowMissed;
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
			$params['ticket'] = $_SESSION['ticket'] ;
			//------------------------------------------------------------------------------------------------

			//------------------------------------------------------------------------------------------------
			// Retrieve user lists
			//------------------------------------------------------------------------------------------------
			$sqlStr = "SELECT user_id, user_name, today_disp, darkroom, anonymized,"
					. " show_missed, passcode FROM users WHERE user_id<>? ORDER BY user_id ASC";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($DEFAULT_CAD_PREF_USER));

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
			$smarty = new SmartyEx();

			$smarty->assign('params',    $params);
			$smarty->assign('userList',  $userList);
			$smarty->assign('groupList', $groupList);

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