<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/base.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=shift_jis" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CIRCUS CS {$smarty.session.circusVersion}</title>
<!-- InstanceEndEditable -->
<link href="css/import.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="jq/jquery-1.3.2.min.js"></script>
<script language="javascript" type="text/javascript" src="jq/ui/jquery-ui-1.7.3.min.js"></script>
<script language="javascript" type="text/javascript" src="jq/jquery.blockUI.js"></script>
<script language="javascript" type="text/javascript" src="jq/jq-btn.js"></script>
<script language="javascript" type="text/javascript" src="js/hover.js"></script>
<script language="javascript" type="text/javascript" src="js/viewControl.js"></script>

<script language="Javascript">;
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
								$("#statRes .col-tbl tbody").html(data.tblHtml);
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
       			      fpFlg:       ((document.form1.checkFP.checked == true) ? 1 : 0),
					  pendingFlg:  ((document.form1.checkPending.checked == true) ? 1 : 0)},
			dataType: "json",

			success: function(data){
						$.unblockUI();
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



<link rel="shortcut icon" href="favicon.ico" />
<!-- InstanceBeginEditable name="head" -->
<link href="./jq/ui/css/jquery-ui-1.7.3.custom.css" rel="stylesheet" type="text/css" media="all" />
<link href="./css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<!-- InstanceEndEditable -->
<!-- InstanceParam name="class" type="text" value="personal-statistics" -->
</head>

<body class="personal-statistics">
<div id="page">
	<div id="container" class="menu-back">
		<div id="leftside">
			{include file='menu.tpl'}
		</div><!-- / #leftside END -->
		<div id="content">
<!-- InstanceBeginEditable name="content" -->

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
							<tr>
								<th rowspan="2">User</th>
								<th rowspan="2">Case</th>
								<th rowspan="2">known TP</th>
								<th rowspan="2">missed TP</th>
								<th rowspan="2">FP</th>
								<th rowspan="2">pending</th>
								<th rowspan="2">FN</th>
								<th rowspan="2">Total</th>
								<th colspan="3">Detail of missed TP</th>
							</tr>
							<tr>
								<th>TP</th>
								<th>FP</th>
								<th>Pending</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				
					<div id="scatterPlot" style="width: 950px;" style="display:none;">
						<table class="block-al-r mb10">
							<tr>
								<td>
									<input name="checkKownTP" type="checkbox" checked="checked" /><img src="images/statistics/knownTP.png" />
								</td>
								<td>
									<input name="checkMissedTP" type="checkbox" checked="checked" /><img src="images/statistics/missedTP.png" />
								</td>
								<td>
									<input name="checkFP" type="checkbox" checked="checked" /><img src="images/statistics/FP.png" />
								</td>
								<td>
									<input name="checkPending" type="checkbox" checked="checked" /><img src="images/statistics/pending.png" />
								</td>
								<td><input name="" type="button" class="form-btn" value="Redraw" onclick="RedrawScatterPlot();" style="margin-left:5px; font-weight:bold;" /></td>
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

<!-- InstanceEndEditable -->
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
<!-- InstanceEnd --></html>
