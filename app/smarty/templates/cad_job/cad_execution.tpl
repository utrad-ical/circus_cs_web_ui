{capture name="require"}
../js/series_ruleset.js
{/capture}
{capture name="extra"}
<script type="text/javascript">
<!--
var mode = "{$params.mode|escape}";

{literal}
function ChangeCheckbox(sid, val)
{
	var numSelectedSeries = jQuery("#numSelectedSrStr").val().split("^");
	var selectedValue = jQuery('#series' + sid + 'Selected').val();

	// Display series rows depending on checked state
	for(var j=2; j<=numSelectedSeries.length; j++)
	{
		$('#series' + j + 'Selected').val("");

		for(var i=1; i<=numSelectedSeries[j-1]; i++)
		{
			if(i%2==0)	$('#rowSeries' + j + '_' + i).attr("class", "column rowDisp");
			else		$('#rowSeries' + j + '_' + i).attr("class", "rowDisp");

			var tmpObj = $('#checkbox' + j + '_' + i);

			if((j == sid) ^ (tmpObj.val() == val))  // XOR
			{
				tmpObj.removeAttr('checked');
			}

			if(tmpObj.attr("checked"))  $('#series' + j + 'Selected').val(tmpObj.val());
		}
	}

	// Hide series rows depending on checked state
	for(var k=2; k<=numSelectedSeries.length; k++)
	{
		selectedValue = $('#series' + k + 'Selected').val();

		if(selectedValue != "")
		{
			for(var j=2; j<=numSelectedSeries.length; j++)
			{
				for(var i=1; i<=numSelectedSeries[j-1]; i++)
				{
					var tmpObj = $('#checkbox' + j + '_' + i);

					if((j == k && selectedValue != "" && tmpObj.val() != selectedValue)
						|| (j != k && tmpObj.val() == selectedValue && !tmpObj.attr(":checked")))
					{
						$('#rowSeries' + j + '_' + i).attr("class", "rowHidden");
					}
				}
			}
		}
	}
}

function ResetSeries()
{
	var numSelectedSeries = $('#numSelectedSrStr').val().split("^");

	for(var k=2; k<=numSelectedSeries.length; k++)
	{
		$('#series' + k + 'Selected').val("");

		for(var j=1; j<=numSelectedSeries[k-1]; j++)
		{
			if(j%2==0)	$('#rowSeries' + k + '_' + j).attr("class", "column rowDisp");
			else		$('#rowSeries' + k + '_' + j).attr("class", "rowDisp");
			$('#checkbox' + k + '_' + j).removeAttr("checked");
		}
	}
}

function CreateSeriesList()
{
	$.post(
		"create_series_list.php",
		{ seriesUIDStr: $("#seriesUIDStr").val() },
		function(data){
			var tableHtml = "";
			for(i=0; i<data.length; i++)
			{
				tableHtml += '<tr';
				if(i%2==1)  tableHtml += ' class="column"';
				tableHtml += '><td>' + (i+1) + '</td>'
					+  '<td>' + data[i].study_id + '</td>'
					+ '<td>' + data[i].series_number + '</td>'
					+ '<td>' + data[i].series_date + '</td>'
					+ '<td>' + data[i].series_time + '</td>'
					+ '<td>' + data[i].modality + '</td>'
					+ '<td>' + data[i].image_number + '</td>'
					+ '<td class="al-l">' + data[i].series_description + '</td>'
					+ '</tr>';
			}
			$("#confirmation .col-tbl tbody").append(tableHtml);
			$("#success .col-tbl tbody").append(tableHtml);
		},
		"json"
	);
}


function CheckSeries()
{
	var seriesUID    = $("#seriesUIDStr").val();
	var numSelectedSeries = $('#numSelectedSrStr').val().split("^");

	var errorFlg = 0;

	if(confirm('Do you select required series?'))
	{
		var selectFlg = 0;

		for(var k=2; k<=numSelectedSeries.length; k++)
		{
			selectFlg = 0;

			for(var j=1; j<=numSelectedSeries[k-1]; j++)
			{
				$('#checkbox' + k + '_' + j + ":checked").each(function()
					{
						selectFlg = 1;
						seriesUID += '^' + this.value;
					});
			}

			if(selectFlg == 0)
			{
				alert ('Series ' + k + ' is not selected !!');
				break;
			}
		}

		if(selectFlg == 1)
		{
			$("#seriesUIDStr").val(seriesUID);
			CreateSeriesList();
			$("#seriesSelect").hide();
			$("#confirmation").show();
		}
	}
}

function RegistrationCADJob()
{
	var requestObj = {
		auth: { type: "session", user: $('#userID').val() },
		action: "InternalExecutePlugin",
		params: {
			pluginName: $('#cadName').val(),
			pluginVersion: $('#version').val(),
			seriesUID: $("#seriesUIDStr").val().split('^'),
			resultPolicy: $('#cadResultPolicy').val()
		}
	};
	$.post("../api/api.php",
		{ request: JSON.stringify(requestObj) },
		function(data){
			$("#confirmation").attr("style", "display:none;");
			$("#success .policyField").html($("#cadResultPolicy").val());

			if(data.status == "OK")
			{
				var htmlStr = '<tr><th style="width: 110px;"><span class="trim01">';
				if(data.result.executedAt != "")
				{
					htmlStr += 'Executed at</span></th><td>' + data.result.executedAt + '</td>';
				}
				else
				{
					htmlStr += 'Registered at</span></th><td>' + data.result.registeredAt + '</td>';
				}
				$("#registMessage").html(data.message);
				$("#success .detail-tbl").prepend(htmlStr);
				$("#success").show();
			}
			else
			{
				$("#errorMessage").html(data.error.message);
				$("#errorDetail").html($("#successDetail").html());
				$("#error").show();
			}
		},
		"json"
	);
}

$(function(){
	if(mode == "confirm")
	{
		CreateSeriesList();
		$("#confirmation").show();
	}
	else if(mode == "select")
	{
		$("#seriesSelect").show();
	}
	else if(mode == "error")
	{
		$("#error").show();
	}

	$('.filter').each(function() {
		var f = $(this);
		var data = JSON.parse(f.text());
		f.empty().append(circus.ruleset.stringifyNode(data));
	});
});
-->
</script>

<style type="text/css">
#seriesSelect .rowDisp   { color:#000; }
#seriesSelect .rowHidden { color:#ccc; }
ul.filters { margin: 0.5em 0 0.5em 2em; }
ul.filters li { list-style-type: disc; }
</style>
{/literal}
{/capture}
{include file="header.tpl" require=$smarty.capture.require
	head_extra=$smarty.capture.extra body_class="cad_execution"}

<div class="tabArea">
	<ul>
		{if $params.srcList!="" && $smarty.session.listAddress!=""}
			<li><a href="../{$smarty.session.listAddress}" class="btn-tab" title="{$params.listTabTitle}">{$params.listTabTitle}</a></li>
		{else}
			<li><a href="../series_list.php?mode=study&studyInstanceUID={$params.studyInstanceUID}" class="btn-tab" title="Series list">Series list</a></li>
		{/if}
		<li><a href="" class="btn-tab" title="list" style="background-image: url(../img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">CAD execution</a></li>
	</ul>
</div><!-- / .tabArea END -->

<div class="tab-content">
	<form id="form1" name="form1" onsubmit="return false;">
	<input type="hidden" id="userID"       value="{$params.userID|escape}" />
	<input type="hidden" id="cadName"      value="{$plugin->plugin_name|escape}" />
	<input type="hidden" id="version"      value="{$plugin->version|escape}" />
	<input type="hidden" id="seriesUIDStr" value="{$seriesUIDStr|escape}" />

	<div id="seriesSelect" style="display:none;">

		<h2>Series selection</h2>
		<p class="mb10">Select DICOM series, and press the <span class="clr-blue fw-bold">[OK]</span> button after selection.</p>

		<p class="mb10">
			<input name="" type="button" value="OK"     class="w100 form-btn" onclick="CheckSeries();" />
			<input name="" type="button" value="Cancel" class="w100 form-btn" onclick="history.back(1);" />
		</p>

		<div class="detail-panel mb20">
			<table class="detail-tbl">
				<tr>
					<th style="width: 9em;"><span class="trim01">CAD name</span></th>
					<td>{$plugin->fullName()|escape}</td>
				</tr>
				<tr>
					<th><span class="trim01">Patient ID</span></th>
					<td>{$params.patientID|escape}</td>
				</tr>
				<tr>
					<th><span class="trim01">Pateint name</span></th>
					<td>{$params.patientName|escape}</td>
				</tr>
			</table>
		</div><!-- / .detail-panel END -->

		{assign var="cnt" value=0}
		{section name=k start=0 loop=$seriesNum}
			{assign var="k" value=$smarty.section.k.index}

			<h3 class="ptn02">Volume {$k} {if $k==0}(Primary Volume){/if}{if $volumeInfo[$k].label}: {$volumeInfo[$k].label|escape}{/if}</h3>
			<ul class="filters">
				{foreach from=$volumeInfo[$k].ruleSetList item=ruleSet}
				<li class="filter">{$ruleSet.filter|@json_encode|escape}</li>
				{/foreach}
			</ul>
			<table id="selectTbl{$k+1}" class="series-list col-tbl mb30" style="width: 100%;">
				<thead>
					<tr>
						{if $k != 0}<th>&nbsp;</th>{/if}
						<th>Study ID</th>
						<th>Series ID</th>
						<th>Series date</th>
						<th>Series time</th>
						<th>Img.</th>
						<th>Series description</th>
					</tr>
				</thead>
				<tbody>
					{* ----- 1st series ----- *}
					{if $k==0}
						<tr>
							<td>{$seriesList[0][0][1]|escape}</td>
							<td>{$seriesList[0][0][2]|escape}</td>
							<td>{$seriesList[0][0][3]|escape}</td>
							<td>{$seriesList[0][0][4]|escape}</td>
							<td>{$seriesList[0][0][5]|escape}</td>
							<td class="al-l">{$seriesList[0][0][6]}</td>
						</tr>
					{else}
						{section name=j start=0 loop=$selectedSrNumArr[$k]}

							{assign var="j" value=$smarty.section.j.index}

								<tr id="rowSeries{$k+1}_{$j+1}" class="{if $j%2==1}column {/if}rowDisp">
									<td align=center>
										<input type="checkbox" id="checkbox{$k+1}_{$j+1}" value="{$seriesList[$k][$j][0]}" onclick="ChangeCheckbox({$k+1},'{$seriesList[$k][$j][0]}');" {if $seriesList[$k][$j][0] == $defaultSelectedSrUID[$k]}checked="checked" {/if}/>
									</td>
									<td>{$seriesList[$k][$j][1]|escape}</td>
									<td>{$seriesList[$k][$j][2]|escape}</td>
									<td>{$seriesList[$k][$j][3]|escape}</td>
									<td>{$seriesList[$k][$j][4]|escape}</td>
									<td>{$seriesList[$k][$j][5]|escape}</td>
									<td class="al-l">{$seriesList[$k][$j][6]}</td>
								</tr>
							{/section}
						<input type="hidden" id="series{$k+1}Selected" value="">
					{/if}
				</tbody>
			</table>
		{/section}

		<input name="" type="button" value="reset" class="s-btn mb30 form-btn" onclick="ResetSeries();" />
		<input type="hidden" id="numSelectedSrStr" value="{$numSelectedSrStr}" />
		</form>
	</div>
	<!-- / Detail END -->

	<!-- Confirmation -->
	<div id="confirmation" style="display:none;">
		<h2>Confirmation</h2>
		<p class="mb10">Do you register following CAD job?</p>

		<p class="mb10">
			<input name="" type="button" value="OK"     class="w100 form-btn" onclick="RegistrationCADJob();" />
			<input name="" type="button" value="Cancel" class="w100 form-btn" onclick="history.back(1);" />
		</p>

		<div class="detail-panel mb20">
			<table class="detail-tbl">
				<tr>
					<th style="width: 9em;"><span class="trim01">CAD name</span></th>
					<td>{$plugin->fullName()|escape}</td>
				</tr>
				<tr>
					<th><span class="trim01">Patient ID</span></th>
					<td>{$params.patientID|escape}</td>
				</tr>
				<tr>
					<th><span class="trim01">Pateint name</span></th>
					<td>{$params.patientName|escape}</td>
				</tr>
				<tr>
					<th><span class="trim01">Policy</span></th>
					<td>
						<select id="cadResultPolicy">
							{foreach from=$policyArr item=item}
							<option value="{$item.name|escape}"{if $item.name=="default"} selected="selected"{/if}>{$item.name|escape}</option>
							{/foreach}
						</select>
					</td>
				</tr>
			</table>
		</div>

		<h3 class="ptn02">Series list</h3>

		<table class="col-tbl mb30" style="width: 100%;">
			<thead>
				<tr>
					<th></th>
					<th>Study ID</th>
					<th>Series ID</th>
					<th>Series date</th>
					<th>Series time</th>
					<th>Modality</th>
					<th>Img.</th>
					<th>Series description</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
	<!-- / confirmation END -->

	<!-- 17.Successfully-registered-in-cad-job-list.html -->
	<div id="success" style="display:none;">
		<p id="registMessage" class="clr-orange mb10">Successfully registered in CAD job list!</p>
		<p class="mb10"><input name="" type="button" value="Close" class="w100 form-btn" onclick="location.replace('../{$smarty.session.listAddress}');" /></p>

		<div id="successDetail">
			<div class="detail-panel mb20">
				<table class="detail-tbl">
			<!--		<tr>
						<th style="width: 110px;"><span class="trim01">Registered at</span></th>
						<td><span id="registeredAt"></span></td>
					</tr> -->
					<tr>
						<th style="width: 10em;"><span class="trim01">Ordered by</span></th>
						<td>{$params.userID|escape}</td>
					</tr>
					<tr>
						<th style="width: 9em;"><span class="trim01">CAD name</span></th>
						<td>{$plugin->fullName()|escape}</td>
					</tr>
					<tr>
						<th><span class="trim01">Patient ID</span></th>
						<td>{$params.patientID|escape}</td>
					</tr>
					<tr>
						<th><span class="trim01">Pateint name</span></th>
						<td>{$params.patientName|escape}</td>
					</tr>
					<tr>
						<th><span class="trim01">Policy</span></th>
						<td class="policyField"></td>
					</tr>
				</table>
			</div>

			<h3 class="ptn02">Series list</h3>
			<table class="col-tbl mb30" style="width: 100%;">
				<thead>
					<tr>
						<th></th>
						<th>Study ID</th>
						<th>Series ID</th>
						<th>Series date</th>
						<th>Series time</th>
						<th>Modality</th>
						<th>Img.</th>
						<th>Series description</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
	<!-- / Seccessfully END -->

	<!-- Error display -->
	<div id="error" style="display:none;">
		<h2>Error</h2>

		<div id="errorMessage" style="color:#f00; font-weight:bold; margin-bottom:10px;">
			{if $params.errorMessage != ""}{$params.errorMessage|escape|nl2br}{else}{$plugin->fullName()|escape} requires following series in the same {if $params.inputType == 1}study{else}patient{/if}!!{/if}&nbsp;&nbsp;
			<input name="" type="button" value="Close" class="w100 form-btn" onclick="location.replace('../{$smarty.session.listAddress}');" />
		</div>

		<div id="errorDetail">
			<div class="detail-panel mb20">
			{if $params.errorMessage == ""}
				<table class="col-tbl">
					<thead>
						<tr>
							<th>Volume ID</th>
							<th>Label</th>
							<th>Filter</th>
						</tr>
					</thead>
					<tbody>
					{foreach from=$volumeInfo item=vol}
						<tr>
							<td>{$vol.id}</td>
							<td>{$vol.label|escape}</td>
							<td class="al-l">
								<ul class="filters">
									{foreach from=$vol.ruleSetList item=ruleSet}
									<li class="filter">{$ruleSet.filter|@json_encode|escape}</li>
									{/foreach}
								</ul>
							</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			{/if}
			</div>
		</div>
	</div>
	<!-- / Error END -->

	<div class="al-r fl-clr">
		<p class="pagetop"><a href="#page">page top</a></p>
	</div>
</div><!-- / .tab-content END -->

{include file="footer.tpl"}
