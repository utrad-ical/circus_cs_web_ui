<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="refresh" content="0; url={$params.fileName}">

<title>Download volume data</title>
<link href="../css/import.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../jq/jquery-1.3.2.min.js"></script>
<script language="javascript" type="text/javascript" src="../jq/jq-btn.js"></script>


<link rel="shortcut icon" href="../favicon.ico" />

{literal}
<style type="text/css" media="all,screen">
<!--
input.close-btn {
	background:url(../images/login_btn_bk_new2.jpg) repeat-x;
	border: 1px solid #444;
	padding-bottom: 3px;
	height: 23px;
	cursor: pointer;
}
-->
</style>
{/literal}

<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>
	<form>
	<form>
	{if $params.message == ""}
		<h4 class="mb10">Please download from [download] button.</h4>

		<div class="block-al-c" style="width:350px;">
			<input name="" value="download" type="button" class="close-btn" style="width: 100px;"
                   onclick="location.replace('{$params.fileName}')" />
			<input name="" value="close" type="button" class="close-btn" style="width: 100px;" onclick="window.close()" />
		</div><!-- / .detail-panel END -->

	{else}
		<h4>{$params.message}</h4>
		<div class="mt15">
			<input name="" value="close" type="button" class="form-btn" style="width: 100px;" onclick="window.close()" />
		</div>
	{/if}

	</form>
</body>
</html>
