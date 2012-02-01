{capture name="extra"}
<script type="text/javascript">;
var adminModeFlg = {$adminModeFlg};
{literal}
$(function() {
	var ok = false;
	if (adminModeFlg) {
		$('#administration').show();
		$('#smoke').hide();
	}
	else
	{
		$('#dialog-confirm').dialog({
			modal: true,
			resizable: false,
			buttons: {
				"OK": function() {
					ok = true;
					$(this).dialog('close');
					$('#administration').show();
					$('#smoke').hide();
					$.get('administration.php', { open: 1 });
				},
				"Cancel": function() {
					$(this).dialog('close');
				}
			},
			close: function() {
				if (!ok) window.location = '../home.php';
			}
		});
	}
})
</script>

<style type="text/css">
#content table td { padding: 0.5em; }
#content h3 { margin-top: 1em; }
.form-btn { width: 100px; }
fieldset { margin: 20px 0; border: 2px solid #8a3b2b; }
fieldset li { margin: 2px; padding: 2px; }
fieldset li em { display: inline-block; width: 20em; }
fieldset li:hover { background-color: #ffddae; }
</style>

{/literal}
{/capture}
{capture name="require"}
jq/ui/jquery-ui.min.js
jq/ui/theme/jquery-ui.custom.css
{/capture}
{include file="header.tpl" head_extra=$smarty.capture.extra
	require=$smarty.capture.require body_class="spot"}

<div id="smoke"></div>

<div id="administration" style="display: none">
<form onsubmit="return false;">
<input type="hidden" id="ticket" value="{$params.ticket|escape}" />

<h2>Administration</h2>

<fieldset>
	<legend>Server Control & Settings</legend>
{if $currentUser->hasPrivilege('serverSettings')}
	<ul>
		<li>
			<em>DICOM storage server</em>
			<input type="button" value="config" class="form-btn"
				onclick="location.href='dicom_storage_server_config.php';" />
		</li>
		<li>
			<em>Data storage</em>
			<input type="button" value="config" class="form-btn"
				onclick="location.href='data_storage_config.php';"/>
		</li>
		<li>
			<em>Server service</em>
			<input type="button" value="config" class="form-btn"
				onclick="location.href='server_service_config.php';" />
		</li>
{/if}
		<li>
			<em>Server logs</em>
			<input type="button" value="show" class="form-btn"
				onclick="location.href='server_logs.php';" />
		</li>
	</ul>
</fieldset>

<fieldset>
	<legend>Users & Groups</legend>
	<ul>
		<li>
			<em>Users</em>
			<input type="button" value="config" class="form-btn"
				onclick="location.href='user_config.php';" />
		</li>
{if $currentUser->hasPrivilege('serverSettings')}
		<li>
			<em>Groups</em>
			<input type="button" value="config" class="form-btn"
				onclick="location.href='group_config.php';" />
		</li>
{/if}
	</ul>
</fieldset>

<fieldset>
	<legend>Plugin Management</legend>
{if $currentUser->hasPrivilege('serverSettings')}
	<ul>
		<li>
			<em>Install plug-in from package</em>
			<input type="button" value="config" class="form-btn"
				onclick="location.href='add_plugin.php';" />
		</li>
		<li>
			<em>Plug-in display order</em>
			<input type="button" value="config" class="form-btn"
				onclick="location.href='plugin_display_order.php';">
		</li>
		<li>
			<em>CAD result policies</em>
			<input type="button" value="config" class="form-btn"
				onclick="location.href='result_policy_config.php';">
		</li>
		<li>
			<em>Series ruleset</em>
			<input type="button" value="config" class="form-btn"
				onclick="location.href='series_ruleset_config.php';">
		</li>
{/if}
		<li>
			<em>Plug-in job queue</em>
			<input type="button" value="show" class="form-btn"
				onclick="location.href='show_job_queue.php';" />
		</li>
	</ul>
</fieldset>

<div id="dialog-confirm" title="Confirmation" style="display: none">
Do you want to enter administration mode?
</div>

</div>
</form>

{include file="footer.tpl"}
