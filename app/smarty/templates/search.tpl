{capture name="require"}
jq/ui/jquery-ui-1.7.3.min.js
js/search_panel.js
jq/ui/css/jquery-ui-1.7.3.custom.css
{/capture}

{capture name="extra"}
<!-- Search div display control script-->
{literal}
<script type="text/javascript">
	$(function(){
		$("#patientSearch,#studySearch,#cadSearch,#researchSearch#groupResearchSearch").attr("style", "display:none;").find('select').hide().end();
	});
</script>
{/literal}
{/capture}

{include file="header.tpl" body_class="search spot"
	head_extra=$smarty.capture.extra require=$smarty.capture.require}

<h2>Search</h2>

<ul class="inline mt5 ml10">
	<li><a href="javascript:void(0)"  class="btn btn-search" title="Patient" id="apatient">Patient</a></li>
	<li><a href="javascript:void(0)"  class="btn btn-search" title="Study" id="astudy">Study</a></li>
	<li><a href="javascript:void(0)"  class="btn selected-btn-search" title="Series" id="aseries">Series</a></li>
	<li><a href="javascript:void(0)"  class="btn btn-search" title="CAD" id="acad">CAD</a></li>
{*	{if $smarty.session.researchFlg==1}
					<li><a href="javascript:void(0)"  class="btn btn-search" title="Research" id="aresearch">Research</a></li>
					<li><a href="javascript:void(0)"  class="btn btn-search" title="GroupResearch" id="agresearch">Group research</a></li>
				{/if}
*}
</ul>

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

{include file="footer.tpl"}