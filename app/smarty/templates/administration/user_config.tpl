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

function deleteUser(userID)
{
	if(confirm('Do you want to delete "'+ userID + '" ?'))
	{
		var address = 'user_config.php?mode=delete'
					+ '&newUserID=' + userID
					+ '&ticket=' + $("#ticket").val();

		location.replace(address);	
	}
}



function UserSetting(mode)
{
	if(mode == 'update' && $("#oldUserID").val() == "" 
	   && $("#oldUserName").val() == "" && $("#oldPassword").val() == "")
	{
		mode = 'add';
	}

	var flg = 1;
	
	if(mode == 'update')
	{
		if(!confirm('Do you want to update "'+ $("#oldUserID").val() +'" ?'))  flg = 0;
	}
	
	if(flg == 1)
	{
		var address = 'user_config.php?mode=' + mode 
					+ '&oldUserID=' + $("#oldUserID").val()
					+ '&oldUserName=' + $("#oldUserName").val()
					+ '&oldPassword=' + $("#oldPassword").val()
					+ '&oldGroupID='  + $("#oldGroupID").val()
					+ '&oldTodayDisp=' + $("#oldTodayDisp").val()
					+ '&oldDarkroom=' + $("#oldDarkroomFlg").val()
					+ '&oldAnonymized=' + $("#oldAnonymizeFlg").val()
					+ '&oldShowMissed=' + $("#oldShowMissed").val()
					+ '&newUserID='   + $("#inputUserID").val()
					+ '&newUserName=' + $("#inputUserName").val()
					+ '&newPassword=' + $("#inputPass").val()
					+ '&newGroupID='  + $("#groupList").val()
					+ '&newTodayDisp=' + $('input[name="newTodayDisp"]:checked').val()
					+ '&newDarkroom=' + $('input[name="newDarkroom"]:checked').val()
					+ '&newAnonymized=' + $('input[name="newAnonymized"]:checked').val()
					+ '&newShowMissed=' + $('input[name="newShowMissed"]:checked').val()
					+ '&ticket=' + $("#ticket").val();

		location.replace(address);	
	}
}

function SetEditBox(userID, userName, password, groupID, todayDisp, darkroom, anonymized, showMissed)
{
	$("#oldUserID").val(userID);
	$("#oldUserName").val(userName);
	$("#oldPassword").val(password);
	$("#oldGroupID").val(groupID);
	$("#oldTodayDisp").val(todayDisp);
	$("#oldDarkroom").val(darkroom);
	$("#oldAnonymized").val(anonymized);
	$("#oldshowMissed").val(showMissed);
	
	$("#inputUserID").val(userID);
	$("#inputUserName").val(userName);
	$("#inputPass").val(password);

	// select 
	$("#groupList").val(groupID);

	$("input[name='newTodayDisp']").filter(function(){ return ($(this).val() == todayDisp) }).attr("checked", true);
	$("input[name='newDarkroom']").filter(function(){ return ($(this).val() == darkroom) }).attr("checked", true);
	$("input[name='newAnonymized']").filter(function(){ return ($(this).val() == anonymized) }).attr("checked", true);
	$("input[name='newShowMissed']").filter(function(){ return ($(this).val() == showMissed) }).attr("checked", true);

	$("#updateBtn, #cancelBtn").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
	$("#addBtn, #userList input[type='button']").attr('disabled', 'disabled')
												.removeClass('form-btn-normal, form-btn-hover')
												.addClass('form-btn-disabled');
}

function CancelUpdate()
{
	$("input[type='hidden'][id^='old']").val("");
	$("#inputUserID,#inputUserName,#inputPass").val("");

	$("#groupList").children().removeAttr("selected");

	$("input[name='newTodayDisp']").filter(function(){ return ($(this).val() == 'cad') }).attr("checked", true);
	$("input[name='newDarkroom']").filter(function(){ return ($(this).val() == 'white') }).attr("checked", true);
	$("input[name='newAnonymized']").filter(function(){ return ($(this).val() == 'f') }).attr("checked", true);
	$("input[name='newShowMissed']").filter(function(){ return ($(this).val() == 'none') }).attr("checked", true);

	$("#addBtn, #userList input[type='button']").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
	$("#updateBtn, #cancelBtn, #userList input[name='loginUser']").attr('disabled', 'disabled')
																  .removeClass('form-btn-normal, form-btn-hover')
																  .addClass('form-btn-disabled');
}


{/literal}
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
			<h2>User configuration</h2>

			<form id="form1" name="form1">
				<input type="hidden" id="ticket"		value="{$params.ticket|escape}" />
				<input type="hidden" id="oldUserID" 	value="" />
				<input type="hidden" id="oldUserName"	value="" />
				<input type="hidden" id="oldPassword"	value="" />
				<input type="hidden" id="oldGroupID"	value="" />
				<input type="hidden" id="oldTodayDisp"	value="" />
				<input type="hidden" id="oldDarkroom"	value="" />
				<input type="hidden" id="oldAnonymized"	value="" />
				<input type="hidden" id="oldShowMissed"	value="" />

				<div id="message" class="mt5 mb5 ml10">{$params.message}</div>

				<div id="userList" class="ml10">
					<table class="col-tbl">
						<tr>
							<th>User ID</th>
							<th>User name</th>
							<th>Group</th>
							<th>Today</th>
							<th>Darkroom</th>
							<th>Anonymize</th>
							<th>Show missed</th>
							<th>&nbsp;</th>
						</tr>
	
						{foreach from=$userList item=item name=cnt}
							<tr {if $smarty.foreach.cnt.iteration%2==0}class="column"{/if}>
								<td class="al-l">{$item[0]}</td>
								<td class="al-l">{$item[1]}</td>
								<td class="al-l">{$item[2]}</td>
								<td>{$item[3]}</td>
								<td>{if $item[4]}black{else}white{/if}</td>
								<td>{$item[5]|OorMinus}</td>
								<td>{$item[6]}</td>
								<td>
									<input type="button" id="editButton{$smarty.foreach.cnt.iteration}" value="edit" class="s-btn form-btn"
									 onclick="SetEditBox('{$item[0]}', '{$item[1]}', '{$item[7]}','{$item[2]}', '{$item[3]}',
														 '{$item[4]|TorF}', '{$item[5]|TorF}','{$item[6]}');" />
									<input type="button" id="deleteButton{$smarty.foreach.cnt.iteration}" value="delete"
										{if $item[0] != $smarty.session.userID}
										 	class="s-btn form-btn" onclick="deleteUser('{$item[0]}');" />
									 	{else}
										 	name="loginUser" class="s-btn form-btn form-btn-disabled" disabled="disabled" />
										{/if}
								</td>
							</tr>
						{/foreach}
					</table>
				</div>

				<div class="mt20 ml40">
					<table class="detail-tbl">
						<tr>
							<th style="width: 15em;"><span class="trim01">User ID</th>
							<td><input class="loginForm" size="40" type="text" id="inputUserID" name="inputUserID" /></td>
						</tr>
						
						<tr>
							<th><span class="trim01">User name</th>
							<td><input class="loginForm" size="40" type="text" id="inputUserName" name="inputUserName" /></td>
						</tr>

						<tr>
							<th><span class="trim01">Password: </th>
							<td><input class="loginForm" size="40" type="password" id="inputPass" name="inputPass"></td>
						</tr>

						<tr>
							<th><span class="trim01">Group ID</th>
							<td>
								<select id="groupList">
									{foreach from=$groupList item=item}
										<option value="{$item[0]}">{$item[0]}</option>
									{/foreach}
								</select>
							</td>
						</tr>

						<tr>
							<th><span class="trim01">Display today's list</span></th>
							<td>
								<input name="newTodayDisp" type="radio" value="series" />series
								<input name="newTodayDisp" type="radio" value="cad" checked="checked" />CAD
							</td>
						</tr>

						<tr>
							<th><span class="trim01">Darkroom mode</span></th>
							<td>
								<input name="newDarkroom" type="radio" value="f" checked="checked" />white
								<input name="newDarkroom" type="radio" value="t" />black
							</td>
						</tr>

						<tr>
							<th><span class="trim01">Anonymization</span></th>
							<td>
								<input name="newAnonymized" type="radio" value="t" />TRUE
								<input name="newAnonymized" type="radio" value="f" checked="checked" />FALSE
							</td>
						</tr>
					
						<tr>
							<th><span class="trim01">Display missed lesions</span></th>
							<td>
								<input name="newShowMissed" type="radio" value="own" />own
								<input name="newShowMissed" type="radio" value="all" />all
								<input name="newShowMissed" type="radio" value="none" checked="checked" />none
							</td>
						</tr>
					</table>

					<div class="pl20 mb20 mt10">
						<p>
							<input type="button" id="addBtn" class="form-btn" value="add" onclick="UserSetting('add');" />&nbsp;
							<input type="button" id="updateBtn" class="form-btn form-btn-disabled" value="update"
								   onclick="UserSetting('update');" disabled="disabled" />
							<input type="button" id="cancelBtn" class="form-btn form-btn-disabled" value="cancel"
								   onclick="CancelUpdate();" disabled="disabled" />
						</p>
					</div>
				</div>
			</form>
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
</html>
