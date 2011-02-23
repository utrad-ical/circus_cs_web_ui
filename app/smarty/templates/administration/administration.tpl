<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />

<title>CIRCUS CS {$smarty.session.circusVersion}</title>

<link href="../css/import.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../jq/jquery-1.3.2.min.js"></script>
<script language="javascript" type="text/javascript" src="../jq/jq-btn.js"></script>
<script language="javascript" type="text/javascript" src="../js/hover.js"></script>
<script language="javascript" type="text/javascript" src="../js/viewControl.js"></script>
<link rel="shortcut icon" href="../favicon.ico" />

<script language="Javascript">;
<!--
{literal}

function ChangeServerStatus(baseName, serviceName, mode)
{
	$.post("change_server_status.php",
			{ serviceName: serviceName,
	  		  mode: mode,
	          ticket: $("#ticket").val()},
			  function(data){

				if(baseName == "storage")
				{
					$("#storageStatusStr").html(data.str);

					if(data.val == 1)
					{
						$("#startStorage").attr('disabled', 'disabled').removeClass('form-btn-normal, form-btn-hover').addClass('form-btn-disabled');
						$("#stopStorage").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
					}	
					else
					{
						$("#startStorage").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
						$("#stopStorage").attr('disabled', 'disabled').removeClass('form-btn-normal, form-btn-hover').addClass('form-btn-disabled');
					}
				}
				else if(baseName == "manager")
				{
					$("#managerStatusStr").html(data.str);

					if(data.val == 1)
					{
						$("#startManager").attr('disabled', 'disabled').removeClass('form-btn-normal, form-btn-hover').addClass('form-btn-disabled');
						$("#stopManager").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
					}	
					else
					{
						$("#startManager").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
						$("#stopManager").attr('disabled', 'disabled').removeClass('form-btn-normal, form-btn-hover').addClass('form-btn-disabled');
					}
				}
		  }, "json");
}

{/literal}

{if $smarty.session.adminModeFlg==0}
	$.event.add(window, "load", 
				function(){ldelim}
					if(confirm("Do you change to administration mode?"))
					{ldelim}
						location.replace('administration.php?adminModeFlg=1');
					{rdelim}
					else
					{ldelim}
						location.replace('../home.php');
					{rdelim}
				{rdelim});
{/if}

-->
</script>

<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/popup.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../js/hover.js"></script>
</head>

<body class="spot">
<div id="page">
	<div id="container" class="menu-back">
		<!-- ***** #leftside ***** -->
		<div id="leftside">
			{include file='menu.tpl'}
		</div>
		<!-- / #leftside END -->

		<div id="content">
			<form id="form1" name="form1" onsubmit="return false;">
			<input type="hidden" id="ticket" value="{$params.ticket|escape}" />

			<h2>Administration</h2>
			
			<h3>Server settings</h3>
			<div class="p5 ml10 mb10">
				<table>
					{if $smarty.session.serverSettingsFlg==1}
						<tr>
							<td>DICOM storage server</td>
							<td class="pl10">
								<input type="button" id="dicomStorageConfButton" value="config" class="form-btn"
							 			onclick="location.href='dicom_storage_server_config.php';" />
							</td>
						</tr>

						<tr>
							<td class="pt5">Data storages</td>
							<td class="pt5 pl10">
								<input type="button" id="diskStorageConfButton" value="config" class="form-btn"
								 		onclick="location.href='data_storage_config.php';"/>
							</td>
						</tr>

						<tr>
							<td class="pt5">Add plug-in from packaged file</td>
							<td class="pt5 pl10">
								<input type="button" id="pluginConfButton" value="config" class="form-btn"
									 onclick="location.href='add_plugin.php';" />
							</td>
						</tr>

						<tr>
							<td class="pt5">Basic configuration for plug-ins</td>
							<td class="pt5 pl10">
								<input type="button" id="pluginConfButton" value="config" class="form-btn"
					 				onclick="location.href='plugin_basic_configuration.php';">
							</td>
						</tr>
					<!--
						<tr>
							<td class="pt5">Grayscale preset</td>
							<td class="pt5 pl10">
								<input type="button" id="grayscaleConfButton" value="config" class="form-btn"
									 onclick="location.href='grayscale_config.php';" disabled="disabled" />
							</td>
						</tr>
					-->
					{/if}
					
					<tr>
						<td class="pt5">Users</td>
						<td class="pt5 pl10">
							<input type="button" id="userConfButton" value="config" class="form-btn"
								 onclick="location.href='user_config.php';" />
						</td>
					</tr>
					
					{if $smarty.session.serverSettingsFlg==1}
						<tr>
							<td class="pt5">Groups</td>
							<td class="pt5 pl10">
								<input type="button" id="groupConfButton" value="config" class="form-btn"
					 					onclick="location.href='group_config.php';" />
							</td>
						</tr>
					{/if}

					<tr>
						<td class="pt5">Server logs</td>
						<td class="pt5 pl10">
							<input type="button" id="groupConfButton" value="show" class="form-btn"
					 				onclick="location.href='server_logs.php';" />
						</td>
					</tr>
					
					</table>
			</div>

			<h3>Plug-in jobs</h3>
			<div class="p5 ml10 mb10">
				<table>
					<tr>
						<td class="pt5">Show plug-in jobs</td>
						<td class="pt5 pl10">
							<input type="button" id="groupConfButton" value="show" class="form-btn"
					 				onclick="location.href='show_plugin_job_list.php';" />
						</td>
					</tr>
				</table>
			</div>

			<h3>Server status</h3>
			<div class="p5 ml10">
				<table>
					<tr>
						<td class="pt5">DICOM Storage Server</td>
						<td class="pt5 pl10" style="width: 95px;"><span id="storageStatusStr" style="font-weight:bold;">{$storageSvStatus.str}</span></td>
						<td class="pt5 pl5">
							<input type="button" id="startStorage" value="start"
                             onclick="ChangeServerStatus('storage', '{$storageSvStatus.serviceName}', 'start');"
							 class ="form-btn{if $storageSvStatus.val == 1} form-btn-disabled" disabled="disabled{/if}" />&nbsp;
                            <input type="button" id="stopStorage" value="stop"
                             onclick="ChangeServerStatus('storage', '{$storageSvStatus.serviceName}', 'stop');"
                             class ="form-btn{if $storageSvStatus.val == 0} form-btn-disabled" disabled="disabled{/if}" />&nbsp;&nbsp;
                            <input type="button" id="storageStatus" value="status" class ="form-btn"
                             onclick="ChangeServerStatus('storage', '{$storageSvStatus.serviceName}', 'status');" />
                        </td>
                    </tr>
	                <tr>
                        <td class="pt5">CAD Job Manager</td>
                        <td class="pt5 pl10" style="width: 90px;"><span id="managerStatusStr" style="font-weight:bold;">{$jobManagerStatus.str}</span></td>
                        <td class="pt5 pl5">
	                        <input type="button" id="startManager" value="start" 
                             onclick="ChangeServerStatus('manager', '{$jobManagerStatus.serviceName}', 'start');"
                             class ="form-btn{if $jobManagerStatus.val == 1} form-btn-disabled" disabled="disabled{/if}" />&nbsp;
                            <input type="button" id="stopManager" value="stop"
                             onclick="ChangeServerStatus('manager', '{$jobManagerStatus.serviceName}', 'stop');"
                             class ="form-btn{if $jobManagerStatus.val == 0} form-btn-disabled" disabled="disabled{/if}" />&nbsp;&nbsp;
                            <input type="button" id="managerStatus" value="status" class ="form-btn"
                             onclick="ChangeServerStatus('manager', '{$jobManagerStatus.serviceName}', 'status');" />
                        </td>
                    </tr>
	            </table>
			</div>
			
			</form>
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
</html>
