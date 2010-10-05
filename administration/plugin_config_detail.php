<?php
	//session_cache_limiter('none');
	session_start();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>Plug-in configuration</title>

<link rel="stylesheet" type="text/css" href="../css/base_style.css">

<script type="text/javascript" src="../js/jquery/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="../js/search_condition.js"></script>

<script language="Javascript">;
<!--
function buttonStyle(mode)
{
	var upButton    = document.getElementById('upButton');
	var downButton  = document.getElementById('downButton');
	var leftButton  = document.getElementById('leftButton');
	var rightButton = document.getElementById('rightButton');
	
	var leftBox = document.getElementById('listBox1');
	var rightBox = document.getElementById('listBox2');
		
	if(mode == 1)  // left box
	{
		rightBox.selectedIndex = -1;

		if(leftBox.options.length > 0)  rightButton.disabled = false;
		else							rightButton.disabled = true;

		leftButton.disabled = true;

		for(var i=0; i<leftBox.options.length; i++)
		{
			if(leftBox.options[i].selected)
			{
				if(i!=0)							upButton.disabled = false;
				else								upButton.disabled = true;
			
				if(i!=(leftBox.options.length-1))	downButton.disabled = false;
				else								downButton.disabled = true;
			}
		}
	}
	else  // right box
	{
		leftBox.selectedIndex = -1;

		if(rightBox.options.length > 0)		leftButton.disabled  = false;
		else								leftButton.disabled  = true;

		rightButton.disabled = true;

		document.getElementById('upButton').disabled   = true;
		document.getElementById('downButton').disabled = true;		
	}
}

function itemMove(mode)
{
	document.getElementById('registButton').disabled = false;
	document.getElementById('resetButton').disabled = false;
	//document.getElementById('msgLine').innerHTML = '&nbsp;'
	
	var leftBox = document.getElementById('listBox1');
	var rightBox = document.getElementById('listBox2');
	
	var fromBox, toBox;
	
	if(mode == 1)
	{
		fromBox = leftBox;
		toBox = rightBox;
	}
	else if(mode == -1)
	{
		fromBox = rightBox;
		toBox = leftBox;
	}

    if((fromBox != null) && (toBox != null))
	{
		while ( fromBox.selectedIndex >= 0 )
		{
			var newOption = new Option();
			newOption.text = fromBox.options[fromBox.selectedIndex].text; 
			newOption.value = fromBox.options[fromBox.selectedIndex].value;
			toBox.options[toBox.length] = newOption;
			fromBox.remove(fromBox.selectedIndex);
		}

		if(mode == 1)
		{
			document.getElementById('upButton').disabled   = true;
			document.getElementById('downButton').disabled = true;
			if(fromBox.length < 1)  document.getElementById('rightButton').disabled = true;
		}
		else if(mode == -1)
		{
			if(fromBox.length < 1)  document.getElementById('leftButton').disabled = true;
		}	

	}
	return false;
}

function optionMove(mode)
{
	document.getElementById('registButton').disabled = false;
	document.getElementById('resetButton').disabled = false;
	//document.getElementById('msgLine').innerHTML = '&nbsp;'

	var opt = document.getElementById('listBox1');
	for(var i=0;i<opt.options.length;i++)
	if(opt.options[i].selected) break;
	var tmpOption = opt.removeChild(opt.options[i]);
	opt.insertBefore(tmpOption,opt.options[i+mode]);

	buttonStyle(1);
}

function RegistSettings()
{
	if(confirm('Do you register changed configuration?'))
	{
		var leftBox = document.getElementById('listBox1');
		var rightBox = document.getElementById('listBox2');
		
		var executableStr = "";
		var hiddenStr = "";

		for(var i=0; i<leftBox.options.length; i++)
		{
			if(i > 0)  executableStr += "\t";
			executableStr += leftBox.options[i].text;
		}
		
		for(var i=0; i<rightBox.options.length; i++)
		{
			if(i > 0)  hiddenStr += "\t";
			hiddenStr += rightBox.options[i].text;
		}

		document.form1.executableStr.value = encodeURIComponent(executableStr);
		document.form1.hiddenStr.value     = encodeURIComponent(hiddenStr);
		document.form1.mode.value          = 'regist';

		document.form1.method = 'POST';
		document.form1.target = '_self';
		document.form1.action = 'plugin_config_detail.php';
		document.form1.submit();
	}
}

function ResetSettings()
{
	location.replace('plugin_config_detail.php');
}

-->
</script>
</head>

<?php

	include ('../common.php');
	
	if($_SESSION['ticket'] != $_REQUEST['ticket'])
	{
		echo '<script language="Javascript">';
		echo "top.main.location.replace('plugin_config_clear_frame.php');";
		echo '</script>';
		exit(0);
	}
	
	$modality = (isset($_REQUEST['modality'])) ? $_REQUEST['modality'] : "CT";
	$mode     = (isset($_REQUEST['mode'])) ? $_REQUEST['mode'] : "";
	
	//--------------------------------------------------------------------------------------------------------
	// Connect to SQL server
	//--------------------------------------------------------------------------------------------------------
	$dbConn = pg_connect($connStr) or die("A connection error occurred. Please try again later.");
	//--------------------------------------------------------------------------------------------------------

	$executableList = array();
	$hiddenList     = array();
	$msg = "&nbsp;";
	$registButtonFlg = 0;

	if($mode == "regist")	// Register configurations to database
	{
		$executableStr = (isset($_REQUEST['executableStr'])) ? urldecode($_REQUEST['executableStr']) : "";
		$hiddenStr     = (isset($_REQUEST['hiddenStr']))     ? urldecode($_REQUEST['hiddenStr'])     : "";
		
		//echo $executableStr;
	
		$executableList = explode("\t", $executableStr);
		$hiddenList     = explode("\t", $hiddenStr);
		
		$sqlStr  = "";
		$cadName = "";
		$version = "";
		$order = 1;
		
		for($i=0; $i<count($executableList); $i++)
		{
			$pos = strpos($executableList[$i], "_v.");
			$cadName = substr($executableList[$i], 0, $pos);
			$version = substr($executableList[$i], $pos+3, strlen($executableList[$i])-$pos-3);
			
			$sqlStr .= "UPDATE cad_master SET exec_flg='t', label_order=" . $order
			        .  " WHERE cad_name='" . $cadName . "' AND version='" . $version . "';";
					
			$order++;
		}
	
		for($i=0; $i<count($hiddenList); $i++)
		{
			$pos = strpos($hiddenList[$i], "_v.");
			$cadName = substr($hiddenList[$i], 0, $pos);
			$version = substr($hiddenList[$i], $pos+3, strlen($hiddenList[$i])-$pos-3);
			
			$sqlStr .= "UPDATE cad_master SET exec_flg='f', label_order=" . $order
			        .  " WHERE cad_name='" . $cadName . "' AND version='" . $version . "';";
					
			$order++;
		}
	
		//echo $sqlStr;
	
		pg_send_query($dbConn, $sqlStr);
		$res = pg_get_result($dbConn);
		$msg = pg_result_error($res);

		if($msg == "")
		{
			$msg = '<font color=#0000ff>'
			     . 'New setting was successfully registered.'
				 . '</font>';
		}
		else
		{
			$msg = '<font color=#ff0000>' . $msg . '</font>';
			$registButtonFlg = 1;
		}
			
	}
	else  // Load data from database
	{
		$sqlStr  = "SELECT * FROM cad_master cm, cad_series cs"
				 . " WHERE cm.cad_name=cs.cad_name AND cm.version=cs.version AND cs.series_id=1"
				 . " AND cs.modality='" . $modality . "' ORDER BY cm.label_order ASC";


		$res = pg_query($dbConn, $sqlStr);
	
		while($row = pg_fetch_assoc($res))
		{
			$tmp = $row['cad_name'] . "_v." . $row['version'];
		
			if($row['exec_flg'] == 't')  array_push($executableList, $tmp);
			else                         array_push($hiddenList,     $tmp);
		}
	}
	
	//--------------------------------------------------------------------------------------------------------
	// Make one-time ticket
	//--------------------------------------------------------------------------------------------------------
	$_SESSION['ticket'] = md5(uniqid().mt_rand());
	//--------------------------------------------------------------------------------------------------------	
	
?>

<body bgcolor=#ffffff>
<form id="form1" name="form1">
<input type="hidden" id="mode"          name="mode"          value="<?= $mode ?>">
<input type="hidden" id="executableStr" name="executableStr" value="">
<input type="hidden" id="hiddenStr"     name="hiddenStr"     value="">
<input type="hidden" name="ticket" value="<? echo htmlspecialchars($_SESSION['ticket'], ENT_QUOTES); ?>">

<?php
	if($msg != "")  echo '<div id="msgLine">' . $msg . '</div>';
?>

<table>
<tr>
<td style="font-size:16px;font-weight:bold;">Executable</td>
<td colspan=3></td>
<td style="font-size:16px;font-weight:bold;">Hidden</td>
</tr>

<tr>
<td valign=top>
<select id="listBox1" size="10" onchange="buttonStyle(1)" style="width:200px;font-size:15px;">

<?php
	for($i=0; $i<count($executableList); $i++)
	{
		echo '<option>' . $executableList[$i] . '</option>';
	}
?>	

</select>
</td>
<td width=15></td>
<td valign=middle>
<p><input type="button" value="&rarr;" id="rightButton" disabled onclick="itemMove(1)" style="width:30px;height:30px;font-size:125%;font-weight:bold;"></p>
<p><input type="button" value="&larr;" id="leftButton" disabled onclick="itemMove(-1)" style="width:30px;height:30px;font-size:125%;font-weight:bold;"></p>
</td>
<td width=15></td>

<td valign=top>
<select id="listBox2" size="10"  onchange="buttonStyle(2)" style="width:200px;font-size:15px;">

<?php
	for($i=0; $i<count($hiddenList); $i++)
	{
		echo '<option>' . $hiddenList[$i] . '</option>';
	}
?>	

</select>
</td>
</tr>

<tr>
</td>
<td align=center>
<input type="button" value="&uarr;" id="upButton" disabled onclick="optionMove(-1)" style="width:30px;height:30px;font-size:125%;font-weight:bold;">
&nbsp;&nbsp;&nbsp;
<input type="button" value="&darr;" id="downButton" disabled onclick="optionMove(1)" style="width:30px;height:30px;font-size:125%;font-weight:bold;">
</td>
<td colspan=4></td>
</tr>

<tr>
<td colspan=5 align=center>
<input type="button" id="registButton" value="registration" disabled onclick="RegistSettings()" style="font-size:16px;">
&nbsp;&nbsp;
<input type="button" id="resetButton" value="reset" disabled onclick="ResetSettings()" style="font-size:16px;">
</td>
</tr>
</table>

<?php
	if($registButtonFlg == 1)
	{
		echo '<script language="Javascript">';
		echo "document.getElementById('registButton').disabled = false;";
		echo "document.getElementById('resetButton').disabled = false;";
		echo '</script>';	
	}
?>

</form>
</body>
</html>