{capture name="extra"}
<script language="Javascript" type="text/javascript">
<!--

var data = {$configData|@json_encode};

{literal}

function UpdateConfig()
{
	if(confirm('Do you want to update configuration file?'))	
	{
		var params = { 	mode:            'update',
				       	newAeTitle:       $("#newAETitle").val(),
						newPort:          $("#newPort").val(),
						newThumbnailFlg:  $('input[name="newThumbnailFlg"]:checked').val(),
						newCompressFlg:   $('input[name="newCompressFlg"]:checked').val(),
						newThumbnailSize: $("#newThumbnailSize").val(),
						ticket:           data.ticket };

		var address = 'dicom_storage_server_config.php?' + $.param(params);
		location.replace(address);
	}
}

function CancelConfig()
{
	$("#newAeTitle.value").val(data.aeTitle);
	$("#newPort").val(data.port);
	$("input[name='newThumbnailFlg']").filter(function(){ return ($(this).val() == data.thumbnailFlg) }).attr("checked", true);
	$("input[name='newCompressFlg']").filter(function(){ return ($(this).val() == data.compressFlg) }).attr("checked", true);
	$("#newThumbnailSize").val(data.defaultThumbnailSize);
}

function RestartStorageSv()
{
	if(confirm('Do you restart DICOM storage server?'))
	{
		var address = 'dicom_storage_server_config.php?mode=restartSv&ticket=' + data.ticket;
		location.replace(address);
	}
}
-->
</script>

<style type="text/css">

#message { margin: 1em 0 0 0 1em; padding: 1em; font-weight: bold; color: red; }
#editor { margin: 1em 0 0 1em; }
#editor-commit { margin: 1em 0 0 1em; }

</style>

{/literal}
{/capture}
{capture name="require"}
css/popup.css
{/capture}
{include file="header.tpl" require=$smarty.capture.require
	head_extra=$smarty.capture.extra body_class="spot"}

<h2>Configuration of DICOM storage server</h2>

<div id="message">{$params.message}</div>

<div id="editor">
	<table class="detail-tbl">
		<tr>
			<th style="width: 20em;"><span class="trim01">AE title</th>
			<td><input id="newAETitle" size="20" type="text" value="{$configData.aeTitle|escape}" /></td>
		</tr>

		<tr>
			<th><span class="trim01">Port number</th>
			<td><input id="newPort" size="20" type="text" value="{$configData.port|escape}" /></td>
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
			<th><span class="trim01">Default thumbnail size</th>
			<td><input id="newThumbnailSize" size="20" type="text" value="{$configData.defaultThumbnailSize|escape}" /></td>
		</tr>

		<tr>
			<th><span class="trim01">Path for log files</th>
			<td><input id="newlogPath" size="60" type="text" value="{$configData.logPath|escape}" disabled="disabled" /></td>
		</tr>

		<tr>
			<th><span class="trim01">IP address of DB server</th>
			<td><input id="newDbHost" size="20" type="text" value="{$configData.dbHost|escape}" disabled="disabled" /></td>
		</tr>

		<tr>
			<th><span class="trim01">Port number of DB server</th>
			<td><input id="newDbPort" size="20" type="text" value="{$configData.dbPort|escape}" disabled="disabled" /></td>
		</tr>

		<tr>
			<th><span class="trim01">Database name of DB server</th>
			<td><input id="newDbName" size="20" type="text" value="{$configData.dbName|escape}" disabled="disabled" /></td>
		</tr>

		<tr>
			<th><span class="trim01">User name to connect DB server</th>
			<td><input id="newDbUser" size="20" type="text" value="{$configData.dbUser|escape}" disabled="disabled" /></td>
		</tr>

		<tr>
			<th><span class="trim01">Password to connect DB server</th>
			<td><input id="newDbPassword" size="20" type="text" value="{$configData.dbPassword|escape}" disabled="disabled" /></td>
		</tr>
	</table>

	<div id="editor-commit">
		<p>
			<input type="button" value="Update" onclick="UpdateConfig();"
				class="form-btn{if $restartFlg} form-btn-disabled" disabled="disabled{/if}" />&nbsp;
			<input type="button" id="addBtn" class="form-btn" value="Cancel" onclick="CancelConfig();"
				class="form-btn{if $restartFlg} form-btn-disabled" disabled="disabled{/if}" />&nbsp;
			{if $restartFlg}
				<input type="button" id="cancelBtn" class="form-btn form-btn-disabled" value="Restart" onclick="RestartStorageSv();" />
			{/if}
		</p>
	</div>
</div>

{include file="footer.tpl"}
