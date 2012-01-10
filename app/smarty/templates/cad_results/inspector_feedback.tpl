<h2>Feedback List</h2>
Number of feedback: {$inspector_feedback|@count|number_format}
<table class="col-tbl">
	<thead>
		<tr>
			<th>Type</th><th>Registerer</th><th>Feedback Contents</th>
		</tr>
	</thead>
	<tbody>
	{foreach from=$inspector_feedback item=item}
		<tr>
			<td class="name themeColor">{$item.type|escape}</td>
			<td>{$item.registerer|escape}</td>
			<td class="parameters">{$item.feedback|@dumpParams}</td>
		</tr>
	{/foreach}
	</tbody>
</table>