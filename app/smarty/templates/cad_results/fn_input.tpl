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
<script language="javascript" type="text/javascript" src="../jq/ui/jquery-ui-1.7.3.min.js"></script>
<script language="javascript" type="text/javascript" src="../jq/jq-btn.js"></script>
<script language="javascript" type="text/javascript" src="../js/hover.js"></script>
<script language="javascript" type="text/javascript" src="../js/viewControl.js"></script>
<script language="javascript" type="text/javascript" src="../js/edit_tag.js"></script>
<script language="javascript" type="text/javascript" src="../js/json2.min.js"></script>
<script language="Javascript">
<!--

var fnData    = {$fnData|@json_encode};
var candPos   = {$candPos|@json_encode};
var colorList = {$colorList|@json_encode};

{if $params.status==1}
var oldFnData = {$fnData|@json_encode};
{/if}

{literal}
$(function() {

	for(var i=0; i<fnData.length; i++)
	{
		AddFnTable(i, fnData[i]);
	}
{/literal}

	$("#slider").slider({ldelim}
		value:{$params.sliceOffset+1},
		min:{$params.sliceOffset+1},
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
<script language="javascript" type="text/javascript" src="../js/fn_input.js"></script>

<link rel="shortcut icon" href="favicon.ico" />
<link href="../jq/ui/css/jquery-ui-1.7.3.custom.css" rel="stylesheet" type="text/css" media="all" />
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
</head>

<body class="lesion_cad_display">
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
					<li><a href="../series_list.php?mode=study&studyInstanceUID={$params.studyInstanceUID}" class="btn-tab" title="Series list">Series list</a></li>
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
					<input type="hidden" id="posStr"            value="{$params.posStr|escape}" />
					<input type="hidden" id="userStr"           value="{$params.userStr|escape}" />
					<input type="hidden" id="feedbackMode"      value="{$params.feedbackMode|escape}" />
					<input type="hidden" id="userID"            value="{$params.userID}">	

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
				{*	<input type="hidden" id="darkroomFlg"  value="{$smarty.session.darkroomFlg}" /> *}
					<input type="hidden" id="status"       value="{$params.status|escape}" />
					<input type="hidden" id="ticket"       value="{$ticket|escape}" />

					<h2>FN input&nbsp;[{$params.cadName|escape} v.{$params.version|escape} ID:{$params.execID|escape}]&nbsp;&nbsp;({$params.feedbackMode|escape} mode)</h2>

					<div class="headerArea">
						<div class="fl-l"><a href="../study_list.php?mode=patient&encryptedPtID={$params.encryptedPtID|escape}">{$params.patientName|escape}&nbsp;({$params.patientID|escape})&nbsp;{$params.age|escape}{$params.sex|escape}</a></div>
						<div class="fl-l"><img src="../img_common/share/path.gif" /><a href="../series_list.php?mode=study&studyInstanceUID={$params.studyInstanceUID|escape}">{$params.studyDate|escape}&nbsp;({$params.studyID|escape})</a></div>
						<div class="fl-l"><img src="../img_common/share/path.gif" />{$params.modality|escape},&nbsp;{$params.seriesDescription|escape}&nbsp;({$params.seriesID|escape})</div>
					</div>

					<p class="mb10">
					{if $params.registTime != ""}Location of false negatives were {if $params.status==1}saved{else}registered{/if} at {$params.registTime}{if $params.feedbackMode =="consensual" && $params.enteredBy != ""} (by {$params.enteredBy}){/if}.{else}Click location of FN, and press the <span class="clr-blue fw-bold">[Confirm]</span> button to save FN locations.{/if}</p>
					<p style="margin-top:-10px; margin-left:10px; font-size:14px;"><input type="checkbox" id="checkVisibleFN" name="checkVisibleFN" "onclick="ChangeVisibleFN();" checked="checked" />&nbsp;Show FN</p>

					<div class="series-detail-img">
						{* ----- Display image with slider ----- *}
						<table cellspacing=0>

							<tr>
								<td colspan="3" valign="top">
									<div id="imgBlock" style="margin:0px;padding:0px;width:{$dispWidth}px;height:{$dispHeight}px;position:relative;">
										<img id="imgArea" class="{if ($params.registTime == "" || $params.status != 2) && $smarty.session.groupID != 'demo'}enter{else}ng{/if}" src="../{$params.dstFnameWeb|escape}" width="{$params.dispWidth|escape}" "height={$params.dispHeight|escape}" />
									</div>
								</td>
							</tr>

							<tr>
								<td align="right" style="{if $params.dispWidth >=256}width:{$widthOfPlusButton|escape}{/if}px;">
									<input type="button" value="-" onclick="Minus();"  />
								</td>
					
								<td align=center style="width:256px;"><div id="slider"></div></td>

								<td align=left  style="{if $params.dispWidth >=256}width:{$params.widthOfPlusButton|escape}{/if}px;">
							 		<input type="button" value="+" onclick="Plus();" />
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
									<span style="font-weight:bold;">Grayscale preset: </span>
									<select id="presetMenu" name="presetMenu" onchange="ChangePresetMenu();">
									{foreach from=$params.presetArr item=item}
										<option value="{$item[1]|escape}^{$item[2]|escape}"{if $detailParams.presetName == $item[0]} selected="selected"{/if}>{$item[0]|escape}</option>
									{/foreach}
									</select>
								</td>
							</tr>
							{/if}
						</table>
					</div>

					{* --- Add FN plots --- *}

					<div class="fl-l" style="width: 40%;">
			
						{if $params.registTime == "" || $params.status != 2}
						<div style="margin-bottom:5px;">
							Action for checked item(s)
							<select id="actionMenu" onchange="RefreshOperationButtons();" disabled="disabled">
								<option value="delete">delete</option>
								{if $params.feedbackMode=="consensual"}<option value="integrate">integrate</option>{/if}
							</select>
							<input type="button" id="actionBtn" class="form-btn form-btn-disabled" value="do" onclick="TableOperation();" disabled="disabled"><br/>
							<span id="tableActionMsg">&nbsp;</span>
						</div>
						{/if}

						<table id="posTable" class="col-tbl mb10" style="width:100%;">
							<thead>
								<tr>
									{if $params.registTime == "" || $params.status != 2}<th>&nbsp;</th>{/if}
									<th>ID</th>
									<th>Pos X</th>
									<th>Pos Y</th>
									<th>Pos Z</th>
									<th>Nearest candidate<br /><span style="font-weight: normal">rank&nbsp;/&nbsp;dist.[voxel]</span></th>
									{if $params.feedbackMode == "consensual"}
										<th>Entered by</th>
										<th style="display:none;">&nbsp;</th>
										<th style="display:none;">&nbsp;</th>
									{/if}
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>

						{if $params.registTime == "" || $params.status != 2}
						<div id="blockDeleteButton" style="text-align:right; margin-top:5px; font-size:14px;">
							<input type="button" id="undoBtn" class="form-btn form-btn-normal" value="undo" onclick="UndoFnTable();"{if $params.status!=1} style="display:none;"{/if} />
							<input type="button" id="resetBtn" class="form-btn form-btn-normal" value="Reset" onclick="ResetFnTable();"{if $params.status!=0} style="display:none;"{/if} />
							<input type="button" id="confirmBtn" class="form-btn form-btn-normal" value="Confirm" onclick="ConfirmFNLocation('');"{if $smarty.session.groupID == 'demo'} disabled="disabled"{/if} />
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

		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
</html>
