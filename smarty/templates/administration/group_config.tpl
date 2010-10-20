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

function deleteGroup(groupID)
{
	if(confirm('Do you want to delete "'+ groupID + '" ?'))
	{
		var address = 'group_config.php?mode=delete'
		            + '&newGroupID=' + groupID
					+ '&ticket=' + $("#ticket").val();

		location.replace(address);	
	}
}


function GroupSetting(mode, ticket)
{

    var flg = 1;

	if(mode == 'update' && $("#oldGroupID").val() == "") 
	{
		mode = 'add';
	}

	if(mode == 'update')
	{
		if(!confirm('Do you want to update "'+ $("#oldGroupID").val() +'" ?'))  flg = 0;
	}

	if(flg == 1)
	{
		var address = 'group_config.php?mode=' + mode;

		if(mode == 'update')
		{
		    address += '&oldGroupID='       + $("#oldGroupID").val()
		            +  '&oldColorSet='      + $("#oldColorSet").val()
		            +  '&oldExecCAD='       + $("#oldExecCAD").val()
		            +  '&oldPersonalFB='    + $("#oldPersonalFB").val()
		            +  '&oldConsensualFB='  + $("#oldConsensualFB").val()
		            +  '&oldAllStatistics=' + $("#oldAllStatistics").val()
		            +  '&oldResearch='      + $("#oldResearch").val()
		            +  '&oldVolumeDL='      + $("#oldVolumeDL").val()
		            +  '&oldAnonymizeFlg='  + $("#oldAnonymizeFlg").val()
		            +  '&oldSuFlg='         + $("#oldSuFlg").val();
		}

		address += '&newGroupID='      + $("#inputGroupID").val()
		        + '&newColorSet='      + $("#colorSet").val()
		        + '&newExecCAD='       + $("input[name='newExecCAD']:checked").val()
		        + '&newPersonalFB='    + $("input[name='newPersonalFB']:checked").val()
		        + '&newConsensualFB='  + $("input[name='newConsensualFB']:checked").val()
		        + '&newAllStatistics=' + $("input[name='newAllStatistics']:checked").val()
		        + '&newResearch='      + $("input[name='newResearch']:checked").val()
		        + '&newVolumeDL='      + $("input[name='newVolumeDL']:checked").val()
		        + '&newAnonymizeFlg='  + $("input[name='newAnonymizeFlg']:checked").val()
		        + '&newSuFlg='         + $("input[name='newSuFlg']:checked").val()
				+ '&ticket=' + ticket

		location.replace(address);	
	}
}

function SetEditBox(groupID, colorSet, execCAD, personalFB, consensualFB, allStatistics, research, volumeDL, anonymizeFlg, suFlg)
{
	$("#oldGroupID").val(groupID);
	$("#oldColorSet").val(colorSet);
	$("#oldExecCAD").val(execCAD);
	$("#oldPersonalFB").val(personalFB);
	$("#oldConsensualFB").val(consensualFB);
	$("#oldAllStatistics").val(allStatistics);
	$("#oldResearch").val(research);
	$("#oldVolumeDL").val(volumeDL);
	$("#oldAnonymizeFlg").val(anonymizeFlg);
	$("#oldSuFlg").val(suFlg);
	
	$("#inputGroupID").val(groupID);

	// select 
	$("#colorSet").val(colorSet);

	$("input[name='newExecCAD']").filter(function(){ return ($(this).val() == execCAD) }).attr("checked", true);
	$("input[name='newPersonalFB']").filter(function(){ return ($(this).val() == personalFB) }).attr("checked", true);
	$("input[name='newConsensualFB']").filter(function(){ return ($(this).val() == consensualFB) }).attr("checked", true);
	$("input[name='newAllStatistics']").filter(function(){ return ($(this).val() == allStatistics) }).attr("checked", true);
	$("input[name='newResearch']").filter(function(){ return ($(this).val() == research) }).attr("checked", true);
	$("input[name='newVolumeDL']").filter(function(){ return ($(this).val() == volumeDL) }).attr("checked", true);
	$("input[name='newAnonymizeFlg']").filter(function(){ return ($(this).val() == anonymizeFlg) }).attr("checked", true);
	$("input[name='newSuFlg']").filter(function(){ return ($(this).val() == suFlg) }).attr("checked", true);

	$("#updateBtn, #cancelBtn").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
	$("#addBtn, #groupList input[type='button']").attr('disabled', 'disabled')
                                                 .removeClass('form-btn-normal, form-btn-hover')
                                                 .addClass('form-btn-disabled');
}

function CancelUpdate()
{

	$("input[type='hidden'][name^='old'], #inputGroupID").val("");
	$("#colorSet").children().removeAttr("selected");

	$("input[name='newExecCAD']").filter(function(){ return ($(this).val() == 't') }).attr("checked", true);
	$("input[name='newPersonalFB']").filter(function(){ return ($(this).val() == 't') }).attr("checked", true);
	$("input[name='newConsensualFB']").filter(function(){ return ($(this).val() == 't') }).attr("checked", true);
	$("input[name='newAllStatistics']").filter(function(){ return ($(this).val() == 'f') }).attr("checked", true);
	$("input[name='newResearch']").filter(function(){ return ($(this).val() == 'f') }).attr("checked", true);
	$("input[name='newVolumeDL']").filter(function(){ return ($(this).val() == 'f') }).attr("checked", true);
	$("input[name='newAnonymizeFlg']").filter(function(){ return ($(this).val() == 'f') }).attr("checked", true);
	$("input[name='newSuFlg']").filter(function(){ return ($(this).val() == 'f') }).attr("checked", true);

	$("#addBtn, #groupList input[type='button']").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
	$("#updateBtn, #cancelBtn, #groupList input[name='noDelete']").attr('disabled', 'disabled')
                                                                  .removeClass('form-btn-normal, form-btn-hover')
                                                                  .addClass('form-btn-disabled');
}


{/literal}
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
			<h2>Group configuration</h2>

			<form id="form1" name="form1">
				<input type="hidden" id="ticket" value="{$ticket}" />
				<input type="hidden" id="oldGroupID"       value="" />
				<input type="hidden" id="oldColorSet"      value="" />
				<input type="hidden" id="oldExecCAD"       value="" />
				<input type="hidden" id="oldPersonalFB"    value="" />
				<input type="hidden" id="oldConsensualFB"  value="" />
				<input type="hidden" id="oldAllStatistics" value="" />
				<input type="hidden" id="oldResearch"      value="" />
				<input type="hidden" id="oldVolumeDL"      value="" />
				<input type="hidden" id="oldAnonymizeFlg"  value="" />
				<input type="hidden" id="oldSuFlg"         value="" />

				<div id="message" class="mt5 mb5 ml10">{$message}</div>

				<div id="groupList" class="ml10">
					<table class="col-tbl">
						<tr>
							<th>Group ID</th>
							<th>Color set</th>
							<th>Exec CAD</th>
							<th style="width:5em;">Personal feedback</th>
							<th style="width:6em;">Consensual feedback</th>
							<th>All Stat.</th>
							<th>Research</th>
							<th style="width:7.5em;">Download volume data</th>
							<th>Anonymization</th>
							<th>Super user</th>
							<th>&nbsp;</th>
						</tr>

						{foreach from=$groupList item=item name=cnt}
							<tr {if $smarty.foreach.cnt.iteration%2==0}class="column"{/if}>

								<td class="al-l">{$item[0]}</td>
								<td>{$item[1]}</td>
								<td>{if $item[2]==true}TRUE{else}FALSE{/if}</td>
								<td>{if $item[3]==true}TRUE{else}FALSE{/if}</td>
								<td>{if $item[4]==true}TRUE{else}FALSE{/if}</td>
								<td>{if $item[5]==true}TRUE{else}FALSE{/if}</td>
								<td>{if $item[6]==true}TRUE{else}FALSE{/if}</td>
								<td>{if $item[7]==true}TRUE{else}FALSE{/if}</td>
								<td>{if $item[8]==true}TRUE{else}FALSE{/if}</td>
								<td>{if $item[9]==true}TRUE{else}FALSE{/if}</td>
								{if $item[0] != "admin"}
									<td>
										<input type="button" id="editButton{$smarty.foreach.cnt.iteration}" value="edit" class="s-btn form-btn"
                                   		  onClick="SetEditBox('{$item[0]}', '{$item[1]}',
                                       		                  '{if $item[2]==true}t{else}f{/if}', '{if $item[3]==true}t{else}f{/if}',
													 		  '{if $item[4]==true}t{else}f{/if}', '{if $item[5]==true}t{else}f{/if}',
														 	  '{if $item[6]==true}t{else}f{/if}', '{if $item[7]==true}t{else}f{/if}',
														 	  '{if $item[8]==true}t{else}f{/if}', '{if $item[9]==true}t{else}f{/if}');" />
										<input type="button" id="deleteButton{$smarty.foreach.cnt.iteration}" value="delete"
											{if $item[0] != $smarty.session.userID}
									 			class="s-btn form-btn" onClick="deleteGroup('{$item[0]}');" />
								 			{else}
									 			name="noDelete" class="s-btn form-btn form-btn-disabled" disabled="disabled" />
											{/if}
									</td>
								{else}
									<td>&nbsp;</td>
								{/if}
							</tr>
						{/foreach}
					</table>
				</div>

				<div class="mt20 ml40">
					<table class="detail-tbl">
						<tr>
							<th style="width: 16em;"><span class="trim01">Group ID</th>
							<td><input class="loginForm" size="40" type="text" id="inputGroupID" name="inputGroupID" /></td>
						</tr>

						<tr>
							<th><span class="trim01">Color set</th>
							<td>
								<select id="colorSet">
									<option value="user" selected="selected">user</option>
									<option value="admin">admin</option>
									<option value="guest">guest</option>
								</select>
							</td>
						</tr>

						<tr>
							<th><span class="trim01">CAD execution</span></th>
							<td>
								<input name="newExecCAD" type="radio" value="t" checked="checked" />TRUE
								<input name="newExecCAD" type="radio" value="f" />FALSE
							</td>
						</tr>

						<tr>
							<th><span class="trim01">Personal feedback</span></th>
							<td>
								<input name="newPersonalFB" type="radio" value="t" checked="checked" />TRUE
								<input name="newPersonalFB" type="radio" value="f"  />FALSE
							</td>
						</tr>

						<tr>
							<th><span class="trim01">Consensual feedback</span></th>
							<td>
								<input name="newConsensualFB" type="radio" value="t" checked="checked" />TRUE
								<input name="newConsensualFB" type="radio" value="f"  />FALSE
							</td>
						</tr>


						<tr>
							<th><span class="trim01">View all user's statistics</span></th>
							<td>
								<input name="newAllStatistics" type="radio" value="t" />TRUE
								<input name="newAllStatistics" type="radio" value="f" checked="checked" />FALSE
							</td>
						</tr>

						<tr>
							<th><span class="trim01">Research</span></th>
							<td>
								<input name="newResearch" type="radio" value="t" />TRUE
								<input name="newResearch" type="radio" value="f" checked="checked" />FALSE
							</td>
						</tr>

						<tr>
							<th><span class="trim01">Download volume data</span></th>
							<td>
								<input name="newVolumeDL" type="radio" value="t" />TRUE
								<input name="newVolumeDL" type="radio" value="f" checked="checked" />FALSE
							</td>
						</tr>

						<tr>
							<th><span class="trim01">Anonymization</span></th>
							<td>
								<input name="newAnonymizeFlg" type="radio" value="t" />TRUE
								<input name="newAnonymizeFlg" type="radio" value="f" checked="checked" />FALSE
							</td>
						</tr>

						<tr>
							<th><span class="trim01">Super user</span></th>
							<td>
								<input name="newSuFlg" type="radio" value="t" />TRUE
								<input name="newSuFlg" type="radio" value="f" checked="checked" />FALSE
							</td>
						</tr>

					</table>

					<div class="pl20 mb20 mt10">
						<p>
							<input type="button" id="addBtn" class="form-btn" value="add" onClick="GroupSetting('add','{$ticket}');" />&nbsp;
							<input type="button" id="updateBtn" class="form-btn form-btn-disabled" value="update"
                                   onClick="GroupSetting('update','{$ticket}');" disabled="disabled" />
							<input type="button" id="cancelBtn" class="form-btn form-btn-disabled" value="cancel"
                                   onClick="CancelUpdate();" disabled="disabled" />
						</p>
					</div>
				</div>
			</form>
<!-- InstanceEndEditable -->
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
<!-- InstanceEnd --></html>

