{capture name="require"}
js/search_condition.js
{/capture}

{include file="header.tpl" body_class="spot" head_extra=$smarty.capture.extra}

<h2>Plug-in information</h2>

<div class="mb20">
	<table class="detail-tbl">
		<tr>
			<th "width:10em;"><span class="trim01">Plug-in name</span></th>
			<td>{$params.pluginName} v.{$params.version}</td>
		</tr>
		<tr>
			<th><span class="trim01">Type</span></th>
			<td>{if $params.pluginType==1}CAD{else}Research{/if}</td>
		</tr>
		<tr>
			<th><span class="trim01">Description</span></th>
			<td>{$params.description}</td>
		</tr>
	</table>
</div>

{if $params.pluginType==1}
<h3>Required DICOM series</h3>
<div class="m10">
	<table class="col-tbl mb10">
		<thead>
			<tr>
				<th>Series</th>
				<th>Modality</th>
				<th>Condition</th>
			</tr>
		</thead>
		<tbody>
			{section name=j start=0 loop=$seriesNum}
				{assign var="j" value=$smarty.section.j.index}
				{section name=i start=0 loop=$seriesFilterNumArr[$j]}
					{assign var="i" value=$smarty.section.i.index}
					<tr>
						{if $i==0}
							<td{if $seriesFilterNumArr[$j]>1} rowspan={$seriesFilterNumArr[$j]}{/if}>{$j+1}</td>
							<td{if $seriesFilterNumArr[$j]>1} rowspan={$seriesFilterNumArr[$j]}{/if}>{$modalityArr[$j]}</td>
						{/if}
						<td class="al-l">{$seriesFilterArr[$j][$i]}</td>
					</tr>
				{/section}
			{/section}
		</tbody>
	</table>
</div>
{/if}

<h3>Number of executed jobs (since: {$params.oldestDate})</h3>
<div class="m10">
	<table class="col-tbl mb10">
		<thead>
			<tr>
				<th style="width:8em;">Successed</th>
				<th style="width:8em;">Failed</th>
				<th style="width:8em;">Processing</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>{$caseNum[0]|number_format}</td>
				<td>{$caseNum[1]|number_format}</td>
				<td>{$caseNum[2]|number_format}</td>
			</tr>
		</tbody>
	</table>
</div>

{*	{if $caseNum > 0 && $resultType == 1}
		<div style="font-size:7px;">&nbsp;</div>

		{if $evalNumConsensual > 0}
			<div style="font-size:16px; margin-left:5px;"><b>Evaluation</b> (Consensual feedback)</div>
			<div style="font-size:15px; margin-left:15px; margin-bottom:5px;">
				<b>No. of TP: </b>{$tpNumConsensual}<br>
				<b>No. of FN: </b>{$fnNumConsensual}<br>
			</div>
		{/if}

		{if $missedTPNum > 0 || $knownTPNum > 0 || $fnNumPersonal > 0}
			<div style="font-size:16px; margin:5px;"><b>Evaluation</b> (by {$userID})</div>
			<div style="font-size:15px; margin-left:15px; margin-bottom:5px;">
				<b>No. of known TP: </b>{$knownTPNum}<br>
				<b>No. of missed TP: </b>{$missedTPNum}<br>
				<b>No. of FN: </b>{$fnNumPersonal}<br>
			</div>
		{/if}
	{/if}
*}

{include file="footer.tpl"}
