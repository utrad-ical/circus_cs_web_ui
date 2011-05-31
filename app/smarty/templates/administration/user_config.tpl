{capture name="require"}
css/popup.css
{/capture}
{capture name="extra"}
<script type="text/javascript">
<!--
var data = {$userList|@json_encode};
var current_user = '{$user->user_id}';

{literal}
$(function () {
	var cancel = function (time) {
		$('#editor').hide(time);
		$('#users tr').removeClass('editing');
	}

	$('.edit-button').click(function (event) {
		cancel();
		var editor = $('#editor');
		var tr = $(event.target).closest('tr.user');
		var user_id = $('.user-id', tr).text();
		var user_data = data[user_id];
		$('#editor-header', editor).text('Editing: ' + user_data.user_id);
		$('#target-user', editor).val(user_id);
		$.each(
			['user_id', 'user_name', 'enabled', 'today_disp', 'darkroom', 'show_missed', 'anonymized'],
			function (dum, key) {
				var dat = user_data[key];
				if (dat === true) dat = 'true';
				if (dat === false) dat = 'false';
				console.log(key, '=>', dat);
				$('input[type=radio][name=' + key + ']', editor).val([dat]);
				$('input[type=checkbox][name=' + key + ']', editor).val([dat]);
				$('input[type=text][name=' + key + ']', editor).val(dat);
			}
		);
		$('#groupList').val(user_data.groups);
		tr.addClass('editing');
		editor.show(300);
	});


	$('.delete-button').click(function (event) {
		cancel(0);
		var tr = $(event.target).closest('tr.user');
		var user_id = $('.user-id', tr).text();
		if (!confirm("Delete user '" + user_id + "'?"))
			return;
		var form = $('#delete-user-form');
		$('#delete-target').val(user_id);
		form.submit();
	});

	$('#add-user-button').click(function () {
		cancel();
		var editor = $('#editor');
		$('#editor-header', editor).text('Add New User');
		$('#target-user', editor).val('');
		$('input[type=radio]', editor).val([]);
		$('input[type=text]', editor).val('');
		$('input[name=enabled]', editor).val(['true']);
		$('#groupList').val([]);
		editor.show(300);
	});

	$('#cancel-button').click(function () {
		cancel(300);
	});

	$('#save-button').click(function () {
		$('#editor-form').submit();
	});

	$('#users tr').each(function () {
		if ($('td.user-id', this).text() == current_user)
		{
			$('input[type=button][value=delete]', this)
				.attr('disabled', 'disabled')
				.trigger('flush');
		}
	});
});

-->
</script>

<style type="text/css">

#message { margin: 1em 0; padding: 1em; font-weight: bold; color: red; }

#users { width: 100%; }
#users td.allow { }
#users div.al {
	text-align: left;
	width: 150px;
	overflow: hidden;
	text-overflow: ellipsis;
}
#users tr.user-disabled { color: silver; }

#users tr.editing td.user-id { background-color: salmon; }

#editor { margin: 2em 0 0 0; background-color: #f9f9f9; }
#panel { margin: 0.5em; }

</style>

{/literal}
{/capture}
{include file="header.tpl" body_class="spot" head_extra=$smarty.capture.extra
	require=$smarty.capture.require}

<h2>User configuration</h2>

<form id="editor-form" method="post">
	<input type="hidden" name="mode" value="set" />
	<input type="hidden" name="ticket" value="{$ticket|escape}" />

	<div id="message">{$message|escape}</div>

	<table id="users" class="col-tbl">
		<tr>
			<th>User ID</th>
			<th>User name</th>
			<th>Enabled</th>
			<th>Group(s)</th>
			<th>Today</th>
			<th>Darkroom</th>
			<th>Anonymize</th>
			<th>Show missed</th>
			<th>Operation</th>
		</tr>

		{foreach from=$userList item=item name=cnt}
		<tr class="user{if !$item.enabled} user-disabled{/if}">
			<td class="user-id">{$item.user_id|escape}</td>
			<td class="user-name">{$item.user_name|escape}</td>
			<td class="user-enabled">{$item.enabled|OorMinus}</td>
			<td class="user-groups">{$item.groups|@implode:', '|escape}</td>
			<td>{$item.today_disp|escape}</td>
			<td>{if $item.darkroom}black{else}white{/if}</td>
			<td>{$item.anonymized|OorMinus}</td>
			<td>{$item.show_missed|escape}</td>
			<td>
				<input type="button" class="edit-button form-btn" value="edit" />
				<input type="button" class="delete-button form-btn" value="delete" />
			</td>
		</tr>
		{/foreach}
	</table>

	<div id="panel">
		<input type="button" class="form-btn" value="Add new user" id="add-user-button" />
	</div>

	<div id="editor" style="display: none">
		<h3 id="editor-header">Editing: </h3>
		<input type="hidden" id="target-user" name="target" />
		<table class="detail-tbl">
			<tr>
				<th>User ID</th>
				<td><input size="20" type="text" name="user_id" />
				Use only alphabets and numbers.</td>
			</tr>
			<tr>
				<th>User name</th>
				<td><input size="40" type="text" name="user_name" /></td>
			</tr>
			<tr>
				<th>Password</th>
				<td><input size="40" type="password" name="passcode"></td>
			</tr>
			<tr>
				<th>Enabled</th>
				<td><input type="checkbox" name="enabled" id="enabled-true" value="true" />
				<label for="enabled-true">Enable login</label></td>
			</tr>
			<tr>
				<th>Groups</th>
				<td>
					<select id="groupList" size="10" multiple="multiple" name="groups[]">
						{foreach from=$groupList item=item}
						<option value="{$item->group_id}">{$item->group_id|escape}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<th>Display today's list</th>
				<td>
					<input name="today_disp" type="radio" value="series" id="today-series"/>
					<label for="today-series">Today's Series</label>&nbsp;&nbsp;
					<input name="today_disp" type="radio" value="cad"  id="today-cad" />
					<label for="today-cad">Today's CAD</label>
				</td>
			</tr>
			<tr>
				<th>Darkroom mode</th>
				<td>
					<input name="darkroom" type="radio" value="false" id="darkroom-white" />
					<label for="darkroom-white">white</label>&nbsp;&nbsp;
					<input name="darkroom" type="radio" value="true" id="darkroom-black" />
					<label for="darkroom-black">black</label>
				</td>
			</tr>
			<tr>
				<th>Anonymization</th>
				<td>
					<input name="anonymized" type="radio" value="true" id="anonymized-true" />
					<label for="anonymized-true">TRUE</label>&nbsp;&nbsp;
					<input name="anonymized" type="radio" value="false" id="anonymized-false" />
					<label for="anonymized-false">FALSE</label>
				</td>
			</tr>
			<tr>
				<th>Missed lesions display</th>
				<td>
					<input name="show_missed" type="radio" value="own" id="show-missed-own" />
					<label for="show-missed-own">own</label>&nbsp;&nbsp;
					<input name="show_missed" type="radio" value="all" id="show-missed-all" />
					<label for="show-missed-all">all</label>&nbsp;&nbsp;
					<input name="show_missed" type="radio" value="none" id="show-missed-none" />
					<label for="show-missed-none">none</label>
				</td>
			</tr>
		</table>
		<div>
			<input type="button" id="save-button" class="form-btn" value="save" />
			<input type="button" id="cancel-button" class="form-btn" value="cancel" />
		</div>
	</div>
</form>

<form id="delete-user-form" method="post">
	<div style="display: none">
		<input type="hidden" name="ticket" value="{$ticket|escape}" />
		<input type="hidden" name="mode" value="delete" />
		<input type="hidden" id="delete-target" name="target" />
	</div>
</form>

{include file="footer.tpl"}