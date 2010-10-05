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
<script language="Javascript">
<!--

function RegistFeedback(feedbackMode)
{
	var address = 'show_cad_results.php?cadName=' + $("#cadName").val()
	            + '&version=' + $("#version").val()
    	        + '&studyInstanceUID=' + $("#studyInstanceUID").val()
      			+ '&seriesInstanceUID=' + $("#seriesInstanceUID").val()
			    + '&feedbackMode=' + feedbackMode;

	MovePageWithTempRegistration(address, 0);

}


function MovePageWithTempRegistration(address, interruptFlg)
{
	if($("#registTime").val() == "")
	{
		if(interruptFlg == 0
           || (interruptFlg == 1 && $("#interruptFlg").val() == 1 && confirm('Do you regist feedbacks temporarily?')))
		{
			var evalStr = $("#cadResult input[name='visualScore']:checked").val();

			$.post("./feedback_registration.php",
       				{ execID: $("#execID").val(),
			  		  cadName: $("#cadName").val(),
			          version: $("#version").val(),
			          interruptFlg: interruptFlg,
			          feedbackMode: $("#feedbackMode").val(),
			          evalStr: evalStr},
    	  	  		  function(data){
						alert(data.message);
						location.href = address;
				  }, "json");
		}
		else location.href = address;
	}
	else location.href = address;
}


function ChangeFeedbackMode(feedbackMode)
{
	var address = 'show_cad_results.php?cadName=' + $("#cadName").val()
                + '&version=' + $("#version").val()
                + '&studyInstanceUID=' + $("#studyInstanceUID").val()
                + '&seriesInstanceUID=' + $("#seriesInstanceUID").val()
                + '&feedbackMode=' + feedbackMode;

	MovePageWithTempRegistration(address, 1);
}

function DispRegistCaution()
{
	var tmpStr = 'Please press the [Registration] button,<br> or your changes will be discarded.';

	if($("#groupID").val() != 'demo')
	{
		$("#registCaution").html(tmpStr);
		$("#interruptFlg").val(1);
		
	}
}


function ShowDetailImage(fileName, annotation)
{
	$("#detailImg").attr("src", fileName);
	$("#detailAnnotation").html(annotation);

	$("#cadResult, #cadResultTab").hide();
	$("#cadDetail, #cadDetailTab").show();
	$("#container").height( $(document).height() - 10 );

}

function ShowCADResult()
{
	$("#cadDetail, #cadDetailTab").hide();
	$("#cadResult, #cadResultTab").show();
	$("#container").height( $(document).height() - 10 );
}

-->
</script>
{/literal}

<link rel="shortcut icon" href="favicon.ico" />

<!-- InstanceBeginEditable name="head" -->
<link href="../jq/ui/css/ui.all.css" rel="stylesheet" type="text/css" media="all" />
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
		{include file='cad_results/cad_result_tab_area.tpl'}
		
		<div class="tab-content">
			<form id="form1" name="form1">
			<input type="hidden" id="feedbackMode"      name="feedbackMode"      value="{$param.feedbackMode}">
			<input type="hidden" id="execID"            name="execID"            value="{$param.execID}">
			<input type="hidden" id="groupID"           name="groupID"           value="{$smarty.session.groupID}">
			<input type="hidden" id="studyInstanceUID"  name="studyInstanceUID"  value="{$param.studyInstanceUID}">
			<input type="hidden" id="seriesInstanceUID" name="seriesInstanceUID" value="{$param.seriesInstanceUID}">
			<input type="hidden" id="cadName"           name="cadName"           value="{$param.cadName}">	
			<input type="hidden" id="version"           name="version"           value="{$param.version}">
			<input type="hidden" id="colorSet"          name="colorSet"          value="{$smarty.session.colorSet}">
			<input type="hidden" id="ticket"            name="ticket"            value="{$ticket}">
			<input type="hidden" id="registTime"        name="registTime"        value="{$registTime}">
			<input type="hidden" id="interruptFlg"      name="interruptFlg"      value="{$param.interruptFlg}">
			<input type="hidden" id="srcList"           name="srcList"           value="{$param.srcList}">
			<input type="hidden" id="tagStr"            name="tagStr"            value="{$param.tagStr}">
			<input type="hidden" id="tagEnteredBy"      name="tagEnteredBy"      value="{$param.tagEnteredBy}">

			<div id="cadResult">

				<h2>CAD Result&nbsp;&nbsp;[{$param.cadName} v.{$param.version} ID:{$param.execID}]</h2>
				{* <h2>CAD Result&nbsp;&nbsp;[{$param.cadName} v.{$param.version}]<span class="ml10" style="font-size:12px;">(ID:{$param.execID})</span></h2> *}

				<div class="headerArea">
					<div class="fl-l"><a onclick="MovePageWithTempRegistration('../study_list.php?mode=patient&encryptedPtID={$param.encryptedPtID}', 1);">{$patientName}&nbsp;({$patientID})&nbsp;{$age}{$sex}</a></div>
					<div class="fl-l"><img src="../img_common/share/path.gif" /><a onclick="MovePageWithTempRegistration('../series_list.php?mode=study&studyInstanceUID={$param.studyInstanceUID}', 1);">{$studyDate}&nbsp;({$studyID})</a></div>
					<div class="fl-l"><img src="../img_common/share/path.gif" />{$modality},&nbsp;{$seriesDescription}&nbsp;({$seriesID})</div>
				</div>
		
				<div class="hide-on-guest">
					<input type="radio" name="change-mode1" value="Personal mode" class="radio-to-button-l" label="Personal mode"  onclick="ChangeFeedbackMode('personal');" {if $param.feedbackMode=='personal'}checked="checked"{/if} />
					<input type="radio" name="change-mode1" value="Consensual mode" class="radio-to-button-l" label="Consensual mode" onclick="ChangeFeedbackMode('consensual');" {if $param.feedbackMode=='consensual'}checked="checked"{/if}{if $smarty.session.consensualFBFlg==0 || ($param.feedbackMode == "personal" && $consensualFBFlg == 0)} disabled="disabled"{/if} />
					<div class="fl-l" style="margin-left:5px;">{$registMsg}</div>
				</div>

				<div class="fl-clr"><!-- 処理結果 -->
					<table style="border-collapse: separate; border-spacing: 5px;">
						<tr>
							<td colspan="3" class="al-l">
								<span style="font-size:14px; font-weight:bold;">Normalized image MPR</span>
							</td>
							<td colspan="2" class="al-r">Click on image for detail</td>
						</tr>
						<tr>
							<td width="{$dispWidth} valign="top" align="center style="background-color:#888;">
								<img src="{$thumbnailImgFname[0][0]}" ondblclick="ShowDetailImage('{$orgImgFname[0][0]}', 'Sagittal MPR');" />
							</td>
							<td width="{$dispWidth} valign="top" align="center style="background-color:#888;">
								<img src="{$thumbnailImgFname[0][1]}" ondblclick="ShowDetailImage('{$orgImgFname[0][1]}', 'Coronal MPR (vertebral body)');" />
							</td>
							<td width="{$dispWidth} valign="top" align="center style="background-color:#888;">
								<img src="{$thumbnailImgFname[0][2]}" ondblclick="ShowDetailImage('{$orgImgFname[0][2]}', 'Coronal MPR (anterior wall of the canal)');" />
							</td>
							<td width="{$dispWidth} valign="top" align="center style="background-color:#888;">
								<img src="{$thumbnailImgFname[0][3]}" ondblclick="ShowDetailImage('{$orgImgFname[0][3]}', 'Coronal MPR (center of the canal)');" />
							</td>
							<td width="{$dispWidth} valign="top" align="center style="background-color:#888;">
								<img src="{$thumbnailImgFname[0][4]}" ondblclick="ShowDetailImage('{$orgImgFname[0][4]}', 'Coronal MPR (posterior wall of the canal)');" />
							</td>
						</tr>

						<!-- captions -->
						<tr>	
							<td valign="top" class="al-c" >
								<span style="font-size:14px; font-weight:bold;">Sagittal MPR</span><br />
							</td>
							<td valign="top" class="al-c" >
								<span style="font-size:14px; font-weight:bold;">Coronal MPR</span><br />vertebral body
							</td>

							<td colspan="3" class="al-c">
								<table>
									<tr>
										<td colspan="2" class="al-c">
											<span style="font-size:14px; font-weight:bold;">Coronal MPR</span>
											<span style="margin-left:5px;">(canal)</span>
										</td>
									</tr>
									<tr>
										<td class="al-l" style="width:230px;">
											<span style="font-size:14px; font-weight:bold;">&larr;&nbsp;anterior</span>
										</td>
										<td class="al-r" style="width:230px;">
											<span style="font-size:14px; font-weight:bold;">posterior&nbsp;&rarr;</span>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan=5 height=15></td>
						</tr>
					</table>
				</div>

				<!-- Scoring interface -->
				{if $smarty.session.personalFBFlg == 1 || $smarty.session.consensualFBFlg == 1 || $smarty.session.groupID == 'demo'}
					<input type="hidden" id="lesionStr"    name="lesionStr"    value="{$lesionStr}">
					<input type="hidden" id="evalStr"      name="evalStr"      value="">
					<input type="hidden" id="interruptFlg" name="interruptFlg" value="{$param.interruptFlg}">
					<input type="hidden" id="registFlg"    name="registFlg"    value="{$param.registFlg}">

					<div class="hide-on-guest fl-clr" style="width: 800px;">
						<div class="ml40 js-personal-or-consensual {$param.feedbackMode}" style="display:inline;">
							{$scoringHtml}
						</div>
						<p class="fl-r" style="width:255px;">
							<input name="" type="button" value="Registration of feedback" class="fs-l form-btn registration" onclick="RegistFeedback('{$param.feedbackMode}');" {if $registTime != ""}disabled="disabled"{/if}/>
							<br />
							<span id="registCaution" class="regist-caution">{if $interruptFlg == 1}Please press the [Registration] button,<br/> or your changes will be discarded.{/if}</span>
						</p>
					</div>
				{/if}
				</div>
			<!-- / Result -->

			<!-- CAD detail -->
			<div id="cadDetail" style="display:none;">

				<h2 id="detailAnnotation">CAD detail</h2>

				<div class="detailAreafl-clr">
					<img class="ml30" id="detailImg" src="" />
				</div><!-- / .detailArea END -->
			</div>
			<!-- / CAD detail END -->

			<!-- Tag area -->
			{include file='cad_results/plugin_tag_area.tpl'}

			</form>

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

