{capture name="require"}
jq/ui/jquery-ui.min.js
jq/jquery.blockUI.js
jq/ui/theme/jquery-ui.custom.css
{/capture}
{capture name="extra"}
<script language="javascript">
<!--
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
	else if(evalUser == "")
	{
		$("#errorMessage").html('"User" is not selected');
	}
	else
	{
		$("#errorMessage").html('&nbsp;');
		$.blockUI();

		$.ajax({
				type:   "POST",
				url:    "statistics/show_personal_stat_detail.php",
				data:   { dateFrom: $("#dateFrom").val(),
						  dateTo:   $("#dateTo").val(),
						  cadName:  $("#cadMenu option:selected").text(),
						  version:  $("#versionMenu").val(),
	       				  evalUser: $("#userMenu").val(),
						  minSize:  $("#minSize").val(),
			              maxSize:  $("#maxSize").val()},
				dataType: "json",
				timeout: 180000,	// 3 minutes (avoid timeout error)

				success: function(data){

							$.unblockUI();
							$("#errorMessage").html(data.errorMessage);

							if(data.errorMessage == "&nbsp;")
							{
								$("#statRes .col-tbl thead").html(data.theadHtml);
								$("#statRes .col-tbl tbody").html(data.tbodyHtml);
								$("#scatterPlotAx").attr("src", data.XY);
								$("#scatterPlotCoro").attr("src", data.XZ);
								$("#sactterPlotSagi").attr("src", data.YZ);

								$("#statRes").show();
								if(version == 'all' || evalUser == 'all' || data.caseNum == 0)
								{
									$("#scatterPlot").hide();
								}
								else
								{
									$("#scatterPlot").show();
									$("#scatterPlot [name^=check]").attr("checked", "checked");
								}

								$("#plotLegend td").hide();
								for(var i = 0; i < data.plotLegend.length; i++)
								{
									$("#plotLegend [name=" + data.plotLegend[i] + "]").show();
								}

								$("#container").height( $(document).height() - 10 );
							}
							else
							{
								$("#statRes").hide();
							}
						},

				error:   function(){
							$.unblockUI();
							alert("Fail to analyze personal statistics.");
						}
			});
	}
}

function RedrawScatterPlot()
{
	$.blockUI();

	$.ajax({
			type:   "POST",
			url:    "statistics/show_personal_stat_detail.php",
			data:   { dateFrom:    $("#dateFrom").val(),
			 		  dateTo:      $("#dateTo").val(),
					  cadName:     $("#cadMenu option:selected").text(),
					  version:     $("#versionMenu").val(),
            		  evalUser:    $("#userMenu").val(),
					  minSize:     $("#minSize").val(),
       			      maxSize:     $("#maxSize").val(),
					  dataStr:     $("#dataStr").val(),
					  knownTpFlg:  ((document.form1.checkKownTP.checked == true) ? 1 : 0),
					  missedTpFlg: ((document.form1.checkMissedTP.checked == true) ? 1 : 0),
					  subTpFlg:    ((document.form1.checkSubTP.checked == true) ? 1 : 0),
       			      fpFlg:       ((document.form1.checkFP.checked == true) ? 1 : 0),
					  pendingFlg:  ((document.form1.checkPending.checked == true) ? 1 : 0)},
			dataType: "json",

			success: function(data){
						$.unblockUI();
						$("#plotLegend td").hide();
						for(var i = 0; i < data.plotLegend.length; i++)
						{
							$("#plotLegend [name=" + data.plotLegend[i] + "]").show();
						}
						$("#scatterPlotAx").attr("src", data.XY);
						$("#scatterPlotCoro").attr("src", data.XZ);
						$("#sactterPlotSagi").attr("src", data.YZ);
					},

			error:   function(){
						$.unblockUI();
						alert("Fail to redraw scatter plots.");
					}
		});
}

function SetDate(mode)
{
	date = new Date();
	dd = date.getDate();

	switch(mode)
	{
		case "today":
			$("#dateFrom, #dateTo").val(date.getFullYear() + '-' + (date.getMonth()+1) + '-' + date.getDate());
			break;

		case "yesterday":
			dd -=  1;
			date.setDate(dd);
			$("#dateFrom, #dateTo").val(date.getFullYear() + '-' + (date.getMonth()+1) + '-' + date.getDate());
			break;

		case "7days":
		    dd -=  7;
			$("#dateTo").val(date.getFullYear() + '-' + (date.getMonth()+1) + '-' + date.getDate());
			date.setDate(dd);
			$("#dateFrom").val(date.getFullYear() + '-' + (date.getMonth()+1) + '-' + date.getDate());
			break;

		case "30days":
		    dd -= 30;
			$("#dateTo").val(date.getFullYear() + '-' + (date.getMonth()+1) + '-' + date.getDate());
			date.setDate(dd);
			$("#dateFrom").val(date.getFullYear() + '-' + (date.getMonth()+1) + '-' + date.getDate());
			break;

		case "thisMonth":
		    month = (date.getMonth()+1);
			$("#dateFrom").val(date.getFullYear() + '-' + month + '-01');
			date.setMonth(month, 01);
			date.setDate(date.getDate() -1);
			$("#dateTo").val(date.getFullYear() + '-' + (date.getMonth()+1) + '-' + date.getDate());
			break;

		case "lastMonth":
			date.setMonth(date.getMonth(), 01);
			date.setDate(date.getDate() -1);
			$("#dateTo").val(date.getFullYear() + '-' + (date.getMonth()+1) + '-' + date.getDate());
			$("#dateFrom").val(date.getFullYear() + '-' + (date.getMonth()+1) + '-01');
			break;
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
		version = 'all';
		$("#versionMenu").html(optionStr);
	}

	if(allStatFlg == 1)
	{
		$.post("statistics/user_list_for_parsonal_stat.php",
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
	$("#dateFrom, #dateTo, #minSize, #maxSize").removeAttr("value");
	$("#cadMenu, #userMenu, #versionMenu").children().removeAttr("selected");

	ChangeCadMenu();
}

$(function() {
	$("#dateFrom, #dateTo").datepicker({
			showOn: "button",
			buttonImage: "images/calendar_view_month.png",
			buttonImageOnly: true,
			buttonText:'',
			constrainInput: false,
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd',
			maxDate: 0}
		);

	$("#dateFrom").datepicker('option', {onSelect: function(selectedDate, instance){
					date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat,
						                          selectedDate, instance.settings );
					$("#dateTo").datepicker("option", "minDate", date);
				}});

	$("#dateTo").datepicker('option', {onSelect: function(selectedDate, instance){
					date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat,
						                          selectedDate, instance.settings );
					$("#dateFrom").datepicker("option", "maxDate", date);
				}});

	// Parameters of UI blocking for ajax requests (using jquery blockUI)
	$.blockUI.defaults.message = '<span style="font-weight:bold; font-size:16px;"><img src="images/busy.gif" />'
							   + ' Under processing, just moment...</span>';
	$.blockUI.defaults.fadeOut = 200;			// set fadeOut effect shorter
	$.blockUI.defaults.css.width   = '320px';
	$.blockUI.defaults.css.padding = '5px';

});
{/literal}
-->
</script>
{/capture}
{include file="header.tpl" body_class="personal-statistics"
	require=$smarty.capture.require head_extra=$smarty.capture.extra}

<!-- ***** TAB ***** -->
<div id="researchListTab" class="tabArea">
	<ul>
		<li><a href="#" class="btn-tab" title="Reading characteristics" style="background-image: url(img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">Personal statistics</a></li>
		<li><a href="statistics/time_for_feedback_entry.php" class="btn-tab" title="Time for feedback entry">Time for feedback</a></li>
	</ul>
</div><!-- / .tabArea END -->

<div class="tab-content">

	<h2>Personal statistics</h2>
	{*<h2>Reading characteristics</h2>*}

	<form name="form1">
	<input type="hidden" id="dataStr" name="dataStr" value="">

	<!-- ***** Serach conditions ***** -->
		<div class="statSearch">
			<h3>Search</h3>
			<div class="p20">
				<table class="search-tbl">
					<tr>
						<th style="width: 8em;"><span class="trim01">Series date</span></th>
						<td style="width: 220px;">
							<input id="dateFrom" type="text" style="width:72px;" />
							-
							<input id="dateTo" type="text" style="width:72px;" />

						</td>
						{*<td colspan="2" style="width:200px;">
							<input name="" type="button" class="form-btn" value="this month" onclick="SetDate('thisMonth');" />
							<input name="" type="button" class="form-btn" value="last month" onclick="SetDate('lastMonth');" />
						</td>*}
						<th style="width: 8em;"><span class="trim01">CAD name</span></th>
						<td>
							<select id="cadMenu" name="cadMenu" style="width: 120px;" onchange="ChangeUserList('cadMenu', {$smarty.session.allStatFlg});">
								<option value="" selected="selected">(Select)</option>
								{foreach from=$cadList item=item}
									<option value="{$item[1]|escape}">{$item[0]|escape}</option>
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
						<th style="width: 5.5em;"><span class="trim01">CAD version</span></th>
						<td>
							<select id="versionMenu" name="versionMenu" style="width: 70px;" onchange="ChangeUserList('versionMenu', {$smarty.session.allStatFlg});">
								<option value="all">all</option>
								{foreach from=$versionDetail item=item}
									<option value="{$item|escape}">{$item|escape}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<th style="width: 9.5em;"><span class="trim01">Size(diameter)</span></th>
						<td colspan="2">
							<input id="minSize" type="text" class="al-r" style="width: 36px">&nbsp;-&nbsp;<input id="maxSize" type="text" class="al-r" style="width: 36px">&nbsp;[mm]
						</td>
						<td></td><td></td>
					</tr>

				</table>
				<div class="al-l mt10 ml20" style="width: 100%;">
					<input name="" type="button" value="Apply" class="w100 form-btn" onclick="ShowPersonalStatResult()" />
					<input name="" type="button" value="Reset" class="w100 form-btn" onclick="ResetCondition()" />
					<p id="errorMessage" class="mt5" style="color:#f00; font-wight:bold;">&nbsp;</p>
				</div>
			</div><!-- / .m20 END -->
		</div><!-- / #statSearch END -->
	<!-- / Search conditions -->

	<div id="statRes" style="display:none;">
		<h3>Results of personal statistics</h3>
		<table class="col-tbl mt20 mb20" style="width: 100%;">
			<thead>
			</thead>
			<tbody>
			</tbody>
		</table>

		<div id="scatterPlot" style="width: 950px;" style="display:none;">
			<table id="plotLegend" class="block-al-r mb10">
				<tr>
					<td name="known TP">
						<input name="checkKownTP" type="checkbox" checked="checked" /><img src="images/statistics/knownTP.png" />
					</td>
					<td name="missed TP">
						<input name="checkMissedTP" type="checkbox" checked="checked" /><img src="images/statistics/missedTP.png" />
					</td>
					<td name="sub TP">
						<input name="checkSubTP" type="checkbox" checked="checked" /><img src="images/statistics/subTP.png" />
					</td>
					<td name="FP">
						<input name="checkFP" type="checkbox" checked="checked" /><img src="images/statistics/FP.png" />
					</td>
					<td name="pending">
						<input name="checkPending" type="checkbox" checked="checked" /><img src="images/statistics/pending.png" />
					</td>
					<td name="redrawBtn">
						<input name="" type="button" class="form-btn" value="Redraw" onclick="RedrawScatterPlot();" style="margin-left:5px; font-weight:bold;" />
					</td>
				</tr>
			</table>

			<table class="ml10">
				<tr>
					<td style="width: 330px;"><img id="scatterPlotAx"   src="images/statistics/ps_scatter_plot_base_xy.png" /></td>
					<td style="width: 330px;"><img id="scatterPlotCoro" src="images/statistics/ps_scatter_plot_base_xz.png" /></td>
					<td style="width: 330px;"><img id="sactterPlotSagi" src="images/statistics/ps_scatter_plot_base_yz.png" /></td>
				</tr>
			</table>
		</div>
	</div>
	</form>

	<div class="al-r fl-clr">
		<p class="pagetop"><a href="#page">page top</a></p>
	</div>

</div><!-- / .tab-content END -->

{include file="footer.tpl"}