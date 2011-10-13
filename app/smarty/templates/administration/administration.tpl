{capture name="extra"}
<script type="text/javascript">;
var adminModeFlg = {$adminModeFlg};
{literal}
$(function() {
	if (adminModeFlg) {
		$('#administration').show();
		$('#smoke').hide();
	}
	else
	{
		$(window).load(function () {
			if (confirm('Do you want to enter administration mode?'))
			{
				$('#administration').show();
				$('#smoke').hide();
				$.get('administration.php', { open: 1 });
			}
			else
			{
				window.location = '../home.php';
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
fieldset label { display: block; margin: 5px; }
fieldset label em { display: inline-block; width: 20em; }
</style>

{/literal}
{/capture}

{include file="header.tpl" head_extra=$smarty.capture.extra body_class="spot"}

<div id="smoke"></div>

<div id="administration" style="display: none">
<form onsubmit="return false;">
<input type="hidden" id="ticket" value="{$params.ticket|escape}" />

<h2>Administration</h2>

<fieldset>
	<legend>Server Control & Settings</legend>
{if $currentUser->hasPrivilege('serverSettings')}
	<label>
		<em>DICOM storage server</em>
		<input type="button" value="config" class="form-btn"
			onclick="location.href='dicom_storage_server_config.php';" />
	</label>
	<label>
		<em>Data storage</em>
		<input type="button" value="config" class="form-btn"
			onclick="location.href='data_storage_config.php';"/>
	</label>
	<label>
		<em>Server service</em>
		<input type="button" value="config" class="form-btn"
			onclick="location.href='server_service_config.php';" />
	</label>
{/if}
	<label>
		<em>Server logs</em>
		<input type="button" value="show" class="form-btn"
			onclick="location.href='server_logs.php';" />
	</label>
</fieldset>

<fieldset>
	<legend>Users and Groups</legend>
	<label>
		<em>Users</em>
		<input type="button" value="config" class="form-btn"
			onclick="location.href='user_config.php';" />
	</label>
{if $currentUser->hasPrivilege('serverSettings')}
	<label>
		<em>Groups</em>
		<input type="button" value="config" class="form-btn"
			onclick="location.href='group_config.php';" />
	</label>
{/if}
</fieldset>

<fieldset>
	<legend>Plugin Management</legend>
{if $currentUser->hasPrivilege('serverSettings')}
	<label>
		<em>Install plug-in from package</em>
		<input type="button" value="config" class="form-btn"
			onclick="location.href='add_plugin.php';" />
	</label>
	<label>
		<em>Plug-in display order</em>
		<input type="button" value="config" class="form-btn"
			onclick="location.href='plugin_basic_configuration.php';">
	</label>
	<label>
		<em>CAD result policies</em>
		<input type="button" value="config" class="form-btn"
			onclick="location.href='result_policy_config.php';">
	</label>
	<label>
		<em>Series ruleset</em>
		<input type="button" value="config" class="form-btn"
			onclick="location.href='series_ruleset_config.php';">
	</label>
{/if}
	<label>
		<em>Plug-in job queue</em>
		<input type="button" value="show" class="form-btn"
			onclick="location.href='show_job_queue.php';" />
	</label>
</fieldset>

</div>
</form>

{include file="footer.tpl"}
