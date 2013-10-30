{capture name="extra"}
<script type="text/javascript">
<!--
{literal}

$(function() {
	$('.delete').click(function(event) {
		var id = $(event.target).closest('tr').find('.storage-id').text();
		if(confirm('Do you want to delete storage ID='+ id + ' ?'))
		{
			$('#target-mode').val('delete');
			$('#target-id').val(id);
			$('#list-form').submit();
		}
	});

	$('.set-current').click(function(event) {
		var id = $(event.target).closest('tr').find('.storage-id').text();
		$('#target-mode').val('setCurrent');
		$('#target-id').val(id);
		$('#list-form').submit();
	});

	$('#add-open').click(function() {
		$('input[name=path]', '#add-new').val('');
		$('select[name=type]', '#add-new').prop('selectedIndex', 0);
		$('#add-new').show(300);
	});

	$('#add-cancel').click(function() { $('#add-new').hide(); });
});
-->
</script>

<style type="text/css">
#storage-list, #add-new p {
	margin: 1em;
}
#storage-list td { padding: 0.2em 1em; }
#storage-list .current-use { font-weight: bold; }
#storage-list .rowheader, #storage-list .rowheader td {
	border-left: 1px solid #ddd;
	border-right: 1px solid #ddd;
	text-align: left;
	padding: 10px 0 0 3px;
	font-weight: bold;
}
#storage-list .storage-path { text-align: left; }
#message { font-weight: bold; color: red; margin: 1.5em; }
#add-new { display: none; margin-top: 1.3em; }
</style>
{/literal}
{/capture}
{include file="header.tpl" head_extra=$smarty.capture.extra
	body_class="spot"}
<h2><div class="breadcrumb"><a href="administration.php">Administration</a> &gt;</div>
Data storage configuration</h2>

<div id="message">{$message|escape|nl2br}</div>

<h3>Storage list</h3>
<form id="list-form" action="data_storage_config.php" method="post">
<input type="hidden" name="ticket" value="{$ticket}" />
<input type="hidden" id="target-mode" name="mode" value="" />
<input type="hidden" id="target-id" name="id" value="" />
<table id="storage-list" class="col-tbl">
	<thead>
		<tr>
			<th>ID</th>
			<th>Path</th>
			<th>Current Use</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
	{assign var=t value=-1}
	{foreach from=$storage item=item name=cnt}
		{if $t != $item->type}
		{assign var=t value=$item->type}
		<tr class="rowheader"><td colspan="4">{$item->storageTypeString()|escape}</td></tr>
		{/if}
		<tr class="{cycle values=",column"}{if $item->current_use} current-use{/if}">
			<td><span class="storage-id">{$item->storage_id|escape}</span></td>
			<td class="storage-path">{$item->path|escape}</td>
			<td>{if $item->current_use==true}TRUE{else}FALSE{/if}</td>
			<td>
				<input type="button" value="Delete" class="delete s-btn form-btn"{if $item->current_use} disabled="disabled"{/if}/>
				<input type="button" value="Set as current" class="set-current s-btn form-btn"{if $item->current_use} disabled="disabled"{/if}/>
			</td>
		</tr>
	{/foreach}
	</tbody>
</table>
</form>

<input type="button" id="add-open" value="Add new storage area" class="form-btn" />

<div id="add-new">
	<h3>Add new storage area</h3>
	<form action="data_storage_config.php" method="post">
		<input type="hidden" name="ticket" value="{$ticket}" />
		<input type="hidden" name="mode" value="add" />
		<table class="detail-tbl">
			<tr>
				<th style="width: 5em;"><span class="trim01">Path</th>
				<td><input type="text" size="60" name="path" /></td>
			</tr>
			<tr>
				<th><span class="trim01">Type</th>
				<td>
					<select name="type">
						<option value="1">DICOM storage</option>
						<option value="2">Plug-in result</option>
						<option value="3">Web cache</option>
					</select>
				</td>
			</tr>
		</table>
		<p>
			<input type="submit" class="form-btn" value="Save" />
			<input type="button" id="add-cancel" class="form-btn" value="Cancel" />
		</p>
	</form>
</div>
{include file="footer.tpl"}