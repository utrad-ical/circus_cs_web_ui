{capture name="require"}
css/popup.css
js/hover.js
{/capture}
{capture name="extra"}

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
           {oldTodayDisp:  $("#oldTodayDisp").val(),
            newTodayDisp:  $('input[name="newTodayDisp"]:checked').val(),
            oldDarkroom:   $("#oldDarkroom").val(),
            newDarkroom:   $('input[name="newDarkroom"]:checked').val(),
            oldAnonymized: $("#oldAnonymized").val(),
            newAnonymized: $('input[name="newAnonymized"]:checked').val(),
            oldShowMissed: $("#oldShowMissed").val(),
            newShowMissed: $('input[name="newShowMissed"]:checked').val()},

            function(data){
				if(data.message == "Success")
				{
					alert('Page preference was successfully changed.');
					$("#oldTodayDisp").val($('input[name="newTodayDisp"]:checked').val());
					$("#oldDarkroom").val($('input[name="newDarkroom"]:checked').val());
					$("#oldAnonymized").val($('input[name="newAnonymized"]:checked').val());
					$("#oldShowMissed").val($('input[name="newShowMissed"]:checked').val());
					$("#linkTodayDisp").attr("href", data.todayList + ".php?mode=today");
				}
				else  alert(data.message);
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
				$("#defaultSortKey").val(data.sortKey[0]);
				$("#defaultSortOrder").val(data.sortOrder[0]);
				$("#defaultMaxDispNum").val(data.maxDispNum[0]);
				$("#defaultConfidenceTh").val(data.confidenceTh[0]);
				$("#defaultDispConfidence").val(data.dispConfidence[0]);
				$("#defaultDispCandidateTag").val(data.dispCandidateTag[0]);
				$("#message").html(data.message);
				$("#maxDispNum").val(data.maxDispNum[1]);
				$("#confidenceTh").val(data.confidenceTh[1]);
				$("#sortKey").val(data.sortKey[1]);
				$("#detailCadPrefrence input[name='sortOrder']").filter(function(){
					return ($(this).val() == data.sortOrder[1])
				}).attr("checked", true);
				$("#detailCadPrefrence input[name='dispConfidence']").filter(function(){
					return ($(this).val() == data.dispConfidence[1])
				}).attr("checked", true);
				$("#detailCadPrefrence input[name='dispCandidateTag']").filter(function(){
					return ($(this).val() == data.dispCandidateTag[1])
				}).attr("checked", true);
				$("#preferenceFlg").val(data.preferenceFlg);

				$("#detailCadPrefrence").show();
				$("#updateCADPrefBtn").show();

				if(data.preferenceFlg == 1)  $("#deleteCADPrefBtn").show();
				else						 $("#deleteCADPrefBtn").hide();

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
										return ($(this).val() == $("#defaultDispConfidence").val())
									}).attr("checked", true);
									$("#detailCadPrefrence input[name='dispCandidateTag']").filter(function(){
										return ($(this).val() == $("#defaultDispCandidateTag").val())
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
</script>
{/capture}

{include file="header.tpl" body_class="spot"
	head_extra=$smarty.capture.extra require=$smarty.capture.require}

<form id="form1" name="form1" onsubmit="return false;">
<input type="hidden" id="oldTodayDisp"            value="{$oldTodayDisp|escape}">
<input type="hidden" id="oldDarkroom"             value="{$oldDarkroom|escape}">
<input type="hidden" id="oldAnonymized"           value="{$oldAnonymized|escape}">
<input type="hidden" id="oldShowMissed"           value="{$oldShowMissed|escape}">
<input type="hidden" id="cadName"                 value="">
<input type="hidden" id="version"                 value="">
<input type="hidden" id="preferenceFlg"           value="">
<input type="hidden" id="defaultSortKey"          value="">
<input type="hidden" id="defaultSortOrder"        value="">
<input type="hidden" id="defaultMaxDispNum"       value="">
<input type="hidden" id="defaultConfidenceTh"     value="">
<input type="hidden" id="defaultDispConfidence"   value="">
<input type="hidden" id="defaultDispCandidateTag" value="">
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
			<input id="changePagePrefBtn" type="button" value="Change" class="w100 form-btn" onclick="ChangePassword();" />
		</p>
	</div>
	</form>
</div>

<h3>Page preference</h3>
<div class="p20" style="width: 50%;">
	<form onsubmit="return false;">
	<table class="detail-tbl" style="width: 100%;">
		<tr>
			<th style="width: 25em;"><span class="trim01">Display today's list</span></th>
			<td>
				<input name="newTodayDisp" type="radio" value="series"{if $oldTodayDisp=="series"} checked="checked"{/if} />series&nbsp;
				<input name="newTodayDisp" type="radio" value="cad"{if $oldTodayDisp=="cad"} checked="checked"{/if} />CAD
			</td>
		</tr>
		<tr>
			<th><span class="trim01">Darkroom mode</span></th>
			<td>
				<input name="newDarkroom" type="radio" value="f"{if $oldDarkroom=="f"} checked="checked"{/if} />white&nbsp;
				<input name="newDarkroom" type="radio" value="t"{if $oldDarkroom=="t"} checked="checked"{/if} />black
			</td>
		</tr>
		<tr>
			<th><span class="trim01">Anonymization</span></th>
			<td>
				<input name="newAnonymized" type="radio" value="t"{if $oldAnonymized=="t"} checked="checked"{/if}{if $smarty.session.anonymizeGroupFlg == 1} disabled="disabled"{/if} />TRUE&nbsp;
				<input name="newAnonymized" type="radio" value="f"{if $oldAnonymized=="f"} checked="checked"{/if}{if $smarty.session.anonymizeGroupFlg == 1} disabled="disabled"{/if} />FALSE
			</td>
		</tr>
		<tr>
			<th><span class="trim01">Display latest missed lesions in home page</span></th>
			<td>
				<input name="newShowMissed" type="radio" value="own"{if $oldShowMissed=="own"} checked="checked"{/if} />own&nbsp;
				<input name="newShowMissed" type="radio" value="all"{if $oldShowMissed=="all"} checked="checked"{/if} />all&nbsp;
				<input name="newShowMissed" type="radio" value="none"{if $oldShowMissed=="none"} checked="checked"{/if} />none
			</td>
		</tr>
	</table>
	<div class="pl20 mb20 mt10">
		<p>
			<input id="changePagePrefBtn" type="button" value="Change" class="w100 form-btn" onclick="ChangePagePreference();" />
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
			<p><input id="applyButton" type="button" value="Select" class="w100 form-btn" onclick="ShowCadPreferenceDetail();" /></p>
		</div>

		<div id="detailCadPrefrence" style="display:none;">
			<h4 id="message" class="themeColor">&nbsp;</h4>

			<table class="detail-tbl" style="width: 100%;">
				<tr>
					<th style="width: 17em;"><span class="trim01">Sort key</span></th>
					<td>
						<select id="sortKey" name="sortKey">
							{foreach from=$sortArr item=item name=cnt}
								<option value="{$item[0]}">{$item[1]}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr>
					<th><span class="trim01">Sort order</span></th>
					<td>
						<input type="radio" name="sortOrder" value="ASC" />Asc.
		    			<input type="radio" name="sortOrder" value="DESC" />Desc.
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
						<input type="radio" name="dispConfidence" value="1" />True
		    			<input type="radio" name="dispConfidence" value="0" />False
					</td>
				</tr>
				<tr>
					<th><span class="trim01">Disp tags for lesion candidate</span></th>
					<td>
						<input type="radio" name="dispCandidateTag" value="1" />True
		    			<input type="radio" name="dispCandidateTag" value="0" />False
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

{include file="footer.tpl"}