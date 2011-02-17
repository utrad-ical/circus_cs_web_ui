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
<script language="javascript" type="text/javascript" src="../jq/jquery-1.3.2.min.js"></script>
<script language="javascript" type="text/javascript" src="../jq/jq-btn.js"></script>
<script language="javascript" type="text/javascript" src="../js/hover.js"></script>
<script language="javascript" type="text/javascript" src="../js/viewControl.js"></script>
<link rel="shortcut icon" href="../favicon.ico" />

<script language="Javascript">;
<!--
{literal}

function Clearlogs(filaneme)
{
	location.replace('server_logs.php?mode=clear&filename=' + filaneme);
}

function Downloadlogs(filaneme)
{
	location.replace('download_textfile.php?filename=' + filaneme);
}

function RefleshLogList()
{
	location.replace('server_logs.php');
}

{/literal}
-->
</script>

<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/popup.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../js/hover.js"></script>
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
			<form id="form1" name="form1" onsubmit="return false;">
			<input type="hidden" id="ticket" value="{$params.ticket}" />

			<h2>Server logs</h2>

			<div>
				<form id="form1" name="form1">
					<table class="col-tbl">
						<tr>
							<th>File name</th>
							<th>Last updates</th>
							<th>Size [byte]</th>
							<th>Link</th>
						</tr>
	
						{foreach from=$fileData item=item name=cnt}
							<tr>
								<td class="al-l">{$item[0]}</td>
								<td>{$item[1]}</td>
								<td class="al-r">{$item[2]}</td>

								<td>
									<input type="button" id="downloadButton{$smarty.foreach.cnt.iteration}" value="download"
									 class="form-btn" onClick="Downloadlogs('{$item[0]}');" />
									<input type="button" id="clearButton{$smarty.foreach.cnt.iteration}" value="clear"
									 class="form-btn" onClick="Clearlogs('{$item[0]}');" />
								</td>
							</tr>
						{/foreach}
					</table>
			</div>
			
			</form>
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
</html>
