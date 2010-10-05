<?php
	//session_cache_limiter('none');
	session_start();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> 
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>Configuration of DICOM storage server</title>
<link rel="stylesheet" type="text/css" href="../css/base_style.css">

<script type="text/javascript" src="../js/jquery/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="../js/list_function.js"></script>

<script language="Javascript">;
<!--

function UpdateConfig(ticket)
{
	var newThumbnailFlg = (document.form1.checkThumbnailFlg.checked == true) ? "1" : "0";
	var newCompressFlg = (document.form1.checkCompressFlg.checked == true) ? "1" : "0";
		
	if(confirm('Do you update configuration file?'))	
	{
		var address = 'dicom_storage_server_config.php?mode=update'
				    + '&oldAeTitle='      + document.form1.oldAeTitle.value
				    + '&oldPortNumber='   + document.form1.oldPortNumber.value
				    + '&oldLogFname='     + document.form1.oldLogFname.value
				    + '&oldErrLogFname='  + document.form1.oldErrLogFname.value
				    + '&oldThumbnailFlg=' + document.form1.oldThumbnailFlg.value
				    + '&newAeTitle='      + document.form1.newAeTitle.value
				    + '&newPortNumber='   + document.form1.newPortNumber.value
				    + '&newLogFname='     + document.form1.newLogFname.value
				    + '&newErrLogFname='  + document.form1.newErrLogFname.value
				    + '&newThumbnailFlg=' + newThumbnailFlg
				    + '&newCompressFlg='  + newCompressFlg
					+ '&ticket=' + ticket;
		self.location.replace(address);	
	}
	else
	{
		document.form1.newAeTitle.value     = document.form1.oldAeTitle.value;
		document.form1.newPortNumber.value  = document.form1.oldPortNumber.value;
		document.form1.newLogFname.value    = document.form1.oldErrLogFname.value;
		document.form1.newErrLogFname.value = document.form1.oldErrLogFname.value;
		document.form1.checkThumbnailFlg.checked = (document.form1.oldThumbnailFlg.value == 1) ? true : false;
		document.form1.checkCompressFlg.checked = (document.form1.oldCompressFlg.value == 1) ? true : false;
	}
}

function RestartDICOMStorageSv(ticket)
{
	if(confirm('Do you restart DICOM storage server?'))
	{
		var address = 'dicom_storage_server_config.php?mode=restartSv&ticket=' + ticket;
		self.location.replace(address);
	}
}

-->
</script>
</head>

<?php

	include("../common.php");
	
	$confFname = $APP_DIR . $DIR_SEPARATOR . $CONFIG_DICOM_STORAGE;

	//--------------------------------------------------------------------------------------------------------
	// Auto logout (session timeout)
	//--------------------------------------------------------------------------------------------------------
	//if(time() > $_SESSION['timeLimit'])
	//{
	//	echo '<script language="Javascript">';
	//	echo "top.location.href = 'index.php?mode=timeout'";
	//	echo '</script>';
	//	exit(0);
	//}
	//else	$_SESSION['timeLimit'] = time() + $SESSION_TIME_LIMIT;
	//--------------------------------------------------------------------------------------------------------
	
	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//--------------------------------------------------------------------------------------------------------
	$mode = (isset($_REQUEST['mode']) && ($_SESSION['ticket'] == $_REQUEST['ticket'])) ? $_REQUEST['mode'] : "";
	$oldAeTitle      = (isset($_REQUEST['oldAeTitle']))      ? $_REQUEST['oldAeTitle']   : "";
	$oldPortNumber   = (isset($_REQUEST['oldPortNumber']))   ? $_REQUEST['oldPortNumber'] : "";
	$oldLogFname     = (isset($_REQUEST['oldLogFname']))     ? $_REQUEST['oldLogFname'] : "";
	$oldErrLogFname  = (isset($_REQUEST['oldErrLogFname']))  ? $_REQUEST['oldErrLogFname'] : "";
	$oldThumbnailFlg = (isset($_REQUEST['oldThumbnailFlg'])) ? $_REQUEST['oldThumbnailFlg']  : "";
	$oldCompressFlg  = (isset($_REQUEST['oldCompressFlg']))  ? $_REQUEST['oldCompressFlg']  : "";
	$newAeTitle      = (isset($_REQUEST['newAeTitle']))      ? $_REQUEST['newAeTitle']   : "";
	$newPortNumber   = (isset($_REQUEST['newPortNumber']))   ? $_REQUEST['newPortNumber'] : "";
	$newLogFname     = (isset($_REQUEST['newLogFname']))     ? stripslashes($_REQUEST['newLogFname']) : "";
	$newErrLogFname  = (isset($_REQUEST['newErrLogFname']))  ? stripslashes($_REQUEST['newErrLogFname']) : "";
	$newThumbnailFlg = (isset($_REQUEST['newThumbnailFlg'])) ? $_REQUEST['newThumbnailFlg']  : "";
	$newCompressFlg  = (isset($_REQUEST['newCompressFlg']))  ? $_REQUEST['newCompressFlg']  : "";
	//--------------------------------------------------------------------------------------------------------

	$msg = "";
	$restartFlg = 0;

	echo "<body bgcolor=#ffffff>";
	echo '<center>';
	echo '<div class="listTitle">Configuration of DICOM storage server</div>';
	
	if($_SESSION['supeUserFlg'])
	{
		if($mode == "update")
		{
			if($newAeTitle != $oldAeTitle || $newPortNumber != $oldPortNumber || $newLogFname != $oldErrLogFname
			   || $newErrLogFname != $oldErrLogFname || $newThumbnailFlg != $oldThumbnailFlg)
			{
				// Update 
				$fp = fopen($confFname, "w");
		
				if($fp != NULL)
				{
					fprintf($fp, "%s\r\n", $newAeTitle);
					fprintf($fp, "%s\r\n", $newPortNumber);
					fprintf($fp, "%s\r\n", $newLogFname);
					fprintf($fp, "%s\r\n", $newErrLogFname);
					fprintf($fp, "%s\r\n", $newThumbnailFlg);
					fprintf($fp, "%s", $newCompressFlg);
				}
				else
				{
					$msg = "<font color=#ff0000>Fail to open file : " . $confFname . "</font>";
				}
				
				
				fclose($fp);
				
				if($msg == "")
				{
					$msg = "<font color=#0000ff>"
					     . "Configure file was successfully updated. Please restart DICOM storage server !!"
						 . "</font>";
					$restartFlg = 1;
				}
			}
		}
		else if($mode == "restartSv")
		{
			win32_stop_service($DICOM_STORAGE_SERVICE);
			win32_start_service($DICOM_STORAGE_SERVICE);
	
			$status = win32_query_service_status($DICOM_STORAGE_SERVICE);
			
			if($status != FALSE)
			{
				if($status['CurrentState'] == WIN32_SERVICE_RUNNING
		   			|| $status['CurrentState'] == WIN32_SERVICE_START_PENDING)
				{
					$msg = "<font color=#0000ff>DICOM Storage Server is restarting.</font>";
				}
			}
		}
		
		//----------------------------------------------------------------------------------------------------
		// Load config file
		//----------------------------------------------------------------------------------------------------
		$fp = fopen($confFname, "r");
		
		if($fp != NULL)
		{
			$oldAeTitle      = $newAeTitle      = rtrim(fgets($fp), "\r\n");
			$oldPortNumber   = $newPortNumber   = rtrim(fgets($fp), "\r\n");
			$oldLogFname     = $newLogFname     = rtrim(fgets($fp), "\r\n");
			$oldErrLogFname  = $newErrLogFname  = rtrim(fgets($fp), "\r\n");
			$oldThumbnailFlg = $newThumbnailFlg = rtrim(fgets($fp), "\r\n");
			$oldCompressFlg  = $newCompressFlg  = rtrim(fgets($fp), "\r\n");
		}
		fclose($fp);
		//----------------------------------------------------------------------------------------------------

		//----------------------------------------------------------------------------------------------------
		// Make one-time ticket
		//----------------------------------------------------------------------------------------------------
		$_SESSION['ticket'] = md5(uniqid().mt_rand());
		//----------------------------------------------------------------------------------------------------

		if($msg != "")  echo '<div>' . $msg . '</div>';

		echo '<form id="form1" name="form1">';
		echo '<input type="hidden" id="oldAeTitle"      name="oldAeTitle"      value="' . $oldAeTitle . '">';
		echo '<input type="hidden" id="oldPortNumber"   name="oldPortNumber"   value="' . $oldPortNumber . '">';
		echo '<input type="hidden" id="oldLogFname"     name="oldLogFname"     value="' . $oldLogFname . '">';
		echo '<input type="hidden" id="oldErrLogFname"  name="oldErrLogFname"  value="' . $oldErrLogFname . '">';
		echo '<input type="hidden" id="oldThumbnailFlg" name="oldThumbnailFlg" value="' . $oldThumbnailFlg . '">';
		echo '<input type="hidden" id="oldCompressFlg"  name="oldCompressFlg"  value="' . $oldCompressFlg . '">';

		echo '<table>';
		echo '<tr><td align=left><b>AE title: </b></td>';
		echo '<td><input class="loginForm" size="40" type="text" id="newAeTitle" name="newAeTitle"';
		echo ' value="' . $newAeTitle . '"></td>';
		echo '</tr>';

		echo '<tr><td align=left><b>Port number: </b></td>';
		echo '<td><input class="loginForm" size="40" type="text" id="newPortNumber" name="newPortNumber"';
		echo ' value="' . $newPortNumber . '"></td>';
		echo '</tr>';

		//echo '<tr><td align=left><b>Filename for log : </b></td>';
		echo '<tr><td align=left><b>Log file: </b></td>';
		echo '<td><input class="loginForm" size="40" type="text" id="newLogFname" name="newLogFname"';
		echo ' value="' . $newLogFname . '"';
		echo ' disabled';
		echo '></td>';
		echo '</tr>';
		
		//echo '<tr><td align=left><b>Filename for error log : </b></td>';
		echo '<tr><td align=left><b>Error log file: </b></td>';
		echo '<td><input class="loginForm" size="40" type="text" id="newErrLogFname" name="newErrLogFname"';
		echo ' value="' . $newErrLogFname . '"';
		echo ' disabled';
		echo '></td>';
		echo '</tr>';
		
		echo '<tr><td colspan=2 align=left>&nbsp;';
		echo '<input type="checkbox" id="checkThumbnailFlg" name="checkThumbnailFlg"';
		if($newThumbnailFlg == 1) echo ' checked';
		echo '>';
		echo 'Create thumbnail images.</td>';
		echo '</tr>';

		echo '<tr><td colspan=2 align=left>&nbsp;';
		echo '<input type="checkbox" id="checkCompressFlg" name="checkCompressFlg"';
		if($newCompressFlg == 1) echo ' checked';
		echo '>';
		echo 'Compress DICOM image with lossless JPEG.</td>';
		echo '</tr>';

		echo '<tr><td colspan=2 height=5></td></tr>';

		echo '<tr><td align=center colspan=2>';
		echo '<input type="button" id="updateButton" VALUE="Update"';
		echo ' onClick="UpdateConfig(\'' . htmlspecialchars($_SESSION['ticket'], ENT_QUOTES) . '\');">';
		
		if($restartFlg == 1)
		{
			echo '&nbsp;&nbsp;';
			echo '<INPUT TYPE="button" id="restartButton" VALUE="Restart"';
			echo ' onClick="RestartDICOMStorageSv(\'' . htmlspecialchars($_SESSION['ticket'], ENT_QUOTES) . '\');">';
		}

		echo '</td></tr>';
	
		echo '</table>';
		echo '</form>';
	
	} // end if($_SESSION['serverSettingFlg'])

?>

</center>
</body>
</html>
