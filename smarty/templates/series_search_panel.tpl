<div id="seriesSearch" class="search-panel">
	<h3>Search</h3>
	<div style="padding:20px 20px 0px;">
		<table class="search-tbl">
			<tr>
				<th style="width: 8.5em;"><span class="trim01">Series date</span></th>
				<td style="width: 220px;">
					<input name="srDateFrom" type="text" style="width:72px;" value="{$params.srDateFrom|escape}" {if $params.mode=='today'}disabled="disabled"{/if} />
					-&nbsp;
					<input name="srDateTo" type="text" style="width:72px;" value="{$params.srDateTo|escape}" {if $params.mode=='today'}disabled="disabled"{/if} />
				</td>
				<th style="width: 11em;"><span class="trim01">Series description</span></th>
				<td style="width: 200px;">
					<input name="filterSrDescription" type="text" style="width: 180px;" value="{$params.filterSrDescription|escape}" />
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
					<input name="filterPtID" type="text" style="width: 160px;" value="{$params.filterPtID|escape}" {if $params.mode=='study'}disabled="disabled"{/if} />
				</td>
				<th><span class="trim01">Sex</span></th>
				<td>
					<label><input name="filterSex" type="radio" value="M" {if $params.filterSex=="M"}checked="checked"{/if} {if $params.mode=='study'}disabled="disabled"{/if} />male</label>
					<label><input name="filterSex" type="radio" value="F" {if $params.filterSex=="F"}checked="checked"{/if} {if $params.mode=='study'}disabled="disabled"{/if} />female</label>
					<label><input name="filterSex" type="radio" value="all" {if $params.filterSex=="all"}checked="checked"{/if} {if $params.mode=='study'}disabled="disabled"{/if} />all</label>
				</td>
				<th><span class="trim01">Age</span></th>
			  	<td>
					<input name="filterAgeMin" type="text" size="4" value="{$params.filterAgeMin|escape}" {if $params.mode=='study'}disabled="disabled"{/if} />
					-&nbsp;
					<input name="filterAgeMax" type="text" size="4" value="{$params.filterAgeMax|escape}" {if $params.mode=='study'}disabled="disabled"{/if} />
				</td>
			</tr>
			<tr>
				<th><span class="trim01">Patient Name</span></th>
				<td>
					<input name="filterPtName" type="text" style="width: 160px;" {if !$smarty.session.anonymizeFlg}value="{$params.filterPtName|escape}"{/if} {if $params.mode=='study' || $smarty.session.anonymizeFlg}disabled="disabled"{/if} />
				</td>
	            <th><span class="trim01">Tag</span></th>
    			<td><input name="filterTag" type="text" style="width: 180px;" value="{$params.filterTag|escape}" /></td>
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
			<input name="" type="button" value="Search" class="w100 form-btn" onclick="DoSearch('series', '{$params.mode|escape}');" />
			<input name="" type="button" value="Reset" class="w100 form-btn" onclick="ResetSearchBlock('series', '{$params.mode|escape}');" />
			<p class="mt5" style="color:#f00; font-wight:bold;">{$params.errorMessage}</p>
		</div>
	</div><!-- / .m20 END -->
</div><!-- / .search-panel END -->


<script language="javascript">
<!-- 
{if $params.mode!='today'}
{literal}
$(function() {
	$("#seriesSearch input[name^='srDate']").datepicker({
			showOn: "button",
			buttonImage: "images/calendar_view_month.png",
			buttonImageOnly: true,
			buttonText:'',
			constrainInput: false,
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd',
			maxDate: 0});


	$("#seriesSearch input[name='srDateTo']").datepicker('option', {onSelect: function(selectedDate, instance){
					date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat,
						                          selectedDate, instance.settings );
					$("#seriesSearch input[name='srDateTo']").datepicker("option", "minDate", date);
				}});

	$("#seriesSearch input[name='srDateTo']").datepicker('option', {onSelect: function(selectedDate, instance){
					date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat,
						                          selectedDate, instance.settings );
					$("#seriesSearch input[name='srDateFrom']").datepicker("option", "maxDate", date);
				}});
});
{/literal}
{/if}

-->
</script>
