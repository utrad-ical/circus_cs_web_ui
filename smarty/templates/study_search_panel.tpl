<div id="studySearch" class="search-panel">
	<h3>Search</h3>
	<div class="p20">
		<table class="search-tbl">
			<tr>
				<th style="width: 8.5em;"><span class="trim01">Study date</span></th>
				<td style="width: 200px;">
					<input name="stDateFrom" type="text" style="width:72px;" value="{$param.stDateFrom}" />
					-&nbsp;
					<input name="stDateTo" type="text" style="width:72px;" value="{$param.stDateTo}" />
				</td>
				<th style="width: 7em;"><span class="trim01">Patient ID</span></th>
				<td style="width: 200px;">
					<input name="filterPtID" type="text" value="{$param.filterPtID}" {if $param.mode=='patient'}disabled="disabled"{/if} />
				</td>
				<th style="width: 6em;"><span class="trim01">Modality</span></th>
				<td>
					<select name="filterModality">
						{foreach from=$modalityList item=item}
							<option value="{$item}" {if $param.filterModality==$item}selected="selected"{/if}>{$item}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<th><span class="trim01">Patient Name</span></th>
				<td>
					<input name="filterPtName" type="text" style="width: 160px;" {if !$smarty.session.anonymizeFlg}value="{$param.filterPtName}"{/if} {if $param.mode=='patient' || $smarty.session.anonymizeFlg}disabled="disabled"{/if} />
				</td>
				<th><span class="trim01">Sex</span></th>
				<td>
					<label><input name="filterSex" type="radio" value="M" {if $param.filterSex=="M"}checked="checked"{/if} {if $param.mode=='patient'}disabled="disabled"{/if} />male</label>
					<label><input name="filterSex" type="radio" value="F" {if $param.filterSex=="F"}checked="checked"{/if} {if $param.mode=='patient'}disabled="disabled"{/if} />female</label>
					<label><input name="filterSex" type="radio" value="all" {if $param.filterSex=="all"}checked="checked"{/if} {if $param.mode=='patient'}disabled="disabled"{/if} />all</label>
				</td>
				<th><span class="trim01">Age</span></th>
			  	<td>
					<input name="filterAgeMin" type="text" size="4" value="{$param.filterAgeMin}" />
					-&nbsp;
					<input name="filterAgeMax" type="text" size="4" value="{$param.filterAgeMax}" />
				</td>
			</tr>
			<tr>
				<th><span class="trim01">Showing</span></th>
				<td>
					<select name="showing">
						<option value="10"  {if $param.showing=="10"}selected="selected"{/if}>10</option>
						<option value="25"  {if $param.showing=="25"}selected="selected"{/if}>25</option>
						<option value="50"  {if $param.showing=="50"}selected="selected"{/if}>50</option>
						<option value="all" {if $param.showing=="all"}selected="selected"{/if}>all</option>
					</select>
				</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
		</table>
		<div class="al-l mt10 ml20" style="width: 100%;">
			<input name="" type="button" value="Search" class="w100 form-btn" onclick="DoSearch('study', '{$param.mode}');" />
			<input name="" type="button" value="Reset" class="w100 form-btn" onclick="ResetSearchBlock('study', '{$param.mode}');" />
		</div>
	</div><!-- / .p20 END -->
</div><!-- / .search-panel END -->
