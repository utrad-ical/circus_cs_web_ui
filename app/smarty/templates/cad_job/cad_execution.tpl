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
<script language="Javascript">
<!--
{literal}

function ChangeCheckbox(sid, val)
{
	var numSelectedSeries = jQuery("#selectedSeriesStr").val().split("^");
	var selectedValue = jQuery('#series' + sid + 'Selected').val();

	// Display series rows depending on checked state
	for(var j=2; j<numSelectedSeries.length; j++)
	{
		document.getElementById('series' + j + 'Selected').value = "";

		for(var i=1; i<=numSelectedSeries[j]; i++)
		{
			var tmpObj = document.getElementById('checkbox' + j + '_' + i);

			if(i%2==0)	document.getElementById('rowSeries' + j + '_' + i).className = "column rowDisp";
			else		document.getElementById('rowSeries' + j + '_' + i).className = "rowDisp";

			if((j == sid && tmpObj.value != val) || (j != sid && tmpObj.value == val))
			{
				tmpObj.checked = false;
			}

			if(tmpObj.checked == true)  document.getElementById('series' + j + 'Selected').value = tmpObj.value;
		}
	}

	// Hide series rows depending on checked state
	for(var k=2; k<numSelectedSeries.length; k++)
	{
		selectedValue = document.getElementById('series' + k + 'Selected').value;

		if(selectedValue != "")
		{
			for(var j=2; j<numSelectedSeries.length; j++)
			{
				for(var i=1; i<=numSelectedSeries[j]; i++)
				{
					var tmpObj = document.getElementById('checkbox' + j + '_' + i);

					if(j == k)
					{
						if(selectedValue != "" && tmpObj.value != selectedValue)
						{
							document.getElementById('rowSeries' + j + '_' + i).className = "rowHidden";
						}
					}
					else
					{
						if(tmpObj.value == selectedValue && tmpObj.checked == false)
						{
							document.getElementById('rowSeries' + j + '_' + i).className = "rowHidden";
						}
					}
				}
			}
		}
	}
}

function ResetSeries()
{
	var numSelectedSeries = document.getElementById('selectedSeriesStr').value.split("^");

	for(var k=2; k<numSelectedSeries.length; k++)
	{
		document.getElementById('series' + k + 'Selected').value="";

		for(var j=1; j<=numSelectedSeries[k]; j++)
		{
			if(j%2==0)	document.getElementById('rowSeries' + k + '_' + j).className = "column rowDisp";
			else		document.getElementById('rowSeries' + k + '_' + j).className = "rowDisp";
			document.getElementById('checkbox' + k + '_' + j).checked = false;
		}
	}
}

function CheckSeries()
{
	var studyUID     = document.form1.studyUIDStr.value;
	var seriesUID    = document.form1.seriesUIDStr.value;
	var numSelectedSeries = document.getElementById('selectedSeriesStr').value.split("^");

	var errorFlg = 0;

	if(confirm('Do you select required series?'))
	{
		var selectFlg = 0;

		for(var k=2; k<numSelectedSeries.length; k++)
		{
			selectFlg = 0;

			for(var j=1; j<=numSelectedSeries[k]; j++)
			{
				var tmpObj = document.getElementById('checkbox' + k + '_' + j);

				if (tmpObj.checked)
				{
					selectFlg = 1;
					var tmpArr = tmpObj.value.split("^");
					studyUID  += '^' + tmpArr[0];
					seriesUID += '^' + tmpArr[1];
				}
			}

			if(selectFlg == 0)
			{
				alert ('Series ' + k + ' is not selected !!');
				break;
			}
		}

		if(selectFlg == 1)
		{
			$("#studyUIDStr").val(studyUID);
			$("#seriesUIDStr").val(seriesUID);

			$.post(
				"create_series_list.php",
				{
					studyUIDStr: $("#studyUIDStr").val(),
					seriesUIDStr: $("#seriesUIDStr").val()
				},
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
					$("#confirm .col-tbl tbody").append(tableHtml);
					$("#success .col-tbl tbody").append(tableHtml);
					$("#seriesSelect").attr("style", "display:none;");
					$("#confirm").removeAttr("style", "display:none;");
				},
				"json"
			);
		}
	}
}

function RegistrationCADJob()
{
	$.post("../api/api.php",
		{
			"request":
			'{'
			+ '"auth":{'
			+ '  "type":"session",'
			+ '  "user":"'+$("#userID").val()+'"'
			+ '},'
			+ '"action":"executePlugin",'
			+ '"params":{'
			+ '  "pluginName":"'+$("#cadName").val()+'",'
			+ '  "pluginVersion":"'+$("#version").val()+'",'
			+ '  "seriesUID":["'+$("#seriesUIDStr").val()+'"]'
			+ '}'
			+ '}'
		},
		function(data){
			var htmlStr = '<tr><th style="width: 110px;"><span class="trim01">';
			if(data.result[0].executedAt != "")
			{
				htmlStr += 'Executed at</span></th><td>' + data.result[0].executedAt + '</td>';
			}
			else
			{
				htmlStr += 'Registered at</span></th><td>' + data.result[0].registeredAt + '</td>';
			}
			$("#registMessage").html(data.message);
			$("#success .detail-tbl").prepend(htmlStr);
			$("#confirm").attr("style", "display:none;");
			$("#success").removeAttr("style", "display:none;");
		},
		"json"
	);
}
{/literal}


-->
</script>

<link rel="shortcut icon" href="favicon.ico" />
<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/popup.css" rel="stylesheet" type="text/css" media="all" />

{literal}
<style type="text/css" media="all,">
<!--

#seriesSelect .rowDisp {
	color:#000;
}

#seriesSelect .rowHidden {
	color:#ccc;
}

-->
</style>
{/literal}
</head>

<body class="cad_execution">
<div id="page">
	<div id="container" class="menu-back">
		<!-- ***** #leftside ***** -->
		<div id="leftside">
			{include file='menu.tpl'}
		</div>
		<!-- / #leftside END -->

		<div id="content">
			<!-- ***** TAB ***** -->
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
				<input type="hidden" id="userID"               name="userID"               value="{$smarty.session.userID}" />
				<input type="hidden" id="cadName"              name="cadName"              value="{$params.cadName}" />
				<input type="hidden" id="version"              name="version"              value="{$params.version}" />
				<input type="hidden" id="studyUIDStr"          name="studyUIDStr"          value="{$studyUIDStr}" />
				<input type="hidden" id="seriesUIDStr"         name="seriesUIDStr"         value="{$seriesUIDStr}" />
				<input type="hidden" id="modalityStr"          name="modalityStr"          value="{$modalityStr}" />
				<input type="hidden" id="seriesDescriptionStr" name="seriesDescriptionStr" value="{$seriesDescriptionStr}" />
				<input type="hidden" id="srcList"              name="srcList"              value="{$params.srcList}">

				<div id="seriesSelect" {if $params.mode!='select'}style="display:none;"{/if}>


					<h2>Series selection</h2>
					<p class="mb10">Select DICOM series,and press the <span class="clr-blue fw-bold">[OK]</span>button after selection.</p>

					<p class="mb10">
						<input name="" type="button" value="OK"     class="w100 form-btn" onclick="CheckSeries();" />
						<input name="" type="button" value="Cancel" class="w100 form-btn" onclick="history.back(1);" />
					</p>

					<div class="detail-panel mb20">
						<table class="detail-tbl">
							<tr>
								<th style="width: 9em;"><span class="trim01">CAD name</span></th>
								<td>{$params.cadName} v.{$params.version}</td>
							</tr>
							<tr>
								<th><span class="trim01">Patient ID</span></th>
								<td>{$params.patientID}</td>
							</tr>
							<tr>
								<th><span class="trim01">Pateint name</span></th>
								<td>{$params.patientName}</td>
							</tr>
						</table>
					</div><!-- / .detail-panel END -->

					{assign var="cnt" value=0}

					{section name=k start=0 loop=$seriesNum}

						{assign var="k" value=$smarty.section.k.index}

							<h3 class="ptn02">Series {$k+1}:
								<span style="font-weight: normal">({$modalityArr[$k]},

								{section name=j start=0 loop=$descriptionNumArr[$k]}

									{assign var="j"  value=$smarty.section.j.index}
									{assign var="tmp" value=$cnt+$j}

									{if $seriesDescriptionArr[$tmp] == '(default)'}
										#image: {$minSliceArr[$tmp]}-{$maxSliceArr[$tmp]}{if $j==$descriptionNumArr[$k]-1}){else},{/if}
									{else}
										series description: {$seriesDescriptionArr[$tmp]}{if $j==$descriptionNumArr[$k]-1}){else},{/if}
									{/if}
								{/section}</span>
							</h3>

						<table id="selectTbl{$k+1}" class="col-tbl mb30" style="width: 100%;">
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
										<td>{$seriesList[0][0][1]}</td>
										<td>{$seriesList[0][0][2]}</td>
										<td>{$seriesList[0][0][3]}</td>
										<td>{$seriesList[0][0][4]}</td>
										<td>{$seriesList[0][0][5]}</td>
										<td class="al-l">{$seriesList[0][0][6]}</td>
									</tr>
								{else}
									{assign var="ktmp" value=$k+1}

									{section name=j start=0 loop=$selectedSeriesArr[$ktmp]}

										{assign var="j" value=$smarty.section.j.index}

											<tr id="rowSeries{$k+1}_{$j+1}" class="{if $j%2==1}column {/if}rowDisp">
												<td align=center>
													<input type="checkbox" id="checkbox{$k+1}_{$j+1}" name="checkbox{$k+1}_{$j+1}" value="{$seriesList[$k][$j][0]}" onclick="ChangeCheckbox({$k+1},'{$seriesList[$k][$j][0]}');" {if {$seriesList[$k][$j][6]} == $defaultSeriesDescriptionArr[$k]}checked{/if}>
												</td>
												<td>{$seriesList[$k][$j][1]}</td>
												<td>{$seriesList[$k][$j][2]}</td>
												<td>{$seriesList[$k][$j][3]}</td>
												<td>{$seriesList[$k][$j][4]}</td>
												<td>{$seriesList[$k][$j][5]}</td>
												<td  class="al-l">{$seriesList[$k][$j][6]}</td>
											</tr>
										{/section}
									<input type="hidden" id="series{$k+1}Selected" value="">
								{/if}
							</tbody>
						</table>
					{/section}

					<input name="" type="button" value="reset" class="s-btn mb30 form-btn" onclick="ResetSeries();" />
					<input type="hidden" id="selectedSeriesStr" name="selectedSeriesStr" value="{$selectedSeriesStr}" />
					</form>
				</div>
				<!-- / Detail END -->

				<!-- Confirmation -->
				<div id="confirm" {if $params.mode!='confirm'}style="display:none;"{/if}>
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
								<td>{$params.cadName} v.{$params.version}</td>
							</tr>
							<tr>
								<th><span class="trim01">Patient ID</span></th>
								<td>{$params.patientID}</td>
							</tr>
							<tr>
								<th><span class="trim01">Pateint name</span></th>
								<td>{$params.patientName}</td>
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
							{if $params.mode=='confirm'}
								{foreach from=$seriesList item=item name=confirmList}
									<tr>
										<td>{$smarty.foreach.confirmList.iteration}</td>
											<td>{$item[0]}</td>
										<td>{$item[1]}</td>
										<td>{$item[2]}</td>
										<td>{$item[3]}</td>
										<td>{$item[4]}</td>
										<td>{$item[5]}</td>
										<td class="al-l">{$item[6]}</td>
									</tr>
								{/foreach}
							{/if}
						</tbody>
					</table>
				</div>
				<!-- / confirmation END -->

				<!-- 17.Successfully-registered-in-cad-job-list.html -->
				<div id="success" style="display:none;">
					<p id="registMessage" class="clr-orange mb10">Successfully registered in CAD job list!</p>
					<p class="mb10"><input name="" type="button" value="Close" class="w100 form-btn" onclick="location.replace('../{$smarty.session.listAddress}');" /></p>

					<div class="detail-panel mb20">
						<table class="detail-tbl">
					<!--		<tr>
								<th style="width: 110px;"><span class="trim01">Registered at</span></th>
								<td><span id="registeredAt"></span></td>
							</tr> -->
							<tr>
								<th style="width: 10em;"><span class="trim01">Ordered by</span></th>
								<td>{$smarty.session.userID}</td>
							</tr>
							<tr>
								<th style="width: 9em;"><span class="trim01">CAD name</span></th>
								<td>{$params.cadName} v.{$params.version}</td>
							</tr>
							<tr>
								<th><span class="trim01">Patient ID</span></th>
								<td>{$params.patientID}</td>
							</tr>
							<tr>
								<th><span class="trim01">Pateint name</span></th>
								<td>{$params.patientName}</td>
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
							{if $params.mode=='confirm'}
								{foreach from=$seriesList item=item name=confirmList}
									<tr>
										<td>{$smarty.foreach.confirmList.iteration}</td>
											<td>{$item[0]}</td>
										<td>{$item[1]}</td>
										<td>{$item[2]}</td>
										<td>{$item[3]}</td>
										<td>{$item[4]}</td>
										<td>{$item[5]}</td>
										<td class="al-l">{$item[6]}</td>
									</tr>
								{/foreach}
							{/if}
						</tbody>
					</table>
				</div>
				<!-- / Seccessfully END -->

				<!-- Error display -->
				<div id="error" {if $params.mode!='error'}style="display:none;"{/if}>
					<h2>Error</h2>

					{if $params.errorMessage != ""}
						<p class="mb10">{$params.errorMessage}</p>
					{else}
						<p class="mb10">{$params.cadName} v.{$params.version} requires following series in the same {if $params.inputType == 1}series{else}patient{/if}!!&nbsp;&nbsp;<input name="" type="button" value="Close" class="w100 form-btn" onclick="location.replace('../{$smarty.session.listAddress}');" /></p>
		</p>
						<table class="col-tbl mb30">
							<thead>
								<tr>
									<th>Series</th>
									<th>Modality</th>
									<th>Condition</th>
								</tr>
							</thead>
							<tbody>
								{assign var="cnt" value=0}

								{section name=j start=0 loop=$seriesNum}

									{assign var="j" value=$smarty.section.j.index}

									{section name=i start=0 loop=$descriptionNumArr[$j]}

										<tr>
											{assign var="i" value=$smarty.section.i.index}
											{assign var="tmp" value=$cnt+$i}

											{if $i==0}
												<td {if $descriptionNumArr[$j]>1}rowspan={$descriptionNumArr[$j]}{/if} align=center>{$j+1}</td>
												<td {if $descriptionNumArr[$j]>1}rowspan={$descriptionNumArr[$j]}{/if} align=center>{$modalityArr[$j]}</td>
											{/if}

											{if $seriesDescriptionArr[$tmp] == '(default)'}
												<td>#image: {$minSliceArr[$tmp]}-{$maxSliceArr[$tmp]}</td>
											{else}
												<td>series description: {$seriesDescriptionArr[$tmp]}</td>
											{/if}
										</tr>
									{/section}

									{assign var="cnt" value=$cnt+$descriptionNumArr[$j]}
								{/section}
							</tbody>
						</table>
					{/if}
				</div>
				<!-- / Error END -->

				<div class="al-r fl-clr">
					<p class="pagetop"><a href="#page">page top</a></p>
				</div>

			</div><!-- / .tab-content END -->

		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
</html>
