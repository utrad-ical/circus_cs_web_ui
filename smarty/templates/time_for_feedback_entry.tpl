<?xml version="1.0" encoding="shift_jis"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/base.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=shift_jis" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CIRCUS CS {$smarty.session.circusVersion}</title>
<!-- InstanceEndEditable -->
<link href="../css/import.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../jq/jquery-1.3.2.min.js"></script>
<script language="javascript" type="text/javascript" src="../jq/ui/jquery-ui-1.7.3.min.js"></script>
<script language="javascript" type="text/javascript" src="../jq/jq-btn.js"></script>
<script language="javascript" type="text/javascript" src="../js/hover.js"></script>
<script language="javascript" type="text/javascript" src="../js/viewControl.js"></script>

<script language="Javascript">;
<!--
{literal}
function ShowPersonalStatResult()
{
	var version  = $("#versionMenu").val();
	var evalUser = $("#userMenu").val()

	$.post("show_feedback_time_list.php",
		   { dateFrom: $("#dateFrom").val(),
			 dateTo:   $("#dateTo").val(),
			 cadName:  $("#cadMenu option:selected").text(),
			 version:  $("#versionMenu").val(),
             evalUser: $("#userMenu").val()},

			function(data){

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

			}, "json");
}


function ChangeCadMenu()
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

	$("#versionMenu").html(optionStr);
}


function ResetCondition()
{
	$("#dateFrom, #dateTo").removeAttr("value");
	$("#cadMenu, #userMenu, #versionMenu").children().removeAttr("selected");

	ChangeCadMenu();
}

$(function() {
	$("#dateFrom, #dateTo").datepicker({
			showOn: "button",
			buttonImage: "../images/calendar_view_month.png",
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
});

{/literal}
-->
</script>



<link rel="shortcut icon" href="favicon.ico" />
<!-- InstanceBeginEditable name="head" -->
<link href="../jq/ui/css/jquery-ui-1.7.3.custom.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<!-- InstanceEndEditable -->
<!-- InstanceParam name="class" type="text" value="personal-statistics" -->
</head>

<body class="personal-statistics spot">
<div id="page">
	<div id="container" class="menu-back">
		<div id="leftside">
			{include file='menu.tpl'}
		</div><!-- / #leftside END -->
		<div id="content">
<!-- InstanceBeginEditable name="content" -->

		<h2>Time for feedback entry</h2>

		<form name="form1">
		<input type="hidden" id="dataStr" name="dataStr" value="">

			
		<!-- ***** 検索部分 ***** -->
			<div class="statSearch">
				<h3>Search</h3>
				<div class="p20">
					<table class="search-tbl">
						<tr>
							<th style="width: 7.5em;"><span class="trim01">Series date</span></th>
							<td style="width: 220px;">
								<input id="dateFrom" type="text" style="width:72px;" />
								-
								<input id="dateTo" type="text" style="width:72px;" />

							</td>
							<th><span class="trim01">User</span></th>
							<td>
								<select id="userMenu" name="userMenu" style="width: 100px;>
									<option value="all">all</option>
									{foreach from=$userList item=item}
										<option value="{$item|escape}" {if $item==$smarty.session.userID}selected="selected"{/if}>{$item|escape}</option>
									{/foreach}
								</select>
							</td>
						</tr>
						<tr>
							<th><span class="trim01">CAD</span></th>
							<td>
								<select id="cadMenu" name="cadMenu" style="width: 120px;" onchange="ChangeCadMenu();">
									{foreach from=$cadList item=item}
										<option value="{$item[1]|escape}">{$item[0]|escape}</option>
									{/foreach}
								</select>
							</td>
							<th style="width: 5.5em;"><span class="trim01">Version</span></th>
							<td>
								<select id="versionMenu" name="versionMenu" style="width: 70px;">
									<option value="all">all</option>
									{foreach from=$versionDetail item=item}
										<option value="{$item|escape}">{$item|escape}</option>
									{/foreach}
								</select>
							</td>
						</tr>
					</table>	
					<div class="al-l mt10 ml20" style="width: 100%;">
						<input name="" type="button" value="Apply" class="w100 form-btn" onclick="ShowPersonalStatResult()" />
						<input name="" type="button" value="Reset" class="w100 form-btn" onclick="ResetCondition()" />
						<p id="errorMessage" class="mt5" style="color:#f00; font-wight:bold;">&nbsp;</p>
					</div>
				</div><!-- / .m20 END -->
			</div><!-- / #statSearch END -->
		<!-- / 検索部分　ここまで -->
		
		<div id="statRes" style="display:none;">
			<h3>Time for feedback entry</h3>
			<table class="col-tbl mt20 mb20" style="width: 100%;">
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
<!-- InstanceEndEditable -->
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
<!-- InstanceEnd --></html>
