<?xml version="1.0" encoding="shift_jis"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/base.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=shift_jis" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CIRCUS CS {$smarty.session.circusVersion}</title>
<!-- InstanceEndEditable -->

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


<!-- InstanceBeginEditable name="head" -->
<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/popup.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../js/hover.js"></script>

<!-- InstanceEndEditable -->
</head>

<!-- InstanceParam name="class" type="text" value="home" -->
<body class="spot">
<div id="page">
	<div id="container" class="menu-back">
		<div id="leftside">
			{include file='menu.tpl'}
		</div><!-- / #leftside END -->
		<div id="content">
<!-- InstanceBeginEditable name="content" -->
			<form id="form1" name="form1" onsubmit="return false;">
			<input type="hidden" id="ticket" value="{$param.ticket}" />

			<h2>Administration</h2>
			
			<h3>Server settings</h3>
			<div class="p5 ml10 mb10">
				<table>
				<!--
					<tr>
					<td>DICOM storage server</td>
					<td class="pl10"><input type="button" id="dicomStorageConfButton" value="config" class="form-btn"
					 onClick="location.href='dicom_storage_server_config.php';" />
					</td>
					</tr>

					<tr>
					<td class="pt5">Disk storages</td>
					<td class="pt5 pl10"><input type="button" id="diskStorageConfButton" value="config" class="form-btn"
					 onClick="location.href='storage_config.php';"/>
					</td>
					</tr>

					<tr>
					<td class="pt5">Add plug-in from pakaged file</td>
					<td class="pt5 pl10"><input type="button" id="pluginConfButton" value="config" class="form-btn"
					 onClick="location.href='add_plugin.php';" />
					</td>
					</tr>

					<tr>
					<td class="pt5">Plug-in</td>
					<td class="pt5 pl10"><input type="button" id="pluginConfButton" value="config" class="form-btn"
					 onClick="location.href='plugin_config.php';">
					</td>
					</tr>

					<tr>
					<td class="pt5">Grayscale preset</td>
					<td class="pt5 pl10"><input type="button" id="grayscaleConfButton" value="config" class="form-btn"
					 onClick="location.href='grayscale_config.php';" disabled="disabled" />
					</td>
					</tr>

					<tr>
					<td class="pt5">Users</td>
					<td class="pt5 pl10"><input type="button" id="userConfButton" value="config" class="form-btn"
					 onClick="location.href='user_config.php';" />
					</td>
					</tr>

					<tr>
					<td class="pt5">Groups</td>
					<td class="pt5 pl10"><input type="button" id="groupConfButton" value="config" class="form-btn"
					 onClick="location.href='group_config.php';" />
					</td>
					</tr>
				-->	
					<tr>
					<td class="pt5">Server logs</td>
					<td class="pt5 pl10"><input type="button" id="groupConfButton" value="show" class="form-btn"
					 onClick="location.href='server_logs.php';" />
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
                             onClick="ChangeServerStatus('storage', '{$storageSvStatus.serviceName}', 'start');"
							 class ="form-btn{if $storageSvStatus.val == 1} form-btn-disabled" disabled="disabled{/if}" />&nbsp;
                            <input type="button" id="stopStorage" value="stop"
                             onClick="ChangeServerStatus('storage', '{$storageSvStatus.serviceName}', 'stop');"
                             class ="form-btn{if $storageSvStatus.val == 0} form-btn-disabled" disabled="disabled{/if}" />&nbsp;&nbsp;
                            <input type="button" id="storageStatus" value="status" class ="form-btn"
                             onClick="ChangeServerStatus('storage', '{$storageSvStatus.serviceName}', 'status');" />
                        </td>
                    </tr>
	                <tr>
                        <td class="pt5">CAD Job Manager</td>
                        <td class="pt5 pl10" style="width: 90px;"><span id="managerStatusStr" style="font-weight:bold;">{$jobManagerStatus.str}</span></td>
                        <td class="pt5 pl5">
	                        <input type="button" id="startManager" value="start" 
                             onClick="ChangeServerStatus('manager', '{$jobManagerStatus.serviceName}', 'start');"
                             class ="form-btn{if $jobManagerStatus.val == 1} form-btn-disabled" disabled="disabled{/if}" />&nbsp;
                            <input type="button" id="stopManager" value="stop"
                             onClick="ChangeServerStatus('manager', '{$jobManagerStatus.serviceName}', 'stop');"
                             class ="form-btn{if $jobManagerStatus.val == 0} form-btn-disabled" disabled="disabled{/if}" />&nbsp;&nbsp;
                            <input type="button" id="managerStatus" value="status" class ="form-btn"
                             onClick="ChangeServerStatus('manager', '{$jobManagerStatus.serviceName}', 'status');" />
                        </td>
                    </tr>
	            </table>
			</div>
			
			</form>
<!-- InstanceEndEditable -->
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
<!-- InstanceEnd --></html>

