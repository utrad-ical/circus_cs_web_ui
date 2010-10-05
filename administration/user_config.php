<?php
	session_start();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>User configuration</title>
<link rel="stylesheet" type="text/css" href="../css/base_style.css">

<script type="text/javascript" src="../js/jquery/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="../js/list_function.js"></script>

<script language="Javascript">;
<!--

function UserSetting(mode, ticket)
{
	var menuID = document.form1.groupList.selectedIndex;
	var groupID = document.form1.groupList.options[menuID].text;
	
	menuID = document.form1.topPageList.selectedIndex;
	var topPage = document.form1.topPageList.options[menuID].value;

	if(mode == 'update' && document.form1.oldUserID.value == ""
	   && document.form1.oldUserName.value == "" && document.form1.oldPassword.value == "")
	{
		mode = 'add';
	}

    var flg = 1;
	
	if(mode == 'update' || mode == 'delete')
	{
	  if(!confirm('Do you ' + mode + ' the row of userID="'+ document.form1.oldUserID.value +'" ?'))  flg = 0;
	}
	
	if(flg == 1)
	{
		var address = 'user_config.php?mode=' + mode 
		            + '&oldUserID=' + document.form1.oldUserID.value
		            + '&oldUserName=' + document.form1.oldUserName.value
		            + '&oldPassword=' + document.form1.oldPassword.value
		            + '&oldGroupID='  + document.form1.oldGroupID.value
		            + '&oldTopPage='  + document.form1.oldTopPage.value
		            + '&newUserID='   + document.form1.inputUserID.value
		            + '&newUserName=' + document.form1.inputUserName.value
		            + '&newPassword=' + document.form1.inputPass.value
		            + '&newGroupID='  + groupID
		            + '&newTopPage='  + topPage
					+ '&ticket=' + ticket;
					+ '&<?= session_name() ?>=<?=session_id() ?>';

		self.location.replace(address);	
	}
}

function UpdateTextBox(idNum, userID, userName, password, groupID, topPage)
{
	ChangeBgColor('row' + idNum);

	document.form1.oldUserID.value   = userID;
	document.form1.oldUserName.value = userName;
	document.form1.oldPassword.value = password;
	document.form1.oldGroupID.value  = groupID;
	document.form1.oldTopPage.value  = topPage;
	
	document.form1.inputUserID.value   = userID;
	document.form1.inputUserName.value = userName;
	document.form1.inputPass.value     = password;

	var length = document.form1.groupList.options.length;

	for(i=0; i<length; i++)
	{
		if( document.form1.groupList.options[i].text == groupID)
		{
			 document.form1.groupList.options[i].selected = true;
		}
		else
		{
			 document.form1.groupList.options[i].selected = false;
		}
	}

	var length = document.form1.topPageList.options.length;

	for(i=0; i<length; i++)
	{
		if( document.form1.topPageList.options[i].value == topPage)
		{
			 document.form1.topPageList.options[i].selected = true;
		}
		else
		{
			 document.form1.topPageList.options[i].selected = false;
		}
	}
}

-->
</script>
</head>

<?php

	include("../common.php");
	
	//-----------------------------------------------------------------------------------------------------------------
	// Auto logout
	//-----------------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'] || $_SESSION['superUserFlg'] == 0)
	{
		header('location: ../index.php?mode=timeout');
	}
	else
	{
		$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	}
	//-----------------------------------------------------------------------------------------------------------------
	
	if($_SESSION['superUserFlg'])
	{
		//----------------------------------------------------------------------------------------------------
		// Import $_REQUEST variables
		//----------------------------------------------------------------------------------------------------
		$mode = (isset($_REQUEST['mode']) && ($_SESSION['ticket'] == $_REQUEST['ticket'])) ? $_REQUEST['mode'] : "";
		$oldUserID   = (isset($_REQUEST['oldUserID']))   ? $_REQUEST['oldUserID']   : "";
		$oldUserName = (isset($_REQUEST['oldUserName'])) ? $_REQUEST['oldUserName'] : "";
		$oldPassword = (isset($_REQUEST['oldPassword'])) ? $_REQUEST['oldPassword'] : "";
		$oldGroupID  = (isset($_REQUEST['oldGroupID']))  ? $_REQUEST['oldGroupID']  : "";
		$oldTopPage  = (isset($_REQUEST['oldTopPage']))  ? $_REQUEST['oldTopPage']  : "";
		$newUserID   = (isset($_REQUEST['newUserID']))   ? $_REQUEST['newUserID']   : "";
		$newUserName = (isset($_REQUEST['newUserName'])) ? $_REQUEST['newUserName'] : "";
		$newPassword = (isset($_REQUEST['newPassword'])) ? $_REQUEST['newPassword'] : "";
		$newGroupID  = (isset($_REQUEST['newGroupID']))  ? $_REQUEST['newGroupID']  : "";
		$newTopPage  = (isset($_REQUEST['newTopPage']))  ? $_REQUEST['newTopPage']  : "";
		//----------------------------------------------------------------------------------------------------

		$longinUser = $_SESSION['userID'];

		//----------------------------------------------------------------------------------------------------
		// Connect to SQL Server
		//----------------------------------------------------------------------------------------------------
		$dbConn = pg_connect($connStr) or die("A connection error occurred. Please try again later.");
		//----------------------------------------------------------------------------------------------------

		//----------------------------------------------------------------------------------------------------
		// Add / Update / Delete user
		//----------------------------------------------------------------------------------------------------
		$msg    = "";
		$sqlStr = "";
		
		if($mode == 'add')
		{
			$sqlStr  = "INSERT INTO users(user_id, user_name, passcode, group_id, today_disp, darkroom_flg, "
			         . " latest_results) VALUES ('" . $newUserID . "',"
					 . "'" . $newUserName . "','" . md5($newPassword) . "',"
					 . "'" . $newGroupID . "','" . $newTopPage . "','f', 'none')";
			
			if($newUserID == "" || $newPassword == "")  $sqlStr = "";
		}
		else if($mode == 'update') // update user config
		{
			$udateCnt = 0;
		
			$sqlStr = 'UPDATE users SET ';
			if($oldUserID == $longinUser && $newUserID != $oldUserID)
			{
				$msg = "You can't change your user ID (" . $longinUser . " -> " . $newUserID . ")";
			}
			else if($newUserID != $oldUserID)
			{
				$sqlStr .= "user_id='". $newUserID . "'";
				$updateCnt++;
			}

			if($msg == "")
			{
				if($oldUserName != $newUserName)
				{
					if($updateCnt > 0)	$sqlStr .= ",";
					$sqlStr .= "user_name='". $newUserName . "'";
					$updateCnt++;
				}

				if($oldPassword != $newPassword && $oldPassword != md5($newPassword))
				{
					if($updateCnt > 0)	$sqlStr .= ",";
					$sqlStr .= "passcode='". md5($newPassword) . "'";
					$updateCnt++;
				}

				if($oldUserID == $longinUser && $oldGroupID != $newGroupID)
				{
					$msg = "You can't change your group ID (" . $oldGroupID . " -> " . $newGroupID . ")";
				}
				else if($oldGroupID != $newGroupID)
				{
					if($updateCnt > 0)	$sqlStr .= ",";
					$sqlStr .= "group_id='". $newGroupID . "'";
					$updateCnt++;
				}

				if($oldTopPage != $newTopPage)
				{
					if($updateCnt > 0)	$sqlStr .= ",";
					$sqlStr .= "today_disp='". $newTopPage . "'";
					$updateCnt++;
				}
				
				$sqlStr .= " WHERE user_id='" . $oldUserID . "'";

				if($updateCnt == 0)  $sqlStr  = "";
			}
		}
		else if($mode == 'delete')	// delete user
		{
			if($newUserID == $longinUser)	$msg = "You can't delete your user ID (" . $longinUser . ")";
			else if($newUserID != "") 		$sqlStr = "DELETE FROM users WHERE user_id='".$newUserID."'";
		}
		
		if($msg == "" && $sqlStr != "")
		{
			pg_send_query($dbConn, $sqlStr);

			$res1 = pg_get_result($dbConn);
			$msg = pg_result_error($res1);

			if($msg == "")
			{
				$msg = '<font color=#0000ff>';
				
				switch($mode)
				{
					case 'add'    :  $msg .= 'New user was successfully added.'; break;
					case 'update' :  $msg .= 'The selected user was successfully updated.'; break;
					case 'delete' :  $msg .= 'The selected user was successfully deleted.'; break;
				}
				$msg .= '</font>';
			}
			else $msg = '<font color=#ff0000>' . $msg . '</font>';
		}
		else $msg = '<font color=#ff0000>' . $msg . '</font>';
		
		//----------------------------------------------------------------------------------------------------

		echo "<body bgcolor=#ffffff>";
		echo '<center>';
		echo '<div class="listTitle">User configuration</div>';

		if($msg != "")  echo '<div>' . $msg . '</div>';
	
		echo '<form id="form1" name="form1">';

		echo '<input type="hidden" id="oldUserID"   name="oldUserID"   value="">';
		echo '<input type="hidden" id="oldUserName" name="oldUserName" value="">';
		echo '<input type="hidden" id="oldPassword" name="oldPassword" value="">';
		echo '<input type="hidden" id="oldGroupID"  name="oldGroupID"  value="">';
		echo '<input type="hidden" id="oldTopPage"  name="oldTopPage"  value="">';
		
		echo '<table border=1>';
	
		//$colUserTitle = array('User ID', 'User name', 'Group', 'Top page', 'Regist. date', 'Update date');
		//$colUserDB    = array('user_id', 'user_name', 'group_id', 'top_page', 'insert_dt', 'update_dt');
		$colUserTitle = array('User ID', 'User name', 'Group', 'Today');
		$colUserDB    = array('user_id', 'user_name', 'group_id', 'today_disp');
		
		//----------------------------------------------------------------------------------------------------
		// Make one-time ticket
		//----------------------------------------------------------------------------------------------------
		$_SESSION['ticket'] = md5(uniqid().mt_rand());
		//----------------------------------------------------------------------------------------------------

		//----------------------------------------------------------------------------------------------------
		// Display title row
		//----------------------------------------------------------------------------------------------------
		echo '<tr>';
		foreach($colUserTitle as $val)	echo '<th align=center>' . $val . '</th>';
		echo '</tr>';
		//----------------------------------------------------------------------------------------------------

		//----------------------------------------------------------------------------------------------------
		// Display data rows
		//----------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT * FROM users ORDER BY user_id ASC;";

		$userRes = pg_query($dbConn, $sqlStr);

		$count=0;

		while($userRow = pg_fetch_assoc($userRes))
		{
			echo "<tr id=\"row" . $count . "\" onClick=\"UpdateTextBox(" . $count . ",";
			echo "'" . $userRow['user_id'] . "','" . $userRow['user_name'] . "',";
			echo "'" . $userRow['passcode'] . "','" . $userRow['group_id'] . "',";
			echo "'" . $userRow['today_disp'] . "');\">";
			
			foreach($colUserDB as $val)
			{
				echo "<td>";
				//if($val == 'top_page')
				//{
				//	switch($userRow[$val])
				//	{
				//		case 'todays_cad_list.php':	 echo "Today's CAD";	 break;
				//		case 'patient_list.php':	 echo "Patient list";	 break;
				//		case 'list_search.php':		 echo "Search";			 break;
				//		default:					 echo "Today's series";	 break;
				//	}
				//}
				if($userRow[$val] =="")	echo "&nbsp;";
				else					echo $userRow[$val];
				
				echo "</td>";
			}

			echo '</tr>';
			$count++;
		}
		pg_free_result($userRes);
		//---------------------------------------------------------------------------------------------------
	
		echo '</table>';

		//---------------------------------------------------------------------------------------------------
		// Show textboxes
		//---------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT group_id FROM groups ORDER BY group_id ASC;";
		$groupRes = pg_query($dbConn, $sqlStr);
				
		$groupID = array();
				
		while($groupRow = pg_fetch_assoc($groupRes))
		{
			array_push($groupID, $groupRow['group_id']);
		}
		pg_free_result($groupRes);

		echo '<table>';
		echo '<tr><td height=10 colspan=2></td></tr>';

		echo '<tr>';
		echo '<td align=right>User ID : </td>';
		echo '<td><input class="loginForm" size="40" type="text" id="inputUserID" name="inputUserID"></td>';
		echo '</tr>';
		
		echo '<tr>';
		echo '<td align=right>User name : </td>';
		echo '<td><input class="loginForm" size="40" type="text" id="inputUserName" name="inputUserName"></td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td>Password : </td>';
		echo '<td><input class="loginForm" size="40" type="password" id="inputPass" name="inputPass"></td>';
		
		echo '</tr>';

		echo '<tr>';
		echo '<td>Group ID : </td>';
		echo '<td><select id="groupList" name="groupList">';
		
		for($i=0; $i<count($groupID); $i++)
		{
			echo '<option value="' . $groupID[$i] . '">' . $groupID[$i].'</option>';
		}
		
		echo '</select>';
		echo '</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td>Top page : </td>';
		echo '<td><select id="topPageList" name="topPageList">';
		
		echo '<option value="series">series</option>';
		echo '<option value="cad">cad</option>';
		
		echo '</select>';
		echo '</td>';
		echo '</tr>';

		echo '<tr><td height=5 colspan=2></td></tr>';
		echo '<tr>';
		echo '<td align=center colspan=2>';
		echo '<input type="button" id="addButton" value="Add" onClick="UserSetting(\'add\',';
		echo '\'' . htmlspecialchars($_SESSION['ticket'], ENT_QUOTES) . '\');">';
		echo '&nbsp;';
		echo '<input type="button" id="updateButton" value="Update" onClick="UserSetting(\'update\',';
		echo '\'' . htmlspecialchars($_SESSION['ticket'], ENT_QUOTES) . '\');">';
		echo '&nbsp;';
		echo '<input type="button" id="deleteButton" value="Delete" onClick="UserSetting(\'delete\',';
		echo '\'' . htmlspecialchars($_SESSION['ticket'], ENT_QUOTES) . '\');">';
		echo '</td>';
		echo '</tr>';

		echo '</table>';
		//---------------------------------------------------------------------------------------------------

		echo '</form>';
		echo '</center>';

		pg_close($dbConn);
	}

?>

</body>
</html>

