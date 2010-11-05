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
<link href="css/import.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="jq/jquery-1.3.2.min.js"></script>
<script language="javascript" type="text/javascript" src="jq/ui/ui.core.js"></script>
<script language="javascript" type="text/javascript" src="jq/ui/ui.slider.js"></script>
<script language="javascript" type="text/javascript" src="jq/jq-btn.js"></script>
<script language="javascript" type="text/javascript" src="js/hover.js"></script>
<script language="javascript" type="text/javascript" src="js/viewControl.js"></script>
<script language="javascript" type="text/javascript" src="js/search_panel.js"></script>
<script language="javascript" type="text/javascript" src="js/list_tab.js"></script>

<script language="Javascript">
<!--
{if $data.errorMessage == ""}
$(function() {ldelim}
	$("#slider").slider({ldelim}
		value:{$data.imgNum},
		min: 1,
		max: {$data.fNum},
		step: 1,
		slide: function(event, ui) {ldelim}
			$("#sliderValue").html(ui.value);
		{rdelim},
		change: function(event, ui) {ldelim}
			$("#sliderValue").html(ui.value);
			JumpImgNumber(ui.value, $("#windowLevel").val(), $("#windowWidth").val(), $("#presetName").val());
		{rdelim}
	{rdelim});
	$("#slider").css("width", "220px");
	$("#sliderValue").html(jQuery("#slider").slider("value"));	

{rdelim});
{/if}

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

function ChangePresetMenu()
{
	var tmpStr = $("#presetMenu").val().split("^");
	var presetName = $("#presetMenu option:selected").text();
	$("#windowLevel").val(tmpStr[0]);
	$("#windowWidth").val(tmpStr[1]);
	$("#presetName").val(presetName);

	JumpImgNumber($("#slider").slider("value"), tmpStr[0], tmpStr[1], presetName);
}

function JumpImgNumber(imgNum, windowLevel, windowWidth, presetName)
{
	$.post("jump_image.php",
			{ studyInstanceUID: $("#studyInstanceUID").val(),
			  seriesInstanceUID: $("#seriesInstanceUID").val(),
			  imgNum: imgNum,
			  windowLevel: windowLevel,
			  windowWidth: windowWidth,
			  presetName:  presetName },
  			  function(data){

				if(data.errorMessage != "")  alert(data.errorMessage);

				if(data.imgFname != "")
				{
					$("#imgBox img").attr("src", data.imgFname);
					$("#imgBox span").html(data.imgNumStr);
					$("#sliceNumber").html(data.sliceNumber);
					$("#sliceLocation").html(data.sliceLocation + ' [mm]');
				}
			}, "json");
}

function DownloadVolume()
{
	window.open("about:blank","Download", "width=400,height=200,location=no,resizable=no");
	document.form1.target = "Download";
	document.form1.action = 'research/convert_volume_data.php';
	document.form1.method = 'POST';
	document.form1.submit();
}


-->
</script>
{/literal}

<link rel="shortcut icon" href="favicon.ico" />
<!-- InstanceBeginEditable name="head" -->
<link href="./css/base_style.css" rel="stylesheet" type="text/css" media="all" />
<link href="./jq/ui/css/ui.all.css" rel="stylesheet" type="text/css" media="all" />
<link href="./css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="./css/popup.css" rel="stylesheet" type="text/css" media="all" />
<link href="./css/darkroom.css" rel="stylesheet" type="text/css" media="all" />

<!-- InstanceEndEditable -->
<!-- InstanceParam name="class" type="text" value="series-list" -->
</head>

<body class="series-list">
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
				<li><a href="series_list.php?mode=study&studyInstanceUID={$data.studyInstanceUID|escape}" class="btn-tab" title="detail">Series list</a></li>
				<li><a href="" class="btn-tab" title="list" style="background-image: url(img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">Series detail</a></li>
			</ul>
			<p class="add-favorite"><a href="" title="favorite"><img src="img_common/btn/favorite.jpg" width="100" height="22" alt="favorite"></a></p>
		</div><!-- / .tabArea END -->
		
		<div class="tab-content">
		{if $data.errorMessage != ""}
			<div style="color:#f00;font-weight:bold;">{$data.errorMessage}</div>
		{else}
			<div id="series_detail">
				<h2>Series detail</h2>

				<div class="series-detail-img">
					<form id="form1" name="form1" action="series_detail.php" method="POST">

					<input type="hidden" id="dispMode"          name="dispMode"           value="{$data.dispMode|escape}" />
					<input type="hidden" id="studyInstanceUID"  name="studyInstanceUID"   value="{$data.studyInstanceUID|escape}" />
					<input type="hidden" id="seriesInstanceUID" name="seriesInstanceUID"  value="{$data.seriesInstanceUID|escape}" />		
					<input type="hidden" id="encryptedPtID"     name="encryptedPtID"      value="{$data.encryptedPtID|escape}" />
					<input type="hidden" id="encryptedPtName"   name="encryptedPtName"    value="{$data.encryptedPtName|escape}" />
					<input type="hidden" id="presetName"        name="presetName"         value="{$data.presetName|escape}" />
					<input type="hidden" id="windowLevel"       name="windowLevel"        value="{$data.windowLevel|escape}" />
					<input type="hidden" id="windowWidth"       name="windowWidth"        value="{$data.windowWidth|escape}" />
					<input type="hidden" name="orgWidth"        value="{$data.orgWidth}" />
					<input type="hidden" name="orgHeight"       value="{$data.orgHeight}" />
					<input type="hidden" id="dispWidth"         name="dispWidth"          value="{$data.dispWidth}" />
					<input type="hidden" id="dispHeight"        name="dispheight"         value="{$data.dispHeight}" />
					<input type="hidden" id="imgNum"            name="imgNum"             value="">

					<table>
						<tr>
							<td valign=top align=left width="320" height="{$data.dispHeight}">
								<div id="imgBox" style="width:{$data.dispWidth}; height:{$data.dispHeight}; position:relative;">
									<img src="{$data.dstFnameWeb|escape}" width="{$data.dispWidth}" height="{$data.dispHeight}" style="position:absolute; left:{$data.imgLeftPos}px; top:0px; z-index:1;" />
									<span style="color:#fff; font-weight:bold; position:absolute; left:{$data.imgNumStrLeftPos}px; top:0px; z-index:2;">Img. No. {$data.imgNum|string_format:"%04d"}</span>
								</div>
							</td>
						</tr>

						<tr>
							<td valign=top align=center>
								<table cellpadding=0 cellspacing=0>
									<tr>
										<td align="right" {if $data.dispWidth>=300}width={math equation="(x-256)/2" x=$data.dispWidth}"{/if}>
 											<input type="button" value="-" onClick="Minus();" />
										</td>
										<td align="center" width="256"><div id="slider"></div></td>
										<td align="left" {if $data.dispWidth>=300}width="{math equation="(x-256)/2" x=$data.dispWidth}"{/if}>
	 										<input type="button" value="+" onClick="Plus();" />
										</td>
									</tr>
									<tr>
										<td align=center colspan=3>
											<span style="font-weight:bold;">Image number: <span id="sliderValue">{$data.imgNum}</span></span>
										</td>
									</tr>
									{if $data.grayscaleStr != ""}
										<tr>
											<td align=center colspan=3>
											<span style="font-weight:bold;">Grayscale preset: </span>
												<select id="presetMenu" name="presetMenu" onchange="ChangePresetMenu();">

												{section name=i start=0 loop=$data.presetNum}
					
													{assign var="i" value=$smarty.section.i.index}
													{assign var="tmp0" value=$i*3}
													{assign var="tmp1" value=$i*3+1}
													{assign var="tmp2" value=$i*3+2}

													<option value="{$data.presetArr[$tmp1]}^{$data.presetArr[$tmp2]}" {if $data.presetName == $data.presetArr[$tmp0]}selected="selected"{/if}>{$data.presetArr[$tmp0]}</option>
												{/section}
									
												</select>

												</td>
										</tr>
									{/if}

								</table>
							</td>
						</tr>
					</table>
				</div>
					
				<div class="detail-panel">
					<table class="detail-tbl">
						<tr>
							<th style="width: 12em;"><span class="trim01">Patient ID</span></th>
							<td>{$data.patientID}</td>
						</tr>
						<tr>
							<th><span class="trim01">Patient name</span></th>
							<td>{$data.patientName}</td>
						</tr>
						<tr>
							<th><span class="trim01">Sex</span></th>
							<td>{$data.sex}</td>
						</tr>
						<tr>
							<th><span class="trim01">Age</span></th>
							<td>{$data.age}</td>
						</tr>
						<tr>
							<th><span class="trim01">Study ID</span></th>
							<td>{$data.studyID}</td>
						</tr>
						<tr>
							<th><span class="trim01">Series date</span></th>
							<td>{$data.seriesDate}</td>
						</tr>
						<tr>
							<th><span class="trim01">Series time</span></th>
							<td>{$data.seriesTime}</td>
						</tr>
						<tr>
							<th><span class="trim01">Modality</span></th>
							<td>{$data.modality}</td>
						</tr>
						<tr>
							<th><span class="trim01">Series description</span></th>
							<td>{$data.seriesDescription}</td>
						</tr>
						<tr>
							<th><span class="trim01">Body part</span></th>
							<td>{$data.bodyPart}</td>
						</tr>
						<tr>
							<th><span class="trim01">Image number</span></th>
							<td><span id="sliceNumber">{$data.sliceNumber}</span></td>
						</tr>
						<tr>
							<th><span class="trim01">Slice location</span></th>
							<td><span id="sliceLocation">{$data.sliceLocation}</span></td>
						</tr>
					</table>
					{if $smarty.session.dlVolumeFlg}
						<div class="mt15 ml10">
							<input name="" value="Download volume data" type="button" class="form-btn" style="width: 200px;" onclick="DownloadVolume();" />
						</div>
					{/if}
				</div><!-- / .detail-panel END -->
				<div class="fl-clr"></div>
			</div>
			<!-- / Series detail END -->

			<div id="tagArea" class="mt10" style="width:500px;">
				Tags:
				{foreach from=$tagArray item=item}
					<a href="series_list.php?filterTag={$item[0]|escape}" title="Entered by {$item[1]|escape}">{$item[0]|escape}</a>&nbsp;
				{/foreach}
				{if $smarty.session.personalFBFlg==1}<a href="#" onclick="EditTag(3,'{$data.sid|escape}');">(Edit)</a>{/if}
			</div>

			<div class="al-r ">
				<p class="pagetop"><a href="#page">page top</a></p>
			</div>
		{/if}
		</div><!-- / .tab-content END -->

		<!-- darkroom button -->
		{include file='darkroom_button.tpl'}
		
		<!-- InstanceEndEditable -->
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
<!-- InstanceEnd --></html>
