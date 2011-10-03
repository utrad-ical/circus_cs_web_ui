<div id="seriesSearch" class="search-panel">
	<h3>Search</h3>
	<div style="padding:20px 20px 0px;">
		<table class="search-tbl">
			<tr>
				<th style="width: 7em;"><span class="trim01">Patient ID</span></th>
				<td style="width: 130px;">
					<input name="filterPtID" type="text" value="{$params.filterPtID|escape}" {if $params.mode=='study'}disabled="disabled"{/if} />
				</td>
				<th style="width: 9em;"><span class="trim01">Patient Name</span></th>
				<td style="width: 180px;">
					<input name="filterPtName" type="text" style="width: 160px;" {if !$smarty.session.anonymizeFlg}value="{$params.filterPtName|escape}"{/if} {if $params.mode=='study' || $smarty.session.anonymizeFlg}disabled="disabled"{/if} />
				</td>
				<th style="width: 4em;"><span class="trim01">Sex</span></th>
				<td style="width: 180px;">
					<label><input name="filterSex" type="radio" value="M"   {if $params.filterSex=="M"}checked="checked"{/if} {if $params.mode=='study'}disabled="disabled"{/if} />male</label>
					<label><input name="filterSex" type="radio" value="F"   {if $params.filterSex=="F"}checked="checked"{/if} {if $params.mode=='study'}disabled="disabled"{/if} />female</label>
					<label><input name="filterSex" type="radio" value="all" {if $params.filterSex=="all"}checked="checked"{/if} {if $params.mode=='study'}disabled="disabled"{/if} />all</label>
				</td>
			</tr>
			<tr>
				<th><span class="trim01">Modality</span></th>
				<td>
					<select name="filterModality">
						{foreach from=$modalityList item=item}
							<option value="{$item}" {if $params.filterModality==$item}selected="selected"{/if}>{$item}</option>
						{/foreach}
					</select>
				</td>
				<th><span class="trim01">Age</span></th>
				<td>
					<input name="filterAgeMin" type="text" size="4" value="{$params.filterAgeMin|escape}" {if $params.mode=='study'}disabled="disabled"{/if} />
					-&nbsp;
					<input name="filterAgeMax" type="text" size="4" value="{$params.filterAgeMax|escape}" {if $params.mode=='study'}disabled="disabled"{/if} />
				</td>
				<th><span class="trim01">Tag</span></th>
				<td><input name="filterTag" type="text" style="width: 150px;" value="{$params.filterTag|escape}" /></td>
			</tr>
			<tr>
				<th><span class="trim01">Series date</span></th>
				<td colspan="5"><span class="srDateRange"></span></td>
			</tr>
			<tr>
				<th style="width: 11em;"><span class="trim01">Series description</span></th>
				<td colspan="5">
					<input name="filterSrDescription" type="text" style="width: 240px;" value="{$params.filterSrDescription|escape}" />
			</tr>
			<tr>
				<th><span class="trim01">Showing</span></th>
				<td colspan="5">
					<select name="showing">
						<option value="10"  {if $params.showing=="10"}selected="selected"{/if}>10</option>
						<option value="25"  {if $params.showing=="25"}selected="selected"{/if}>25</option>
						<option value="50"  {if $params.showing=="50"}selected="selected"{/if}>50</option>
						<option value="all" {if $params.showing=="all"}selected="selected"{/if}>all</option>
					</select>
				</td>
			</tr>
		</table>
		<div class="al-l mt10 ml20" style="width: 100%;">
			<input name="" type="button" value="Search" class="w100 form-btn" onclick="DoSearch('series', '{$params.mode|escape}');" />
			<input name="" type="button" value="Reset" class="w100 form-btn" onclick="ResetSearchBlock('series', '{$params.mode|escape}');" />
			<p class="mt5" style="color:#f00; font-wight:bold;">{$params.errorMessage}</p>
		</div>
	</div><!-- / .m20 END -->
</div><!-- / .search-panel END -->


<script type="text/javascript">
<!--
var srDateKind = {if $params.srDateKind != ""}"{$params.srDateKind}"{else}null{/if};
var srFromDate = {if $params.srDateFrom != ""}"{$params.srDateFrom}"{else}null{/if};
var srToDate   = {if $params.srDateTo != ""}"{$params.srDateTo}"{else}null{/if};
var mode       = {if $params.mode != ""}"{$params.mode}"{else}null{/if};

if(mode == "today") srDateKind = 'today';


{literal}
$(function() {
	$("#seriesSearch .srDateRange").daterange({ kind: srDateKind});

	if(srDateKind == "custom...")
	{
	 	$("#seriesSearch .srDateRange")
			.daterange('option', 'fromDate', srFromDate)
			.daterange('option', 'toDate', srToDate);
	}

	if(mode == 'today')  $("#seriesSearch .srDateRange select").attr('disabled', 'disabled');

});
{/literal}

-->
</script>
