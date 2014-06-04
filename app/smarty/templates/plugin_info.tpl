{capture name="require"}
js/series_ruleset.js
{/capture}
{capture name="extra"}
{literal}
<style type="text/css">
h3 { margin-top: 20px; margin-bottom: 20px; }
table.col-tbl { margin-left: 10px; }
table.col-tbl tr td.rulesets { text-align: left; }
li.filter { list-style-type: disc; list-style-position: inside; margin: 5px; }
table.casenum th { width: 8em; }
</style>
<script type="text/javascript">
$(function() {
	$('.filter').each(function() {
		var f = $(this);
		var data = JSON.parse(f.text());
		f.empty().append(circus.ruleset.stringifyNode(data));
	});
});
</script>
{/literal}
{/capture}
{include file="header.tpl" body_class="spot" head_extra=$smarty.capture.extra
	require=$smarty.capture.require}

<h2>Plug-in information</h2>

<table class="detail-tbl">
	<tr>
		<th><span class="trim01">Plug-in name</span></th>
		<td>{$plugin->fullName()|escape}</td>
	</tr>
	<tr>
		<th><span class="trim01">Type</span></th>
		<td>{$plugin->pluginType()|escape}</td>
	</tr>
	<tr>
		<th><span class="trim01">Description</span></th>
		<td>{$plugin->description|escape}</td>
	</tr>
	<tr>
		<th><span class="trim01">Installed date</span></th>
		<td>{$plugin->install_dt|escape}</td>
	</tr>
</table>

{if $plugin->type==1}
<h3>Input volume information</h3>
<table class="col-tbl">
	<thead>
		<tr>
			<th>Volume</th>
			<th>Condition</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$volumes item=volume}
		<tr>
			<td class="al-l">{$volume.volume_id|escape}{if $volume.label} ({$volume.label|escape}){/if}</td>
			<td class="rulesets">
				<ul>
				{foreach from=$volume.filters item=filter}
					<li class="filter">{$filter|@json_encode}</li>
				{/foreach}
				</ul>
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>
{/if}

<h3>Number of executed jobs (since: {$params.oldestDate})</h3>
<table class="col-tbl casenum">
	<thead>
		<tr>
			<th>Success</th>
			<th>Failed</th>
			<th>Invalidated</th>
			<th>Processing</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>{$caseNum.success|number_format}</td>
			<td>{$caseNum.failed|number_format}</td>
			<td>{$caseNum.invalidated|number_format}</td>
			<td>{$caseNum.processing|number_format}</td>
		</tr>
	</tbody>
</table>

{include file="footer.tpl"}
