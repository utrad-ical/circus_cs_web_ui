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

{literal}
<script language="Javascript">
<!--
	function RedrawRocCurve(execID, inputPath)
	{
		$.post("plugin_template/redraw_roc_curve.php",
			 	{ execID: execID,
			 	  curveType: $(".tab-content input[name='curveType']:checked").val(),
			      pendigType: $(".tab-content input[name='pendigType']:checked").val(),
			      inputPath:  inputPath},
			   	function(data){
			 		$("#rocGraph").attr("src", data.imgFname);
			 		$("#tpNum").html(data.tpNum);
			 		$("#fpNum").html(data.fpNum);
			 		$("#underRocArea").html(data.underRocArea);
				}, "json");
	}
-->
</script>
{/literal}

<link rel="shortcut icon" href="../favicon.ico" />

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
				<li><a href="{if $params.srcList!="" && $smarty.session.listAddress!=""}{$smarty.session.listAddress}{else}research_list.php{/if}" class="btn-tab" title="Research list">Research list</a></li>
				<li><a href="#" class="btn-tab" title="list" style="background-image: url(../img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">Research result</a></li>
			</ul>
			<p class="add-favorite"><a href="#" title="favorite"><img src="../img_common/btn/favorite.jpg" width="100" height="22" alt="favorite"></a></p>
		</div><!-- / .tabArea END -->

		<div class="tab-content">
			<h2>Research result&nbsp;&nbsp;[{$params.pluginName} v.{$params.version} ID:{$params.execID}]</h2>
			<div class="headerArea">Executed at: {$params.executedAt}</div>

			<table>
				<tr>
					<td>
			 			<table>
							<tr>
								<td width="360" height="320"><img id="rocGraph" src="{$curveFnameWeb}" width="360" height="320" /></td>
						 	</tr>
						 </table>
					</td>
					<td>
						<table>
							<tr><td></td></tr>
							<tr>
								<td class="detail-panel">
									<table class="detail-tbl">
										<tr>
											<th style="width:11em;"><span class="trim01">Number of cases</span></th>
											<td>{$data.caseNum}</td>
						 				</tr>
									 	<tr>
											<th style="width:11em;"><span class="trim01">Number of TP</span></th>
											<td><span id="tpNum">{$data.tpNum}</span></td>
			 							</tr>
			 							<tr>
			 								<th style="width:11em;"><span class="trim01">Number of FP</span></th>
			 								<td><span id="fpNum">{$data.fpNum}</span></td>
			 							</tr>
			 							<tr>
			 								<th style="width:11em;"><span class="trim01">Az</span></th>
			 								<td><span id="underRocArea">{$data.underRocArea}</span></td>
										</tr>
									</table>
			 					</td>
							</tr>
			 				<tr>
								<td height=10></td>
							</tr>
							<tr>
								<td class="detail-panel">
									<table class="detail-tbl">
										<tr>
			 								<th style="width: 6.0em;"><span class="trim01">Curve</span></th>
											<td>
												<label><input name="curveType" type="radio" value="0" checked="checked" />ROC</label>
												<label><input name="curveType" type="radio" value="1" />FROC</label>
											</td>
			 							</tr>
			 							<tr>
			 								<th><span class="trim01">Pending</span></th>
			 								<td>
			 									<label><input name="pendigType" type="radio" value="0" checked="checked" />as FP</label>
											 	<label><input name="pendigType" type="radio" value="1" />as TP</label>
											</td>
										</tr>
									</table>
									<div class="al-l mt10 ml20" style="width: 100%;">
				 						<input type="button" value="Redraw" class="w100 form-btn" onclick="RedrawRocCurve({$params.execID},'{$params.resPath}');" />
			 						</div>
			 					</td>
							</tr>
			 			</table>
			 		</td>
				</tr>
			 </table>

			<!-- Tag area -->
			{include file='cad_results/plugin_tag_area.tpl'}

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

