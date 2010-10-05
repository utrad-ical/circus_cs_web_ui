<?php
	session_start();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>Storage config</title>
<link rel="stylesheet" type="text/css" href="../css/base_style.css">

<script type="text/javascript" src="../js/jquery/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="../js/list_function.js"></script>

<script language="Javascript">;
<!--

function StorageSetting(mode, ticket, numRows)
{
    var flg = 1;
	var storageID = "";
	
	if(mode != 'add')  document.getElementById('storageIdText').innerHTML;
	
	if(mode == 'update')
	{
		if(!confirm('Do you update the row of storageID='+ storageID +' ?'))  flg = 0;
	}
	else if(mode == 'changeCurrent')
	{
		var menuID = document.form1.storageIdList.selectedIndex;
		storageID = document.form1.storageIdList.options[menuID].text;
		if(!confirm('Do you update current storage ID to storageID='+ storageID +' ?'))  flg = 0;
	}
	else if(mode == 'delete')
	{
		if(!confirm('Do you delete the row of storageID='+ storageID +' ?'))  flg = 0;
	}

	var path   = document.form1.inputPath.value;
	var length = path.length;

	if((mode == 'add' || mode == 'update') && (path.charAt(length-1) == "/" || path.charAt(length-1) == "\\"))
	{
		if(path.charAt(length-2) == "\:")
		{
			alert("Drive's root directory can't be set as storage path.");
			flg = 0;	
		}
		else
		{
			path = path.substr(0,length-1);
		}
	}
	
	if(flg == 1)
	{
		var address = 'storage_config.php?mode=' + mode;
		
		if(numRows > 0)  address += '&oldStorageID=' + document.form1.oldStorageID.value
		
		address += '&oldPath=' + document.form1.oldPath.value
				+  '&newPath=' + path
				+  '&ticket=' + ticket
				+  '&<?= session_name() ?>=<?=session_id() ?>';
		
		if(mode == 'changeCurrent')  address += '&newStorageID=' + storageID;

		self.location.replace(address);
	}
}

function RestartServer(ticket)
{
	if(confirm('Do you restart DICOM storage server and HTTP server?'))
	{
		var address = 'storage_config.php?mode=restart&ticket=' + ticket
		            + '&<?= session_name() ?>=<?=session_id() ?>';
		self.location.replace(address);
	}
}

function UpdateTextBox(idNum, storageID, path, alias)
{
	ChangeBgColor('row' + idNum);

	document.form1.oldPath.value   = path;
	document.form1.inputPath.value = path;

	document.getElementById('storageIdText').innerHTML = storageID;
	document.getElementById('aliasText').innerHTML = alias;
}

-->
</script>
</head>
<body bgcolor=#ffffff>

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
	$mode = (isset($_REQUEST['mode']) && ($_SESSION['ticket'] == $_REQUEST['ticket'])) ? $_REQUEST['mode'] : "";
	$oldStorageID  = (isset($_REQUEST['oldStorageID']))  ? $_REQUEST['oldStorageID']  : "";
	$oldPath       = (isset($_REQUEST['oldPath']))       ? $_REQUEST['oldPath']       : "";
	$newStorageID  = (isset($_REQUEST['newStorageID']))  ? $_REQUEST['newStorageID']  : "";
	$newPath       = (isset($_REQUEST['newPath']))       ? $_REQUEST['newPath']       : "";
	//--------------------------------------------------------------------------------------------------------
	
	if($_SESSION['supeUserFlg'])
	{

		$restartButtonFlg = 0;

		//----------------------------------------------------------------------------------------------------
		// Connect to SQL Server
		//----------------------------------------------------------------------------------------------------
		$dbConn = pg_connect($connStr) or die("A connection error occurred. Please try again later.");
		//----------------------------------------------------------------------------------------------------

		//----------------------------------------------------------------------------------------------------
		// Registration of storage settings
		//----------------------------------------------------------------------------------------------------
		$msg    = "";
		$sqlStr = "";
		
		if($mode == 'add')
		{
			$sqlStr = "SELECT COUNT(*) FROM storage_master WHERE path='" . $newPath . "'";

			$res = pg_query($dbConn, $sqlStr);
			
			if(pg_fetch_result($res, 0, 0)==1)
			{
				$msg = "<font color=#ff0000>Entered path (" . $newPath . ") was already used.</font>";
			}
		
			if($msg=="")
			{
				$sqlStr = "SELECT * FROM storage_master ORDER BY storage_id ASC;";
				$res = pg_query($dbConn, $sqlStr);
				$currentFlg = (pg_num_rows($res) == 0) ? 't' : 'f';

				$sqlStr = "SELECT nextval('storage_master_storage_id_seq');";
				$res = pg_query($dbConn, $sqlStr);
				$newStorageID = pg_fetch_result($res, 0, 0);
				$newAlias = sprintf("store%d/", $newStorageID);

				pg_free_result($res);

				$sqlStr  = "INSERT INTO storage_master(storage_id, path, apache_alias, current_flg, insert_dt, update_dt)"
			    	     . " VALUES (" . $newStorageID . ",'" . $newPath . "','" . $newAlias . "','" . $currentFlg . "',"
						 . "LOCALTIMESTAMP(0), LOCALTIMESTAMP(0))";
			}
		}
		else if($mode == 'update' && $newPath != $oldPath) 
		{
			$sqlStr = "UPDATE storage_master SET path='". $newPath . "'"
					. ",update_dt=LOCALTIMESTAMP(0) WHERE storage_id='".$newStorageID."';";
		}
		else if($mode == 'changeCurrent') 
		{
			$udateCnt = 0;
		
			$sqlStr = "UPDATE storage_master SET current_flg='f' WHERE storage_id=" . $oldStorageID.";"
			        . "UPDATE storage_master SET current_flg='t' WHERE storage_id=" . $newStorageID.";";
		}
		else if($mode == 'delete')
		{
			$sqlStr = "SELECT current_flg FROM storage_master WHERE storage_id='" . $newStorageID . "'";

			$res = pg_query($dbConn, $sqlStr);
			
			if(pg_fetch_result($res, 0, 0)=='t')
			{
				$msg = "<font color=#ff0000>Storage ID " . $newStorageID . " is currently using.</font>";
			}
			else
			{
				$sqlStr = "DELETE FROM storage_master WHERE storage_id=" . $newStorageID . "AND current_flg = 'f'";
			}
		}
		else if($mode == 'restart')
		{
			echo 'DICOM storage server and HTTP server are restarting. Please relogin later.<br>';
			flush();

			win32_stop_service($DICOM_STORAGE_SERVICE);
			win32_start_service($DICOM_STORAGE_SERVICE);

			echo '<script language="Javascript">';
			echo "top.location.replace('../index.php?mode=restartApache');";
			echo '</script>';
			flush();
		}
		
		if($mode == 'add' || ($mode == 'update' && $newPath != $oldPath))
		{
			$newPath = (realpath($newPath) == "") ? $newPath : realpath($newPath);
			
			//echo dirname($newPath);
		
			if(substr_count($newPath, $APACHE_DOCUMENT_ROOT)==0 && dirname($newPath) != "." && !is_dir($newPath))
			{
				if(mkdir($newPath) == FALSE)
				{
					$msg = "<font color=#ff0000> Can't create dir: " . $newPath . "</font>";
				}
				
				if($msg == "")
				{
					if(mkdir($newPath . $DIR_SEPARATOR . "tmp") == FALSE)
					{
						rmdir($newPath);
						$msg = "<font color=#ff0000> Can't create dir: " . $newPath . $DIR_SEPARATOR . "tmp</font>";
					}
				}
			}
			else
			{
				$msg = "<font color=#ff0000> Error: Illegal path (" . $newPath . ")</font>";
			}
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
					case 'add'           :  $msg .= 'New setting was successfully added.'; break;
					case 'update'        :  $msg .= 'The selected setting was successfully updated.'; break;
					case 'changeCurrent' :  $msg .= 'The current storage was successfully changed.'; break;
					case 'delete'        :  $msg .= 'The selected setting was successfully deleted.'; break;
				}
				$msg .= '</font>';
				
				if($mode == 'add' || $mode == 'update' || $mode =='changeCurrent')
				{
					$restartButtonFlg = 1;
				}
				
				//--------------------------------------------------------------------------------------------
				// Modify httpd-aliases.conf
				//--------------------------------------------------------------------------------------------
				if($mode == 'add')
				{
					$newPath = str_replace("\\", "/", stripslashes($newPath));
				
					$fp = fopen($apacheAliasFname, "a");
					
					fprintf($fp, "\r\nAlias /CIRCUS-CS/%s \"%s/\"\r\n\r\n", $newAlias, $newPath);
					fprintf($fp, "<Directory \"%s/\">\r\n", $newPath);
					fprintf($fp, "\tOptions Indexes MultiViews\r\n");
					fprintf($fp, "\tAllowOverride None\r\n");
					fprintf($fp, "\tOrder allow,deny\r\n");
					fprintf($fp, "\tAllow from all\r\n");
					fprintf($fp, "</Directory>\r\n");					
				
					fclose($fp);
				}
				else if($mode == 'update')
				{
					$data = file($apacheAliasFname);
					
					$oldPath = str_replace("\\", "/", stripslashes($oldPath));
					$newPath = str_replace("\\", "/", stripslashes($newPath));

					for($i=0; $i<count($data); $i++)
					{
						$data[$i] = str_replace($oldPath, $newPath, $data[$i]);
					}
					
					file_put_contents($apacheAliasFname, $data);
					unset($data);
				}
				else if($mode == 'delete')
				{
					$srcData = file($apacheAliasFname);
					$dstData = array();
					
					$alias = "/CIRCUS-CS/store" . $newStorageID . "/";
					
					for($i=0; $i<count($srcData); $i++)
					{
						if(substr_count($srcData[$i], $alias)>=1)
						{
							$i += 8;
						}
						else
						{
							array_push($dstData, $srcData[$i]);
							$count++;
						}
					}
					
					file_put_contents($apacheAliasFname, $dstData);
					
					unset($srcData);
					unset($dstData);
				}
				//--------------------------------------------------------------------------------------------
			}
			else $msg = '<font color=#ff0000>' . $msg . '</font>';
		}
		
		//----------------------------------------------------------------------------------------------------

		echo '<center>';
		echo '<div class="listTitle">Storage configuration</div>';

		if($msg != "")  echo '<div>' . $msg . '</div>';
	
		echo '<form id="form1" name="form1">';
		echo '<input type="hidden" id="mode"    name="mode"    value="'. $mode . '">';
		echo '<input type="hidden" id="oldPath" name="oldPath" value="">';
		
		echo '<table>';
		echo '<tr><td>';
		echo '<table border=1>';
	
		$colStorageTitle = array('ID', 'path', 'alias', 'Current Flg', 'Regist. date', 'Update date');
		$colStorageDB    = array('storage_id', 'path', 'apache_alias', 'current_flg', 'insert_dt', 'update_dt');
		
		//----------------------------------------------------------------------------------------------------
		// Make one-time ticket
		//----------------------------------------------------------------------------------------------------
		$_SESSION['ticket'] = md5(uniqid().mt_rand());
		//----------------------------------------------------------------------------------------------------

		//----------------------------------------------------------------------------------------------------
		// Display title row
		//----------------------------------------------------------------------------------------------------
		echo '<tr>';
		foreach($colStorageTitle as $val)	echo '<th align=center>' . $val . '</th>';
		echo '</tr>';
		//----------------------------------------------------------------------------------------------------

		//----------------------------------------------------------------------------------------------------
		// Display data row
		//----------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT * FROM storage_master ORDER BY storage_id ASC;";

		$storageRes = pg_query($dbConn, $sqlStr);
		$numRows = pg_num_rows($storageRes);

		$count=0;

		while($row = pg_fetch_assoc($storageRes))
		{
			echo "<tr id=\"row" . $count . "\" onClick=\"UpdateTextBox(" . $count . ",";
			echo $row['storage_id'] . ",'" . addslashes($row['path']) . "','" . $row['apache_alias'] . "');\">";
			
			foreach($colStorageDB as $val)
			{
				echo "<td";
				if($val == 'current_flg')
				{
					if($row[$val] == "t")	echo " align=center>TRUE";
					else					echo " align=center>FALSE";
				}
				else if($row[$val]== "")	echo ">&nbsp;";
				else						echo ">" . $row[$val];
				echo "</td>";
			}
			$count++;
			echo '</tr>';
		}
		pg_free_result($storageRes);
		
		echo '</table>';
		echo '</tr></td>';
		//---------------------------------------------------------------------------------------------------
	
		//---------------------------------------------------------------------------------------------------
		// dispay textbox and button
		//---------------------------------------------------------------------------------------------------
		echo '<tr><td height=10></td></tr>';
		
		echo '<tr><td align=center>';
		echo '<table>';

		echo '<tr>';
		echo '<td align=right>Storage ID: </td>';
		echo '<td id="storageIdText" name="storageIdText"></td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td align=right>Path: </td>';
		echo '<td><input class="loginForm" size="50" type="text" id="inputPath" name="inputPath"></td>';
		echo '</tr>';
		
		echo '<tr>';
		echo '<td align=right>Alias: </td>';
		echo '<td id="aliasText" name="aliasText"></td>';
		echo '</tr>';

		echo '<tr><td height=5 colspan=2></td></tr>';
		echo '<tr>';
		echo '<td align=center colspan=2>';
		echo '<input type="button" id="addButton" value="add" onClick="StorageSetting(\'add\',';
		echo '\'' . htmlspecialchars($_SESSION['ticket'], ENT_QUOTES) . '\',' . $numRows . ');">';
		echo '&nbsp;';
		echo '<input type="button" id="updateButton" value="update" onClick="StorageSetting(\'update\',';
		echo '\'' . htmlspecialchars($_SESSION['ticket'], ENT_QUOTES) . '\',' . $numRows . ');"';
		if($numRows <= 0) echo ' disabled';
		echo '>';
		echo '&nbsp;';
		echo '<input type="button" id="deleteButton" value="delete" onClick="StorageSetting(\'delete\',';
		echo '\'' . htmlspecialchars($_SESSION['ticket'], ENT_QUOTES) . '\',' . $numRows . ');"';
		if($numRows <= 0) echo ' disabled';
		echo '>';
		echo '</td>';
		echo '</tr>';
		echo '</table>';

		echo '</td></tr>';
		//---------------------------------------------------------------------------------------------------

		//---------------------------------------------------------------------------------------------------
		// change current storage id
		//---------------------------------------------------------------------------------------------------
		if($numRows > 0)
		{
			$sqlStr = "SELECT storage_id FROM storage_master WHERE current_flg='t' ORDER BY storage_id ASC;";
			$res = pg_query($dbConn, $sqlStr);
		
			$oldStorageID = pg_fetch_result($res, 0, 0);

			$sqlStr = "SELECT storage_id FROM storage_master ORDER BY storage_id ASC;";
			$res = pg_query($dbConn, $sqlStr);

			$storageIdArr = array();
				
			while($row = pg_fetch_assoc($res))
			{
				array_push($storageIdArr, $row['storage_id']);
			}
			pg_free_result($res);

			echo '<input type="hidden" name="oldStorageID"  value="' . $oldStorageID . '">';

			echo '<tr><td height=30 valign=middle><hr></td></tr>';

			echo '<tr><td align=center>';
			echo 'Current storage ID: <select id="storageIdList" name="storageIdList">';
			
			for($i=0; $i<count($storageIdArr); $i++)
			{
				echo '<option value="' . $storageIdArr[$i] . '"';
				if($storageIdArr[$i] == $oldStorageID) echo ' selected';
				echo '>' . $storageIdArr[$i].'</option>';
			}
		
			echo '</select>';
			echo '&nbsp;&nbsp;';
			echo '<input type="button" id="defStorageButton" value="update" onClick="StorageSetting(\'changeCurrent\',';
			echo '\'' . htmlspecialchars($_SESSION['ticket'], ENT_QUOTES) . '\',' . $numRows . ');">';
			echo '</td></tr>';
		}
		//---------------------------------------------------------------------------------------------------

		//---------------------------------------------------------------------------------------------------
		// Reboot button
		//---------------------------------------------------------------------------------------------------
		if($restartButtonFlg == 1)
		{
			echo '<tr><td align=center>';
			echo 'Please restart DICOM storage server and HTTP server to activate settings.';
			echo '&nbsp;';
			echo '<input type="button" id="restartButton" value="restart"';
			echo 'onClick="RestartServer(\'' . htmlspecialchars($_SESSION['ticket'], ENT_QUOTES) . '\');">';
			echo '</td></tr>';
		
		}

		echo '</table>';
		echo '</form>';
		echo '</center>';

		pg_close($dbConn);
	}
	
	echo '</body>';
	echo '</html>';
?>
