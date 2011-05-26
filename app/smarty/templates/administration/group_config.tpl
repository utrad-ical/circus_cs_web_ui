{capture name="extra"}
<script language="Javascript" type="text/javascript">
<!--

var data = {$groupList|@json_encode};

{literal}
$(function () {
	var cancel = function (time) {
		$('#editor').hide(time);
		$('#groups tr').removeClass('editing');
	}

	$('.edit-button').click(function (event) {
		cancel(0);
		var tr = $(event.target).closest('tr.group').addClass('editing');
		var group_id = $('.group-id', tr).text();
		var group_data = data[group_id];
		if (!group_data)
			return;
		var editor = $('#editor');
		$('input.privCheck', editor).each(function () {
			var targetPriv = $(this).val();
			var checked = group_data.privs.indexOf(targetPriv) >= 0;
			$(this).attr('checked', checked ? 'checked' : '');
		});
		editingTarget = group_id;
		$('#editor-header').text('Editing Gruop: ' + group_id);
		$('#color-set').val(group_data.color_set);
		$('#target-group').val(group_id);
		$('#new-name').val(group_id);
		editor.show(300);
	});

	$('#add-group-button').click(function () {
		cancel();
		var editor = $('#editor');
		$('input.privCheck', editor).attr('checked', '');
		$('#editor-header').text('Adding New Group');
		$('#target-group').val('');
		$('#new-name').val('');
		editingTarget = null;
		editor.show(300);
	});

	$('.delete-button').click(function (event) {
		cancel(0);
		var tr = $(event.target).closest('tr.group');
		var group_id = $('.group-id', tr).text();
		if (!confirm("Delete user group '" + group_id + "'? This cannot be undone."))
			return;
		var form = $('#delete-form');
		$('input[name=target]', form).val(group_id);
		form.submit();
	});

	$('#cancel-button').click(function () {
		$('#groups tr').removeClass('editing');
		cancel(300);
	});

	$('#save-button').click(function () {
		if (!$('#new-name').val()) {
			alert('Specify the group name.');
			return;
		}
		$('#editor-form').submit();
	});


	$('span.privname').hover(
		function (event) {
			var privname = $(event.target).text();
			$('span.priv-' + privname).addClass('highlight')
			.closest('tr').addClass('column');
		},
		function () {
			$('span.privname').removeClass('highlight');
			$('#groups tr').removeClass('column');
		}
	);

});
-->
</script>

<style type="text/css">

#message { margin: 1em 0; padding: 1em; font-weight: bold; color: red; }

#editor { margin: 2em 0 0 0; background-color: #f9f9f9; }
#editor table#privs-table { margin: 1em; }
#editor li.priv { margin: 0.2em; 0; }
#editor td.priv-desc { color: gray; font-size: small; }

#groups { width: 100%; }
#groups td.group-id { font-weight: bold; text-align: left; width: 100px; }
#groups td.privileges { text-align: left; }
#groups td.operations { width: 10em; }

#panel { margin: 0.5em; }

span.privname { background-color: #ddd; border-radius: 3px; padding: 0 0.5em; }
#editor span.privname { font-weight: bold; }

span.highlight { background-color: #8a3b2b; color: white; }
#groups tr.editing td.group-id { background-color: salmon; }
</style>

{/literal}
{/capture}
{capture name="require"}
css/popup.css
{/capture}
{include file="header.tpl" require=$smarty.capture.require
	head_extra=$smarty.capture.extra body_class="spot"}

<h2>Group configuration</h2>

<div id="message">{$message|escape}</div>

<table id="groups" class="col-tbl">
	<tr>
		<th>Group ID</th>
		<th>Color Set</th>
		<th>Privileges</th>
		<th>Operation</th>
	</tr>
	{foreach from=$groupList item=group name=cnt}
	<tr class="group">
		{if $group.group_id != "admin"}
		{/if}
		<td class="group-id">{$group.group_id|escape}</td>
		<td class="color-set">{$group.color_set|escape}</td>
		<td class="privileges">
			{foreach from=$group.privs item=priv}
			<span class="privname priv-{$priv}">{$priv}</span>
			{/foreach}
		</td>
		<td class="operations">{if $group.group_id != 'admin'}
			<input type="button" class="edit-button form-btn" value="edit" />
			<input type="button" class="delete-button form-btn" value="delete" />
		{/if}</td>
	</tr>
	{/foreach}
</table>

<div id="panel">
	<input type="button" class="form-btn" value="Add new group" id="add-group-button" />
</div>

<div id="editor" style="display: none">
<form id="editor-form" method="post">
	<input type="hidden" name="mode" value="set" />
	<input type="hidden" id="target-group" name="target" />
	<input type="hidden" name="ticket" value="{$ticket|escape}" />
	<h3 id="editor-header"></h3>
	<p>New group name: <input type="text" name="newname" id="new-name" /></p>
	<p>Color Set:
		<select id="color-set" name="colorSet">
			<option value="user">user</option>
			<option value="admin">admin</option>
			<option value="guest">guest</option>
		</select>
	</p>

	<table class="detail-tbl" id="privs-table">
		{foreach from=$privs item=priv}
		<tr class="priv">
			<td>
				<input type="checkbox" class="privCheck" name="priv[]" id="cbx-{$priv[0]}" value="{$priv[0]|escape}"/>
				<label for="cbx-{$priv[0]}">
				<span class="privname priv-{$priv[0]}">{$priv[0]|escape}</span>
				</label>
			</td>
			<td class="priv-desc">{$priv[1]|escape}</span></td>
		</tr>
		{/foreach}
	</table>
	<div id="editor-commit">
		<input type="button" id="save-button" class="form-btn" value="Save" />
		<input type="button" id="cancel-button" class="form-btn" value="Cancel" />
	</div>
</form>
</div>

<form id="delete-form" method="post">
	<input type="hidden" name="mode" value="delete" />
	<input type="hidden" name="target" />
	<input type="hidden" name="ticket" value="{$ticket|escape}" />
</form>

{include file="footer.tpl"}