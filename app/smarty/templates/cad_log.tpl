{capture name="require"}
jq/ui/jquery-ui-1.7.3.min.js
js/search_panel.js
js/list_tab.js
js/edit_tag.js
js/radio-to-button.js
jq/ui/css/jquery-ui-1.7.3.custom.css
css/popup.css
{/capture}

{include file="header.tpl" body_class="cad-log" require=$smarty.capture.require}

<!-- ***** TAB ***** -->
<div class="tabArea">
	<ul>
		{if $params.mode=='today'}
			<li><a href="series_list.php?mode=today" class="btn-tab" title="Today's series">Today's series</a></li>
		{/if}
		<li><a href="" class="btn-tab" title="{if $params.mode=='today'}Today's CAD{else}CAD log{/if}" style="background-image: url(img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">{if $params.mode=='today'}Today's CAD{else}CAD log{/if}</a></li>
	</ul>
	{if $params.mode!='today'}<p class="add-favorite"><a href="" title="favorite"><img src="img_common/btn/favorite.jpg" width="100" height="22" alt="favorite"></a></p>{/if}
	</ul>

</div>
<!-- / .tabArea END -->

<div class="tab-content">
	{if $params.mode=='today'}
		<div id="todays_cad">
			<!-- <h2>Today's CAD</h2> -->
	{else}
		<div id="cad_log">
			<!-- <h2>CAD log</h2> -->
	{/if}

		<!-- ***** Search ***** -->
			<form name="" onsubmit="return false;">
				<input type="hidden" id="mode"                     value="{$params.mode|escape}" />
				<input type="hidden" id="hiddenFilterPtID"         value="{$params.filterPtID|escape}" />
				<input type="hidden" id="hiddenFilterPtName"       value="{$params.filterPtName|escape}" />
				<input type="hidden" id="hiddenFilterSex"          value="{$params.filterSex|escape}" />
				<input type="hidden" id="hiddenFilterAgeMin"       value="{$params.filterAgeMin|escape}" />
				<input type="hidden" id="hiddenFilterAgeMax"       value="{$params.filterAgeMax|escape}" />
				<input type="hidden" id="hiddenFilterModality"     value="{$params.filterModality|escape}" />
				<input type="hidden" id="hiddenFilterCAD"          value="{$params.filterCAD|escape}" />
				<input type="hidden" id="hiddenFilterVersion"      value="{$params.filterVersion|escape}" />
				<input type="hidden" id="hiddenFilterCadID"        value="{$params.filterCadID|escape}" />
				<input type="hidden" id="hiddenFilterTP"           value="{$params.filterTP|escape}" />
				<input type="hidden" id="hiddenFilterFN"           value="{$params.filterFN|escape}" />
				<input type="hidden" id="hiddenFilterPersonalFB"   value="{$params.personalFB|escape}" />
				<input type="hidden" id="hiddenFilterConsensualFB" value="{$params.consensualFB|escape}" />
				<input type="hidden" id="hiddenSrDateFrom"         value="{$params.srDateFrom|escape}" />
				<input type="hidden" id="hiddenSrDateTo"           value="{$params.srDateTo|escape}" />
				<input type="hidden" id="hiddenSrTimeTo"           value="{$params.srTimeTo|escape}" />
				<input type="hidden" id="hiddenCadDateFrom"        value="{$params.cadDateFrom|escape}" />
				<input type="hidden" id="hiddenCadDateTo"          value="{$params.cadDateTo|escape}" />
				<input type="hidden" id="hiddenCadTimeTo"          value="{$params.cadTimeTo|escape}" />
				<input type="hidden" id="hiddenShowing"            value="{$params.showing|escape}" />

				<input type="hidden" id="orderMode"        value="{$params.orderMode|escape}" />
				<input type="hidden" id="orderCol"         value="{$params.orderCol|escape}" />

				{include file='cad_search_panel.tpl'}
			</form>
		<!-- / Search End -->

		<!-- ***** List ***** -->

		<div class="serp">
			Showing {$params.startNum|escape} - {$params.endNum|escape} of {$params.totalNum|escape} results
		</div>
		<table class="col-tbl" style="width: 100%;">
			<thead>
				<tr>
					<th rowspan="2">
						{if $params.orderCol=='PatientID'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfCADList('PatientID', '{if $params.orderCol=='PatientID' && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Patient ID</a></span>
					</th>

					<th rowspan="2">
						{if $params.orderCol=='Name'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfCADList('Name', '{if $params.orderCol=='Name' && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Name</a></span>
					</th>

					<th rowspan="2">
						{if $params.orderCol=='Age'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfCADList('Age', '{if $params.orderCol=='Age' && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Age</a></span>
					</th>

					<th rowspan="2">
						{if $params.orderCol=='Sex'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfCADList('Sex', '{if $params.orderCol=='Sex' && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Sex</a></span>
					</th>

					<th colspan="2">
						{if $params.orderCol=='Series'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfCADList('Series', '{if $params.orderCol=='Series' && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Series</a></span>

					<th rowspan="2">
						{if $params.orderCol=='CAD'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfCADList('CAD', '{if $params.orderCol=='CAD' && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">CAD</a></span>
					</th>

					<th rowspan="2">
						{if $params.orderCol=='CADdate'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfCADList('CADdate', '{if $params.orderCol=='CADdate' && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">CAD date</a></span>
					</th>

					{if $smarty.session.colorSet != "guest"}<th rowspan="2">Executed<br />by</th>{/if}

					<th rowspan="2">Result</th>

					{if $params.mode=='today'}
						{if $smarty.session.colorSet == "admin"}
							<th colspan="2">Feedback</th>
						{elseif $smarty.session.colorSet == "user" && $smarty.session.personalFBFlg == 1}
							<th rowspan="2">Personal<br />feedback</th>
						{/if}
					{else}
						{if $smarty.session.colorSet == "admin" || ($smarty.session.colorSet == "user" && $smarty.session.personalFBFlg == 1)}
							<th colspan="3">Feedback</th>
						{else}
							<th colspan="2">Feedback</th>
						{/if}
					{/if}
				</tr>
				<tr>
					<th>Date</th>
					<th>Time</th>

					{if $params.mode=='today'}
						{if $smarty.session.colorSet == "admin"}
							<th>Personal</th>
							<th>Cons.</th>
						{/if}
					{else}
						{if $smarty.session.colorSet == "admin" || ($smarty.session.colorSet == "user" && $smarty.session.personalFBFlg == 1)}
							<th>Personal</th>
						{/if}
						<th>TP</th>
						<th>FN</th>
					{/if}
				</tr>
			</thead>
			<tbody>
				{foreach from=$data item=item name=cnt}

					<tr id="row{$smarty.foreach.cnt.iteration}" {if $smarty.foreach.cnt.iteration%2==0}class="column"{/if}>

						<td class="al-l"><a href="cad_log.php?filterPtID={$item[0]|escape}">{$item[0]|escape}</td>
						<td class="al-l">{$item[1]|escape}</td>
						<td>{$item[2]|escape}</td>
						<td>{$item[3]|escape}</td>
						<td>{$item[4]|escape}</td>
						<td>{$item[5]|escape}</td>
						<td>{$item[6]|escape}</td>
						<td>{$item[7]|escape}</td>
						{if $smarty.session.colorSet != "guest"}<td>{$item[8]|escape}</td>{/if}
						<td><input name="" type="button" value="show" class="s-btn form-btn" onclick="ShowCADResultFromCADLog({$item[9]|escape}, {$smarty.session.personalFBFlg});" /></td>

						{if $params.mode=='today'}
							{if $smarty.session.colorSet == "admin"}
								<td>{$item[10]}</td>
								<td>{$item[11]}</td>
							{elseif $smarty.session.colorSet == "user" && $smarty.session.personalFBFlg == 1}
								<td>{$item[10]}</td>
							{/if}
						{else}
							<td>{$item[10]}</td>
							<td>{$item[11]}</td>
							{if $smarty.session.colorSet == "admin" || ($smarty.session.colorSet == "user" && $smarty.session.personalFBFlg == 1)}
								<td>{$item[12]}</td>
							{/if}
						{/if}
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

	<!-- / List -->
	</div> <!-- / CAD log End -->

	<div class="al-r fl-clr">
		<p class="pagetop"><a href="#page">page top</a></p>
	</div>

</div><!-- / .tab-content END -->
