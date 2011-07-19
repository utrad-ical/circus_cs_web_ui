<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />

<title>CIRCUS CS {$smarty.session.circusVersion}</title>

<link href="../css/import.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../jq/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="../js/circus-common.js"></script>
<script language="javascript" type="text/javascript" src="../jq/jquery.upload-1.0.2.min.js"></script>
<script language="javascript" type="text/javascript" src="../js/viewControl.js"></script>
<link rel="shortcut icon" href="../favicon.ico" />

<script language="Javascript">;
<!--
{literal}

$(function() {

	$('#uploadBtn').click(function() {

		if(confirm('Do you upload the plug-in package to CIRCUS CS?'))
		{
			$("#field").upload('plugin_init_registration.php', function(data){
				 
				$("#message").html(data);
			 
			}, "html");
		}
	});
});

-->
{/literal}

</script>

<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />

{literal}
<style type="text/css">

div.line{
	margin-top: 10px;
	margin-bottom: 10px;
	border-bottom: solid 2px #8a3b2b;
}


</style>
{/literal}
</head>

<body class="spot">
<div id="page">
	<div id="container" class="menu-back">
		<!-- ***** #leftside ***** -->
		<div id="leftside">
			{include file='menu.tpl'}
		</div>
		<!-- / #leftside END -->

		<div id="content">
			<h2>Add plug-in from packaged file</h2>

			<form id="form1" name="form1">
				<input type="hidden" id="ticket" name="ticket" value="{$params.ticket|escape}" />
				<div id="field">
					<span style="font-weight:bold;">File name:</span>
					<input id="uploadFname" type="file" name="upfile" size="50" />
					<input type="button" id="uploadBtn" value="upload" />
				</div>
			</form>

			<div class="line"></div>

			<div id="message"></div>

		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
</html>
