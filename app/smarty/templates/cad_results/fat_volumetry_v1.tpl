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
{/literal}

function ChangeSlice(imgNum)
{ldelim}
	$.post("plugin_template/change_slice_fat_volumetry_v1.php",
			{ldelim}
              version: {$params.version},
			  imgNum: imgNum,
			  jobID: $("#jobID").val(),
			  orgImgFname: $("#orgImg").attr("src"),
			  resImgFname: $("#resImg").attr("src"),
			{rdelim},
  			  function(data){ldelim}

				//alert(data.message);
				$("#sliceNumber").html(imgNum);
				$("#orgImg").attr("src", data.orgImgFname);
				$("#resImg").attr("src", data.resImgFname);
				$("#dcmSliceNum").html(data.dcmSliceNum);
				$("#sliceLocation").html(data.sliceLocation);
				$("#bodyTrunkArea").html(data.bodyTrunkArea);
				$("#satArea").html(data.satArea);
				$("#vatArea").html(data.vatArea);
				$("#areaRatio").html(data.areaRatio);
				$("#boundaryLength").html(data.boundaryLength);
				
			{rdelim}, "json");
{rdelim}

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

<link href="../jq/ui/css/jquery-ui-1.7.3.custom.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/darkroom.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../js/radio-to-button.js"></script>
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
			{include file='cad_results/cad_result_tab_area.tpl'}
			
			<div class="tab-content">
				<form id="form1" name="form1">
				<input type="hidden" id="jobID"             name="jobID"             value="{$params.jobID}">
				<input type="hidden" id="studyInstanceUID"  name="studyInstanceUID"  value="{$params.studyInstanceUID}">
				<input type="hidden" id="seriesInstanceUID" name="seriesInstanceUID" value="{$params.seriesInstanceUID}">
				<input type="hidden" id="colorSet"          name="colorSet"          value="{$smarty.session.colorSet}">
				<input type="hidden" id="srcList"           name="srcList"           value="{$params.srcList}">
				<input type="hidden" id="tagStr"            name="tagStr"            value="{$params.tagStr}">
				<input type="hidden" id="tagEnteredBy"      name="tagEnteredBy"      value="{$params.tagEnteredBy}">

				<div id="cadResult">

					<h2>CAD Result&nbsp;&nbsp;[{$params.cadName} v.{$params.version} ID:{$params.jobID}]</h2>

					<div class="headerArea">
						<div class="fl-l"><a href="../study_list.php?mode=patient&encryptedPtID={$params.encryptedPtID}">{$params.patientName}&nbsp;({$params.patientID})&nbsp;{$params.age}{$params.sex}</a></div>
						<div class="fl-l"><img src="../img_common/share/path.gif" /><a href="../series_list.php?mode=study&studyInstanceUID={$params.studyInstanceUID}">{$params.studyDate}&nbsp;({$params.studyID})</a></div>
						<div class="fl-l"><img src="../img_common/share/path.gif" />{$params.modality},&nbsp;{$params.seriesDescription}&nbsp;({$params.seriesID})</div>
					</div>
			
					<div class="detailArea fl-clr">
						<div class="series-detail-img" style="width:{$dispWidth}px;">
							<table>
								<tr>
									<td valign=top width="{$dispWidth}" height="{$dispHeight}">
										<img id="orgImg" src="../{$orgImg}" width="{$dispWidth}" height="{$dispHeight}" />
									</td>
								</tr>
								<tr>
									<td class="pt10" valign=top width="{$dispWidth}" height="{$dispHeight}">
										<img id="resImg" src="../{$resImg}" width="{$dispWidth}" height="{$dispHeight}" />
									</td>
								<tr>
									<td valign=top align=center>
										<table cellpadding=0 cellspacing=0>
											<tr>
												<td align="right" {if $dispWidth>=300}width={math equation="(x-256)/2" x=$dispWidth}"{/if}>
	 												<input type="button" value="-" onclick="Minus();" />
												</td>
												<td align="center" width="256"><div id="slider"></div></td>
												<td align="left" {if $dispWidth>=300}width="{math equation="(x-256)/2" x=$dispWidth}"{/if}>
		 											<input type="button" value="+" onclick="Plus();" />
												</td>
											</tr>
											<tr>
												<td align=center colspan=3>
													<span style="font-weight:bold;">Image number: <span id="sliderValue">1</span></span>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</div>

						<div class="detail-panel mt10">
							<table class="detail-tbl mb20">
								<tr>
									<th style="width: 18em;"><span class="trim01">Body trunk volume</span></th>
									<td>{$data.body_trunk_volume|string_format:"%.2f"} [cm3]</td>
								</tr>
								<tr>
									<th><span class="trim01">SAT volume</span></th>
									<td>{$data.sat_volume|string_format:"%.2f"} [cm3]</td>
								</tr>
								<tr>
									<th><span class="trim01">VAT volume</span></th>
									<td>{$data.vat_volume|string_format:"%.2f"} [cm3]</td>
								</tr>
								<tr>
									<th><span class="trim01">VAT/SAT</span></th>
									<td>{$data.vol_ratio|string_format:"%.3f"}</td>
								</tr>
								<tr><td colspan="2" style="border-bottom:0; height:20px;"></td></tr>
								<tr>
									<th><span class="trim01">Slice number of DICOM series</span></th>
									<td><span id="dcmSliceNum">{$data.image_num}</span></td>
								</tr>
								<tr>
									<th><span class="trim01">Slice location</span></th>
									<td><span id="sliceLocation">{$data.slice_location|string_format:"%.2f"}</span> [mm]</td>
								</tr>
								<tr>
									<th><span class="trim01">Body trunk area</span></th>
									<td><span id="bodyTrunkArea">{$data.body_trunk_area|string_format:"%.2f"}</span> [cm2]</td>
								</tr>
								<tr>
									<th><span class="trim01">SAT area</span></th>
									<td><span id="satArea">{$data.sat_area|string_format:"%.2f"}</span> [cm2]</td>
								</tr>
								<tr>
									<th><span class="trim01">VAT area</span></th>
									<td><span id="vatArea">{$data.vat_area|string_format:"%.2f"}</span> [cm2]</td>
								</tr>
								<tr>
									<th><span class="trim01">VAT/SAT</span></th>
									<td><span id="areaRatio">{$data.area_ratio|string_format:"%.3f"}</span></td>
								</tr>
								<tr>
									<th><span class="trim01">Boundary length</span></th>
									<td><span id="boundaryLength">{$data.boundary_length|string_format:"%.2f"}</span> [cm]</td>
								</tr>
							</table>
						</div><!-- / .detail-panel END -->
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
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
</html>
