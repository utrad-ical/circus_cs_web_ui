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
<script language="javascript" type="text/javascript" src="../jq/ui/jquery-ui-1.7.3.min.js"></script>
<script language="javascript" type="text/javascript" src="../jq/jq-btn.js"></script>
<script language="javascript" type="text/javascript" src="../js/hover.js"></script>
<script language="javascript" type="text/javascript" src="../js/viewControl.js"></script>

{literal}
<script language="Javascript">
<!--

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

function ChangeSlice(imgNum)
{
	var numStr = imgNum.toString();
    numStr = Array(5 - numStr.length).join('0') + numStr;

	var orgImgFname = '../' + $("#webPathOfCADReslut").val() + '/in' + numStr + '.jpg';
	var resImgFname = '../' + $("#webPathOfCADReslut").val() + '/out' + numStr + '.jpg';

	$("#orgImg").attr("src", orgImgFname);
	$("#resImg").attr("src", resImgFname);
				
}

function DownloadSegResult(address)
{
	location.replace(address);
}


{/literal}


$(function() {ldelim}
	$("#slider").slider({ldelim}
		value:{$imgNum},
		min: 1,
		max: {$maxImgNum},
		step: 1,
		slide: function(event, ui) {ldelim}
			$("#sliderValue").html(ui.value);
		{rdelim},
		change: function(event, ui) {ldelim}
			$("#sliderValue").html(ui.value);
			ChangeSlice(ui.value);
		{rdelim}
	{rdelim});
	$("#slider").css("width", "220px");
	$("#sliderValue").html(jQuery("#slider").slider("value"));	

{rdelim});


-->
</script>

<link rel="shortcut icon" href="favicon.ico" />

<!-- InstanceBeginEditable name="head" -->
<link href="../jq/ui/css/jquery-ui-1.7.3.custom.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/popup.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/darkroom.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../js/radio-to-button.js"></script>
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
		{include file='cad_results/cad_result_tab_area.tpl'}
		
		<div class="tab-content">
			<form id="form1" name="form1">
			<input type="hidden" id="execID"             name="execID"             value="{$params.execID}">
			<input type="hidden" id="studyInstanceUID"   name="studyInstanceUID"   value="{$params.studyInstanceUID}">
			<input type="hidden" id="seriesInstanceUID"  name="seriesInstanceUID"  value="{$params.seriesInstanceUID}">
			<input type="hidden" id="colorSet"           name="colorSet"           value="{$smarty.session.colorSet}">
			<input type="hidden" id="srcList"            name="srcList"            value="{$params.srcList}">
			<input type="hidden" id="tagStr"             name="tagStr"             value="{$params.tagStr}">
			<input type="hidden" id="tagEnteredBy"       name="tagEnteredBy"       value="{$params.tagEnteredBy}">
			<input type="hidden" id="webPathOfCADReslut" name="webPathOfCADReslut" value="{$webPathOfCADReslut}">

			<div id="cadResult">

				<h2>CAD Result&nbsp;&nbsp;[{$params.cadName} v.{$params.version} ID:{$params.execID}]</h2>
				{* <h2>CAD Result&nbsp;&nbsp;[{$params.cadName} v.{$params.version}]<span class="ml10" style="font-size:12px;">(ID:{$params.execID})</span></h2> *}
			
				<div class="headerArea">
					<div class="fl-l"><a onclick="location.href='../study_list.php?mode=patient&encryptedPtID={$params.encryptedPtID}';">{$patientName}&nbsp;({$patientID})&nbsp;{$age}{$sex}</a></div>
					<div class="fl-l"><img src="../img_common/share/path.gif" /><a onclick="location.href='../series_list.php?mode=study&studyInstanceUID={$params.studyInstanceUID}';">{$studyDate}&nbsp;({$studyID})</a></div>
					<div class="fl-l"><img src="../img_common/share/path.gif" />{$modality},&nbsp;{$seriesDescription}&nbsp;({$seriesID})</div>
				</div>
		
				<div class="detailArea fl-clr">
					<div class="series-detail-img" style="width:775px; border: 0px;">
						<table>
							<tr>
								<td valign=top width="{$dispWidth}" height="{$dispHeight}">
									<img id="orgImg" src="../{$orgImg}" width="{$dispWidth}" height="{$dispHeight}" />
								</td>
								<td width=5>&nbsp;</td>
								<td valign=top width="{$dispWidth}" height="{$dispHeight}">
									<img id="resImg" src="../{$resImg}" width="{$dispWidth}" height="{$dispHeight}" />
								</td>
							</tr>
							<tr>
								<td valign=top align=center colspan=3>
									<table cellpadding=0 cellspacing=0>
										<tr>
											<td align="right" {if $dispWidth>=300}width={math equation="(x-256)/2" x=$dispWidth}"{/if}>
 												<input type="button" value="-" onClick="Minus();" />
											</td>
											<td align="center" width="256"><div id="slider"></div></td>
											<td align="left" {if $dispWidth>=300}width="{math equation="(x-256)/2" x=$dispWidth}"{/if}>
	 											<input type="button" value="+" onClick="Plus();" />
											</td>
										</tr>
										<tr>
											<td align=center colspan=3>
												<span style="font-weight:bold;">Image number: <span id="sliderValue">1</span></span>
											</td>
										</tr>
										<tr>
											<td align=center colspan=3>
												<input class="mt10 form-btn" name="" value="Download segmentation result" type="button" style="width: 220px; padding:3px;" onclick="DownloadSegResult('{$segResultFile}');" />
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</div>
				</div><!-- / .detailArea END -->
			</div>
			<div class="fl-clr"></div>
			<!-- / CAD detail END -->

			<!-- Tag area -->
			{include file='cad_results/plugin_tag_area.tpl'}

			</form>

			<div class="al-r">
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

