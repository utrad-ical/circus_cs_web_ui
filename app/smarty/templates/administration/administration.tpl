{capture name="extra"}
<script type="text/javascript">;
var adminModeFlg = {$adminModeFlg};
{literal}
$(function() {
	$('#storagePanel, #managerPanel').each(function () {
		var serviceName = $('input[name=serviceName]', this).val();
		var panel = this;
		$('input[type=button]', this).each(function () {
			$(this).click(function(event) {
				var mode = $(event.target).attr('value');
				$('.serviceStatus', panel).text('...');
				$('input[type=button]', panel).attr('disabled', 'disabled').trigger('flush');
				$.post(
					"change_server_status.php",
					{ serviceName: serviceName, mode: mode, ticket: $("#ticket").val() },
					function (data) {
						var panel = $('.panel:has(input[name=serviceName][value=' + serviceName + '])');
						$('.serviceStatus', panel).text(data.str);
						var started = data.val == 1;
						$('input[type=button][value=start]', panel)
							.attr('disabled', started ? 'disabled': '')
							.trigger('flush');
						$('input[type=button][value=stop]', panel)
							.attr('disabled', started ? '' : 'disabled')
							.trigger('flush');
						$('input[type=button][value=status]', panel)
							.attr('disabled', '')
							.trigger('flush');
					},
					"json"
				);
			});
		});

		$('input[type=button][value=status]', this).click(); // query status
	});

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
-->
</script>

<style type="text/css">
#content table td { padding: 0.5em; }
#content h3 { margin-top: 1em; }
.serviceStatus { font-weight: bold; width: 150px; }
</style>

{/literal}
{/capture}

{include file="header.tpl" head_extra=$smarty.capture.extra body_class="spot"}

<div id="smoke"></div>

<div id="administration" style="display: none">
<form onsubmit="return false;">
<input type="hidden" id="ticket" value="{$params.ticket|escape}" />

<h2>Administration</h2>

<h3>Server settings</h3>
<table>
	{if $smarty.session.serverSettingsFlg==1}
	<tr>
		<td>DICOM storage server</td>
		<td>
			<input type="button" id="dicomStorageConfButton" value="config" class="form-btn"
				onclick="location.href='dicom_storage_server_config.php';" />
		</td>
	</tr>
	<tr>
		<td>Data storages</td>
		<td>
			<input type="button" id="diskStorageConfButton" value="config" class="form-btn"
				onclick="location.href='data_storage_config.php';"/>
		</td>
	</tr>
	<tr>
		<td>Add plug-in from packaged file</td>
		<td>
			<input type="button" id="pluginConfButton" value="config" class="form-btn"
				onclick="location.href='add_plugin.php';" />
		</td>
	</tr>

	<tr>
		<td>Basic configuration for plug-ins</td>
		<td>
			<input type="button" id="pluginConfButton" value="config" class="form-btn"
				onclick="location.href='plugin_basic_configuration.php';">
		</td>
	</tr>
	{/if}

	<tr>
		<td>Users</td>
		<td>
			<input type="button" id="userConfButton" value="config" class="form-btn"
				onclick="location.href='user_config.php';" />
		</td>
	</tr>

	{if $smarty.session.serverSettingsFlg==1}
	<tr>
		<td>Groups</td>
		<td>
			<input type="button" id="groupConfButton" value="config" class="form-btn"
				onclick="location.href='group_config.php';" />
		</td>
	</tr>
	{/if}

	<tr>
		<td>Server logs</td>
		<td>
			<input type="button" id="groupConfButton" value="show" class="form-btn"
				onclick="location.href='server_logs.php';" />
		</td>
	</tr>
</table>

<h3>Plug-in jobs</h3>
<div>
	<table>
		<tr>
			<td>Show plug-in job queue</td>
			<td>
				<input type="button" id="groupConfButton" value="show" class="form-btn"
					onclick="location.href='show_job_queue.php';" />
			</td>
		</tr>
	</table>
</div>

<h3>Server status</h3>
<table>
	<tbody>
		<tr id="storagePanel" class="panel">
			<td>DICOM Storage Server</td>
			<td class="serviceStatus themeColor" id="storageStatusStr">...</td>
			<td>
				<input type="hidden" name="serviceName" value="{$storageServerName|escape}" />
				<input type="button" value="start" class="form-btn" disabled="disabled" />
				<input type="button" value="stop"  class="form-btn" disabled="disabled" />
				<input type="button" value="status" class="form-btn" />
			</td>
		</tr>
		<tr id="managerPanel" class="panel">
			<td>Plug-in Job Manager</td>
			<td class="serviceStatus themeColor" id="managerStatusStr">...</td>
			<td>
				<input type="hidden" name="serviceName" value="{$managerServerName|escape}" />
				<input type="button" value="start" class="form-btn" disabled="disabled" />
				<input type="button" value="stop"  class="form-btn" disabled="disabled" />
				<input type="button" value="status" class="form-btn" />
			</td>
		</tr>
	</tbody>
</table>
</div>
</form>

{include file="footer.tpl"}
