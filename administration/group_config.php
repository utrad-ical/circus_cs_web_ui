<?php
	session_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>Group configuration</title>
<link rel="stylesheet" type="text/css" href="../scriptaculous-js/colorpicker/colorPicker.css">
<link rel="stylesheet" type="text/css" href="../css/base_style.css">

<script type="text/javascript" src="../js/jquery/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="../js/list_function.js"></script>
<script type="text/javascript" src="../scriptaculous-js/lib/prototype.js"></script>
<script type="text/javascript" src="../scriptaculous-js/src/scriptaculous.js"></script>
<script type="text/javascript" src="../scriptaculous-js/colorpicker/yahoo.color.js"></script>
<script type="text/javascript" src="../scriptaculous-js/colorpicker/colorPicker.js"></script>


<script language="Javascript">;
<!--

function GroupSetting(mode, ticket)
{
	if(mode == 'update' && document.form1.oldGroupID.value == "" && document.form1.oldMenuColor.value == "")
	{
		mode = 'add';
	}
	document.form1.mode.value = mode;

    var flg = 1;
	
	if(mode == 'update' || mode == 'delete')
	{
	  if(!confirm('Do you ' + mode + ' the row of groupID="'+ document.form1.oldGroupID.value +'" ?'))  flg = 0;
	}
	
	if(flg == 1)
	{
		var newAnonymizePinfo     = (document.form1.checkAnonymizePinfo.checked     == true) ? 't' : 'f';
		var newExecCAD            = (document.form1.checkExecCAD.checked            == true) ? 't' : 'f';
		var newPersonalFeedback   = (document.form1.checkPersonalFeedback.checked   == true) ? 't' : 'f';
		var newConsensualFeedback = (document.form1.checkConsensualFeedback.checked == true) ? 't' : 'f';
		var newAllStatistics      = (document.form1.checkAllStatistics.checked      == true) ? 't' : 'f';
		var newCaseDelete         = (document.form1.checkCaseDelete.checked         == true) ? 't' : 'f';
		var newSvSetting          = (document.form1.checkSvSetting.checked          == true) ? 't' : 'f';

		var address = 'group_config.php?mode=' + mode 
		            + '&oldGroupID='            + document.form1.oldGroupID.value
		            + '&oldMenuColor='          + document.form1.oldMenuColor.value
		            + '&oldAnonymizePinfo='     + document.form1.oldMenuColor.value
		            + '&oldExecCAD='            + document.form1.oldAnonymizePinfo.value
		            + '&oldPersonalFeedback='   + document.form1.oldPersonalFeedback.value
		            + '&oldConsensualFeedback=' + document.form1.oldConsensualFeedback.value
		            + '&oldAllStatistics='      + document.form1.oldAllStatistics.value
		            + '&oldCaseDelete='         + document.form1.oldCaseDelete.value
		            + '&oldSvSetting='          + document.form1.oldSvSetting.value
		            + '&newGroupID='            + document.form1.inputGroupID.value
		            + '&newMenuColor='          + document.form1.inputMenuColor.value
		            + '&newAnonymizePinfo='     + newAnonymizePinfo
		            + '&newExecCAD='            + newExecCAD
		            + '&newPersonalFeedback='   + newPersonalFeedback
		            + '&newConsensualFeedback=' + newConsensualFeedback
		            + '&newAllStatistics='      + newAllStatistics
		            + '&newCaseDelete='         + newCaseDelete
		            + '&newSvSetting='          + newSvSetting
					+ '&ticket=' + ticket
					+ '&<?= session_name() ?>=<?=session_id() ?>';

		self.location.replace(address);	
	}
}

function UpdateTextBox(idNum, groupID, menuColor, anonymizePinfo, execCAD, personalFeedback,
                       consensualFeedback, allStatistics, caseDelete, svSetting)
{
	ChangeBgColor('row' + idNum);

	document.form1.oldGroupID.value   = groupID;
	document.form1.oldMenuColor.value = menuColor;
	
	document.form1.inputGroupID.value   = groupID;
	document.form1.inputMenuColor.value = menuColor;

	document.form1.checkAnonymizePinfo.checked     = (anonymizePinfo     == 't') ? true : false;
	document.form1.checkExecCAD.checked            = (execCAD            == 't') ? true : false;
	document.form1.checkPersonalFeedback.checked   = (personalFeedback   == 't') ? true : false;
	document.form1.checkConsensualFeedback.checked = (consensualFeedback == 't') ? true : false;
	document.form1.checkAllStatistics.checked      = (allStatistics      == 't') ? true : false;
	document.form1.checkCaseDelete.checked         = (caseDelete         == 't') ? true : false;
	document.form1.checkSvSetting.checked          = (svSetting          == 't') ? true : false;
}

-->
</script>
</head>

<?php

	include("../common.php");
	
	//--------------------------------------------------------------------------------------------------------
	// Auto logout (session timeout)
	//--------------------------------------------------------------------------------------------------------
	if(time() > $_SESSION['timeLimit'])
	{
		echo '<script language="Javascript">';
		echo "top.location.href = '../index.php?mode=timeout'";
		echo '</script>';
		exit(0);
	}
	else	$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	//--------------------------------------------------------------------------------------------------------
	
	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//--------------------------------------------------------------------------------------------------------
	$mode                  = (isset($_REQUEST['mode']))                  ? $_REQUEST['mode']                  : "";
	$oldGroupID            = (isset($_REQUEST['oldGroupID']))            ? $_REQUEST['oldGroupID']            : "";
	$oldMenuColor          = (isset($_REQUEST['oldMenuColor']))          ? $_REQUEST['oldMenuColor']          : "";
	$oldAnonymizePinfo     = (isset($_REQUEST['oldAnonymizePinfo']))     ? $_REQUEST['oldAnonymizePinfo']     : "";
	$oldExecCAD            = (isset($_REQUEST['oldExecCAD']))            ? $_REQUEST['oldExecCAD']            : "";
	$oldPersonalFeedback   = (isset($_REQUEST['oldPersonalFeedback']))   ? $_REQUEST['oldPersonalFeedback']   : "";
	$oldConsensualFeedback = (isset($_REQUEST['oldConsensualFeedback'])) ? $_REQUEST['oldConsensualFeedback'] : "";
	$oldAllStatistics      = (isset($_REQUEST['oldAllStatistics']))      ? $_REQUEST['oldAllStatistics']      : "";
	$oldCaseDelete         = (isset($_REQUEST['oldCaseDelete']))         ? $_REQUEST['oldCaseDelete']         : "";
	$oldSvSetting          = (isset($_REQUEST['oldSvSetting']))          ? $_REQUEST['oldSvSetting']          : "";
	$newGroupID            = (isset($_REQUEST['newGroupID']))            ? $_REQUEST['newGroupID']            : "";
	$newMenuColor          = (isset($_REQUEST['newMenuColor']))          ? $_REQUEST['newMenuColor']          : "";
	$newAnonymizePinfo     = (isset($_REQUEST['newAnonymizePinfo']))     ? $_REQUEST['newAnonymizePinfo']     : "";
	$newExecCAD            = (isset($_REQUEST['newExecCAD']))            ? $_REQUEST['newExecCAD']            : "";
	$newPersonalFeedback   = (isset($_REQUEST['newPersonalFeedback']))   ? $_REQUEST['newPersonalFeedback']   : "";
	$newConsensualFeedback = (isset($_REQUEST['newConsensualFeedback'])) ? $_REQUEST['newConsensualFeedback'] : "";
	$newAllStatistics      = (isset($_REQUEST['newAllStatistics']))      ? $_REQUEST['newAllStatistics']      : "";
	$newCaseDelete         = (isset($_REQUEST['newCaseDelete']))         ? $_REQUEST['newCaseDelete']         : "";
	$newSvSetting          = (isset($_REQUEST['newSvSetting']))          ? $_REQUEST['newSvSetting']          : "";
	//--------------------------------------------------------------------------------------------------------

	if($_SESSION['supeUserFlg'])
	{
		//----------------------------------------------------------------------------------------------------
		// Connect to SQL Server
		//----------------------------------------------------------------------------------------------------
		$dbConn = pg_connect($connStr) or die("A connection error occurred. Please try again later.");
		//----------------------------------------------------------------------------------------------------

		//----------------------------------------------------------------------------------------------------
		// Add / Update / Delete group
		//----------------------------------------------------------------------------------------------------
		$msg    = "";
		$sqlStr = "";
		
		if($mode == 'add')
		{
			$sqlStr = 'INSERT INTO groups(group_id, menu_color, anonymize_personal_info, exec_cad,'
			        . ' personal_feedback, consensual_feedback, view_all_statistics, case_delete,'
					. ' server_setting, insert_dt, update_dt)'
					. "VALUES ('" . $newGroupID . "','" . $newMenuColor . "','" . $newAnonymizePinfo . "',"
					. "'" . $newExecCAD . "','" . $newPersonalFeedback . "','" . $newConsensualFeedback . "',"
					. "'" . $newAllStatistics . "','" . $newCaseDelete . "','" . $newSvSetting."',"
			        . "LOCALTIMESTAMP(0), LOCALTIMESTAMP(0))";

			if($newGroupID == "" || $newMenuColor =="")  $sqlStr = "";
		}
		else if($mode == 'update') // Update group
		{
			$udateCnt = 0;
			
			if($oldGroupID == "admin")	$msg = "You can't change setting of <b>admin</b> group.";
		
			if($msg == "")
			{
				$sqlStr = 'UPDATE groups SET ';
				if($newGroupID != $oldGroupID)
				{
					$sqlStr .= "group_id='". $newGroupID . "'";
					$updateCnt++;
				}
	
				if($newMenuColor != $oldMenuColor)
				{
					if($updateCnt > 0)	$sqlStr .= ",";
					$sqlStr .= "menu_color='". $newMenuColor . "'";
					$updateCnt++;
				}

				if($newAnonymizePinfo != $oldAnonymizePinfo)
				{
					if($updateCnt > 0)	$sqlStr .= ",";
					$sqlStr .= "anonymize_personal_info='". $newAnonymizePinfo . "'";
					$updateCnt++;
				}
	
				if($newExecCAD != $oldExecCAD)
				{
					if($updateCnt > 0)	$sqlStr .= ",";
					$sqlStr .= "exec_cad='". $newExecCAD . "'";
					$updateCnt++;
				}
	
				if($newPersonalFeedback != $oldPersonalFeedback)
				{
					if($updateCnt > 0)	$sqlStr .= ",";
					$sqlStr .= "personal_feedback='". $newPersonalFeedback . "'";
					$updateCnt++;
				}
	
				if($newConsensualFeedback != $oldConsensualFeedback)
				{
					if($updateCnt > 0)	$sqlStr .= ",";
					$sqlStr .= "consensual_feedback='". $newConsensualFeedback . "'";
					$updateCnt++;
				}
	
				if($newAllStatistics != $oldAllStatistics)
				{
					if($updateCnt > 0)	$sqlStr .= ",";
					$sqlStr .= "view_all_statistics='". $newAllStatistics . "'";
					$updateCnt++;
				}

				if($newCaseDelete != $oldCaseDelete)
				{
					if($updateCnt > 0)	$sqlStr .= ",";
					$sqlStr .= "case_delete='". $newCaseDelete . "'";
					$updateCnt++;
				}
	
				if($newSvSetting != $oldSvSetting)
				{
					if($updateCnt > 0)	$sqlStr .= ",";
					$sqlStr .= "server_setting='". $newSvSetting . "'";
					$updateCnt++;
				}
	
				if($updateCnt > 0)  $sqlStr .= ",update_dt=LOCALTIMESTAMP(0) WHERE group_id='".$oldGroupID."';";
				else				$sqlStr  = "";
			}
		}
		else if($mode == 'delete')	// Delete group
		{
			if($newGroupID == "admin")	$msg = "You can't delete <b>admin</b> group.";
			else if($newGroupID != "")  $sqlStr = "DELETE FROM groups WHERE group_id='".$newGroupID."'";
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
					case 'add'    :  $msg .= 'New group was successfully added.'; break;
					case 'update' :  $msg .= 'The selected group was successfully updated.'; break;
					case 'delete' :  $msg .= 'The selected group was successfully deleted.'; break;
				}
				$msg .= '</font>';
			}
			else $msg = '<font color=#ff0000>' . $msg . '</font>';
		}
		else $msg = '<font color=#ff0000>' . $msg . '</font>';
		
		//----------------------------------------------------------------------------------------------------

		//----------------------------------------------------------------------------------------------------
		// Make one-time ticket
		//----------------------------------------------------------------------------------------------------
		$_SESSION['ticket'] = md5(uniqid().mt_rand());
		//----------------------------------------------------------------------------------------------------

		$colList[] = array('Group ID',            'group_id',                'left',   'inputGroupID');
		$colList[] = array('Menu color',          'menu_color',              'center', 'inputMenuColor');
		$colList[] = array('Anon. info',          'anonymize_personal_info', 'center', 'checkAnonymizePinfo');
		$colList[] = array('Exec CAD',            'exec_cad',                'center', 'checkExecCAD');
		$colList[] = array('Personal feedback',   'personal_feedback',       'center', 'checkPersonalFeedback');
		$colList[] = array('Consensual feedback', 'consensual_feedback',     'center', 'checkConsensualFeedback');
		$colList[] = array('All Stat.',           'view_all_statistics',     'center', 'checkAllStatistics');
		$colList[] = array('Case delete',         'case_delete',             'center', 'checkCaseDelete');
		$colList[] = array('Sv. setting',         'server_setting',          'center', 'checkSvSetting');
		//$colList[] = array('Regist. date',         'insert_dt',              'left',   '');
		//$colList[] = array('Update date',         'update_dt',               'left',   '');

		echo "<body bgcolor=#ffffff>";

		echo '<form id="form1" name="form1">';
		echo '<input type="hidden" id="mode" name="mode" value="'. $mode . '">';
		echo '<input type="hidden" id="oldGroupID"            name="oldGroupID"            value="">';
		echo '<input type="hidden" id="oldMenuColor"          name="oldMenuColor"          value="">';
		echo '<input type="hidden" id="oldExecCAD"            name="oldExecCAD"            value="">';
		echo '<input type="hidden" id="oldAnonymizePinfo"     name="oldAnonymizePinfo"     value="">';
		echo '<input type="hidden" id="oldPersonalFeedback"   name="oldPersonalFeedback"   value="">';
		echo '<input type="hidden" id="oldConsensualFeedback" name="oldConsensualFeedback" value="">';
		echo '<input type="hidden" id="oldAllStatistics"      name="oldAllStatistics"      value="">';
		echo '<input type="hidden" id="oldCaseDelete"         name="oldCaseDelete"         value="">';
		echo '<input type="hidden" id="oldSvSetting"          name="oldSvSetting"          value="">';



		echo '<center>';
		echo '<div class="listTitle">Group configuration</div>';
		echo '<div style="font-size:7px;">&nbsp;</div>';
		
		if($msg != "")
		{
			 echo '<div>' . $msg . '</div>';
			 echo '<div style="font-size:7px;">&nbsp;</div>';
		}
	
		echo '<table border=1 cellpadding=3>';

		//----------------------------------------------------------------------------------------------------
		// Display title row
		//----------------------------------------------------------------------------------------------------
		echo '<tr>';
		for($i=0; $i<count($colList); $i++)  echo '<th align=center>' . $colList[$i][0] . '</th>';
		echo '</tr>';
		//----------------------------------------------------------------------------------------------------

		//----------------------------------------------------------------------------------------------------
		// Display data rows
		//----------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT * FROM groups ORDER BY group_id ASC;";

		$groupRes = pg_query($dbConn, $sqlStr);
		$count=0;

		while($groupRow = pg_fetch_assoc($groupRes))
		{
			echo '<tr id="row' . $count . '">';
			
			for($i=0; $i<count($colList); $i++)
			{
				echo "<td align=" . $colList[$i][2];
				echo " onClick=\"UpdateTextBox(" . $count . ",'" . $groupRow['group_id'] . "',";
				echo "'" . $groupRow['menu_color'] . "','" . $groupRow['anonymize_personal_info'] . "',";
				echo "'" . $groupRow['exec_cad'] . "','" . $groupRow['personal_feedback'] . "',";
				echo "'" . $groupRow['consensual_feedback'] . "','" . $groupRow['view_all_statistics'] . "',";
				echo "'" . $groupRow['case_delete'] . "','" . $groupRow['server_setting'] . "');\">";

				if($groupRow[$colList[$i][1]] =="")	echo "&nbsp;";
				else
				{
					if($groupRow[$colList[$i][1]] == "t")		echo "TRUE";
					else if($groupRow[$colList[$i][1]] == "f")	echo "FALSE";
					else										echo $groupRow[$colList[$i][1]];
				}
				echo "</td>";
			}

			echo '</tr>';
			$count++;
		}
		pg_free_result($groupRes);
		//---------------------------------------------------------------------------------------------------
	
		echo '</table>';

		//---------------------------------------------------------------------------------------------------
		// Show textboxes and buttons
		//---------------------------------------------------------------------------------------------------
		echo '<table>';
		echo '<tr><td height=10 colspan=4></td></tr>';
		
		// Group ID
		echo '<tr>';
		echo '<td align=right>' . $colList[0][0] . ': </td>';
		echo '<td align=left>';
		echo '<input size="30" type="text" id="'.$colList[0][3].'" name="'.$colList[0][3].'">';
		echo '</td>';
		echo '</tr>';		
		
		// Menu color
		echo '<tr>';
		echo '<td align=right>' . $colList[1][0] . ': </td>';
		echo '<td align=left>';
		echo '<input size="30" type="text" id="inputMenuColor">';
		echo '<button style="width: 1.5em; height: 1.5em; border: 1px outset #666;" id="colorbox1" class="colorbox"></button>';
		echo '</td>';
		echo '</tr>';
		
		for($i=2; $i<count($colList); $i++)
		{
			echo '<tr>';
			echo '<td align=right>' . $colList[$i][0] . ': </td>';
			echo '<td align=left>';
		
			if($i <= 1)	echo '<input size="30" type="text" ';
			else		echo '<input type="checkbox" ';
			
			echo 'id="'.$colList[$i][3].'" name="'.$colList[$i][3].'">';
			echo '</td>';
			echo '</tr>';
		}

		echo '<tr><td height=5></td></tr>';
		echo '<tr>';
		echo '<td align=center colspan=2>';
		echo '<input type="button" id="addButton" value="Add" onClick="GroupSetting(\'add\',';
		echo '\'' . htmlspecialchars($_SESSION['ticket'], ENT_QUOTES) . '\');">';
		echo '&nbsp;';
		echo '<input type="button" id="updateButton" value="Update" onClick="GroupSetting(\'update\',';
		echo '\'' . htmlspecialchars($_SESSION['ticket'], ENT_QUOTES) . '\');">';
		echo '&nbsp;';
		echo '<input type="button" id="deleteButton" value="Delete" onClick="GroupSetting(\'delete\',';
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

<script type="text/javascript">
// <![CDATA[
new Control.ColorPicker("inputMenuColor", {"swatch" : "colorbox1" });
// ]]>
</script>

</body>
</html>

