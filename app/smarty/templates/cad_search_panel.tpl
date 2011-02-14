<div id="cadSearch" class="search-panel">
	<h3>Search</h3>
	<div style="padding:20px 20px 0px;">
		<table class="search-tbl">
			<tr>
	            <th style="width: 8.5em"><span class="trim01">CAD date</span></th>
				<td style="width: 220px;">
					<input name="cadDateFrom" type="text" style="width:72px;" value="{$params.cadDateFrom|escape}" {if $params.mode=='today'}disabled="disabled"{/if} />
					-&nbsp;
					<input name="cadDateTo" type="text" style="width:72px;" value="{$params.cadDateTo|escape}" {if $params.mode=='today'}disabled="disabled"{/if} />
				</td>
				<th style="width: 6em"><span class="trim01">CAD ID</span></th>
				<td style="width: 180px;">
					<input name="filterCadID" type="text" style="width: 160px;" value="{$params.filterCadID|escape}" />
				</td>
				<th  style="width: 9em"><span class="trim01">Personal FB</span></th>
				<td>
					<label><input name="personalFB" type="radio" value="entered" {if $params.personalFB=="entered"}checked="checked" {/if}/>entered</label>
					<label><input name="personalFB" type="radio" value="notEntered" {if $params.personalFB=="notEntered"}checked="checked" {/if}/>not entered</label>
					<label><input name="personalFB" type="radio" value="all" {if $params.personalFB=="all"}checked="checked" {/if}/>all</label>
				</td>
			</tr>
   			<tr>
     			<th><span class="trim01">Series date</span></th>
				<td>
					<input name="srDateFrom" type="text" style="width:72px;" value="{$params.srDateFrom|escape}" />
					-&nbsp;
					<input name="srDateTo" type="text" style="width:72px;" value="{$params.srDateTo|escape}" />
				</td>
				<th style="width: 6em"><span class="trim01">Modality</span></th>
				<td>
					<select name="filterModality" onchange="ChangefilterModality()" style="width: 60px;">
						{foreach from=$modalityList item=item name=modality}
							<option value="{$modalityMenuVal[$smarty.foreach.modality.index]|escape}" {if $params.filterModality==$item}selected="selected"{/if}>{$item|escape}</option>
						{/foreach}
					</select>
				</td>
				<th><span class="trim01">Consensual FB</span></th>
				<td>
					<label><input name="consensualFB" type="radio" value="entered" {if $params.consensualFB=="entered"}checked="checked" {/if}/>entered</label>
					<label><input name="consensualFB" type="radio" value="notEntered" {if $params.consensualFB=="notEntered"}checked="checked" {/if}/>not entered</label>
					<label><input name="consensualFB" type="radio" value="all" {if $params.consensualFB=="all"}checked="checked" {/if}/>all</label>
				</td>
			</tr>
			<tr>
     			<th><span class="trim01">Patient ID</span></th>
	            <td>
					<input name="filterPtID" type="text" style="width: 160px;" value="{$params.filterPtID|escape}" />
				</td>
				<th><span class="trim01">CAD</span></th>
				<td>
					<select name="filterCAD" onchange="ChangefilterCad();" style="width: 160px;">
						<option value="">all</option>
						{foreach from=$cadList item=item}
							<option value="{$item[1]|escape}"{if $params.filterCAD==$item[0]} selected="selected"{/if}>{$item[0]|escape}</option>
						{/foreach}
					</select>
				</td>
	            <th><span class="trim01">Entered by</span></th>
    			<td><input name="filterFBUser" type="text" style="width: 200px;" value="{$params.filterFBUser|escape}" /></td>
			</tr>
			
			<tr>
     			<th><span class="trim01">Patient Name</span></th>
     			<td>
					<input name="filterPtName" type="text" style="width: 160px;" value="{$params.filterPtName|escape}" {if !$smarty.session.anonymizeFlg}{$params.filterPtName}{/if}" {if $smarty.session.anonymizeFlg}disabled="disabled"{/if} />
				</td>
				<th><span class="trim01">Version</span></th>
				<td>
					<select name="filterVersion" style="width: 60px;">
						{foreach from=$versionList item=item}
							<option value="{$item|escape}"{if $params.filterVersion==$item} selected="selected"{/if}>{$item|escape}</option>
						{/foreach}
					</select>
				</td>
		        <th><span class="trim01">TP</span></th>
    			<td> 
					<label><input name="filterTP" type="radio" value="with" {if $params.filterTP=="with"}checked="checked" {/if}/>with</label>
					<label><input name="filterTP" type="radio" value="without" {if $params.filterTP=="without"}checked="checked" {/if}/>without</label>
					<label><input name="filterTP" type="radio" value="all" {if $params.filterTP=="all"}checked="checked" {/if}/>all</label>
				</td>
			</tr>
   			<tr>
     			<th><span class="trim01">Sex</span></th>
    			 <td>
					<label><input name="filterSex" type="radio" value="M" {if $params.filterSex=="M"}checked="checked" {/if}/>male</label>
					<label><input name="filterSex" type="radio" value="F" {if $params.filterSex=="F"}checked="checked" {/if}/>female</label>
					<label><input name="filterSex" type="radio" value="all" {if $params.filterSex=="all"}checked="checked" {/if}/>all</label>
				</td>
				<th><span class="trim01">Age</span></th>
			  	<td>
					<input name="filterAgeMin" type="text" size="4" value="{$params.filterAgeMin|escape}" />
					-&nbsp;
					<input name="filterAgeMax" type="text" size="4" value="{$params.filterAgeMax|escape}" />
				</td>
	            <th><span class="trim01">FN</span></th>
	            <td>
					<label><input name="filterFN" type="radio" value="with" {if $params.filterFN=="with"}checked="checked" {/if}/>with</label>
					<label><input name="filterFN" type="radio" value="without" {if $params.filterFN=="without"}checked="checked" {/if}/>without</label>
					<label><input name="filterFN" type="radio" value="all" {if $params.filterFN=="all"}checked="checked" {/if}/>all</label>
				</td>
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

	            <th><span class="trim01">Tag</span></th>
    			<td colspan="3"><input name="filterTag" type="text" style="width: 160px;" value="{$params.filterTag|escape}" /></td>
			</tr>
		</table>
		<div class="al-l mt10 ml20" style="width: 100%;">
			<input name="" type="button" value="Search" class="w100 form-btn" onclick="DoSearch('cad', '{$params.mode|escape}');" />
			<input name="" type="button" value="Reset" class="w100 form-btn" onclick="ResetSearchBlock('cad', '{$params.mode|escape}');" />
			<p class="mt5" style="color:#f00; font-wight:bold;">{$params.errorMessage}</p>
		</div>
	</div><!-- / .p20 END -->
</div><!-- / .search-panel END -->

{if $params.mode!='today'}
{literal}
<script language="javascript">
<!-- 
$(function() {

	$("#cadSearch input[name^='srDate'], #cadSearch input[name^='cadDate']").datepicker({
			showOn: "button",
			buttonImage: "images/calendar_view_month.png",
			buttonImageOnly: true,
			buttonText:'',
			constrainInput: false,
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd',
			maxDate: 0});

	$("#cadSearch input[name='srDateFrom']").datepicker('option', {onSelect: function(selectedDate, instance){
					var date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat,
						                          selectedDate, instance.settings );

					alert(date);
					$("#cadSearch input[name='srDateTo']").datepicker("option", "minDate", date);
				}});

	$("#cadSearch input[name='srDateTo']").datepicker('option', {onSelect: function(selectedDate, instance){
					var date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat,
						                          selectedDate, instance.settings );
					$("#cadSearch input[name='srDateFrom']").datepicker("option", "maxDate", date);
				}});

	$("#cadSearch input[name='cadDateFrom']").datepicker('option', {onSelect: function(selectedDate, instance){
					var date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat,
						                          selectedDate, instance.settings );
					$("#cadSearch input[name='cadDateTo']").datepicker("option", "minDate", date);
				}});

	$("#cadSearch input[name='cadDateTo']").datepicker('option', {onSelect: function(selectedDate, instance){
					var date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat,
						                          selectedDate, instance.settings );
					$("#cadSearch input[name='cadDateFrom']").datepicker("option", "maxDate", date);
				}});

});
-->
</script>
{/literal}
{/if}