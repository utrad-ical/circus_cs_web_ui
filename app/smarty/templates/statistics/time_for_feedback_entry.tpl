{capture name="require"}
jq/ui/jquery-ui.min.js
js/jquery.daterange.js
jq/ui/theme/jquery-ui.custom.css
jq/jquery.blockUI.js
{/capture}
{capture name="extra"}
<script>;
{literal}
function ShowPersonalStatResult()
{
	var cadName  = $("#cadMenu option:selected").text();
	var version  = $("#versionMenu").val();
	var evalUser = $("#userMenu").val();

	if(cadName == "(Select)")
	{
		$("#errorMessage").html('"CAD" is not selected."');
	}
	else if(!evalUser)
	{
		$("#errorMessage").html('"User" is not selected');
	}
	else
	{
		$("#errorMessage").html('&nbsp;');
		$.blockUI();

		$.ajax({
				type:   "POST",
				url:    "show_feedback_time_list.php",
				data:   { dateFrom: $('#srDateRange').daterange('option', 'fromDate'),
						  dateTo:   $('#srDateRange').daterange('option', 'toDate'),
						  cadName:  $("#cadMenu option:selected").text(),
						  version:  $("#versionMenu").val(),
	       				  evalUser: $("#userMenu").val()},
				dataType: "json",
				timeout: 120000,	// 2 minutes (avoid timeout error)

				success: function(data){

							$.unblockUI();
							$("#errorMessage").html(data.errorMessage);

							if(data.errorMessage == "&nbsp;")
							{
								$("#statRes .col-tbl tbody").html(data.tblHtml);
								$("#statRes").show();
							}
							else
							{
								$("#statRes").hide();
							}
						},

				error:   function(){
							$.unblockUI();
							alert("Fail to analyze.");
						}
			});
	}
}

function ChangeUserList(mode, allStatFlg)
{
	var cadName = $("#cadMenu option:selected").text();
	var version = $("#versionMenu option:selected").text();

	// Set <option> of version menu
	if(mode == 'cadMenu')
	{
		var versionStr = $("#cadMenu option:selected").val().split("^");
		var optionStr = '<option value="all" selected="selected">all</option>';

		if(versionStr)
		{
			for(var i=0; i<versionStr.length; i++)
			{
				if(versionStr[i] != 'all')
				{
					optionStr += '<option value="' + versionStr[i] + '">' + versionStr[i] + '</option>';
				}
			}
		}
		version = 'all';
		$("#versionMenu").html(optionStr);
	}

	if(allStatFlg == 1)
	{
		$.post("user_list_for_parsonal_stat.php",
			 	{ cadName: cadName,
			 	  version: version},
				  function(data){
							if(data.errorMessage == "" && data.userOptionStr != "")
							{
								$("#userMenu").html(data.userOptionStr);
							}
					}, "json");
	}
}


function ResetCondition()
{
	$("#srDateRange").daterange('option', 'kind', 'all');
	$("#cadMenu, #userMenu, #versionMenu").children().removeAttr("selected");
}

$(function() {

	$('#srDateRange').daterange({ icon: '../images/calendar_view_month.png' });

	// Parameters of UI blocking for ajax requests (using jquery blockUI)
	$.blockUI.defaults.message = '<span style="font-weight:bold; font-size:16px;"><img src="../images/busy.gif" />'
							   + ' Under processing, just moment...</span>';
	$.blockUI.defaults.fadeOut = 200;			// set fadeOut effect shorter
	$.blockUI.defaults.css.width   = '320px';
	$.blockUI.defaults.css.padding = '5px';
});

{/literal}
</script>
{/capture}
{include file="header.tpl" body_class="spot"
	head_extra=$smarty.capture.extra require=$smarty.capture.require}

<h2><div class="breadcrumb"><a href="statistics.php">Statistics</a> &gt;</div>
Time for feedback entry</h2>

<form name="form1">
<input type="hidden" id="dataStr" name="dataStr" value="">

<!-- ***** Search conditions ***** -->
	<div class="statSearch">
		<h3>Search</h3>
		<div style="padding: 20px;">
			<table class="search-tbl">
				<tr>
				<tr>
					<th style="width: 8em;"><span class="trim01">Series date</span></th>
					<td colspan="3"><span id="srDateRange"></span></td>
				</tr>
				<tr>
					<th><span class="trim01">CAD name</span></th>
					<td style="width: 150px;">
						<select id="cadMenu" name="cadMenu" style="width: 120px;" onchange="ChangeUserList('cadMenu', {$smarty.session.allStatFlg});">
							<option value="" selected="selected">(Select)</option>
							{foreach from=$cadList item=item}
								<option value="{$item[1]|escape}">{$item[0]|escape}</option>
							{/foreach}
						</select>
					</td>
					<th style="width: 9.5em;"><span class="trim01">CAD version</span></th>
					<td>
						<select id="versionMenu" name="versionMenu" style="width: 70px;" onchange="ChangeUserList('versionMenu', {$smarty.session.allStatFlg});">
							<option value="" selected="selected">(Select)</option>
							{foreach from=$versionDetail item=item}
								<option value="{$item|escape}">{$item|escape}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr>
					<th><span class="trim01">User</span></th>
					<td>
						<select id="userMenu" name="userMenu" style="width: 100px;">
							{if $smarty.session.allStatFlg}
								<option value="">(Select)</option>
							{else}
								<option value="{$smarty.session.userID|escape}">{$smarty.session.userID|escape}</option>
							{/if}
						</select>
					</td>
				</tr>
			</table>
			<div class="al-l" style="margin-top: 10px; margin-left: 20px; width: 100%;">
				<input name="" type="button" value="Apply" class="form-btn" style="width: 100px;" onclick="ShowPersonalStatResult()" />
				<input name="" type="button" value="Reset" class="form-btn" style="width: 100px;" onclick="ResetCondition()" />
				<p id="errorMessage" style="margin-top: 5px; color:#f00; font-wight:bold;">&nbsp;</p>
			</div>
		</div>
	</div><!-- / #statSearch END -->
<!-- / Search conditions END -->

<div id="statRes" style="display:none;">
	<h3>Time for feedback entry</h3>
	<table class="col-tbl" style="margin-top: 20px; margin-bottom: 20px; width: 100%;">
		<thead>
			<tr>
				<th rowspan="2">CAD ID</th>
				<th rowspan="2">Patient ID</th>
				<th colspan="2">Series</th>
				<th rowspan="2">CAD</th>
				<th rowspan="2">CAD date</th>
				<th colspan="2">Elapsed time [sec]</th>
				<th colspan="2">Number of</th>
			</tr>
			<tr>
				<th>Date</th>
				<th>Time</th>
				<th>Cand. classify</th>
				<th>FN input</th>
				<th>Disp. cand.</th>
				<th>Entered FN</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>

</div>
</form>

{include file="footer.tpl"}