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

function UpdateConfig()
{
		
	if(confirm('Do you want to update configuration file?'))	
	{
		var address = 'dicom_storage_server_config.php?mode=update'
				    + '&newAeTitle='      + encodeURIComponent($("#newAETitle").val())
				    + '&newPortNumber='   + encodeURIComponent($("#newPortNumber").val())
				    + '&newLogFname='     + encodeURIComponent($("#newLogFname").val())
				    + '&newErrLogFname='  + encodeURIComponent($("#newErrLogFname").val())
				    + '&newThumbnailFlg=' + $('input[name="newThumbnailFlg"]:checked').val()
				    + '&newCompressFlg='  + $('input[name="newCompressFlg"]:checked').val()
				    + '&newDbConnectAddress=' + encodeURIComponent($("#newDbConnectAddress").val())
				    + '&newDbConnectPort='    + encodeURIComponent($("#newDbConnectPort").val())
				    + '&newDbName='           + encodeURIComponent($("#newDbName").val())
				    + '&newDbUserName='       + encodeURIComponent($("#newDbUserName").val())
				    + '&newDbPassword='       + encodeURIComponent($("#newDbPassword").val())
					+ '&ticket=' + $("#ticket").val();
		location.replace(address);	
	}
}

function CancelConfig()
{
	$("#newAeTitle.value").val($("#oldAeTitle").val());
	$("#newPortNumber").val($("#oldPortNumber").val());
	$("#newLogFname").val($("#oldLogFname").val());
	$("#newErrLogFname").val($("#oldErrLogFname").val());
	$("input[name='newThumbnailFlg']").filter(function(){ return ($(this).val() == $("#oldThumbnailFlg").val()) }).attr("checked", true);
	$("input[name='newCompressFlg']").filter(function(){ return ($(this).val() == $("#oldCompressFlg").val()) }).attr("checked", true);
}

function RestartStorageSv()
{
	if(confirm('Do you restart DICOM storage server?'))
	{
		var address = 'dicom_storage_server_config.php?mode=restartSv&ticket=' + $("#ticket").val();
		location.replace(address);
	}
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
			<h2>Configuration of DICOM storage server</h2>

			<form id="form1" name="form1">
				<input type="hidden" id="ticket"               value="{$configData.ticket|escape}" />
				<input type="hidden" id="oldAETitle"           value="{$configData.aeTitle|escape}">
				<input type="hidden" id="oldPortNumber"        value="{$configData.portNumber|escape}">
				<input type="hidden" id="oldLogFname"          value="{$configData.logFname|escape}">
				<input type="hidden" id="oldErrLogFname"       value="{$configData.errLogFname|escape}">
				<input type="hidden" id="oldThumbnailFlg"      value="{$configData.thumbnailFlg}">
				<input type="hidden" id="oldCompressFlg"       value="{$configData.compressFlg}">
				<input type="hidden" id="oldDbConnectAddress"  value="{$configData.dbConnectAddress}">
				<input type="hidden" id="oldDbConnectPort"     value="{$configData.dbConnectPort}">
				<input type="hidden" id="oldDbName"            value="{$configData.dbName}">
				<input type="hidden" id="oldDbUserName"        value="{$configData.dbUserName}">
				<input type="hidden" id="oldDbPassword"        value="{$configData.dbPassword}">

				<div id="message" class="mt5 ml20">{$params.message}</div>

				<div class="mt20 ml20">
					<table class="detail-tbl">
						<tr>
							<th style="width: 20em;"><span class="trim01">AE title</th>
							<td><input id="newAETitle" size="20" type="text" value="{$configData.aeTitle|escape}" /></td>
						</tr>

						<tr>
							<th><span class="trim01">Port number</th>
							<td><input id="newPortNumber" size="20" type="text" value="{$configData.portNumber|escape}" /></td>
						</tr>

						<tr>
							<th><span class="trim01">Log file</th>
							<td><input id="newLogFname" size="60" type="text" value="{$configData.logFname|escape}" disabled="disabled" /></td>
						</tr>


						<tr>
							<th><span class="trim01">Error log file</th>
							<td><input id="newErrLogFname" size="60" type="text" value="{$configData.errLogFname|escape}" disabled="disabled" /></td>
						</tr>

						<tr>
							<th><span class="trim01">Create thumbnail images</th>
							<td>
								<input name="newThumbnailFlg" type="radio" value="1"{if $configData.thumbnailFlg} checked="checked"{/if} />TRUE
								<input name="newThumbnailFlg" type="radio" value="0"{if !$configData.thumbnailFlg} checked="checked"{/if} />FALSE
							</td>
						</tr>

						<tr>
							<th><span class="trim01">Compress DICOM image with lossless JPEG</th>
							<td>
								<input name="newCompressFlg" type="radio" value="1"{if $configData.compressFlg} checked="checked"{/if} />TRUE
								<input name="newCompressFlg" type="radio" value="0"{if !$configData.compressFlg} checked="checked"{/if} />FALSE
							</td>
						</tr>

						<tr>
							<th><span class="trim01">IP address of DB server</th>
							<td><input id="newDbConnectAddress" size="20" type="text" value="{$configData.dbConnectAddress|escape}" disabled="disabled" /></td>
						</tr>

						<tr>
							<th><span class="trim01">Port number of DB server</th>
							<td><input id="newDbConnectPort" size="20" type="text" value="{$configData.dbConnectPort|escape}" disabled="disabled" /></td>
						</tr>

						<tr>
							<th><span class="trim01">Database name of DB server</th>
							<td><input id="newDbName" size="20" type="text" value="{$configData.dbName|escape}" disabled="disabled" /></td>
						</tr>

						<tr>
							<th><span class="trim01">User name to connect DB server</th>
							<td><input id="newDbUserName" size="20" type="text" value="{$configData.dbUserName|escape}" disabled="disabled" /></td>
						</tr>

						<tr>
							<th><span class="trim01">Password to connect DB server</th>
							<td><input id="newDbPassword" size="20" type="text" value="{$configData.dbPassword|escape}" disabled="disabled" /></td>
						</tr>
					</table>

					<div class="pl20 mb20 mt10">
						<p>
							<input type="button" value="Update" onClick="UpdateConfig();"
								class="form-btn{if $restartFlg} form-btn-disabled" disabled="disabled{/if}" />&nbsp;
							<input type="button" id="addBtn" class="form-btn" value="Cancel" onClick="CancelConfig();"
								class="form-btn{if $restartFlg} form-btn-disabled" disabled="disabled{/if}" />&nbsp;
							{if $restartFlg}
								<input type="button" id="cancelBtn" class="form-btn form-btn-disabled" value="Restart" onClick="RestartStorageSv();" />
							{/if}
						</p>
					</div>
				</div>
			</form>
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
</html>

