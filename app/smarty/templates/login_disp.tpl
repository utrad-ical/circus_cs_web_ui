<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>CIRCUS CS {$version}</title>
<link href="css/import.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="jq/jquery-1.3.2.min.js"></script>
<link rel="shortcut icon" href="favicon.ico" />

{literal}
<script language="javascript">
<!-- 
$(function()
{
	// Detecting IE6 or below
	if ($.browser.msie && $.browser.version < 7)
	{
		$("#messageArea").html("Your browser is not supported.");
		$("#mode").attr("disabled", "disabled");
	}
});
-->
</script>

<style type="text/css" media="all,screen">
<!--
body {
	background-color: #b4ebfa;
	margin: 0 auto;
	text-align: center;
}
#login-pnl {
	margin-left: auto;
	margin-right: auto;
	margin-top: 100px;
	text-align: center;
	width: 407px;
	height: 270px;
	background: #396 url(images/login_bk.png) no-repeat;
}
.longin-pnl-innner {
	width: 295px; 
	margin: 0 auto; 
	padding-top: 70px; 
	text-align: center;
}
table.login-tbl {
	width: 100%;
	font-size: 12px;
}
table.login-tbl th,
table.login-tbl td {
	background-color: #e0e0e0;
	text-align: center;
	color: #404040;
	padding: 5px;
	border-bottom: 5px solid #fff;
}
table.login-tbl td {
	text-align: left;
}
.error-blk {
	height: 30px;
	text-align: center;
	font-weight: bold;
	color: red;
}
input.login-btn {
	background:url(images/login_btn_bk_new2.jpg) repeat-x;
	border: 1px solid #444;
	padding-bottom: 3px;
	width: 100px;
	height: 23px;
	cursor: pointer;
}
p.version {
	text-align: right;
	margin-right: 30px;
	padding-top: 20px;
	color: gray;
}
-->
</style>
{/literal}

</head>

<body>
<div id="login-pnl">
<form action="index.php" method="post">
	<p class="version">clinical server {$version}</p>
	<div class="longin-pnl-innner">
		<table class="login-tbl">
			<tr>
				<th style="width: 80px;">User ID</th>
				<td><input type="text" name="userID" value="" style="ime-mode: disabled; width: 190px;" /></td>
			</tr>
			<tr>
				<th>Password</th>
				<td><input type="password" name="pswd" value="" style="ime-mode: disabled; width: 190px" /></td>
			</tr>
		</table>
	</div>
	<div id="messageArea" class="error-blk">
		{$message}
	</div>	
	<input type="submit" id="mode" name="mode" value="Login" class="login-btn" />
</form>
</div>
</body>
</html>
