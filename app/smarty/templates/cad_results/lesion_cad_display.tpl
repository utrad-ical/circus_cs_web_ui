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
{/literal}
var candData = {$detailData|@json_encode};
{literal}
function CreateEvalStr(lesionArr)
{
	var evalArr = new Array();

	for(var j=0; j<lesionArr.length; j++)
	{
		if($("#lesionBlock" + lesionArr[j] + " input[name:'radioCand" + lesionArr[j] + "']:checked").val() == undefined)
		{
			evalArr.push(-99);
		}
		else 
		{
			evalArr.push($("#lesionBlock" + lesionArr[j] + " input[name:'radioCand" + lesionArr[j] + "']:checked").val());
		}
	}
	return evalArr.join("^");
}

function RegistFeedback(feedbackMode, interruptFlg, candStr, evalStr, dstAddress)
{
	$.post("feedback_registration.php",
			{ jobID:  $("#jobID").val(),
	  		  cadName: $("#cadName").val(),
	          version: $("#version").val(),
	          interruptFlg: interruptFlg,
			  fnFoundFlg: $('input[name="fnFoundFlg"]:checked').val(),
	          feedbackMode: feedbackMode,
	  		  candStr: candStr,
	          evalStr: evalStr},
			  function(data){

				if(interruptFlg == 0)	alert(data.message);

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
		else							location.href=address;
	}
}


function ShowFNinput()
{
	var address = 'fn_input.php'
				+ '?jobID=' + $("#jobID").val()
                + '&feedbackMode=' + $("#feedbackMode").val();
	
	MovePageWithTempRegistration(address);
}

function ChangeCondition(mode, feedbackMode)
{
	var address = 'show_cad_results.php?jobID=' + $("#jobID").val()
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
			RegistFeedback(feedbackMode, 0, candStr, evalStr, address);
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
	var address = 'show_cad_results.php?jobID=' + $("#jobID").val()
                + '&feedbackMode=' + feedbackMode;

	if($("#remarkCand").val() > 0)  address += '&remarkCand=' + $("#remarkCand").val();

	MovePageWithTempRegistration(address);
}

function ChangeRegistCondition()
{
	var checkCnt = $("input[name^='radioCand']:checked").length;

	var tmpStr = 'Candidate classification: <span style="color:' 
               + (($("#candNum").val()==checkCnt) ? 'blue;">complete' : 'red;">incomplete') + '</span><br/>'
	           + 'FN input: <span style="color:'
		       + (($("#fnInputStatus").val()==1) ? 'blue;">complete' : 'red;">incomplete') + '</span>';

	if($("#registTime").val() =="" && $("#candNum").val()==checkCnt && $("#fnInputStatus").val()==1)
	{
		$("#registBtn").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
		$("#interruptFlg").val(0);
	}
	else
	{
		$("#registBtn").attr("disabled", "disabled").removeClass('form-btn-normal').addClass('form-btn-disabled');
		$("#interruptFlg").val(1);
	}

	if($("#groupID").val() != 'demo')
	{
		$("#registCaution").html(tmpStr);

		$("#interruptFlg").val(1);

		// Measures to click button of menu bar during lesion candidate classification
		$("#linkAbout, #menu a, #listTab").click(
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


function ChangeLesionClassification(candID, label)
{
	if($("#feedbackMode").val()=="personal" && $("#registTime").val()=="")
	{
		var options = "Candidate " + candID + ":" + label;

		$.post("write_feedback_action_log.php",
				{ jobID: $("#jobID").val(),
				  action: 'classify',
				  options: options
				});
	}

	ChangeRegistCondition();
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
	$("#windowLevel").val(tmpStr[0]);
	$("#windowWidth").val(tmpStr[1]);
	$("#presetName").val($("#presetMenu option:selected").text());

	JumpImgNumber($("#slider").slider("value"));
}

function JumpImgNumber(imgNum)
{
	$.post("../jump_image.php",
		{ studyInstanceUID: $("#studyInstanceUID").val(),
		  seriesInstanceUID: $("#seriesInstanceUID").val(),
		  imgNum: imgNum,
		  windowLevel: $("#windowLevel").val(),
		  windowWidth: $("#windowWidth").val(),
		  presetName:  $("#presetName").val()},
		  function(data){

			if(data.errorMessage != "")
			{
				alert(data.errorMessage);
			}
			else if(data.imgFname != "")
			{
				$("#imgArea").attr("src", '../' + data.imgFname);
				$("#imgBlock span").remove();
				$("#sliceLocation").val(data.sliceLocation);

				if($("#checkVisibleCand").is(':checked'))
				{
					for(var i=0; i<candData.length; i++)
					{
						if(candData[i][4] == data.imgNum)
						{
							var xPos = parseInt(candData[i][2] * parseFloat($("#detailDispWidth").val())
				                                 / parseFloat($("#detailOrgWidth").val())  + 0.5);
							var yPos = parseInt(candData[i][3] * parseFloat($("#detailDispHeight").val())
       		                                     / parseFloat($("#detailOrgHeight").val()) + 0.5);

							plotDots(i+1, xPos, yPos, 0);
						}
					}
				}
			}
		}, "json");
}

function plotDots(id, x, y, colorSet)
{
	var dotOffsetX = -1;
	var dotOffsetY = -1;
	var labelOffsetX = 0;
	var labelOffsetY = 0;

	var labelBaseX = 3;
	var labelBaseY = 0;
	var color = "#ff00ff";

	// for IE
	if (document.all)
	{
		dotOffsetX = 2;
		labelOffsetX = 3;
		labelOffsetY = 1;
	}

	var htmlStr = '<span id="dot' + id + '" class="dot" style="top:' + (y+dotOffsetY) + 'px; '
                + 'left:' + (x+dotOffsetX) + 'px; height:3px; width:3px; padding:0px; '
                + 'background-color:' + color + ';position:absolute;"></span>'
				+ '<span id="label' + id + '" class="dot" style="top:' + (y+labelBaseY+labelOffsetY) + 'px;'
				+ ' left:' + (x+labelBaseX+labelOffsetX) + 'px; color:' + color + ';'
				+ ' filter:dropshadow(color=#000000 offX=1 offY=0) dropshadow(color=#000000 offX=-1 offY=0)'
				+ ' dropshadow(color=#000000 offX=0 offY=1) dropshadow(color=#000000 offX=0 offY=-1);'
				+ ' font-weight:bold;position:absolute;">' + id + '</span>';

	$("#imgBlock").append(htmlStr);
}

function EditCandidateTag(jobID, candID, feedbackMode, userID)
{
	var dstAddress = "../cad_results/edit_candidate_tag.php?jobID=" + jobID + "&candID=" + candID
                   + "&feedbackMode=" + feedbackMode + "&userID=" + userID;
	window.open(dstAddress,"Edit lesion candidate tag", "width=400,height=250,location=no,resizable=no,scrollbars=1");
}

function ChangeVisibleCand()
{
	if($("#checkVisibleCand").is(':checked'))	JumpImgNumber($("#slider").slider("value"));
	else										$("#imgBlock span").remove();
}

var rowClickHandler = function(event) {

	if ($(event.target).parents('td.tagColumn').length == 0)
	{
		$('#checkVisibleFN').attr('checked', 'checked');
		// for jQuery 1.3.2
		var imgNum = $(event.target).parents('tr').children('td.z').html();
		$("#slider").slider("value", imgNum);

		// for jQuery 1.4.3
		//var idx = $(event.target).parents('tr').index();
		//var item = fnData[idx];
		//$("#slider").slider("value", item.z);
	}
}

$(function() {
{/literal}

	$('#posTable tbody tr').live('click', rowClickHandler);

	$("#slider").slider({ldelim}
		value:{$params.sliceOffset+1},
		min: {$params.sliceOffset+1},
		max: {$detailParams.fNum},
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

{literal}

	$("input[name='fnFoundFlg']").change(function() {
	
		var options = "";

		if($(this).val() == 0)
		{
			$("#fnInputStatus").val(1);
			$("#fnInputBtn").attr("disabled", "disabled").removeClass('form-btn-normal').addClass('form-btn-disabled');
			options = "FN  not found";
		}
		else
		{
			$("#fnInputBtn").removeAttr("disabled").removeClass('form-btn-disabled').addClass('form-btn-normal');
		    $("#fnInputStatus").val(($("#fnNum").val() > 0) ? 1 : 0);
			options = "FN  found";
		}

		if($("#feedbackMode").val()=="personal")
		{
			$.post("write_feedback_action_log.php",
					{ jobID: $("#jobID").val(),
					  action: 'select',
					  options: options
					});
		}

		ChangeRegistCondition();
	});
});
{/literal}


-->
</script>

<link rel="shortcut icon" href="favicon.ico" />

<link href="../jq/ui/css/jquery-ui-1.7.3.custom.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/popup.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/darkroom.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="../js/radio-to-button.js"></script>

{literal}
<style type="text/css">

#posTable tr.emphasis td{
	font-weight:bold;
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
			<div id="cadResultTab" class="tabArea">
				<ul>
					{if $params.srcList!="" && $smarty.session.listAddress!=""}
						<li><a id="listTab" href="../{$smarty.session.listAddress}" class="btn-tab" title="{$params.listTabTitle}">{$params.listTabTitle}</a></li>
					{else}
						<li><a id="listTab" href="../series_list.php?mode=study&studyInstanceUID={$params.studyInstanceUID}" class="btn-tab" title="Series list">Series list</a></li>
					{/if}
					<li><a href="#" class="btn-tab" title="list" style="background-image: url(../img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">CAD result</a></li>
					<li><a href="#" onclick="ShowCADDetail({$params.sliceOffset+1});" class="btn-tab" title="CAD detail">CAD detail</a></li>

				</ul>
				<p class="add-favorite"><a href="#" title="favorite"><img src="../img_common/btn/favorite.jpg" width="100" height="22" alt="favorite"></a></p>
			</div><!-- / .tabArea END -->

			<div id="cadDetailTab" class="tabArea" style="display:none;">
				<ul>
					{if $params.srcList!="" && $smarty.session.listAddress!=""}
						<li><a href="../{$smarty.session.listAddress}" class="btn-tab" title="{$params.listTabTitle}">{$params.listTabTitle}</a></li>
					{else}
						<li><a href="../series_list.php?mode=study&studyInstanceUID={$params.studyInstanceUID}" class="btn-tab" title="Series list">Series list</a></li>
					{/if}
					<li><a href="#" onclick="ShowCADResult();" class="btn-tab" title="CAD result">CAD result</a></li>
					<li><a href="#" class="btn-tab" title="list" style="background-image: url(../img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">CAD detail</a></li>
				</ul>
				<p class="add-favorite"><a href="#" title="favorite"><img src="../img_common/btn/favorite.jpg" width="100" height="22" alt="favorite"></a></p>
			</div><!-- / .tabArea END -->

			
			<div class="tab-content">
			{if $data.errorMessage != ""}
				<div style="color:#f00;font-weight:bold;">{$data.errorMessage}</div>
			{else}
				<form id="form1" name="form1">
				<input type="hidden" id="feedbackMode"      name="feedbackMode"      value="{$params.feedbackMode}" />
				<input type="hidden" id="jobID"             name="jobID"             value="{$params.jobID}" />
				<input type="hidden" id="groupID"           name="groupID"           value="{$smarty.session.groupID}" />
				<input type="hidden" id="studyInstanceUID"  name="studyInstanceUID"  value="{$params.studyInstanceUID}" />
				<input type="hidden" id="seriesInstanceUID" name="seriesInstanceUID" value="{$params.seriesInstanceUID}" />
				<input type="hidden" id="cadName"           name="cadName"           value="{$params.cadName}" />	
				<input type="hidden" id="version"           name="version"           value="{$params.version}" />
				<input type="hidden" id="ticket"            name="ticket"            value="{$params.ticket|escape}" />
				<input type="hidden" id="registTime"        name="registTime"        value="{$params.registTime}" />
				<input type="hidden" id="srcList"           name="srcList"           value="{$params.srcList}" />
				<input type="hidden" id="remarkCand"        name="remarkCand"        value="{$params.remarkCand}" />

				<input type="hidden" id="candNum"        value="{$params.candNum}" />
				<input type="hidden" id="fnInputStatus"  value="{$params.fnInputStatus}" />
				<input type="hidden" id="fnPersonalCnt"  value="{$params.fnPersonalCnt}" />

				<div id="cadResult">

					<h2>CAD Result&nbsp;&nbsp;[{$params.cadName} v.{$params.version} ID:{$params.jobID}]</h2>

				<div class="headerArea">
						<div class="fl-l"><a onclick="MovePageWithTempRegistration('../study_list.php?mode=patient&encryptedPtID={$params.encryptedPtID}');">{$params.patientName}&nbsp;({$params.patientID})&nbsp;{$params.age}{$params.sex}</a></div>
						<div class="fl-l"><img src="../img_common/share/path.gif" /><a onclick="MovePageWithTempRegistration('../series_list.php?mode=study&studyInstanceUID={$params.studyInstanceUID}');">{$params.studyDate}&nbsp;({$params.studyID})</a></div>
						<div class="fl-l"><img src="../img_common/share/path.gif" />{$params.modality},&nbsp;{$params.seriesDescription}&nbsp;({$params.seriesID})</div>
					</div>
			
					<div class="hide-on-guest">
						<input type="radio" name="change-mode1" value="Personal mode" class="radio-to-button-l" label="Personal mode"  onclick="ChangeFeedbackMode('personal');" {if $params.feedbackMode=='personal'}checked="checked"{/if} />
						<input type="radio" name="change-mode1" value="Consensual mode" class="radio-to-button-l" label="Consensual mode" onclick="ChangeFeedbackMode('consensual');" {if $params.feedbackMode=='consensual'}checked="checked"{/if}{if $smarty.session.consensualFBFlg==0 || ($params.feedbackMode == "personal" && $consensualFBFlg == 0)} disabled="disabled"{/if} />
						{* <div class="fl-l" style="margin-left:5px;font-size:80%;"><a href="#">about classification types</a></div> *}
					</div>
				
					<div class="fl-clr"></div>

					<div class="sort-by">
						<div class="total-cand">
							{if $smarty.session.researchFlg==1}<span style="font-weight:bold;">Total candidates:</span> {$params.totalCandNum}{else}&nbsp;{/if}
						</div>
						<div class="sort-btn">
							<input id="sortBtn" type="button" value="Sort" class="s-btn w50 form-btn" onclick="ChangeCondition('changeSort','{$params.feedbackMode}');" />
							by
							<select id="sortKey" name="sortKey">
								<option value="confidence"  {if $params.sortKey=='confidence'}selected="selected"{/if}>Confidence</option>
								<option value="location_z"  {if $params.sortKey=='location_z'}selected="selected"{/if}>Img. No.</option>
								<option value="volume_size" {if $params.sortKey=='volume_size'}selected="selected"{/if}>Volume</option>
							</select>
							<input name="sortOrder" type="radio" value="ASC"  {if $params.sortOrder=='ASC'}checked="checked"{/if} />Asc.
							<input name="sortOrder" type="radio" value="DESC" {if $params.sortOrder=='DESC'}checked="checked"{/if} />Desc.
							</div>
					</div>

					<!-- CAD result (lesionBlock) -->
					{if $params.candNum==0}
						<div style="margin:10px;">&nbsp;</div>
					{else}
						{foreach from=$candHtml item=htmlStr}{$htmlStr}{/foreach}
					{/if}

					{*<div class="fl-clr mb10" style="margin-top:-50px; border-top: 1px solid #888;"></div>*}

					<!-- Input FN number -->
					{if $smarty.session.personalFBFlg == 1 || $smarty.session.consensualFBFlg == 1 || $smarty.session.groupID == 'demo'}

						<input type="hidden" id="candStr"      name="candStr"    value="{$candStr}">
						<input type="hidden" id="evalStr"      name="evalStr"      value="">
						<input type="hidden" id="interruptFlg" name="interruptFlg" value="{$params.interruptFlg}">
						<input type="hidden" id="registFlg"    name="registFlg"    value="{$params.registFlg}">

						<div class="hide-on-guest fl-clr" style="width: 820px;">
							<div class="fl-l" style="width:570px;">
								<input type="radio" name="fnFoundFlg" value="1"{if !$params.fnInputStatus>=1 ||$params.fnNum>0 || ($params.feedbackMode=="consensual" && $params.fnPersonalCnt>0)} checked="checked"{/if} {if $params.fnInputStatus==2} disabled="disabled"{/if}/> False negative found&nbsp;&nbsp;<input id="fnInputBtn" type="button" class="form-btn {if $params.fnInputStatus>=1 && $params.fnNum==0}form-btn-disabled{else}form-btn-normai{/if}" value="input" onclick="ShowFNinput();"{if $params.fnInputStatus>=1 && $params.fnNum==0} disabled="disabled"{/if} />&nbsp;&nbsp;(<span id="fnNum" style="font-weight:bold;color:red;">{$params.fnNum}</span> entered)<br/>
								<input type="radio" name="fnFoundFlg" value="0"{if $params.fnInputStatus>=1 && $params.fnNum==0} checked="checked"{/if}{if ($params.fnInputStatus>=1 && $params.fnNum>0) || ($params.feedbackMode=="consensual" && $params.fnPersonalCnt>0)} disabled="disabled"{/if} /> FN&nbsp;&nbsp;NOT&nbsp;&nbsp;found
							</div>
							<p class="fl-r" style="width:250px;">
								<input id="registBtn" type="button" value="Registration of feedback" class="fs-l form-btn registration form-btn-disabled" onclick="ChangeCondition('registration', '{$params.feedbackMode}');" {if $params.registTime!="" || !($params.candNum==$params.lesionCheckCnt && $params.fnInputStatus==1)} disabled="disabled"{/if} />
								<br />
								<span id="registCaution" style="font-weight:bold;">{if $params.registTime=="" || $params.fnInputStatus!=2}{$params.registStr}{else}{$registMsg}{/if}</span>
							</p>
						</div>
					{/if}
					<div class="fl-clr"></div>
					</div>
				<!-- / Result -->

				<!-- CAD detail -->
				<div id="cadDetail" style="display:none;">
					<input type="hidden" id="detailOrgWidth"     value="{$detailParams.orgWidth|escape}" />
					<input type="hidden" id="detailOrgHeight"    value="{$detailParams.orgHeight|escape}" />
					<input type="hidden" id="detailDispWidth"    value="{$detailParams.dispWidth|escape}" />
					<input type="hidden" id="detailDispHeight"   value="{$detailParams.dispHeight|escape}" />

					<input type="hidden" id="presetName"   name="presetName"   value="{$detailParams.presetName}" />
					<input type="hidden" id="windowLevel"  name="windowLevel"  value="{$detailParams.windowLevel}" />
					<input type="hidden" id="windowWidth"  name="windowWidth"  value="{$detailParams.windowWidth}" />

					<h2>CAD Detail&nbsp;&nbsp;[{$params.cadName} v.{$params.version} ID:{$params.jobID}]</h2>
					<div class="headerArea">
							<div class="fl-l"><a onclick="MovePageWithTempRegistration('../study_list.php?mode=patient&encryptedPtID={$params.encryptedPtID}');">{$params.patientName}&nbsp;({$params.patientID})&nbsp;{$params.age}{$params.sex}</a></div>
							<div class="fl-l"><img src="../img_common/share/path.gif" /><a onclick="MovePageWithTempRegistration('../series_list.php?mode=study&studyInstanceUID={$params.studyInstanceUID}');">{$params.studyDate}&nbsp;({$params.studyID})</a></div>
							<div class="fl-l"><img src="../img_common/share/path.gif" />{$params.modality},&nbsp;{$params.seriesDescription}&nbsp;({$params.seriesID})</div>
					</div>
					<div class="fl-clr"></div>
					<p style="margin-top:-10px; margin-left:10px; font-size:14px;"><input type="checkbox" id="checkVisibleCand" name="checkVisibleCand" "onclick="ChangeVisibleCand();" checked="checked" />&nbsp;Show lesion candidate</p>

					<div class="detailArea">
						<div class="series-detail-img">
							<table cellspacing=0>
								<tr>
									<td colspan="3" valign="top">
										<div id="imgBlock" style="margin:0px;padding:0px;width:{$detailParams.dispWidth}px;height:{$detailParams.dispHeight}px;position:relative;">
											<img id="imgArea" src="../{$detailParams.dstFnameWeb}" width="{$detailParams.dispWidth}" height="{$detailParams.dispHeight}" style="position:absolute; left:{$detailParams.imgLeftPos}px; top:0px;" />
											{*<span style="color:#fff; font-weight:bold; position:absolute; left:{$detailParams.imgNumStrLeftPos}px; top:0px; z-index:2;">Img. No. {$detailParams.imgNum|string_format:"%04d"}</span>*}
										</div>
									</td>
								</tr>
								<tr>
									<td valign=top align=center>
										<table cellpadding=0 cellspacing=0>
											<tr>
												<td align="right" {if $detailParams.dispWidth>=300}width={math equation="(x-256)/2" x=$detailParams.dispWidth}"{/if}>
	 												<input type="button" value="-" onclick="Minus();" />
												</td>
												<td align="center" width="256"><div id="slider"></div></td>
												<td align="left" {if $detailParams.dispWidth>=300}width="{math equation="(x-256)/2" x=$detailParams.dispWidth}"{/if}>
		 											<input type="button" value="+" onclick="Plus();" />
												</td>
											</tr>
											<tr>
												<td align=center colspan=3>
													<span style="font-weight:bold;">Image number: <span id="sliderValue">1</span></span>
												</td>
											</tr>
											{if $detailParams.grayscaleStr != ""}
											<tr>
												<td align=center colspan=3>
													<span style="font-weight:bold;">Grayscale preset: </span>
													<select id="presetMenu" name="presetMenu" onchange="ChangePresetMenu();">
													{foreach from=$detailParams.presetArr item=item}
														<option value="{$item[1]|escape}^{$item[2]|escape}"{if $detailParams.presetName == $item[0]} selected="selected"{/if}>{$item[0]|escape}</option>
													{/foreach}
													</select>
												</td>
											</tr>
											{/if}
										</table>
									</td>
								</tr>
							</table>
						</div>
						
						<div div class="fl-l">
							<p style="margin:0px 0px 3px 10px;">{$detailParams.sortStr}</p>
							<div style="overflow-y:scroll; overflow-x:hidden; width: 330px; height: 400px;">
								<table id="posTable" class="col-tbl mb10">
									<thead>
										<tr>
											<th>ID</th>
											<th>Pos X</th>
											<th>Pos Y</th>
											<th>Pos Z</th>
											<th style="width:4em;">Volume [mm3]</th>
											<th>Confidence</th>
											<th>Tag</th>
										</tr>
									</thead>
									<tbody>
										{foreach from=$detailData item=item name=detailData}
											<tr class="{if $smarty.foreach.detailData.index%2==1}column{/if}{$item[9]}">
												<td class="id">{$smarty.foreach.detailData.iteration}</td>
												<td class="x">{$item[2]|escape}</td>
												<td class="y">{$item[3]|escape}</td>
												<td class="z">{$item[4]|escape}</td>
												<td class="volume">{$item[6]|string_format:"%.1f"|escape}</td>
												<td class="confidence">{$item[7]|string_format:"%.3f"|escape}</td>
												<td class="tagColumn">
													<input id="tagBtn{$item[0]|escape}" type="button" value="tag" class="s-btn form-btn"
														    onclick="EditTag(5, '{$item[0]|escape}', '../')" title="{$item[8]|escape}" />
												</td>
											</tr>
										{/foreach}
									</tbody>
								</table>
							</div>
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
			{/if}
			</div><!-- / .tab-content END -->

			<!-- darkroom button -->
			{include file='darkroom_button.tpl'}

		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
</html>
