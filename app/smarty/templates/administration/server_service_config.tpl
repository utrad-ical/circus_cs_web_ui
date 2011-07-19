{capture name="extra"}
<script type="text/javascript">;
{literal}
$(function() {
	$('.storagePanel, .managerPanel').each(function () {
		var serviceName = $('input[name=serviceName]', this).val();
		var ipAddress = $('input[name=ipAddress]', this).val();
		var panel = this;
		$('input[type=button]', this).each(function () {
			$(this).click(function(event) {
				var mode = $(event.target).attr('value');
				$('.serviceStatus', panel).empty().append($('.loading').clone().show(0));
				$('input[type=button]', panel).disable();
				$.post(
					"change_server_status.php",
					{ serviceName: serviceName, mode: mode, ipAddress: ipAddress, ticket: $("#ticket").val() },
					function (data) {
						$('.serviceStatus', panel).empty().text(data.str);
						var started = data.val == 1;
						$('input[type=button][value=start]', panel).enable(!started);
						$('input[type=button][value=stop]', panel).enable(started);
						$('input[type=button][value=status]', panel).enable();
					},
					"json"
				);
			});
		});

		$('input[type=button][value=status]', this).click(); // query status
	});
})
-->
</script>

<style type="text/css">
.machineDetail table td { padding: 0.5em; }
.machineDetail { margin: 1em 0 1.5em; }
.serviceStatus { font-weight: bold; width: 150px; }
.form-btn { width: 75px; }
</style>

{/literal}
{/capture}

{include file="header.tpl"
	head_extra=$smarty.capture.extra body_class="spot"}

<div id="machineList">
<form onsubmit="return false;">
<input type="hidden" id="ticket" value="{$ticket|escape}" />

<h2>Server service</h2>

{foreach from=$machineList item=item}
<div id="{$item.ip_address|escape}" class="machineDetail">
<h3>{$item.host_name|escape} ({$item.ip_address|escape})</h3>
<table>
	<tbody>
		{if $item.dicom_storage_server}
		<tr class="panel storagePanel">
			<td>DICOM Storage Server</td>
			<td class="serviceStatus themeColor" name="storageStatusStr"></td>
			<td>
				<input type="hidden" name="serviceName" value="{$storageServerName|escape}" />
				<input type="hidden" name="ipAddress" value="{$item.ip_address|escape}" />
				<input type="button" value="start" class="form-btn" disabled="disabled" />
				<input type="button" value="stop"  class="form-btn" disabled="disabled" />
				<input type="button" value="status" class="form-btn" />
			</td>
		</tr>
		{/if}
		{if $item.plugin_job_manager>0}
		<tr class="panel managerPanel">
			<td>
				Plug-in Job Manager
				{if $item.plugin_job_manager==1}(controller)
				{elseif $item.plugin_job_manager==2}(process machine)
				{elseif $item.plugin_job_manager==3}(all)
				{/if}
			</td>
			<td class="serviceStatus themeColor" name="managerStatusStr"></td>
			<td>
				<input type="hidden" name="serviceName" value="{$managerServerName|escape}" />
				<input type="hidden" name="ipAddress" value="{$item.ip_address|escape}" />
				<input type="button" value="start" class="form-btn" disabled="disabled" />
				<input type="button" value="stop"  class="form-btn" disabled="disabled" />
				<input type="button" value="status" class="form-btn" />
			</td>
		</tr>
		{/if}
	</tbody>
</table>
</div>
{/foreach}

</div>
</form>
<img class="loading" width="15" height="15" src="../images/busy.gif" style="display: none" />
{include file="footer.tpl"}
