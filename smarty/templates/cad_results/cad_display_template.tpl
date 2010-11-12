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
<script language="javascript" type="text/javascript" src="../js/edit_tag.js"></script>

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
		<div class="tabArea">
			<ul>
				{if $params.srcList!="" && $smarty.session.listAddress!=""}
					<li><a href="../{$smarty.session.listAddress}" class="btn-tab" title="{$params.listTabTitle}">{$params.listTabTitle}</a></li>
				{else}
					<li><a href="../series_list.php?mode=study&studyInstanceUID={$params.studyInstanceUID}" class="btn-tab" title="Series list">Series list</a></li>
				{/if}
				<li><a href="#" class="btn-tab" title="list" style="background-image: url(../img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">CAD result</a></li>
			</ul>
			<p class="add-favorite"><a href="#" title="favorite"><img src="../img_common/btn/favorite.jpg" width="100" height="22" alt="favorite"></a></p>
		</div><!-- / .tabArea END -->

		<div class="tab-content">
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
			
			<!-- / Result -->

		</div><!-- / .tab-content END -->

		<!-- darkroom button -->
		{include file='darkroom_button.tpl'}

<!-- InstanceEndEditable -->
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
<!-- InstanceEnd --></html>

