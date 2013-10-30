{capture name="extra"}
{literal}

<script language="Javascript" type="text/javascript">;
<!--
$(function () {
	var getFile = function (btn) {
		return $(btn).closest('tr').find('td.file-name').text();
	}

	$('#filelist input[type=button][value=download]').click(function (event) {
		var fname = getFile(event.target);
		location.replace('download_textfile.php?filename=' + fname);
	});

	$('#filelist input[type=button][value=clear]').click(function (event) {
		var fname = getFile(event.target);
		if (confirm('Clear "' + fname + '"?'))
			location.replace('server_logs.php?mode=clear&filename=' + fname);
	});

});
-->
</script>
<style type="text/css">
#filelist td.file-name { text-align: left; }
#filelist td.last-update { text-align: left; }
#filelist td.size { text-align: right; }
</style>

{/literal}
{/capture}
{include file="header.tpl" head_extra=$smarty.capture.extra body_class="spot"}
<form id="form1" name="form1" onsubmit="return false;">
<input type="hidden" id="ticket" value="{$params.ticket}" />

<h2><div class="breadcrumb"><a href="administration.php">Administration</a> &gt;</div>
Server logs</h2>
<form id="form1" name="form1">
	<table class="col-tbl" id="filelist">
		<tr>
			<th>file name</th>
			<th>last update</th>
			<th>size [byte]</th>
			<th>link</th>
		</tr>

		{foreach from=$fileData item=item name=cnt}
		<tr>
			<td class="file-name">{$item.name|escape}</td>
			<td class="last-update">{$item.lastUpdate|escape}</td>
			<td class="size">{$item.size|number_format}</td>
			<td>
				<input type="button" value="download" class="form-btn" />
				<input type="button" value="clear" class="form-btn" />
			</td>
		</tr>
		{/foreach}
	</table>
</form>
{include file="footer.tpl"}