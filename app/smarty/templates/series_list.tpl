{capture name="require"}
jq/ui/jquery-ui.min.js
js/jquery.daterange.js
js/search_panel.js
js/list_tab.js
jq/ui/theme/jquery-ui.custom.css
{/capture}
{include file="header.tpl" body_class="series-list" require=$smarty.capture.require}

<!-- ***** TAB ***** -->
<div class="tabArea">
	<ul>
		<li><a href="" class="btn-tab" title="{if $params.mode=='today'}Today's series{else}Series list{/if}" style="background-image: url(img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">{if $params.mode=='today'}Today's series{else}Series list{/if}</a></li>
		{if $params.mode=='today'}
			<li><a href="cad_log.php?mode=today" class="btn-tab" title="Today's CAD">Today's CAD</a></li>
		{/if}
	</ul>
</div><!-- / .tabArea END -->

<div class="tab-content">

	{if $params.mode=='today'}
		<div id="todays_series">
			<!-- <h2>Today's series</h2> -->
	{else}
		<div id="series_list">
			<!-- <h2>Series list</h2> -->
	{/if}

	<!-- ***** Search ***** -->
		<form name="" onsubmit="return false;">
			<input type="hidden" id="mode"                      value="{$params.mode|escape}" />
			<input type="hidden" id="studyInstanceUID"          value="{$params.studyInstanceUID|escape}" />
			<input type="hidden" id="hiddenFilterPtID"          value="{$params.filterPtID|escape}" />
			<input type="hidden" id="hiddenFilterPtName"        value="{$params.filterPtName|escape}" />
			<input type="hidden" id="hiddenFilterSex"           value="{$params.filterSex|escape}" />
			<input type="hidden" id="hiddenFilterAgeMin"        value="{$params.filterAgeMin|escape}" />
			<input type="hidden" id="hiddenFilterAgeMax"        value="{$params.filterAgeMax|escape}" />
			<input type="hidden" id="hiddenFilterModality"      value="{$params.filterModality|escape}" />
			<input type="hidden" id="hiddenFilterSrDescription" value="{$params.filterSrDescription|escape}" />
			<input type="hidden" id="hiddenSrDateKind"          value="{$params.srDateKind|escape}" />
			<input type="hidden" id="hiddenSrDateFrom"          value="{$params.srDateFrom|escape}" />
			<input type="hidden" id="hiddenSrDateTo"            value="{$params.srDateTo|escape}" />
			<input type="hidden" id="hiddenSrTimeTo"            value="{$params.srTimeTo|escape}" />
			<input type="hidden" id="hiddenShowing"             value="{$params.showing|escape}" />

			<input type="hidden" id="orderMode"        value="{$params.orderMode|escape}" />
			<input type="hidden" id="orderCol"         value="{$params.orderCol|escape}" />

			{if $smarty.session.dataDeleteFlg}<input type="hidden" id="ticket" value="{$params.ticket|escape}" />{/if}


			{include file='series_search_panel.tpl'}
		</form>
	<!-- / Search End -->

	<!-- ***** List ***** -->
		<div class="serp">
			{if $params.startNum>0 && $params.endNum>0}Showing {$params.startNum} - {$params.endNum} of {$params.totalNum} results{/if}
		</div>

		<table class="col-tbl" style="width: 100%;">
			<thead>
				<tr>
					{if $smarty.session.dataDeleteFlg}<th><input type="checkbox" onclick="$(this.parentNode.parentNode.parentNode.parentNode).find('input[type=\'checkbox\']').attr('checked', this.checked)" /></th>{/if}
					<th>
						{if $params.orderCol=='PatientID'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfSeriesList('PatientID', '{if $params.orderCol=="PatientID" && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Patient ID</a></span>
					</th>

					<th>
						{if $params.orderCol=='Name'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfSeriesList('Name', '{if $params.orderCol=="Name" && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Name</a></span>
					</th>

					<th>
						{if $params.orderCol=='Age'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfSeriesList('Age', '{if $params.orderCol=="Age" && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Age</a></span>
					</th>

					<th>
						{if $params.orderCol=='Sex'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfSeriesList('Sex', '{if $params.orderCol=="sex" && $params.orderMode=="SSC"}DESC{else}ASC{/if}');">Sex</a></span>
					</th>

					{if $params.mode!='today'}
						<th>
							{if $params.orderCol=='Date'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfSeriesList('Date', '{if $params.orderCol=="Date" && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Date</a></span>
						</th>
						<th>Time</th>
					{else}
						<th>
							{if $params.orderCol=='Time'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfSeriesList('Time', '{if $params.orderCol=="Time" && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Time</a></span>
						</th>
					{/if}

					<th>
						{if $params.orderCol=='SeriesID'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfSeriesList('SeriesID', '{if $params.orderCol=="SeriesID" && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">ID</a></span>
					</th>

					<th>
						{if $params.orderCol=='Modality'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfSeriesList('Modality', '{if $params.orderCol=="Modality" && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Modality</a></span>
					</th>

					<th>
						{if $params.orderCol=='ImgNum'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfSeriesList('ImgNum', '{if $params.orderCol=="ImgNum" && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Img.</a></span>
					</th>

					<th>
						{if $params.orderCol=='SeriesDesc'}<span style="color:#fff; font-size:10px">{if $params.orderMode=="ASC"}&#9650;{else}&#9660;{/if}</span>{/if}<span><a onclick="ChangeOrderOfSeriesList('SeriesDesc', '{if $params.orderCol=="SeriesDesc" && $params.orderMode=="ASC"}DESC{else}ASC{/if}');">Desc.</a></span>
					</th>

					<th>Detail</th>
					<th>CAD</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$data item=item name=cnt}
					<tr id="row{$smarty.foreach.cnt.iteration}" {if $smarty.foreach.cnt.iteration%2==0}class="column"{/if}>
						{if $smarty.session.dataDeleteFlg}<td><input type="checkbox" name="sidList[]" value="{$item[0]|escape}"></td>{/if}
						<td class="al-l"><a href="series_list.php?filterPtID={$item[3]|escape}">{$item[3]|escape}</a></td>
						<td class="al-l">{$item[4]|escape}</td>
						<td>{$item[5]|escape}</td>
						<td>{$item[6]|escape}</td>
						{if $params.mode!='today'}<td>{$item[7]|escape}</td>{/if}
						<td>{$item[8]|escape}</td>
						<td>{$item[9]|escape}</td>
						<td>{$item[10]|escape}</td>
						<td class="al-r">{$item[11]|escape}</td>
						<td class="al-l">{$item[12]|escape}</td>
						<td><input name="" type="button" value="show" class="s-btn form-btn" onclick="ShowSeriesDetail({$item[0]|escape});"/></td>

						{* ----- CAD column ----- *}
						<td class="al-l">
							{if $item[13] > 0}
								{* ----- pull-down menu ----- *}
								<select id="cadMenu{$smarty.foreach.cnt.iteration}" onchange="ChangeCADMenu({if $params.mode=='today'}'todaysSeriesList'{else}'seriesList'{/if},'{$smarty.foreach.cnt.iteration}', this.selectedIndex)" style="width:100px;">
									{section name=i start=0 loop=$item[13]}

										{assign var="i"         value=$smarty.section.i.index}
										{assign var="optionFlg" value=0}

										{if $item[14][$i][2] || $item[14][$i][3]}

											<option value="{$item[14][$i][0]}^{$item[14][$i][1]}^{$item[14][$i][3]}^{$item[14][$i][4]}^{$item[14][$i][5]}"

											{*{if $item[14][$i][2] && $optionFlg == 0 && $item[14][$i][6] == $item[11]}
										 		selected="selected"
								 				{assign var="optionFlg" value=1}
											{/if}*}
											>
											{$item[14][$i][0]} v.{$item[14][$i][1]}</option>
										{/if}
									{/section}
								</select>

								{if $currentUser->hasPrivilege('cadExec')}
									<input type="button" id="execButton{$smarty.foreach.cnt.iteration}" name="execButton{$smarty.foreach.cnt.iteration}" value="&nbsp;Exec&nbsp;" onclick="RegistCADJob('{$smarty.foreach.cnt.iteration}', '{$item[1]}', '{$item[2]}');" class="s-btn form-btn"{if $item[14][$selectedID][2] || $item[14][0][3]>0} style="display:none;"{/if} />
								{/if}

								<input type="button" id="resultButton{$smarty.foreach.cnt.iteration}" name="resultButton{$smarty.foreach.cnt.iteration}" value="Result" onclick="ShowCADResultFromSeriesList({$smarty.foreach.cnt.iteration}, {$smarty.session.personalFBFlg});" class="s-btn form-btn"{if $item[14][0][3]<4} style="display:none;"{/if} />
								<div id="cadInfo{$smarty.foreach.cnt.iteration}">
									{if $item[14][0][4] != ''}Executed at {$item[14][0][4]|escape}{elseif 0<$item[14][0][3] && $item[14][0][3]<4}Registered in CAD job list{elseif $item[14][0][3]==-1}<span style="color:#f00;">Failed to execute</span>{elseif $params.mode == 'today'}<span style="color:#00f;">Not executed</span>{else}&nbsp;{/if}
								</div>

							{else}
								&nbsp;
							{/if}
						</td>
						{* ----- End of CAD column ----- *}
					</tr>
				{/foreach}
			</tbody>
		</table>

		{if $smarty.session.dataDeleteFlg}
			<div class="mt10 ml10">
				<input type="button" value="delete" class="s-btn form-btn"  onclick="DeleteData('series');" />
			</div>
		{/if}

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
	</div>
<!-- / Series list END -->

	<div class="al-r fl-clr">
		<p class="pagetop"><a href="#page">page top</a></p>
	</div>

</div><!-- / .tab-content END -->

{include file="footer.tpl"}