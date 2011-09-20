{include file="header.tpl" body_class="spot" require=$smarty.capture.require}

<h2>Plug-in information</h2>

<div class="mb20">
	<table class="detail-tbl">
		<tr>
			<th><span class="trim01">Plug-in name</span></th>
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
				<th style="width:8em;">Success</th>
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

{include file="footer.tpl"}
