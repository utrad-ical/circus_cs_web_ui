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
<script language="javascript" type="text/javascript" src="../jq/ui/ui.dialog.js"></script>
<script language="javascript" type="text/javascript" src="../jq/jq-btn.js"></script>
<script language="javascript" type="text/javascript" src="../js/hover.js"></script>
<script language="javascript" type="text/javascript" src="../js/viewControl.js"></script>

{literal}

<!--[if lte IE 6]>
<script language="javascript" type="text/javascript" src="../js/DD_belatedPNG_0.0.8a-min.js"></script>
<script language="javascript">
	DD_belatedPNG.fix('.transparent');
</script>
<![endif]-->


<script language="Javascript">
<!--

function CreateEvalStr(lesionArr)
{
	var evalStr = "";

	for(var j=0; j<(lesionArr.length-1); j++)
	{
		if($("#lesionBlock" + lesionArr[j] + " input[name:'radio_" + lesionArr[j] + "']:checked").val() == undefined)
		{
			evalStr += "99^";
		}
		else 
		{
			evalStr += $("#lesionBlock" + lesionArr[j] + " input[name:'radio_" + lesionArr[j] + "']:checked").val() + "^";
		}
	}

	return evalStr;
}

function RegistFeedback(feedbackMode, interruptFlg, candStr, evalStr, dstAddress)
{
	var execID  = $("#execID").val();
	var cadName = $("#cadName").val();
	var version = $("#version").val();
	var fnNum   = $("#fnNum").val();


	$.post("feedback_registration.php",
			{ execID: execID,
	  		  cadName: cadName,
	          version: version,
	          interruptFlg: interruptFlg,
	          feedbackMode: feedbackMode,
	  		  candStr: candStr,
	          evalStr: evalStr,
       		  fnNum: fnNum},
			  function(data){
				if(dstAddress != "")
				{
					if(dstAddress == "historyBack")  history.back();
					else						   	 location.replace(dstAddress);
				}
		  }, "json");
}

function MovePageWithTempRegistration(address)
{
	if($("#registTime").val() == "" && $("#interruptFlg").val() == 1)
	{
		var candStr = $("#candStr").val();
		var lesionArr = candStr.split("^");
		var evalStr = CreateEvalStr(lesionArr);

		RegistFeedback($("#feedbackMode").val(), 1, candStr, evalStr, address);
	}
	else
	{
		if(address == "historyBack")	history.back();
		else							location.replace(address);
	}
}


function ShowFNinput()
{
	var address = 'fn_input.php'
				+ '?execID=' + $("#execID").val()
                + '&cadName=' + $("#cadName").val()
                + '&version=' + $("#version").val()
                + '&studyInstanceUID=' + $("#studyInstanceUID").val()
                + '&seriesInstanceUID=' + $("#seriesInstanceUID").val()
                + '&feedbackMode=' + $("#feedbackMode").val();
	
	MovePageWithTempRegistration(address);
}

function ChangeCondition(mode, feedbackMode)
{
	var address = 'show_cad_results.php?cadName=' + $("#cadName").val()
	            + '&version=' + $("#version").val()
    	        + '&studyInstanceUID=' + $("#studyInstanceUID").val()
      			+ '&seriesInstanceUID=' + $("#seriesInstanceUID").val()
			    + '&feedbackMode=' + feedbackMode
				+ '&sortKey=' + $("#sortKey").val()
				+ '&sortOrder=' + $(".sort-by input[name='sortOrder']:checked").val();

	if($("#remarkCand").val() > 0)  address += '&remarkCand=' + $("#remarkCand").val();

	if((feedbackMode == "personal" || feedbackMode == "consensual") && $("#registTime").val() == "")
	{
		var candStr = $("#candStr").val();
		var lesionArr = candStr.split("^");
		var evalStr = CreateEvalStr(lesionArr);

		if(mode == 'registration')
		{
			evalArr = evalStr.split("^");
			errFlg = 0;
			
			for(var j=0; j<(evalArr.length-1); j++)
			{
				if(evalArr[j] == 99)
				{
					alert("[ERROR] Lesion classification is not completed!");
					errFlg = 1;
					break;
				}
			}

			if(errFlg == 0)
			{
				RegistFeedback(feedbackMode, 0, candStr, evalStr, address);
			}
		}
		else if(mode == 'changeSort' && $("#interruptFlg").val()==1)
		{
			RegistFeedback(feedbackMode, 1, candStr, evalStr, address);
		}
		else  location.replace(address);
	}
	else	location.replace(address);
}

function ChangeFeedbackMode(feedbackMode)
{
	var address = 'show_cad_results.php?cadName=' + $("#cadName").val()
                + '&version=' + $("#version").val()
                + '&studyInstanceUID=' + $("#studyInstanceUID").val()
                + '&seriesInstanceUID=' + $("#seriesInstanceUID").val()
                + '&feedbackMode=' + feedbackMode;

	if($("#remarkCand").val() > 0)  address += '&remarkCand=' + $("#remarkCand").val();

	MovePageWithTempRegistration(address);
}

function DispRegistCaution()
{
	var tmpStr = 'Please press the [Registration] button,<br> or your changes will be discarded.';

	if($("#groupID").val() != 'demo')
	{
		$("#registCaution").html(tmpStr);
		$("#interruptFlg").val(1);

		// 候補分類入力中にメニューバーを押された場合の対策
		$("#linkAbout, #menu a").click(
			function(event){ 

				if(!event.isDefaultPrevented())
				{
					event.preventDefault();  // prevent link action
					
					if(confirm("Do you want to save the changes?"))
					{
						MovePageWithTempRegistration(event.currentTarget.href);
					}
				}
			});
	}
}


function ShowCADDetail(imgNum)
{
	$("#slider").slider("value", imgNum);

	if($("#registTime").val() == "" && $("#interruptFlg").val() == 1)
	{
		var candStr = $("#candStr").val();
		var lesionArr = candStr.split("^");
		var evalStr = CreateEvalStr(lesionArr);

		RegistFeedback($("#feedbackMode").val(), 1, candStr, evalStr, "");
	}

	$("#cadResult, #cadResultTab").hide();
	$("#cadDetailTab, #cadDetail").show();
	$('#container').height( $(document).height() - 10 );

}

function ShowCADResult()
{
	$("#cadDetailTab, #cadDetail").hide();
	$("#cadResult, #cadResultTab").show();
	$('#container').height( $(document).height() - 10 );
}

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
	$.post("../jump_image.php",
			{ studyInstanceUID: $("#studyInstanceUID").val(),
			  seriesInstanceUID: $("#seriesInstanceUID").val(),
			  imgNum: imgNum,
			  windowLevel: windowLevel,
			  windowWidth: windowWidth,
			  presetName:  presetName },
  			  function(data){

				//alert(data.message);

				$("#imgBox img").attr("src", '../' + data.imgFname);
				$("#imgBox span").html(data.imgNumStr);
				$("#sliceNumber").html(data.sliceNumber);
				$("#sliceLocation").html(data.sliceLocation);
			}, "json");
}


function EditCandidateTag(execID, candID, feedbackMode, userID)
{
	var dstAddress = "../cad_results/edit_candidate_tag.php?execID=" + execID + "&candID=" + candID
                   + "&feedbackMode=" + feedbackMode + "&userID=" + userID;
	window.open(dstAddress,"Edit lesion candidate tag", "width=400,height=250,location=no,resizable=no,scrollbars=1");
}


{/literal}

$(function() {ldelim}
	$("#slider").slider({ldelim}
		value:{$detailData.imgNum},
		min: 1,
		max: {$detailData.fNum},
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

{if $fnConsCheck == 1}
	$.event.add(window, "load", 
				function(){ldelim}
					 alert("[CAUTION] Please confirm FN list!");

					var address = 'fn_input.php'
								+ '?execID=' + $("#execID").val()
              				    + '&cadName=' + $("#cadName").val()
                				+ '&version=' + $("#version").val()
                				+ '&studyInstanceUID=' + $("#studyInstanceUID").val()
                				+ '&seriesInstanceUID=' + $("#seriesInstanceUID").val()
                				+ '&feedbackMode=' + $("#feedbackMode").val();
					
					location.href = address;

				{rdelim});
{/if}


{rdelim});


-->
</script>

<link rel="shortcut icon" href="favicon.ico" />

<!-- InstanceBeginEditable name="head" -->
<link href="../jq/ui/css/ui.all.css" rel="stylesheet" type="text/css" media="all" />
<link href="../jq/ui/css/jquery-ui-1.7.2.custom.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/monochrome.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/popup.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/darkroom.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../js/radio-to-button.js"></script>

<!-- InstanceEndEditable -->
</head>

<!-- InstanceParam name="class" type="text" value="home" -->
<body class="lesion_cad_display{if $smarty.session.darkroomFlg==1} mono{/if}">
<div id="page">
	<div id="container" class="menu-back">
		<div id="leftside">
			{include file='menu.tpl'}
		</div><!-- / #leftside END -->
		<div id="content">
<!-- InstanceBeginEditable name="content" -->

		<!-- ***** TAB ***** -->
		<div id="cadResultTab" class="tabArea">
			<ul>
				{if $param.srcList!="" && $smarty.session.listAddress!=""}
					<li><a href="#" onclick="MovePageWithTempRegistration('../{$smarty.session.listAddress}');" class="btn-tab" title="{$param.listTabTitle}">{$param.listTabTitle}</a></li>
				{else}
					<li><a href="#" onclick="MovePageWithTempRegistration('../series_list.php?mode=study&studyInstanceUID={$param.studyInstanceUID}');" class="btn-tab" title="Series list">Series list</a></li>
				{/if}
				<li><a href="#" class="btn-tab" title="list" style="background-image: url(../img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">CAD result</a></li>
			</ul>
			<p class="add-favorite"><a href="#" title="favorite"><img src="../img_common/btn/favorite.jpg" width="100" height="22" alt="favorite"></a></p>
		</div><!-- / .tabArea END -->

		<div id="cadDetailTab" class="tabArea" style="display:none;">
			<ul>
				{if $param.srcList!="" && $smarty.session.listAddress!=""}
					<li><a href="../{$smarty.session.listAddress}" class="btn-tab" title="{$param.listTabTitle}">{$param.listTabTitle}</a></li>
				{else}
					<li><a href="../series_list.php?mode=study&studyInstanceUID={$param.studyInstanceUID}" class="btn-tab" title="Series list">Series list</a></li>
				{/if}
				<li><a href="#" onclick="ShowCADResult();" class="btn-tab" title="CAD result">CAD result</a></li>
				<li><a href="#" class="btn-tab" title="list" style="background-image: url(../img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">CAD detail</a></li>
			</ul>
			<p class="add-favorite"><a href="#" title="favorite"><img src="../img_common/btn/favorite.jpg" width="100" height="22" alt="favorite"></a></p>
		</div><!-- / .tabArea END -->

		
		<div class="tab-content">
			<form id="form1" name="form1">
			<input type="hidden" id="feedbackMode"      name="feedbackMode"      value="{$param.feedbackMode}" />
			<input type="hidden" id="execID"            name="execID"            value="{$param.execID}" />
			<input type="hidden" id="groupID"           name="groupID"           value="{$smarty.session.groupID}" />
			<input type="hidden" id="studyInstanceUID"  name="studyInstanceUID"  value="{$param.studyInstanceUID}" />
			<input type="hidden" id="seriesInstanceUID" name="seriesInstanceUID" value="{$param.seriesInstanceUID}" />
			<input type="hidden" id="cadName"           name="cadName"           value="{$param.cadName}" />	
			<input type="hidden" id="version"           name="version"           value="{$param.version}" />
			<input type="hidden" id="colorSet"          name="colorSet"          value="{$smarty.session.colorSet}" />
			<input type="hidden" id="ticket"            name="ticket"            value="{$ticket}" />
			<input type="hidden" id="registTime"        name="registTime"        value="{$registTime}" />
			<input type="hidden" id="srcList"           name="srcList"           value="{$param.srcList}" />
			<input type="hidden" id="tagStr"            name="tagStr"            value="{$param.tagStr}" />
			<input type="hidden" id="tagEnteredBy"      name="tagEnteredBy"      value="{$param.tagEnteredBy}" />
			<input type="hidden" id="remarkCand"        name="remarkCand"        value="{$param.remarkCand}" />

			<div id="cadResult">

				<h2>CAD Result&nbsp;&nbsp;[{$param.cadName} v.{$param.version} ID:{$param.execID}]</h2>
				{* <h2>CAD Result&nbsp;&nbsp;[{$param.cadName} v.{$param.version}]<span class="ml10" style="font-size:12px;">(ID:{$param.execID})</span></h2> *}

			<div class="headerArea">
					<div class="fl-l"><a onclick="MovePageWithTempRegistration('../study_list.php?mode=patient&encryptedPtID={$param.encryptedPtID}');">{$patientName}&nbsp;({$patientID})&nbsp;{$age}{$sex}</a></div>
					<div class="fl-l"><img src="../img_common/share/path.gif" /><a onclick="MovePageWithTempRegistration('../series_list.php?mode=study&studyInstanceUID={$param.studyInstanceUID}');">{$studyDate}&nbsp;({$studyID})</a></div>
					<div class="fl-l"><img src="../img_common/share/path.gif" />{$modality},&nbsp;{$seriesDescription}&nbsp;({$seriesID})</div>
				</div>
		
				<div class="hide-on-guest">
					<input type="radio" name="change-mode1" value="Personal mode" class="radio-to-button-l" label="Personal mode"  onclick="ChangeFeedbackMode('personal');" {if $param.feedbackMode=='personal'}checked="checked"{/if} />
					<input type="radio" name="change-mode1" value="Consensual mode" class="radio-to-button-l" label="Consensual mode" onclick="ChangeFeedbackMode('consensual');" {if $param.feedbackMode=='consensual'}checked="checked"{/if}{if $smarty.session.consensualFBFlg==0 || ($param.feedbackMode == "personal" && $consensualFBFlg == 0)} disabled="disabled"{/if} />
					<div class="fl-l" style="margin-left:5px;">{$registMsg}</div>
				</div>
			
				<div class="fl-clr"></div>

				<div class="sort-by">
					<div class="total-cand">
						{if $smarty.session.researchFlg==1}<span style="font-weight:bold;">Total candidates:</span> {$param.totalCandNum}{else}&nbsp;{/if}
					</div>
					<div class="sort-btn">
						<input id="sortBtn" type="button" value="Sort" class="s-btn w50 form-btn" onclick="ChangeCondition('changeSort','{$param.feedbackMode}');" />
						by
						<select id="sortKey" name="sortKey">
							<option value="0" {if $param.sortKey==0}selected="selected"{/if}>Confidence</option>
							<option value="1" {if $param.sortKey==1}selected="selected"{/if}>Img. No.</option>
							<option value="2" {if $param.sortKey==2}selected="selected"{/if}>Volume</option>
						</select>
						<input name="sortOrder" type="radio" value="f" {if $param.sortOrder=='f'}checked="checked"{/if} />Asc.
						<input name="sortOrder" type="radio" value="t" {if $param.sortOrder=='t'}checked="checked"{/if} />Desc.
						</div>
				</div>

				<!-- CAD result (lesionBlock) -->

				{foreach from=$candHtml item=htmlStr}
					{$htmlStr}
				{/foreach}

				{*<div class="fl-clr mb10" style="margin-top:-50px; border-top: 1px solid #888;"></div>*}

				<!-- Input FN number -->
				{if $smarty.session.personalFBFlg == 1 || $smarty.session.consensualFBFlg == 1 || $smarty.session.groupID == 'demo'}

					<input type="hidden" id="candStr"      name="candStr"    value="{$candStr}">
					<input type="hidden" id="evalStr"      name="evalStr"      value="">
					<input type="hidden" id="interruptFlg" name="interruptFlg" value="{$param.interruptFlg}">
					<input type="hidden" id="registFlg"    name="registFlg"    value="{$param.registFlg}">

					<div class="hide-on-guest fl-clr" style="width: 780px;">
						<div class="fl-l">
							<span style="font-weight:bold;">Number of false negatives: </span><input id="fnNum" name="fnNum" type="text" class="al-r" style="width: 30px;" value={$fnNum} {if $registTime != "" || $fnCountStatus == 2}disabled="disabled"{/if} />
							<input name="" type="button" class="form-btn" value="FN input" onclick="ShowFNinput();" />
						</div>
						<p class="fl-r" style="width:255px;">
							<input name="" type="button" value="Registration of feedback" class="fs-l form-btn registration" onclick="ChangeCondition('registration', '{$param.feedbackMode}');" {if $registTime != ""}disabled="disabled"{/if}/>
							<br />
							<span id="registCaution" class="regist-caution">{if $param.interruptFlg == 1}Please press the [Registration] button,<br/> or your changes will be discarded.{/if}</span>
						</p>
					</div>
				{/if}
				<div class="fl-clr"></div>
				</div>
			<!-- / Result -->

			<!-- CAD detail -->
			<div id="cadDetail" style="display:none;">
				<input type="hidden" id="presetName"   name="presetName"   value="{$detailData.presetName}" />
				<input type="hidden" id="windowLevel"  name="windowLevel"  value="{$detailData.windowLevel}" />
				<input type="hidden" id="windowWidth"  name="windowWidth"  value="{$detailData.windowWidth}" />

				<h2>CAD Detail</h2>

				<div class="detailArea fl-clr">
					<div class="series-detail-img">
						<table>
							<tr>
								<td valign=top align=left width="320" height="{$detailData.dispHeight}">
									<div id="imgBox" style="width:{$detailData.dispWidth}; height:{$detailData.dispHeight}; position:relative;">
										<img src="../{$detailData.dstFnameWeb}" width="{$detailData.dispWidth}" height="{$detailData.dispHeight}" style="position:absolute; left:{$detailData.imgLeftPos}px; top:0px; z-index:1;" />
										<span style="color:#fff; font-weight:bold; position:absolute; left:{$detailData.imgNumStrLeftPos}px; top:0px; z-index:2;">Img. No. {$detailData.imgNum|string_format:"%04d"}</span>
									</div>
								</td>
							</tr>
							<tr>
								<td valign=top align=center>
									<table cellpadding=0 cellspacing=0>
										<tr>
											<td align="right" {if $detailData.dispWidth>=300}width={math equation="(x-256)/2" x=$detailData.dispWidth}"{/if}>
 												<input type="button" value="-" onClick="Minus();" />
											</td>
											<td align="center" width="256"><div id="slider"></div></td>
											<td align="left" {if $detailData.dispWidth>=300}width="{math equation="(x-256)/2" x=$detailData.dispWidth}"{/if}>
	 											<input type="button" value="+" onClick="Plus();" />
											</td>
										</tr>
										<tr>
											<td align=center colspan=3>
												<span style="font-weight:bold;">Image number: <span id="sliderValue">1</span></span>
											</td>
										</tr>
										{if $detailData.grayscaleStr != ""}
											<tr>
												<td align=center colspan=3>
													<span style="font-weight:bold;">Grayscale preset: </span>
													<select id="presetMenu" name="presetMenu" onchange="ChangePresetMenu();">
														{section name=i start=0 loop=$detailData.presetNum}
															{assign var="i" value=$smarty.section.i.index}
															{assign var="tmp0" value=$i*3}
															{assign var="tmp1" value=$i*3+1}
															{assign var="tmp2" value=$i*3+2}

															<option value="{$detailData.presetArr[$tmp1]}^{$detailData.presetArr[$tmp2]}" {if $detailData.presetName == $detailData.presetArr[$tmp0]}selected="selected"{/if}>{$detailData.presetArr[$tmp0]}</option>
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
								<td>{$patientID}</td>
							</tr>
							<tr>
								<th><span class="trim01">Patient name</span></th>
								<td>{$patientName}</td>
							</tr>
							<tr>
								<th><span class="trim01">Sex</span></th>
								<td>{$sex}</td>
							</tr>
							<tr>
								<th><span class="trim01">Age</span></th>
								<td>{$age}</td>
							</tr>
							<tr>
								<th><span class="trim01">Study ID</span></th>
								<td>{$studyID}</td>
							</tr>
							<tr>
								<th><span class="trim01">Series date</span></th>
								<td>{$seriesDate}</td>
							</tr>
							<tr>
								<th><span class="trim01">Series time</span></th>
								<td>{$seriesTime}</td>
							</tr>
							<tr>
								<th><span class="trim01">Modality</span></th>
								<td>{$modality}</td>
							</tr>
							<tr>
								<th><span class="trim01">Series description</span></th>
								<td>{$seriesDescription}</td>
							</tr>
							<tr>
								<th><span class="trim01">Body part</span></th>
								<td>{$bodyPart}</td>
							</tr>
							<tr>
								<th><span class="trim01">Image number</span></th>
								<td><span id="sliceNumber">{$detailData.imgNum}</span></td>
							</tr>
							<tr>
								<th><span class="trim01">Slice location</span></th>
								<td><span id="sliceLocation">{$detailData.sliceLocation}</span></td>
							</tr>
						</table>
					</div><!-- / .detail-panel END -->
				</div><!-- / .detailArea END -->
				<div class="fl-clr"></div>
			</div>
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

