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

<link href="../css/import.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../jq/jquery-1.3.2.min.js"></script>
<script language="javascript" type="text/javascript" src="../jq/ui/ui.core.js"></script>
<script language="javascript" type="text/javascript" src="../jq/ui/ui.slider.js"></script>
<script language="javascript" type="text/javascript" src="../jq/jq-btn.js"></script>
<script language="javascript" type="text/javascript" src="../js/hover.js"></script>
<script language="javascript" type="text/javascript" src="../js/viewControl.js"></script>
<script language="javascript" type="text/javascript" src="../js/fn_input.js"></script>
<script language="Javascript">
<!--

$(function() {ldelim}
	$("#slider").slider({ldelim}
		value:1,
		min:{$sliceOffset+1},
		max:{$params.fNum},
		step: 1,
		slide: function(event, ui) {ldelim}
			$("#sliderValue").html(ui.value);
		{rdelim},
		change: function(event, ui) {ldelim}
			$("#sliderValue").html(ui.value);
			JumpImgNumber(ui.value);
		{rdelim}
	{rdelim});
	$("#slider").css("width", "220px");
	$("#sliderValue").html(jQuery("#slider").slider("value"));

{if $moveCadResultFlg == 1}
{literal}
	$.event.add(window, "load", 
				function(){
					 alert("[CAUTION] Lesion classification is not completed!");

					var address = 'show_cad_results.php'
								+ '?execID=' + $("#execID").val()
                				+ '&feedbackMode=' + $("#feedbackMode").val();

					location.href = address;

				});
{/literal}
{/if}

{rdelim});

{literal}
function Plus()
{
	var value = $("#slider").slider("value");

	if(value < $("#slider").slider("option", "max"))
	{
		value++;
		$("#sliderValue").html(value);
		$("#slider").slider("value", value);
	}
}

function Minus()
{
	var value = $("#slider").slider("value");

	if($("#slider").slider("option", "min") <= value)
	{
		value--;
		$("#sliderValue").html(value);
		$("#slider").slider("value", value);
	}
}
{/literal}

-->
</script>

<link rel="shortcut icon" href="favicon.ico" />

<!-- InstanceBeginEditable name="head" -->
<link href="../jq/ui/css/ui.all.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/popup.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/darkroom.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../js/radio-to-button.js"></script>


{literal}
<style type="text/css">
.dot{
  position: absolute;
  overflow: hidden;
}
</style>
{/literal}

<!-- InstanceEndEditable -->
</head>

<!-- InstanceParam name="class" type="text" value="home" -->
<body class="lesion_cad_display">
<div id="page">
	<div id="container" class="menu-back">
		<div id="leftside">
			{include file='menu.tpl'}
		</div><!-- / #leftside END -->
		<div id="content">
<!-- InstanceBeginEditable name="content" -->

		<!-- ***** TAB ***** -->
		<div class="tabArea">
			<ul>
				<li><a href="href="../series_list.php?mode=study&studyInstanceUID={$params.studyInstanceUID}" class="btn-tab" title="Series list">Series list</a></li>
				<li><a href="show_cad_results.php?cadName={$params.cadName}&version={$params.version}&studyInstanceUID={$params.studyInstanceUID}&seriesInstanceUID={$params.seriesInstanceUID}&feedbackMode={$params.feedbackMode}" class="btn-tab" title="CAD result">CAD result</a></li>
				<li><a href="#" class="btn-tab" title="FN input" style="background-image: url(../img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">FN input</a></li>
			</ul>
			<p class="add-favorite"><a href="" title="favorite"><img src="../img_common/btn/favorite.jpg" width="100" height="22" alt="favorite"></a></p>
		</div><!-- / .tabArea END -->

		<!-- FN input 13.fn-input.html -->
		<div class="tab-content">
			<div id="fnInput">
				<form id="form1" name="form1">
				<input type="hidden" id="execID"            value="{$params.execID|escape}" />
				<input type="hidden" id="studyInstanceUID"  value="{$params.studyInstanceUID|escape}" />
				<input type="hidden" id="seriesInstanceUID" value="{$params.seriesInstanceUID|escape}" />
				<input type="hidden" id="cadName"           value="{$params.cadName|escape}" />
				<input type="hidden" id="version"           value="{$params.version|escape}" />
				<input type="hidden" id="posStr"            value="{$params.posStr|escape}" />
				<input type="hidden" id="userStr"           value="{$params.userStr|escape}" />
				<input type="hidden" id="candPosStr"        value="{$params.candPosStr|escape}" />
				<input type="hidden" id="feedbackMode"      value="{$params.feedbackMode|escape}" />
				<input type="hidden" id="userID"            value="{$userID}">	

				<input type="hidden" id="grayscaleStr"      value="{$params.grayscaleStr|escape}" />
				<input type="hidden" id="presetName"        value="{$params.presetName|escape}" />
				<input type="hidden" id="windowLevel"       value="{$params.windowLevel|escape}" />
				<input type="hidden" id="windowWidth"       value="{$params.windowWidth|escape}" />

				<input type="hidden" id="sliceOrigin"    value="{$params.sliceOrigin|escape}" />
				<input type="hidden" id="slicePitch"     value="{$params.slicePitch|escape}" />
				<input type="hidden" id="sliceOffset"    value="{$params.sliceOffset|escape}" />
					
				<input type="hidden" id="distTh"         value="{$params.distTh|escape}" />
				<input type="hidden" id="orgWidth"       value="{$params.orgWidth|escape}" />
				<input type="hidden" id="orgHeight"      value="{$params.orgHeight|escape}" />
				<input type="hidden" id="dispWidth"      value="{$params.dispWidth|escape}" />
				<input type="hidden" id="dispHeight"     value="{$params.dispHeight|escape}" />
					
				<input type="hidden" id="registTime"   value="{$params.registTime|escape}" />
				<input type="hidden" id="ticket"       value="{$ticket|escape}" />

				<h2>FN input&nbsp;[{$params.cadName|escape} v.{$params.version|escape} ID:{$params.execID|escape}]&nbsp;&nbsp;({$params.feedbackMode|escape} mode)</h2>

				<div class="headerArea">
					<div class="fl-l"><a href="../study_list.php?mode=patient&encryptedPtID={$params.encryptedPtID|escape}">{$params.patientName|escape}&nbsp;({$params.patientID|escape})&nbsp;{$params.age|escape}{$params.sex|escape}</a></div>
					<div class="fl-l"><img src="../img_common/share/path.gif" /><a href="../series_list.php?mode=study&studyInstanceUID={$params.studyInstanceUID|escape}">{$params.studyDate|escape}&nbsp;({$params.studyID|escape})</a></div>
					<div class="fl-l"><img src="../img_common/share/path.gif" />{$params.modality|escape},&nbsp;{$params.seriesDescription|escape}&nbsp;({$params.seriesID|escape})</div>
				</div>

				<p class="mb10">
				{if $params.registTime != ""}Location of false negatives were registered at {$params.registTime}{if $params.feedbackMode =="consensual" && $params.enteredBy != ""} (by {$params.enteredBy}){/if}.{else}Click location of FN, and press the <span class="clr-blue fw-bold">[Confirm]</span> button to save FN locations.{/if}</p>
				<p style="margin-top:-10px; margin-left:10px; font-size:14px;"><input type="checkbox" id="checkVisibleFN" name="id="checkVisibleFN" "onclick="ChangeVisibleFN();" checked="checked" />&nbsp;Show FN</p>

				<div class="series-detail-img">
					{* ----- Display image with slider ----- *}
					<table cellspacing=0>

						<tr>
							<td colspan="3" valign="top">
								<div id="imgBlock" style="margin:0px;padding:0px;width:{$dispWidth}px;height:{$dispHeight}px;position:relative;">
									<img id="imgArea" class="{if $params.registTime == "" && $smarty.session.groupID != 'demo'}enter{else}ng{/if}" src="../{$params.dstFnameWeb|escape}" width="{$params.dispWidth|escape}" "height={$params.dispHeight|escape}" />
								</div>
							</td>
						</tr>

						<tr>
							<td align="right" style="{if $params.dispWidth >=256}width:{$widthOfPlusButton|escape}{/if}px;">
								<input type="button" value="-" onClick="Minus();" {if $params.imgNum == ($params.sliceOffset+1)} disabled="disabled"{/if} />
							</td>
				
							<td align=center style="width:256px;"><div id="slider"></div></td>

							<td align=left  style="{if $params.dispWidth >=256}width:{$params.widthOfPlusButton|escape}{/if}px;">
						 		<input type="button" value="+" onClick="Plus();" {if $params.imgNum == $params.fNum} disabled="disabled"{/if} />
							</td>
						</tr>

						<tr>
							<td align=center colspan=3>
								<b>Image number: <span id="sliderValue">{$params.imgNum|escape}</span></b>
							</td>
						</tr>

						{*<tr>
							<td align=center colspan=3>
								<b>Slice location [mm]: </b>
								<input type="text" id="sliceLocation" value="{$params.sliceLocation|escape}" style="width:64px; text-align:right" onKeyPress="return submitStop(event);" />
								<input type="button" id="applySL" class="form-btn" value="Jump" onclick="JumpImgNumBySliceLocation({$params.sliceOrigin}, {$params.slicePitch}, {$params.sliceOffset}, {$params.fNum});" />
					</td></tr>*}

						{if $params.grayscaleStr != ""}
							<tr>
								<td align=center colspan=3>
									<b>Grayscale preset: </b>
									<select id="presetMenu" name="presetMenu" onchange="ChangePresetMenu({$params.imgNum});">

										{section name=i start=0 loop=$params.presetNum}
											{assign var="i" value=$smarty.section.i.index}	
											<option value="{$presetArr[$i][1]}^{$presetArr[$i][2]}" {if $params.presetName == $presetArr[$i][0]} selected{/if}>{$presetArr[$i][0]}</option>
										{/section}
									</select>
								</td>
							</tr>
						{/if}
					</table>
				</div>

				{* --- Add FN plots --- *}

				<script language="Javascript">
				<!--
				{section name=j start=0 loop=$params.enteredFnNum}
					{assign var="j" value=$smarty.section.j.index}
					{if $locationList[$j][3] == 1}
						plotClickedLocation({$j+1}, {$locationList[$j][1]}, {$locationList[$j][2]}, {$locationList[$j][7]});
					{/if}
					{/section}
				-->
				</script>

				<div class="fl-l" style="width: 40%;">
		
					{if $params.registTime == ""}
					<div style="margin-bottom:10px;">
						<select id="actionMenu" onchange="ChangeAction();" disabled="disabled">
							<option value="">(action)</option>
						</select>
						<input type="button" id="actionBtn" class="form-btn form-btn-disabled" value="action" onclick="TableOperation();" disabled="disabled">
					</div>
					{/if}

					<table id="posTable" class="col-tbl mb10" style="width:100%; background:#ffffff;">
						<thead>
							<tr>
								{if $params.registTime == ""}<th>&nbsp;</th>{/if}
								<th>ID</th>
								<th>Pos X</th>
								<th>Pos Y</th>
								<th>Pos Z</th>
								<th>Nearest candidate<br /><span style="font-weight: normal">rank&nbsp;/&nbsp;dist.[voxel]</span></th>
								{if $params.feedbackMode == "consensual"}
									<th>Entered by</th>
									<th style="display:none;">&nbsp;</th>
								{/if}
							</tr>
						</thead>
						<tbody>
							{section name=j start=0 loop=$params.enteredFnNum}
							
								{assign var="j" value=$smarty.section.j.index}	

								<tr id="row{$j+1}" {if $j%2==1}class="column"{/if}>

								{if $params.registTime == ""}
									<td align=center>
									<input type="checkbox" name="rowCheckList[]" onclick="ClickRowCheckList();" value="{$j+1}">
									</td>
								{/if}
						
								<td class="al-r" onclick="ClickPosTable('row{$j+1}', {$locationList[$j][3]});" style="color:{$locationList[$j][0]};">{$j+1}</td>

								{section name=i start=1 loop=4}
									{assign var="i" value=$smarty.section.i.index}

									<td class="al-r" onclick="ClickPosTable('row{$j+1}', {$locationList[$j][3]});" style="color:{$locationList[$j][0]};">{$locationList[$j][$i]}</td>
								{/section}
							
								<td onclick="ClickPosTable('row{$j+1}', {$locationList[$j][3]});" style="color:{$locationList[$j][0]};">{$locationList[$j][4]}</td>

								{if $params.feedbackMode == "consensual"}
									<td onclick="ClickPosTable('row{$j+1}', {$locationList[$j][3]});" style="color:{$locationList[$j][0]};">{$locationList[$j][5]}</td>
									<td style="display:none;">{$locationList[$j][6]}</td>
								{/if}
							
								</tr>
							{/section}
						</tbody>
					</table>

					{if $params.registTime == ""}
					<div id="blockDeleteButton" style="text-align:right; margin-top:5px; font-size:14px;">
						<input type="button" id="undoBtn" class="form-btn form-btn-normal" value="undo" onclick="UndoFnTable();"{if $params.posStr == "" || $smarty.session.groupID == 'demo'} style="display:none;"{/if} />
						<input type="button" id="resetBtn" class="form-btn form-btn-normal" value="Reset" onclick="ResetFnTable();"{if $params.posStr != "" && $smarty.session.groupID != 'demo'} style="display:none;"{/if} />
						<input type="button" id="confirmBtn" class="form-btn form-btn-normal" value="Confirm" onclick="ConfirmFNLocation();"{if $registTime != ""} disabled="disabled"{/if} />
					</div>
					{/if}

				</div><!-- / .fl-l END -->
			</div>
			<!-- / Detail -->

			<div class="al-r fl-clr">
				<p class="pagetop"><a href="#page">page top</a></p>
			</div>
		
		</div><!-- / .tab-content END -->

		<!-- darkroom button -->
		{include file='darkroom_button.tpl'}

<!-- InstanceEndEditable -->
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
<!-- InstanceEnd --></html>
