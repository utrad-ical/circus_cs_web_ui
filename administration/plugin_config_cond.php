<?php
	//session_cache_limiter('none');
	session_start();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>CAD preference</title>

<link rel="stylesheet" type="text/css" href="../css/base_style.css">

<script type="text/javascript" src="../js/jquery/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="../js/search_condition.js"></script>

<script language="Javascript">;
<!--

function ShowPluginConfDetail(ticket)
{
	var id = document.form1.modalityMenu.selectedIndex;
	var modality = document.form1.elements["modalityMenu"][id].text;

	var address = 'plugin_config_detail.php?&modality=' + modality
				+ '&ticket=' + ticket;
				
	parent.bottom_detail.location.replace(address);
}

-->
</SCRIPT>
</HEAD>

<?php

	include ('../common.php');
	include("auto_logout_administration.php");
		
	//--------------------------------------------------------------------------------------------------------
	// Connect to SQL Server
	//--------------------------------------------------------------------------------------------------------
	$dbConn = pg_connect($connStr) or die("A connection error occurred. Please try again later.");
	//--------------------------------------------------------------------------------------------------------
	
	//--------------------------------------------------------------------------------------------------------
	// Make one-time ticket
	//--------------------------------------------------------------------------------------------------------
	$_SESSION['ticket'] = md5(uniqid().mt_rand());
	//--------------------------------------------------------------------------------------------------------	

	$sqlStr  = "SELECT DISTINCT cs.modality FROM cad_master cm, cad_series cs"
	         . " WHERE cm.cad_name=cs.cad_name AND cm.version=cs.version"
			 . " AND cs.series_id=1 ORDER BY cs.modality ASC";

	$res = pg_query($dbConn, $sqlStr);

	$modalityList = array();

	while($row = pg_fetch_array($res))
	{
		array_push($modalityList, $row[0]);
	}
	
?>

<body bgcolor=#ffffff>
<form id="form1" name="form1">
<input type="hidden" id="sessionName" name="<?= session_name() ?>" value="<?= session_id() ?>">

<div class="listTitle">Plug-in configuration</div>
<div style="font-size:5px;">&nbsp</div>
<div style="font-size:16px;">Source modality:&nbsp;

<select id="modalityMenu" name="modalityMenu">

<?php
		
	for($i=0; $i<count($modalityList); $i++)
	{
		echo '<option value="' . $modalityList[$i] .'">';
		echo $modalityList[$i] . '</option>';
	}
?>
</select>
&nbsp;&nbsp;
<input type="button" id="applyButton" name="applyButton" value="Apply"
 onClick="ShowPluginConfDetail('<? echo htmlspecialchars($_SESSION['ticket'], ENT_QUOTES); ?>');">
</div>
</form>
</body>
</html>