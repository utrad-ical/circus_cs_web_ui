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

<link href="css/import.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="jq/jquery-1.3.2.min.js"></script>
<script language="javascript" type="text/javascript" src="jq/jq-btn.js"></script>
<script language="javascript" type="text/javascript" src="js/hover.js"></script>
<script language="javascript" type="text/javascript" src="js/viewControl.js"></script>
<link rel="shortcut icon" href="favicon.ico" />

<script language="Javascript">;
<!--
{literal}


function ChangePassword()
{
	$.post("./preference/change_password.php",
           {oldPassword:     $("#oldPassword").val(),
            newPassword:     $("#newPassword").val(),
            reenterPassword: $("#reenterPassword").val()},
            function(ret){
				alert(ret);
				$("#oldPassword, #newPassword, #reenterPassword").val("");
			 });
}

function ChangePagePreference()
{
	$.post("./preference/change_page_preference.php",
           {oldTodayDisp:     $("#oldTodayDisp").val(),
            newTodayDisp:     $('input[name="newTodayDisp"]:checked').val(),
            oldDarkroomFlg:   $("#oldDarkroomFlg").val(),
            newDarkroomFlg:   $('input[name="newDarkroomFlg"]:checked').val(),
            oldAnonymizeFlg:  $("#oldAnonymizeFlg").val(),
            newAnonymizeFlg:  $('input[name="newAnonymizeFlg"]:checked').val(),
            oldLatestResults: $("#oldLatestResults").val(),
            newLatestResults: $('input[name="newLatestResults"]:checked').val()},

            function(data){
				if(data.message == "Success")
				{
					alert('Page preference was successfully changed.');
					$("#oldTodayDisp").val($('input[name="newTodayDisp"]:checked').val());
					$("#oldDarkroomFlg").val($('input[name="newDarkroomFlg"]:checked').val());
					$("#oldAnonymizeFlg").val($('input[name="newAnonymizeFlg"]:checked').val());
					$("#oldLatestResults").val($('input[name="newLatestResults"]:checked').val());
					$("#linkTodayDisp").attr("href", data.todayList + ".php?mode=today");
				}
				else  alert(data.messaget);
			}, "json");
}

function ShowCadPreferenceDetail()
{
	var cadName = $("#cadMenu option:selected").text();
	var version = $("#versionMenu").val();

	$.post("./preference/show_cad_preference_detail.php",
            {cadName: cadName, version: version},
             function(data){
				$("#cadName").val(data.cadName);
				$("#version").val(data.version);
				$("#preferenceFlg").val(data.preferenceFlg);
				$("#defaultSortKey").val(data.defaultSortKey);
				$("#defaultSortOrder").val(data.defaultSortOrder);
				$("#defaultMaxDispNum").val(data.defaultMaxDispNum);
				$("#defaultConfidenceTh").val(data.defaultConfidenceTh);
				$("#message").html(data.message);
				$("#maxDispNum").val(data.maxDispNum);
				$("#confidenceTh").val(data.confidenceTh);
				$("#sortKey").val(data.sortKey);
				$("#detailCadPrefrence input[name='sortOrder']").filter(function(){
					return ($(this).val() == data.sortOrder)
				}).attr("checked", true);
				$("#detailCadPrefrence input[name='dispConfidence']").filter(function(){
					return ($(this).val() == data.dispConfidence)
				}).attr("checked", true);
				$("#detailCadPrefrence input[name='dispCandidateTag']").filter(function(){
					return ($(this).val() == data.dispCandidateTag)
				}).attr("checked", true);
				$("#preferenceFlg").val(data.preferenceFlg);

				$("#detailCadPrefrence").show();
				$("#updateCADPrefBtn").show();
				if(data.preferenceFlg == 1)  $("#deleteCADPrefBtn").show();
				$("#container").height( $(document).height() - 10 );

			   }, "json");
}

function RegisterCadPreference(mode)
{
	if(mode == 'update' || mode == 'delete')
	{
		if(confirm('Do you ' + mode + ' the preference?'))
		{
			var cadName = $("#cadMenu option:selected").text();
			var version = $("#versionMenu").val();

			$.post("./preference/regist_cad_preference.php",
                   { mode: mode, cadName: cadName, version: version,
				     sortKey: $("#sortKey").val(),
					 sortOrder: $('#detailCadPrefrence input[name="sortOrder"]:checked').val(),
                     maxDispNum: $("#maxDispNum").val(),
                     confidenceTh: $("#confidenceTh").val(),
					 preferenceFlg: $("#preferenceFlg").val(),
					 dispConfidenceFlg: $('#detailCadPrefrence input[name="dispConfidence"]:checked').val(),
					 dispCandidateTagFlg: $('#detailCadPrefrence input[name="dispCandidateTag"]:checked').val()},
  					 function(data){

						if(data.message != null)
						{
							alert(data.message);

							if(data.message == 'Succeeded!')
							{
								$("#preferenceFlg").val(data.preferenceFlg);

								if(mode == 'delete')
								{
									$("#message").html('Default settings');
									$("#maxDispNum").val($("#defaultMaxDispNum").val());
									$("#confidenceTh").val($("#defaultConfidenceTh").val());
									$("#sortKey").val($("#defaultSortKey").val());
									$("#detailCadPrefrence input[name='sortOrder']").filter(function(){
										return ($(this).val() == $("#defaultSortOrder").val())
									}).attr("checked", true);
									$("#detailCadPrefrence input[name='dispConfidence']").filter(function(){
										return ($(this).val() == "f")
									}).attr("checked", true);
									$("#detailCadPrefrence input[name='dispCandidateTag']").filter(function(){
										return ($(this).val() == "f")
									}).attr("checked", true);
									$("#deleteCADPrefBtn").hide();
								}
								else
								{
									$("#message").html("&nbsp;");
									if(data.preferenceFlg == 1)  $("#deleteCADPrefBtn").show();

									if(data.newMaxDispNum==0)	$("#maxDispNum").val("all");
									else						$("#maxDispNum").val(data.newMaxDispNum);
								}
							}
						}
					}, "json");
		}
	}
}

function ChangeCadMenu()
{
	var versionStr = $("#cadMenu option:selected").val().split("^");
	
	var optionStr = "";

	if(versionStr != "")
	{
		for(var i=0; i<versionStr.length; i++)
		{
			if(versionStr[i] != 'all')
			{
				optionStr += '<option value="' + versionStr[i] + '">' + versionStr[i] + '</option>';
			}
		}
	}
	$("#versionMenu").html(optionStr);
}


{/literal}
-->
</script>


<!-- InstanceBeginEditable name="head" -->
<link href="./css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="./css/popup.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="./js/hover.js"></script>

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
			<input type="hidden" id="oldTodayDisp"        value="{$oldTodayDisp|escape}">
			<input type="hidden" id="oldDarkroomFlg"      value="{$oldDarkroomFlg|escape}">
			<input type="hidden" id="oldAnonymizeFlg"     value="{$oldAnonymizeFlg|escape}">
			<input type="hidden" id="oldLatestResults"    value="{$oldLatestResults|escape}">
			<input type="hidden" id="cadName"             value="">
			<input type="hidden" id="version"             value="">
			<input type="hidden" id="preferenceFlg"       value="">
			<input type="hidden" id="defaultSortKey"      value="">
			<input type="hidden" id="defaultSortOrder"    value="">
			<input type="hidden" id="defaultMaxDispNum"   value="">
			<input type="hidden" id="defaultConfidenceTh" value="">
			<input type="hidden" name="ticket" value="{$ticket|escape}">

			<h2>User preference</h2>
			
			<h3>Change password</h3>
			<div class="p20" style="width: 50%;">
				<form onsubmit="return false;">
				<table class="detail-tbl" style="width: 100%;">
					<tr>
						<th style="width:15em;"><span class="trim01">Current password</span></th>
						<td>
							<input id="oldPassword" type="password" style="width: 150px;" />
						</td>
					</tr>
					<tr>
						<th><span class="trim01">New password</span></th>
						<td><input id="newPassword" type="password" value="" style="width: 150px;" /></td>
					</tr>
					<tr>
						<th><span class="trim01">Re-enter new password</span></th>
						<td><input id="reenterPassword" type="password" value="" style="width: 150px;" /></td>
					</tr>
				</table>

				<div class="pl20 mb20 mt10">
					<p>
						<input id="changePagePrefBtn" type="button" value="Change" class="w100 form-btn" onClick="ChangePassword();" />
					</p>
				</div>
				</form>
			</div>

			<h3>Page preference</h3>
			<div class="p20" style="width: 50%;">
				<form onsubmit="return false;">
				<table class="detail-tbl" style="width: 100%;">
					<tr>
						<th style="width: 17em;"><span class="trim01">Display today's list</span></th>
						<td>
							<input name="newTodayDisp" type="radio" value="series"{if $oldTodayDisp=="series"} checked="checked"{/if} />series&nbsp;
							<input name="newTodayDisp" type="radio" value="cad"{if $oldTodayDisp=="cad"} checked="checked"{/if} />CAD
						</td>
					</tr>
					<tr>
						<th><span class="trim01">Darkroom mode</span></th>
						<td>
							<input name="newDarkroomFlg" type="radio" value="f"{if $oldDarkroomFlg=="f"} checked="checked"{/if} />white&nbsp;
							<input name="newDarkroomFlg" type="radio" value="t"{if $oldDarkroomFlg=="t"} checked="checked"{/if} />black
						</td>
					</tr>
					<tr>
						<th><span class="trim01">Anonymization</span></th>
						<td>
							<input name="newAnonymizeFlg" type="radio" value="t"{if $oldAnonymizeFlg=="t"} checked="checked"{/if}{if $smarty.session.anonymizeGroupFlg == 1} disabled="disabled"{/if} />TRUE&nbsp;
							<input name="newAnonymizeFlg" type="radio" value="f"{if $oldAnonymizeFlg=="f"} checked="checked"{/if}{if $smarty.session.anonymizeGroupFlg == 1} disabled="disabled"{/if} />FALSE
						</td>
					</tr>
					<tr>
						<th><span class="trim01">Latest results</span></th>
						<td>
							<input name="newLatestResults" type="radio" value="own"{if $oldLatestResults=="own"} checked="checked"{/if} />own&nbsp;
							<input name="newLatestResults" type="radio" value="all"{if $oldLatestResults=="all"} checked="checked"{/if} />all&nbsp;
							<input name="newLatestResults" type="radio" value="none"{if $oldLatestResults=="none"} checked="checked"{/if} />none
						</td>
					</tr>
				</table>
				<div class="pl20 mb20 mt10">
					<p>
						<input id="changePagePrefBtn" type="button" value="Change" class="w100 form-btn" onClick="ChangePagePreference();" />
					</p>
				</div>
				</form>
			</div>
			
			<h3>CAD preference</h3>
			<div class="p20" style="width: 50%;">
				<div class="detail-panel02">
					<table class="detail-tbl" style="width: 100%;">
						<tr>
							<th style="width:4em;"><span class="trim01">CAD</span></th>
							<td style="width:120px;">
								<select id="cadMenu" name="cadMenu" onchange="ChangeCadMenu();">';
									{foreach from=$cadList item=item}
										<option value="{$item[1]}">{$item[0]}</option>
									{/foreach}
								</select>
							</td>
							<th style="width:5em;"><span class="trim01">Version</span></th>
							<td>
								<select id="versionMenu">
									{foreach from=$verDetail item=item}
										<option value="{$item}">{$item}</option>
									{/foreach}
								</select>
							</td>
						</tr>
					</table>
				
					<div class="pl20 mb20 mt10">
						<p><input id="applyButton" type="button" value="Select" class="w100 form-btn" onClick="ShowCadPreferenceDetail();" /></p>
					</div>

					<div id="detailCadPrefrence" style="display:none;">
						<h4 id="message" class="upSec">&nbsp;</h4>
					
						<table class="detail-tbl" style="width: 100%;">
							<tr>
								<th style="width: 17em;"><span class="trim01">Sort key</span></th>
								<td>
									<select id="sortKey" name="sortKey">
										{foreach from=$sortStr item=item name=cnt}
											<option value="{$smarty.foreach.cnt.index}">{$item}</option>
										{/foreach}	
									</select>
								</td>
							</tr>
							<tr>
								<th><span class="trim01">Sort order</span></th>
								<td>
									<input type="radio" name="sortOrder" value="f" />Asc.
					    			<input type="radio" name="sortOrder" value="t" />Desc.
								</td>
							</tr>
							<tr>
								<th><span class="trim01">Maximum display candidates</span></th>
								<td>
									<input id="maxDispNum" type="text" class="al-r" style="width: 100px;" />
								</td>
							</tr>
							<tr>
								<th><span class="trim01">Threshold of confidence</span></th>
								<td>
									<input id="confidenceTh" type="text" class="al-r" style="width: 100px;" />
								</td>
							</tr>
							<tr>
								<th><span class="trim01">Disp confidence</span></th>
								<td>
									<input type="radio" name="dispConfidence" value="t" />True
					    			<input type="radio" name="dispConfidence" value="f" />False
								</td>
							</tr>
							<tr>
								<th><span class="trim01">Disp tags for lesion candidate</span></th>
								<td>
									<input type="radio" name="dispCandidateTag" value="t" />True
					    			<input type="radio" name="dispCandidateTag" value="f" />False
								</td>
							</tr>
						</table>
					
						<div class="pl20 mb20 mt10">
								<input id="updateCADPrefBtn" type="button" value="Update" class="w100 form-btn" onclick="RegisterCadPreference('update');">
			   					<input id="deleteCADPrefBtn" type="button" value="Delete" class="w100 form-btn" onclick="RegisterCadPreference('delete');" style="display:none;">
						</div>
					</div>
				</div>
			</div>
			</form>
<!-- InstanceEndEditable -->
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
<!-- InstanceEnd --></html>

