<div id="seriesSearch" class="search-panel">
	<h3>Search</h3>
	<div class="p20">
		<table class="search-tbl">
			<tr>
				<th style="width: 8.5em;"><span class="trim01">Series date</span></th>
				<td style="width: 200px;">
					<input name="srDateFrom" type="text" style="width:72px;" value="{$params.srDateFrom}" {if $params.mode=='today'}disabled="disabled"{/if} />
					-&nbsp;
					<input name="srDateTo" type="text" style="width:72px;" value="{$params.srDateTo}" {if $params.mode=='today'}disabled="disabled"{/if} />
				</td>
				<th style="width: 11em;"><span class="trim01">Series description</span></th>
				<td style="width: 200px;">
					<input name="filterSrDescription" type="text" style="width: 180px;" value="{$params.filterSrDescription}" />
				</td>
				<th style="width: 6em;"><span class="trim01">Modality</span></th>
				<td>
					<select name="filterModality">
						{foreach from=$modalityList item=item}
							<option value="{$item}" {if $params.filterModality==$item}selected="selected"{/if}>{$item}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<th><span class="trim01">Patient ID</span></th>
				<td>
					<input name="filterPtID" type="text" style="width: 160px;" value="{$params.filterPtID}" {if $params.mode=='study'}disabled="disabled"{/if} />
				</td>
				<th><span class="trim01">Sex</span></th>
				<td>
					<label><input name="filterSex" type="radio" value="M" {if $params.filterSex=="M"}checked="checked"{/if} {if $params.mode=='study'}disabled="disabled"{/if} />male</label>
					<label><input name="filterSex" type="radio" value="F" {if $params.filterSex=="F"}checked="checked"{/if} {if $params.mode=='study'}disabled="disabled"{/if} />female</label>
					<label><input name="filterSex" type="radio" value="all" {if $params.filterSex=="all"}checked="checked"{/if} {if $params.mode=='study'}disabled="disabled"{/if} />all</label>
				</td>
				<th><span class="trim01">Age</span></th>
			  	<td>
					<input name="filterAgeMin" type="text" size="4" value="{$params.filterAgeMin}" {if $params.mode=='study'}disabled="disabled"{/if} />
					-&nbsp;
					<input name="filterAgeMax" type="text" size="4" value="{$params.filterAgeMax}" {if $params.mode=='study'}disabled="disabled"{/if} />
				</td>
			</tr>
			<tr>
				<th><span class="trim01">Patient Name</span></th>
				<td>
					<input name="filterPtName" type="text" style="width: 160px;" {if !$smarty.session.anonymizeFlg}value="{$params.filterPtName}"{/if} {if $params.mode=='study' || $smarty.session.anonymizeFlg}disabled="disabled"{/if} />
				</td>
	            <th><span class="trim01">Tag</span></th>
    			<td><input name="filterTag" type="text" style="width: 180px;" value="{$params.filterTag}" /></td>
				<td colspan="2"></td>
			</tr>
			<tr>
				<th><span class="trim01">Showing</span></th>
				<td>
					<select name="showing">
						<option value="10"  {if $params.showing=="10"}selected="selected"{/if}>10</option>
						<option value="25"  {if $params.showing=="25"}selected="selected"{/if}>25</option>
						<option value="50"  {if $params.showing=="50"}selected="selected"{/if}>50</option>
						<option value="all" {if $params.showing=="all"}selected="selected"{/if}>all</option>
					</select>
				</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
		</table>
		<div class="al-l mt10 ml20" style="width: 100%;">
			<input name="" type="button" value="Search" class="w100 form-btn" onclick="DoSearch('series', '{$params.mode}');" />
			<input name="" type="button" value="Reset" class="w100 form-btn" onclick="ResetSearchBlock('series', '{$params.mode}');" />
		</div>
	</div><!-- / .m20 END -->
</div><!-- / .search-panel END -->
