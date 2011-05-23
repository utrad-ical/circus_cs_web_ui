<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>Compress DICOM files</title>
<link rel="stylesheet" type="text/css" href="../css/base_style.css">

<script type="text/javascript" src="../js/jquery/jquery-1.3.2.min.js"></script>

<script language="Javascript">;
<!--

-->
</script>
</head>
<body bgcolor="#ffffff">

<?php

	include("../common.php");
	Auth::checkSession();
	Auth::purgeUnlessGranted(Auth::SERVER_OPERATION);

	// Prevent timeout error
	set_time_limit(0);

	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables
	//--------------------------------------------------------------------------------------------------------
	$seriesDir = "";

	$seriesUID = $_REQUEST['seriesInstanceUID'];

	if(isset($_REQUEST['seriesDir']))
	{
		if(ini_get('magic_quotes_gpc') == "1")  $seriesDir = stripslashes($_REQUEST['seriesDir']);
		else                                    $seriesDir = $_REQUEST['seriesDir'];
	}
	else
	{
		echo '<div class="listTitle" style="margin-left:5px;">Error: seriesDir is not defined.</div>';
		flush();
		exit(-1);
	}

	$flist = array();
	$flist = GetDicomFileListInPath($seriesDir);
	$fNum = count($flist);

	echo '<div class="listTitle" style="margin-left:5px;">Compress DICOM files</div>';
	flush();

	//echo $fNum;

	for($i=0; $i<$fNum; $i++)
	{
		$fileName = $seriesDir . $DIR_SEPARATOR . $flist[$i];

		echo $flist[$i] . "...";
		flush();

		$cmdStr  = $cmdForProcess . ' "' . $cmdDcmCompress . ' ' . $seriesDir . ' ' . $flist[$i] . '"';
		flush();

		$res = shell_exec($cmdStr);
		echo $res . "<br>";
		flush();

		if(!(strncmp($res,"successed",9) || strncmp($res,"already compressed",18)))  exit(-1);
	}

	//--------------------------------------------------------------------------------------------------------
	// Connect to SQL server
	//--------------------------------------------------------------------------------------------------------
	$dbConn = pg_connect($connStr) or die("A connection error occurred. Please try again later.");
	//--------------------------------------------------------------------------------------------------------

	$msg = "";

	$sqlStr = "UPDATE series_list SET compress_flg='t' WHERE series_instance_uid='" . $seriesUID . "';";
	pg_send_query($dbConn, $sqlStr);

	$res = pg_get_result($dbConn);
	$msg = pg_result_error($res);

	if($msg != "")
	{
		echo '<font color=#ff0000>' . $msg . '</font><br>';
	}
	else
	{
		echo '<font color=#0000ff>DICOM files were successfully compressed.</font><br>';
	}
?>

</body>
</html>