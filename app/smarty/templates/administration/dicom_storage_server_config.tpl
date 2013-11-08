{capture name="extra"}
<script type="text/javascript">
<!--

var data = {$configData|@json_encode};

{literal}

function UpdateConfig()
{
	if(confirm('Do you want to update configuration file?'))
	{
		var params = {
			mode:            'update',
			newAeTitle:              $("#newAeTitle").val(),
			newPort:                 $("#newPort").val(),
			newThumbnailFlg:         $('input[name="newThumbnailFlg"]:checked').val(),
			newThumbnailSize:        $("#newThumbnailSize").val(),
			newCompressDicomFile:    $('input[name="newCompressDicomFile"]:checked').val(),
			newOverwriteDicomFile:   $('input[name="newOverwriteDicomFile"]:checked').val(),
			newOverwritePatientName: $('input[name="newOverwritePatientName"]:checked').val(),
			ticket:           data.ticket
		};
		var address = 'dicom_storage_server_config.php?' + $.param(params);
		location.replace(address);
	}
}

function CancelConfig()
{
	$("#newAeTitle").val(data.aeTitle);
	$("#newPort").val(data.port);
	$("input[name='newThumbnailFlg']").filter(function(){ return ($(this).val() == data.thumbnailFlg) }).prop("checked", true);
	$("input[name='newCompressDicomFile']").filter(function(){ return ($(this).val() == data.compressDicomFile) }).prop("checked", true);
	$("input[name='newOverwriteDicomFile']").filter(function(){ return ($(this).val() == data.overwriteDicomFile) }).prop("checked", true);
	$("input[name='newOverwritePatientName']").filter(function(){ return ($(this).val() == data.overwritePatientName) }).prop("checked", true);
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

#message { margin: 1em 0 0 0; padding: 1em; font-weight: bold; color: red; }
#editor { margin: 1em 0 0 1em; }
#editor-commit { margin: 1em 0 0 1em; }

</style>

{/literal}
{/capture}
{include file="header.tpl"
	head_extra=$smarty.capture.extra body_class="spot"}

<h2><div class="breadcrumb"><a href="administration.php">Administration</a> &gt;</div>
Configuration of DICOM storage server</h2>

<div id="message">{$params.message}</div>

<div id="editor">
	<table class="detail-tbl">
		<tr>
			<th style="width: 20em;"><span class="trim01">AE title</th>
			<td><input id="newAeTitle" size="20" type="text" value="{$configData.aeTitle|escape}" /></td>
		</tr>

		<tr>
			<th><span class="trim01">Port number</th>
			<td><input id="newPort" size="20" type="text" value="{$configData.port|escape}" /></td>
		</tr>

		<tr>
			<th><span class="trim01">Create thumbnail images</th>
			<td>
				<label><input name="newThumbnailFlg" type="radio" value="1"{if $configData.thumbnailFlg} checked="checked"{/if} />TRUE</label>
				<label><input name="newThumbnailFlg" type="radio" value="0"{if !$configData.thumbnailFlg} checked="checked"{/if} />FALSE</label>
			</td>
		</tr>

		<tr>
			<th><span class="trim01">Default thumbnail size</th>
			<td><input id="newThumbnailSize" size="20" type="text" value="{$configData.defaultThumbnailSize|escape}" /></td>
		</tr>
		
		<tr>
			<th><span class="trim01">Compress DICOM image with lossless JPEG</th>
			<td>
				<label><input name="newCompressDicomFile" type="radio" value="1"{if $configData.compressDicomFile} checked="checked"{/if} />TRUE</label>
				<label><input name="newCompressDicomFile" type="radio" value="0"{if !$configData.compressDicomFile} checked="checked"{/if} />FALSE</label>
			</td>
		</tr>

		<tr>
			<th><span class="trim01">Overwrite DICOM file</th>
			<td>
				<label><input name="newOverwriteDicomFile" type="radio" value="1"{if $configData.overwriteDicomFile} checked="checked"{/if} />TRUE</label>
				<label><input name="newOverwriteDicomFile" type="radio" value="0"{if !$configData.overwriteDicomFile} checked="checked"{/if} />FALSE</label>
			</td>
		</tr>

		<tr>
			<th><span class="trim01">Overwrite patient's name</th>
			<td>
				<label><input name="newOverwritePatientName" type="radio" value="1"{if $configData.overwritePatientName} checked="checked"{/if} />TRUE</label>
				<label><input name="newOverwritePatientName" type="radio" value="0"{if !$configData.overwritePatientName} checked="checked"{/if} />FALSE</label>
			</td>
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
