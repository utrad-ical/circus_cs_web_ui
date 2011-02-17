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
<link href="../jq/ui/css/jquery-ui-1.7.3.custom.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<link href="../css/popup.css" rel="stylesheet" type="text/css" media="all" />
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
				<input type="hidden" id="feedbackMode"      name="feedbackMode"      value="{$params.feedbackMode}">
				<input type="hidden" id="execID"            name="execID"            value="{$params.execID}">
				<input type="hidden" id="groupID"           name="groupID"           value="{$smarty.session.groupID}">
				<input type="hidden" id="studyInstanceUID"  name="studyInstanceUID"  value="{$params.studyInstanceUID}">
				<input type="hidden" id="seriesInstanceUID" name="seriesInstanceUID" value="{$params.seriesInstanceUID}">
				<input type="hidden" id="cadName"           name="cadName"           value="{$params.cadName}">	
				<input type="hidden" id="version"           name="version"           value="{$params.version}">
				<input type="hidden" id="colorSet"          name="colorSet"          value="{$smarty.session.colorSet}">
				<input type="hidden" id="ticket"            name="ticket"            value="{$params.ticket|escape}">
				<input type="hidden" id="registTime"        name="registTime"        value="{$registTime}">
				<input type="hidden" id="interruptFlg"      name="interruptFlg"      value="{$params.interruptFlg}">
				<input type="hidden" id="srcList"           name="srcList"           value="{$params.srcList}">
				<input type="hidden" id="tagStr"            name="tagStr"            value="{$params.tagStr}">
				<input type="hidden" id="tagEnteredBy"      name="tagEnteredBy"      value="{$params.tagEnteredBy}">

				<div id="cadResult">

					<h2>CAD Result&nbsp;&nbsp;[{$params.cadName} v.{$params.version} ID:{$params.execID}]</h2>

					<div class="headerArea">
						<div class="fl-l"><a href="../study_list.php?mode=patient&encryptedPtID={$params.encryptedPtID}">{$params.patientName}&nbsp;({$params.patientID})&nbsp;{$params.age}{$params.sex}</a></div>
						<div class="fl-l"><img src="../img_common/share/path.gif" /><a href="../series_list.php?mode=study&studyInstanceUID={$params.studyInstanceUID}">{$params.studyDate}&nbsp;({$params.studyID})</a></div>
						<div class="fl-l"><img src="../img_common/share/path.gif" />{$params.modality},&nbsp;{$params.seriesDescription}&nbsp;({$params.seriesID})</div>
					</div>
			
					<div class="hide-on-guest">
						<input type="radio" name="change-mode1" value="Personal mode" class="radio-to-button-l" label="Personal mode"  onclick="ChangeFeedbackMode('personal');" {if $params.feedbackMode=='personal'}checked="checked"{/if} />
						<input type="radio" name="change-mode1" value="Consensual mode" class="radio-to-button-l" label="Consensual mode" onclick="ChangeFeedbackMode('consensual');" {if $params.feedbackMode=='consensual'}checked="checked"{/if}{if $smarty.session.consensualFBFlg==0 || ($params.feedbackMode == "personal" && $consensualFBFlg == 0)} disabled="disabled"{/if} />
						<div class="fl-l" style="margin-left:5px;">{$registMsg}</div>
					</div>

					<!-- Display results -->
					<div class="fl-clr">
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
						<input type="hidden" id="interruptFlg" name="interruptFlg" value="{$params.interruptFlg}">
						<input type="hidden" id="registFlg"    name="registFlg"    value="{$params.registFlg}">

						<div class="hide-on-guest fl-clr" style="width: 800px;">
							<div class="ml40 js-personal-or-consensual {$params.feedbackMode}" style="display:inline;">
								{$scoringHtml}
							</div>
							<p class="fl-r" style="width:255px;">
								<input name="" type="button" value="Registration of feedback" class="fs-l form-btn registration" onclick="RegistFeedback('{$params.feedbackMode}');" {if $registTime != ""}disabled="disabled"{/if}/>
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
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
</html>
