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
		value:{$imgNum},
		min:{$sliceOffset+1},
		max:{$fNum},
		step: 1,
		slide: function(event, ui) {ldelim}
			jQuery("#sliderValue").html(ui.value);
		{rdelim},
		change: function(event, ui) {ldelim}
			jQuery("#sliderValue").html(ui.value);
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

-->
</script>

<link rel="shortcut icon" href="favicon.ico" />

<!-- InstanceBeginEditable name="head" -->
<link href="../jq/ui/css/ui.all.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/monochrome.css" rel="stylesheet" type="text/css" media="all" />
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
<body class="lesion_cad_display{if $smarty.session.backgroundFlg==1} mono{/if}">
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
				<input type="hidden" id="execID"            name="execID"            value="{$params.execID}">
				<input type="hidden" id="studyInstanceUID"  name="studyInstanceUID"  value="{$params.studyInstanceUID}">
				<input type="hidden" id="seriesInstanceUID" name="seriesInstanceUID" value="{$params.seriesInstanceUID}">
				<input type="hidden" id="cadName"           name="cadName"           value="{$params.cadName}">
				<input type="hidden" id="version"           name="version"           value="{$params.version}">
				<input type="hidden" id="imgNum"            name="imgNum"            value="{$imgNum}">
				<input type="hidden" id="rowNum"            name="rowNum"            value="">
				<input type="hidden" id="posStr"            name="posStr"            value="">
				<input type="hidden" id="userStr"           name="userStr"           value="{$userStr}">
				<input type="hidden" id="candStr"           name="candStr"           value="{$candStr}">
				<input type="hidden" id="feedbackMode"      name="feedbackMode"      value="{$params.feedbackMode}">	
				<input type="hidden" id="userID"            name="userID"            value="{$userID}">	
				<input type="hidden" id="encryptedPatientID"   name="encryptedPatientID"   value="{$encryptedPatientID}">
				<input type="hidden" id="encryptedPatientName" name="encryptedPatientName" value="{$encryptedPatientName}">
				<input type="hidden" id="sex"            name="sex"            value="{$sex}">
				<input type="hidden" id="age"            name="age"            value="{$age}">
				<input type="hidden" id="seriesDate"     name="seriesDate"     value="{$seriesDate}">
				<input type="hidden" id="modality"       name="modality"       value="{$modality}">	

				<input type="hidden" id="tableName"      name="tableName"      value="{$tableName}">
				<input type="hidden" id="grayscaleStr"   name="grayscaleStr"   value="{$grayscaleStr}">
				<input type="hidden" id="presetName"     name="presetName"     value="{$presetName}">
				<input type="hidden" id="windowLevel"    name="windowLevel"    value="{$windowLevel}">
				<input type="hidden" id="windowWidth"    name="windowWidth"    value="{$windowWidth}">
					
				<input type="hidden" id="sliceOrigin"    name="sliceOrigin"    value="{$sliceOrigin}">
				<input type="hidden" id="slicePitch"     name="slicePitch"     value="{$slicePitch}">
				<input type="hidden" id="sliceOffset"    name="sliceOffset"    value="{$sliceOffset}">
					
				<input type="hidden" id="distTh"         name="distTh"         value="{$distTh}">
				<input type="hidden" id="orgWidth"       name="orgWidth"       value="{$orgWidth}">
				<input type="hidden" id="orgHeight"      name="orgHeight"      value="{$orgHeight}">
				<input type="hidden" id="dispWidth"      name="dispWidth"      value="{$dispWidth}">
				<input type="hidden" id="dispHeight"     name="dispHeight"     value="{$dispHeight}">
					
				<input type="hidden" id="registFNFlg"    name="registFNFlg"    value="{$registFNFlg}">
				<input type="hidden" id="visibleFlg"     name="visibleFlg"     value="{$visibleFlg}">
				<input type="hidden" id="interruptFNFlg" name="interruptFNFlg" value="{$interruptFNFlg}">	

				<input type="hidden" id="registTime"   name="registTime"   value="{$registTime}">
				<input type="hidden" id="ticket"       name="ticket"       value="{$ticket}">

				<h2>FN input&nbsp;[{$params.cadName} v.{$params.version} ID:{$params.execID}]&nbsp;&nbsp;({$params.feedbackMode} mode)</h2>

				<div class="headerArea">
					<div class="fl-l"><a href="../study_list.php?mode=patient&encryptedPtID={$params.encryptedPtID}">{$patientName}&nbsp;({$patientID})&nbsp;{$age}{$sex}</a></div>
					<div class="fl-l"><img src="../img_common/share/path.gif" /><a href="../series_list.php?mode=study&studyInstanceUID={$params.studyInstanceUID}">{$studyDate}&nbsp;({$studyID})</a></div>
					<div class="fl-l"><img src="../img_common/share/path.gif" />{$modality},&nbsp;{$seriesDescription}&nbsp;({$seriesID})</div>
				</div>

				<p class="mb10">
				{if $registTime != ""}Location of false negatives were registered at {$registTime}{if $params.feedbackMode =="consensual" && $enteredBy != ""} (by {$enteredBy}){/if}.{else}Click location of FN, and press the <span class="clr-blue fw-bold">[Confirm]</span> button after definition of all FN.{/if}</p>
				<p style="margin-top:-10px; margin-left:10px; font-size:14px;"><input type="checkbox" id="checkVisibleFN" name=id="checkVisibleFN" onclick="ChangeVisibleFN();"{if $visibleFlg == 1} checked="checked"{/if} />&nbsp;Show FN</p>

				<div class="series-detail-img">
					{* ----- Display image with slider ----- *}
					<table cellspacing=0>

						<tr>
							<td colspan="3" valign="top">
								<div id="imgBlock" style="margin:0px;padding:0px;width:{$dispWidth}px;height:{$dispHeight}px;position:relative;">
									<img id="imgArea" class="{if $registTime == "" && $smarty.session.groupID != 'demo'}enter{else}ng{/if}" src="../{$dstFnameWeb}" width="{$dispWidth}" "height={$dispHeight}" />
								</div>
							</td>
						</tr>

						<tr>
							<td align="right" style="{if $dispWidth >=256}width:{$widthOfPlusButton}{/if}px;">
								<input type="button" value="-" onClick="JumpImgNumber({$imgNum-1});" {if $imgNum == ($sliceOffset+1)} disabled{/if}>
							</td>
				
							<td align=center style="width:256px;"><div id="slider"></div></td>

							<td align=left  style="{if $dispWidth >=256}width:{$widthOfPlusButton}{/if}px;">
						 		<input type="button" value="+" onClick="JumpImgNumber({$imgNum+1});" {if $imgNum == $fNum} disabled{/if}>
							</td>
						</tr>

						<tr>
							<td align=center colspan=3>
								<b>Image number: <span id="sliderValue">{$imgNum}</span></b>
							</td>
						</tr>

						<tr>
							<td align=center colspan=3>
								<b>Slice location [mm]: </b>
								<input type="text" id="sliceLoc" name="sliceLoc" value={$sliceLoc} style="width:64px; text-align:right" onKeyPress="return submitStop(event);" />
								<input type="button" id="applySL" name="applySL" value="Jump" onclick="JumpImgNumBySliceLocation({$sliceOrigin}, {$slicePitch}, {$sliceOffset}, {$fNum});" />
					</td></tr>

						{if $grayscaleStr != ""}
							<tr>
								<td align=center colspan=3>
									<b>Grayscale preset: </b>
									<select id="presetMenu" name="presetMenu" onchange="ChangePresetMenu({$imgNum});">

										{section name=i start=0 loop=$presetNum}
											{assign var="i" value=$smarty.section.i.index}	
											<option value="{$presetArr[$i][1]}^{$presetArr[$i][2]}" {if $presetName == $presetArr[$i][0]} selected{/if}>{$presetArr[$i][0]}</option>
										{/section}
									</select>
								</td>
							</tr>
						{/if}
					</table>
				</div>

				{* --- Add FN plots --- *}
				{if $visibleFlg == 1}
					<script language="Javascript">
					<!--
					{section name=j start=0 loop=$enteredFnNum}
						{assign var="j" value=$smarty.section.j.index}
						
						{if $imgNum == $locationList[$j][3]}
							plotClickedLocation({$j+1}, {$locationList[$j][1]}, {$locationList[$j][2]}, {$locationList[$j][7]});
						{/if}
					{/section}
					-->
					</script>
				{/if}

				<div class="fl-l" style="width: 40%;">
		
					{if $smarty.session.groupID != 'demo'}
						<div style="text-align:center; margin-bottom:10px;">
							<input type="button" id="confirmButton" class="w100 form-btn" value="Confirm" onclick="ConfirmFNLocation();"{if $registTime != ""} disabled="disabled"{/if}>
						</div>
					{/if}

<!--					<p class="mb10"><input name="" type="button form-btn" value="Confirm" class="w100" /></p> -->
			
					<table id="posTable" class="col-tbl mb10" style="width:100%">
						<thead>
							<tr>
								{if $registTime == ""}<th>&nbsp;</th>{/if}
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
							{section name=j start=0 loop=$enteredFnNum}
							
								{assign var="j" value=$smarty.section.j.index}	

								<tr id="row{$j+1}" {if $j%2==1}class="column"{/if}>

								{if $registTime == ""}
									<td align=center>
									<input type="checkbox" name="rowCheckList[]" value="{$j+1}">
									</td>
								{/if}
						
								<td class="al-r" onclick="ClickPositionTable('row{$j+1}', {$locationList[$j][3]});"{if $locationList[$j][0]!='black'} style="color:{$locationList[$j][0]};"{/if}>{$j+1}</td>

								{section name=i start=1 loop=4}
									{assign var="i" value=$smarty.section.i.index}

									<td class="al-r" onclick="ClickPositionTable('row{$j+1}', {$locationList[$j][3]});"{if $locationList[$j][0]!='black'} style="color:{$locationList[$j][0]};"{/if}>{$locationList[$j][$i]}</td>
								{/section}
							
								<td align=center onclick="ClickPositionTable('row{$j+1}', {$locationList[$j][3]});"{if $locationList[$j][0]!='black'} style="color:{$locationList[$j][0]};"{/if}>{$locationList[$j][4]}</td>

								{if $params.feedbackMode == "consensual"}
									<td align=center onclick="ClickPositionTable('row{$j+1}', {$locationList[$j][3]});"{if $locationList[$j][0]!='black'} style="color:{$locationList[$j][0]};"{/if}>{$locationList[$j][5]}</td>
									<td align=center style=display:none;">{$locationList[$j][6]}</td>
								{/if}
							
								</tr>
							{/section}
						</tbody>
					</table>

					<div id="blockDeleteButton" style="margin-top:7px; font-size:14px;">
						{if $registTime == "" && $enteredFnNum > 0 && $smarty.session.groupID != 'demo'}
							<input type="button" id="delButton" class="s-btn form-btn" value="delete the checked" onclick="DeleteLocationRows();">
							{if $params.feedbackMode == "consensual"}
								&nbsp;&nbsp;<input type="button" id="integrationButton" class="s-btn form-btn" value="integrate the checked" onclick="IntegrateLocationRows();">
							{/if}
						{/if}
					</div>

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
