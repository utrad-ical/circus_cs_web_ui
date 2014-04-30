{capture name="require"}
jq/ui/jquery-ui.min.js
js/jquery.daterange.js
jq/jquery.blockUI.js
jq/ui/theme/jquery-ui.custom.css
{/capture}
{capture name="extra"}
<script>
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
	else if(!evalUser)
	{
		$("#errorMessage").html('"User" is not selected');
	}
	else
	{
		$("#errorMessage").html('&nbsp;');
		$.blockUI();

		$("#scatterPlot [name^='check']").prop("checked", false);
        $("[name='checkKownTP'],[name='checkMissedTP']","#scatterPlot").prop("checked", true);

		$.ajax({
				type:   "POST",
				url:    "show_personal_stat_detail.php",
				data:   { dateFrom: $('#srDateRange').daterange('option', 'fromDate'),
				 		  dateTo:   $('#srDateRange').daterange('option', 'toDate'),
				 		  cadName:  $("#cadMenu option:selected").text(),
						  version:  $("#versionMenu").val(),
						  evalUser: $("#userMenu").val(),
						  minSize:  $("#minSize").val(),
						  maxSize:  $("#maxSize").val(),
						  dataStr:  $("#dataStr").val(),
						  knownTpFlg:  ((document.form1.checkKownTP.checked == true) ? 1 : 0),
						  missedTpFlg: ((document.form1.checkMissedTP.checked == true) ? 1 : 0),
						  subTpFlg:    ((document.form1.checkSubTP.checked == true) ? 1 : 0),
						  fpFlg:       ((document.form1.checkFP.checked == true) ? 1 : 0),
						  pendingFlg:  ((document.form1.checkPending.checked == true) ? 1 : 0)},
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
								}

								$("#plotLegend td").hide();
								for(var i = 0; i < data.plotLegend.length; i++)
								{
									$('#plotLegend [name="' + data.plotLegend[i] + '"]').show();
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
							alert("Failed to analyze personal statistics.");
						}
			});
	}
}

function RedrawScatterPlot()
{
	$.blockUI();

	$.ajax({
			type:   "POST",
			url:    "show_personal_stat_detail.php",
			data:   { dateFrom:    $('#srDateRange').daterange('option', 'fromDate'),
			 		  dateTo:      $('#srDateRange').daterange('option', 'toDate'),
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
							$('#plotLegend [name="' + data.plotLegend[i] + '"]').show();
						}
						$("#scatterPlotAx").attr("src", data.XY);
						$("#scatterPlotCoro").attr("src", data.XZ);
						$("#sactterPlotSagi").attr("src", data.YZ);
					},

			error:   function(){
						$.unblockUI();
						alert("Failed to redraw scatter plots.");
					}
		});
}

function ChangeUserList(mode, allStatFlg)
{
	var cadName = $("#cadMenu option:selected").text();
	var version = $("#versionMenu option:selected").text();

	// Set <option> of version menu
	if(mode == 'cadMenu')
	{
		var versionStr = $("#cadMenu option:selected").val().split("^");
		var optionStr = '<option value="" selected="selected">(Select)</option>';

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
		//version = 'all';
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
    $("#minSize, #maxSize").removeAttr("value");
    $("#cadMenu, #userMenu, #versionMenu").children().removeAttr("selected");
}

$(function() {

	$('#srDateRange').daterange({ icon: '../images/calendar_view_month.png' });

	// Parameters of UI blocking for ajax requests (using jquery blockUI)
	$.blockUI.defaults.message = '<span style="font-weight:bold; font-size:16px;"><img src="images/busy.gif" />'
							   + ' Processing, just a moment...</span>';
	$.blockUI.defaults.fadeOut = 200;			// set fadeOut effect shorter
	$.blockUI.defaults.css.width   = '280px';
	$.blockUI.defaults.css.padding = '5px';

});
{/literal}
-->
</script>
{/capture}
{include file="header.tpl" body_class="spot"
	require=$smarty.capture.require head_extra=$smarty.capture.extra}

<h2><div class="breadcrumb"><a href="index.php">Analysis</a> &gt;</div>
Lesion Locations</h2>
{*<h2>Reading characteristics</h2>*}

<form name="form1">
<input type="hidden" id="dataStr" name="dataStr" value="">

<!-- ***** Serach conditions ***** -->
	<div class="statSearch">
		<h3>Search</h3>
		<div style="padding: 20px;">
			<table class="search-tbl">
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
					<th><span class="trim01">Size(diameter)</span></th>
					<td>
						<input id="minSize" type="text" class="al-r" style="width: 36px">&nbsp;-&nbsp;<input id="maxSize" type="text" class="al-r" style="width: 36px">&nbsp;[mm]
					</td>
					<td></td><td></td>
				</tr>

			</table>
			<div class="al-l" style="margin-top: 10px; margin-left: 20px; width: 100%;">
				<input name="" type="button" value="Apply" class="form-btn" style="width: 100px;" onclick="ShowPersonalStatResult()" />
				<input name="" type="button" value="Reset" class="form-btn" style="width: 100px;" onclick="ResetCondition()" />
				<p id="errorMessage" style="margin-top: 5px; color:#f00; font-wight:bold;">&nbsp;</p>
			</div>
		</div>
	</div><!-- / #statSearch END -->
<!-- / Search conditions -->

<div id="statRes" style="display:none;">
	<h3>Results of personal statistics</h3>
	<table class="col-tbl" style="margin-top: 20px; margin-bottom: 20px; width: 100%;">
		<thead>
		</thead>
		<tbody>
		</tbody>
	</table>

	<div id="scatterPlot" style="width: 950px;" style="display:none;">
		<table id="plotLegend" class="block-al-r" style="margin-bottom: 10px;">
			<tr>
				<td name="known TP">
					<input name="checkKownTP" type="checkbox" /><img src="../images/statistics/knownTP.png" />
				</td>
				<td name="missed TP">
					<input name="checkMissedTP" type="checkbox" /><img src="../images/statistics/missedTP.png" />
				</td>
				<td name="sub TP">
					<input name="checkSubTP" type="checkbox" /><img src="../images/statistics/subTP.png" />
				</td>
				<td name="FP">
					<input name="checkFP" type="checkbox" /><img src="../images/statistics/FP.png" />
				</td>
				<td name="pending">
					<input name="checkPending" type="checkbox" /><img src="../images/statistics/pending.png" />
				</td>
				<td name="redrawBtn">
					<input name="" type="button" class="form-btn" value="Redraw" onclick="RedrawScatterPlot();" style="margin-left:5px; font-weight:bold;" />
				</td>
			</tr>
		</table>

		<table style="margin-left: 10px">
			<tr>
				<td style="width: 330px;"><img id="scatterPlotAx"   src="../images/statistics/ps_scatter_plot_base_xy.png" /></td>
				<td style="width: 330px;"><img id="scatterPlotCoro" src="../images/statistics/ps_scatter_plot_base_xz.png" /></td>
				<td style="width: 330px;"><img id="sactterPlotSagi" src="../images/statistics/ps_scatter_plot_base_yz.png" /></td>
			</tr>
		</table>
	</div>
</div>
</form>

{include file="footer.tpl"}