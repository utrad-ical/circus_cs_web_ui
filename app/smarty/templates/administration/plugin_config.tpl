{capture name="require"}
js/jquery.formserializer.js
jq/ui/jquery-ui.min.js
jq/ui/theme/jquery-ui.custom.css
{/capture}
{capture name="extra"}
<script type="text/javascript">
var data = {$plugins|@json_encode};

{literal}
$(function () {
	function cancel(time) {
		$('#editor').hide(time);
		$('#plugins tr').removeClass('editing');
	}

	$('.edit_button').click(function(event) {
		cancel(0);
		var tr = $(event.target).closest('tr.plugin').addClass('editing');
		var plugin_id = parseInt($('.plugin_id', tr).text());
		var plugin_data = null;
		$.each(data, function(i, item) { if (item.plugin_id == plugin_id) plugin_data = item; });
		if (plugin_data === null) return;
		$('#target').text(plugin_data.full_name);
		var editor = $('#editor');
		$('#plugin_id').val(plugin_id);
		editor.fromObject(plugin_data);
		editor.show(300);
	});

	$('#cancel_button').click(function () {
		$('#groups tr').removeClass('editing');
		cancel(300);
	});

	$('#save_button').click(function () {
		$('#editor_form').submit();
	});

	$('#order_button').click(function() {
		cancel(0);
		var dialog = $('#order_dialog');
		var ul = $('#plugin_order');
		ul.empty();
		$.each(data, function(i, item) {
			var li = $('<li>').text(item.full_name).data('pid', item.plugin_id).appendTo(ul);
			if (!item.exec_enabled) li.addClass('disabled');
		});
		ul.sortable({ axis: 'y', containment: dialog });
		$('#order_dialog').dialog({
			modal: true,
			buttons: {
				OK: function() {
					var txt = $('li', ul).map(function() { return $(this).data('pid'); }).get().join(',');
					txt = '[' + txt + ']';
					$('#order').val(txt);
					$('#order_form').submit();
				},
				Cancel: function() { dialog.dialog('close'); }
			}
		});
	});
});
</script>

<style type="text/css">

#message { margin: 1em 0; padding: 1em; font-weight: bold; color: red; }

#editor { margin: 2em 0 0 0; background-color: #f9f9f9; }

#plugins { width: 100%; }
#plugins td.plugin_name { font-weight: bold; text-align: left; }
#plugins td.plugin_description { text-align: left; width: 350px; }
#plugins tr.disabled { color: silver; }

#panel { margin: 0.5em; }

#plugins tr.editing td.plugin_id { background-color: salmon; }

#order_dialog { display: none; }
#plugin_order { padding: 1em; }
#plugin_order li {
	background-color: #eee;
	margin: 2px;
	padding: 2px;
	cursor: n-resize;
	font-weight: bolder;
}
#plugin_order li.disabled { color: gray; }
#plugin_order li:hover { background-color: #ebbe8c; }

</style>

{/literal}
{/capture}
{include file="header.tpl" head_extra=$smarty.capture.extra
require=$smarty.capture.require body_class="spot"}

<h2>Plug-in configuration</h2>

<div id="message">{$message|escape}</div>

<table id="plugins" class="col-tbl">
	<tr>
		<th>ID</th>
		<th>Plugin</th>
		<th>Default policy</th>
		<th>Installed at</th>
		<th>Description</th>
		<th>Enabled</th>
		<th>Operation</th>
	</tr>
	{foreach from=$plugins item=p}
	<tr class="plugin{if !$p.exec_enabled} disabled{/if}">
		<td class="plugin_id">{$p.plugin_id|escape}</td>
		<td class="plugin_name">{$p.plugin_name|escape} v.{$p.version|escape}</td>
		<td class="plugin_default_policy">{$p.default_policy_name|escape}</td>
		<td class="plugin_install_dt">{$p.install_dt|escape}</td>
		<td class="plugin_description">{$p.description|escape}</td>
		<td class="plugin_enabled">{$p.exec_enabled|OorMinus}</td>
		<td class="operations">
			<input type="button" class="edit_button form-btn" value="edit" />
		</td>
	</tr>
	{/foreach}
</table>

<div id="panel">
	<input type="button" class="form-btn" value="Plugin Display Order" id="order_button" />
</div>

<div id="editor" style="display: none">
<form id="editor_form" method="post">
	<input type="hidden" name="mode" value="set" />
	<input type="hidden" id="plugin_id" name="plugin_id" />
	<input type="hidden" name="ticket" value="{$ticket|escape}" />
	<h3 id="editor-header">Editing Plug-in: <span id="target"></span></h3>
	<table class="detail-tbl">
		<tr>
			<th>Default result policy</th>
			<td>
				<select name="default_policy">
				{foreach from=$policies item=pol}<option value="{$pol.policy_id|escape}">{$pol.policy_name|escape}</option>{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<th>Enable execution</th>
			<td><label><input type="checkbox" value="1" name="exec_enabled" />
			<span>Uncheck this if you do not want to execute this plug-in any more</span></label></td>
		</tr>
	</table>
	<div id="editor-commit">
		<input type="button" id="save_button" class="form-btn" value="Save" />
		<input type="button" id="cancel_button" class="form-btn" value="Cancel" />
	</div>
</form>
</div>

<div id="order_dialog" title="Plugin Display Order">
	<p>Sort plug-in display order by dragging.</p>
	<ul id="plugin_order">
	</ul>
</div>
<form id="order_form" method="POST">
<input type="hidden" name="mode" value="set_order" />
<input type="hidden" name="ticket" value="{$ticket|escape}" />
<input type="hidden" name="order" value="" id="order" />
</form>

{include file="footer.tpl"}