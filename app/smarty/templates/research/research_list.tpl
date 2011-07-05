{capture name="extra"}
<script type="text/javascript">;
<!--
{literal}

function SearchResearchList()
{
	var address = "research_list.php";
	var conditionNum = 0;

	var pluginName  = $(".search-panel select[name='researchMenu'] option:selected").text();
	var resDateFrom = $(".search-panel input[name='resDateFrom']").val();
	var resDateTo   = $(".search-panel input[name='resDateTo']").val();
	var filterTag   = $(".search-panel input[name='filterTag']").val();

	if(pluginName != "")
	{
		address += ((conditionNum == 0) ? '?' : '&') + 'pluginName=' + pluginName;
		conditionNum++;
	}

	if(resDateFrom != "")
	{
		address += ((conditionNum == 0) ? '?' : '&') + 'resDateFrom=' + resDateFrom;
		conditionNum++;
	}

	if(resDateTo != "")
	{
		address += ((conditionNum == 0) ? '?' : '&') + 'resDateTo=' + resDateTo;
		conditionNum++;
	}

	if(filterTag != "")
	{
		address += ((conditionNum == 0) ? '?' : '&') + 'filterTag=' + filterTag;
		conditionNum++;
	}

	address += ((conditionNum ==0) ? '?' : '&') + 'showing=' + $(".search-panel select[name='showing']").val();

	location.href=address;
}

function ShowResearchResult(jobID)
{
	location.href = "show_research_result.php?jobID=" + jobID + "&srcList=resList";
}

function ResetSearchBlock()
{
	$(".search-panel input[name*='resDate'], .search-panel input[name='filterTag']").removeAttr("value");
	$(".search-panel select[name='researchMenu'], .search-panel select[name='showing']").children().removeAttr("selected");
}

$(function() {
	$("#resSearch input[name^='resDate']").datepicker({
			showOn: "button",
			buttonImage: "../images/calendar_view_month.png",
			buttonImageOnly: true,
			buttonText:'',
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd',
			maxDate: 0}
		);

	$("#resSearch input[name='resDateFrom']").datepicker('option', {onSelect: function(selectedDate, instance){
					date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat,
						                          selectedDate, instance.settings );
					$("#resSearch input[name='resDateTo']").datepicker("option", "minDate", date);
				}});

	$("#resSearch input[name='resDateTo']").datepicker('option', {onSelect: function(selectedDate, instance){
					date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat,
						                          selectedDate, instance.settings );
					$("#resSearch input[name='resDateFrom']").datepicker("option", "maxDate", date);
				}});
});

{/literal}
-->
</script>
{/capture}
{capture name="require"}
jq/ui/jquery-ui-1.7.3.min.js
jq/ui/css/jquery-ui-1.7.3.custom.css
{/capture}
{include file="header.tpl" body_class="research"
	head_extra=$smarty.capture.extra require=$smarty.capture.require}

<div id="researchListTab" class="tabArea">
	<ul>
		<li><a href="#" id="listTab" class="btn-tab" title="list" style="background-image: url(../img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">Research list</a></li>
		{if $smarty.session.researchExecFlg}
			<li><a href="research_job.php" class="btn-tab" title="job">Research job</a></li>
		{/if}
	</ul>
</div><!-- / .tabArea END -->

<div class="tab-content">
	<form id="form1" name="form1">

	<!-- ***** Search conditions ***** -->
	<div id="resSearch" class="search-panel">
		<h3>Search</h3>
		<div class="p20">
			<table class="search-tbl">
				<tr>
					<th style="width: 10em;"><span class="trim01">Research</span></th>
					<td style="width: 220px;">
						<select id="researchMenu" name="researchMenu" style="width: 150px;">
								<option value="all">all</option>
							{foreach from=$pluginList item=item}
								<option value="{$item[0]}">{$item[0]} v.{$item[1]}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr>
	    	        <th><span class="trim01">Research date</span></th>
					<td>
						<input name="resDateFrom" type="text" style="width:72px;" value="{$params.resDateFrom|escape}" />
						-&nbsp;
						<input name="resDateTo" type="text" style="width:72px;" value="{$params.resDateTo|escape}" />
					</td>
				</tr>
				<tr>
   					<th><span class="trim01">Tag</span></th>
					<td><input name="filterTag" type="text" style="width:160px;" value="{$params.filterTag|escape}" /></td>
				</tr>
				<tr>
					<th><span class="trim01">Showing</span></th>
					<td>
						<select name="showing" style="width: 50px;">
							<option value="10"  {if $params.showing=="10"}selected="selected"{/if}>10</option>
							<option value="25"  {if $params.showing=="25"}selected="selected"{/if}>25</option>
							<option value="50"  {if $params.showing=="50"}selected="selected"{/if}>50</option>
							<option value="all" {if $params.showing=="all"}selected="selected"{/if}>all</option>
						</select>
					</td>
				</tr>
			</table>
			<div class="al-l mt10 ml20" style="width: 100%;">
				<input name="" type="button" value="Search" class="w100 form-btn" onclick="SearchResearchList();" />
				<input name="" type="button" value="Reset" class="w100 form-btn"  onclick="ResetSearchBlock();" />
			</div>
		</div><!-- / .m20 END -->
	</div><!-- / .search-panel END -->
	<!-- / Search conditions END -->

	<!-- ***** List ***** -->
	<div class="serp">
		Showing {$params.startNum} - {$params.endNum} of {$params.totalNum} results
	</div>

	<table class="col-tbl" style="width:100%;">
		<thead>
			<tr>
				<th>ID</th>
				<th>Research</th>
				<th>Executed at</th>
				<!-- <th>Tag</th> -->
				{if $smarty.session.colorSet != "guest"}<th>Executed by</th>{/if}
				<th>&nbsp;</th>
			</tr>
		</thead>

		<tbody>
			{foreach from=$data item=item}
				<tr>
					<td>{$item[0]|escape}</td>
                	<td>{$item[1]|escape}</td>
                	<td>{$item[2]|escape}</td>
					{if $smarty.session.colorSet != "guest"}<td>{$item[3]|escape}</td>{/if}
					<td><input name="" type="button" value="show" class="s-btn form-btn" onclick="ShowResearchResult('{$item[0]|escape}');" /></td>
				</tr>
			{/foreach}
		</tbody>
	</table>

	{* ------ Hooter with page list --- *}
	<div id="serp-paging" class="al-c mt10">
		{if $params.maxPageNum > 1}
			{if $params.pageNum > 1}
				<div><a href="{$params.pageAddress}&pageNum={$params.pageNum-1}"><span style="color: red">&laquo;</span>&nbsp;Previous</a></div>
			{/if}

			{if $params.startPageNum > 1}
				<div><a href="{$params.pageAddress}&pageNum=1">1</a></div>
				{if $params.startPageNum > 2}<div>...</div>{/if}
			{/if}

			{section name=i start=$params.startPageNum loop=$params.endPageNum+1}
				{assign var="i" value=$smarty.section.i.index}

	    		{if $i==$params.pageNum}
					<div><span style="color: red" class="fw-bold">{$i}</span></div>
				{else}
					<div><a href="{$params.pageAddress}&pageNum={$i}">{$i}</a></div>
				{/if}
			{/section}

			{if $params.endPageNum < $params.maxPageNum}
				{if $params.maxPageNum-1 > $params.endPageNum}<div>...</div>{/if}
				<div><a href="{$params.pageAddress}&pageNum={$params.maxPageNum}">{$params.maxPageNum}</a></div>
			{/if}

			{if $params.pageNum < $params.maxPageNum}
				<div><a href="{$params.pageAddress}&pageNum={$params.pageNum+1}">Next&nbsp;<span style="color: red">&raquo;</span></a></div>
			{/if}
		{/if}
	</div>
	{* ------ / Hooter end --- *}

	</form>

	<div class="al-r fl-clr">
		<p class="pagetop"><a href="#page">page top</a></p>
	</div>

</div><!-- / .tab-content END -->
{include file="footer.tpl"}