<div id="patientSearch" class="search-panel">
<h3>Search</h3>
	<div class="p20">
		<table class="search-tbl">
			<tr>
				<th style="width: 7em;"><span class="trim01">Patient ID</span></th>
				<td style="width: 150px;">
					<input name="filterPtID" type="text" value="{$param.filterPtID}" />
				</td>
				<th style="width: 9em;"><span class="trim01">Patient Name</span></th>
				<td style="width: 200px;">
					<input name="filterPtName" type="text" style="width: 160px;" value="{$param.filterPtName}" {if !$smarty.session.anonymizeFlg}{$param.filterPtName}{/if}" {if $smarty.session.anonymizeFlg}disabled="disabled"{/if} />
				</td>
				<th style="width: 4em;"><span class="trim01">Sex</span></th>
				<td style="width: 180px;">
					<label><input name="filterSex" type="radio" value="M" {if $param.filterSex=="M"}checked="checked"{/if} />male</label>
					<label><input name="filterSex" type="radio" value="F" {if $param.filterSex=="F"}checked="checked"{/if} />female</label>
					<label><input name="filterSex" type="radio" value="all" {if $param.filterSex=="all"}checked="checked"{/if} />all</label>
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
			<input name="" type="button" value="Search" class="w100 form-btn" onclick="DoSearch('patient', '');" />
			<input name="" type="button" value="Reset" class="w100 form-btn"  onclick="ResetSearchBlock('patient', '');" />
		</div>
	</div><!-- / .p20 END -->
</div><!-- / .search-panel END -->
