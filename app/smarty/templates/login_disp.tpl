<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>CIRCUS CS {$version}</title>
<link href="css/import.css" rel="stylesheet" type="text/css" media="all" />
<script type="text/javascript" src="jq/jquery.min.js"></script>
<link rel="shortcut icon" href="favicon.ico" />

{literal}
<script type="text/javascript">
<!--
$(function()
{
	// Detecting IE7 or below
	if ($.browser.msie && $.browser.version < 8 || !JSON)
	{
		$("#messageArea").text("Your browser is not supported.");
		$("#mode").attr("disabled", "disabled");
	}
});
-->
</script>

<style type="text/css" media="all,screen">
body {
	background-color: #b4ebfa;
	margin: 0 auto;
	text-align: center;
}
#login-pnl {
	margin: 100px auto 0;
	width: 357px;
	height: 250px;
	padding: 20px 25px 0 25px;
	background: #396 url(images/login_bk.png) no-repeat;
}
label {
	display: block;
	margin: 3px auto;
	padding: 3px 0;
	width: 295px;
	background-color: #e0e0e0;
}
label span {
	display: inline-block;
	width: 80px;
	font-weight: bold;
}
.message {
	min-height: 30px;
	font-weight: bold;
	color: red;
}
input.field {
	ime-mode: disabled;
	width: 190px;
}
input.login-btn {
	background:url(images/login_btn_bk_new2.jpg) repeat-x;
	border: 1px solid #444;
	width: 100px;
	height: 23px;
	line-height: 23px;
	cursor: pointer;
}
p.version {
	text-align: right;
	margin: 5px 5px 70px 0;
	color: gray;
}
</style>
{/literal}

</head>

<body>
<div id="login-pnl">
<form action="index.php" method="post">
	<p class="version">Clinical Server {$version|escape}</p>
	{if $critical_error}
	<div id="critical-error">
		<p class="message">{$critical_error|escape|nl2br}</p>
	</div>
	{else}
	<div id="normal-login">
		<label>
			<span>User ID</span>
			<input type="text" name="userID" value="" class="field" autofocus="autofocus" />
		</label>
		<label>
			<span>Password</span>
			<input type="password" name="pswd" value="" class="field" />
		</label>
		<p id="messageArea" class="message">
			{$message|escape}
		</p>
		<input type="submit" id="mode" name="mode" value="Login" class="login-btn" />
	</div>
	{/if}
</form>
</div>
</body>
</html>
