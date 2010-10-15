<div id="cadSearch" class="search-panel">
	<h3>Search</h3>
	<div class="p20">
		<table class="search-tbl">
			<tr>
	            <th style="width: 8.5em"><span class="trim01">CAD date</span></th>
				<td style="width: 180px;">
					<input name="cadDateFrom" type="text" style="width:72px;" value="{$param.cadDateFrom}" {if $param.mode=='today'}disabled="disabled"{/if} />
					-&nbsp;
					<input name="cadDateTo" type="text" style="width:72px;" value="{$param.cadDateTo}" {if $param.mode=='today'}disabled="disabled"{/if} />
				</td>
				<th style="width: 6em"><span class="trim01">CAD ID</span></th>
				<td style="width: 180px;">
					<input name="filterCadID" type="text" style="width: 160px;" value="{$param.filterCadID}" />
				</td>
				<th  style="width: 9em"><span class="trim01">Personal FB</span></th>
				<td>
					<label><input name="personalFB" type="radio" value="entered" {if $param.personalFB=="entered"}checked="checked" {/if}/>entered</label>
					<label><input name="personalFB" type="radio" value="notEntered" {if $param.personalFB=="notEntered"}checked="checked" {/if}/>not entered</label>
					<label><input name="personalFB" type="radio" value="all" {if $param.personalFB=="all"}checked="checked" {/if}/>all</label>
				</td>
			</tr>
   			<tr>
     			<th><span class="trim01">Series date</span></th>
				<td>
					<input name="srDateFrom" type="text" style="width:72px;" value="{$param.srDateFrom}" />
					-&nbsp;
					<input name="srDateTo" type="text" style="width:72px;" value="{$param.srDateTo}" />
				</td>
				<th style="width: 6em"><span class="trim01">Modality</span></th>
				<td>
					<select name="filterModality" onchange="ChangefilterModality()" style="width: 60px;">
						{foreach from=$modalityList item=item name=modality}
							<option value="{$modalityMenuVal[$smarty.foreach.modality.index]}" {if $param.filterModality==$item}selected="selected"{/if}>{$item}</option>
						{/foreach}
					</select>
				</td>
				<th><span class="trim01">Consensual FB</span></th>
				<td>
					<label><input name="consensualFB" type="radio" value="entered" {if $param.consensualFB=="entered"}checked="checked" {/if}/>entered</label>
					<label><input name="consensualFB" type="radio" value="notEntered" {if $param.consensualFB=="notEntered"}checked="checked" {/if}/>not entered</label>
					<label><input name="consensualFB" type="radio" value="all" {if $param.consensualFB=="all"}checked="checked" {/if}/>all</label>
				</td>
			</tr>
			<tr>
     			<th><span class="trim01">Patient ID</span></th>
	            <td>
					<input name="filterPtID" type="text" style="width: 160px;" value="{$param.filterPtID}" />
				</td>
				<th><span class="trim01">CAD</span></th>
				<td>
					<select name="filterCAD" onchange="ChangefilterCad();" style="width: 160px;">
						<option value="">all</option>
						{foreach from=$cadList item=item}
							<option value="{$item[1]}"{if $param.filterCAD==$item[0]} selected="selected"{/if}>{$item[0]}</option>
						{/foreach}
					</select>
				</td>
	            <th><span class="trim01">Entered by</span></th>
    			<td><input name="filterFBUser" type="text" style="width: 200px;" value="{$param.filterFBUser}" /></td>
			</tr>
			
			<tr>
     			<th><span class="trim01">Patient Name</span></th>
     			<td>
					<input name="filterPtName" type="text" style="width: 160px;" value="{$param.filterPtName}" {if !$smarty.session.anonymizeFlg}{$param.filterPtName}{/if}" {if $smarty.session.anonymizeFlg}disabled="disabled"{/if} />
				</td>
				<th><span class="trim01">Version</span></th>
				<td>
					<select name="filterVersion" style="width: 60px;">
						{foreach from=$versionList item=item}
							<option value="{$item}"{if $param.filterVersion==$item} selected="selected"{/if}>{$item}</option>
						{/foreach}
					</select>
				</td>
		        <th><span class="trim01">TP</span></th>
    			<td> 
					<label><input name="filterTP" type="radio" value="with" {if $param.filterTP=="with"}checked="checked" {/if}/>with</label>
					<label><input name="filterTP" type="radio" value="without" {if $param.filterTP=="without"}checked="checked" {/if}/>without</label>
					<label><input name="filterTP" type="radio" value="all" {if $param.filterTP=="all"}checked="checked" {/if}/>all</label>
				</td>
			</tr>
   			<tr>
     			<th><span class="trim01">Sex</span></th>
    			 <td>
					<label><input name="filterSex" type="radio" value="M" {if $param.filterSex=="M"}checked="checked" {/if}/>male</label>
					<label><input name="filterSex" type="radio" value="F" {if $param.filterSex=="F"}checked="checked" {/if}/>female</label>
					<label><input name="filterSex" type="radio" value="all" {if $param.filterSex=="all"}checked="checked" {/if}/>all</label>
				</td>
				<th><span class="trim01">Age</span></th>
			  	<td>
					<input name="filterAgeMin" type="text" size="4" value="{$param.filterAgeMin}" />
					-&nbsp;
					<input name="filterAgeMax" type="text" size="4" value="{$param.filterAgeMax}" />
				</td>
	            <th><span class="trim01">FN</span></th>
	            <td>
					<label><input name="filterFN" type="radio" value="with" {if $param.filterFN=="with"}checked="checked" {/if}/>with</label>
					<label><input name="filterFN" type="radio" value="without" {if $param.filterFN=="without"}checked="checked" {/if}/>without</label>
					<label><input name="filterFN" type="radio" value="all" {if $param.filterFN=="all"}checked="checked" {/if}/>all</label>
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

	            <th><span class="trim01">Tag</span></th>
    			<td colspan="3"><input name="filterTag" type="text" style="width: 160px;" value="{$param.filterTag}" /></td>
			</tr>
		</table>
		<div class="al-l mt10 ml20" style="width: 100%;">
			<input name="" type="button" value="Search" class="w100 form-btn" onclick="DoSearch('cad', '{$param.mode}');" />
			<input name="" type="button" value="Reset" class="w100 form-btn" onclick="ResetSearchBlock('cad', '{$param.mode}');" />
		</div>
	</div><!-- / .p20 END -->
</div><!-- / .search-panel END -->

