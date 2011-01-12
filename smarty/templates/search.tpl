<?xml version="1.0" encoding="utf-8"?>
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
<script language="javascript" type="text/javascript" src="jq/ui/jquery-ui-1.7.3.min.js"></script>
<script language="javascript" type="text/javascript" src="jq/jq-btn.js"></script>
<script language="javascript" type="text/javascript" src="js/hover.js"></script>
<script language="javascript" type="text/javascript" src="js/viewControl.js"></script>
<script language="javascript" type="text/javascript" src="js/search_panel.js"></script>
<!-- Search div display control script-->
{literal}
<script type="text/javascript">
	$(function(){
		$("#patientSearch,#studySearch,#cadSearch,#researchSearch#groupResearchSearch").attr("style", "display:none;").find('select').hide().end();
	});

</script>
{/literal}	


<link rel="shortcut icon" href="favicon.ico" />
<!-- InstanceBeginEditable name="head" -->
<link href="./jq/ui/css/jquery-ui-1.7.3.custom.css" rel="stylesheet" type="text/css" media="all" />
<link href="./css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />
<!-- InstanceEndEditable -->
<!-- InstanceParam name="class" type="text" value="search" -->
</head>

<body class="search spot">
<div id="page">
	<div id="container" class="menu-back">
		<div id="leftside">
			{include file='menu.tpl'}
		</div><!-- / #leftside END -->
		<div id="content">
<!-- InstanceBeginEditable name="content" -->
			<h2>Search</h2>
			

			<ul class="inline mt5 ml10">
				<li><a href="javascript:void(0)"  class="btn btn-search" title="Patient" id="apatient">Patient</a></li>
				<li><a href="javascript:void(0)"  class="btn btn-search" title="Study" id="astudy">Study</a></li>
				<li><a href="javascript:void(0)"  class="btn selected-btn-search" title="Series" id="aseries">Series</a></li>
				<li><a href="javascript:void(0)"  class="btn btn-search" title="CAD" id="acad">CAD</a></li>
{*				{if $smarty.session.researchFlg==1}
					<li><a href="javascript:void(0)"  class="btn btn-search" title="Research" id="aresearch">Research</a></li>
					<li><a href="javascript:void(0)"  class="btn btn-search" title="GroupResearch" id="agresearch">Group research</a></li>
				{/if}
*}			</ul>

			<br class="fl-clr">
			
		<!-- ***** Search panel ******************************************** -->
		
		<!-- ***** Patient ***** -->
			<form name="" onsubmit="return false;">
				{include file='patient_search_panel.tpl'}
			</form>
		<!-- / Patient END -->

		<!-- ***** Study ***** -->
			<form name="" onsubmit="return false;">
				{include file='study_search_panel.tpl'}
			</form>
		<!-- / Study END -->

		<!-- ***** Series ***** -->
			<form name="" onsubmit="return false;">
				{include file='series_search_panel.tpl'}
			</form>
		<!-- / Series END -->
	
		<!-- ***** CAD ***** -->
			<form name="" onsubmit="return false;">
				{include file='cad_search_panel.tpl'}
			</form>
		<!-- / CAD END -->

		<!-- ***** Reserach ***** -->
		{*	<form name="" onsubmit="return false;">
				{include file='research_search_panel.tpl'}
			</form>*}
		<!-- / Research END -->

		<!-- ***** Group reserach ***** -->
		{*	<form name="" onsubmit="return false;">
				{include file='group_research_search_panel.tpl'}
			</form>*}
		<!-- / Group research END -->

		<!-- <div class="al-r">
			<p class="pagetop"><a href="#page">page top</a></p>
		</div> -->
<!-- InstanceEndEditable -->
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
<!-- InstanceEnd --></html>
