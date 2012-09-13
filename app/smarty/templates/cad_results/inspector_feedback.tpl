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
		<tr{if $item.type == 'Consensual'} class="column"{/if}>
			<td class="name themeColor">{$item.type|escape}</td>
			<td>
				{$item.registerer|escape}
				{if $item.type == 'Consensual' && $currentUser->hasPrivilege('serverOperation')
					|| $item.type != 'Consensual' && $currentUser->hasPrivilege('consensualFeedbackModify')}
					<p><input class="form-btn inspector-unreg" type="button" value="Delete/Unregister" /></p>
				{/if}
			</td>
			<td class="parameters">{$item.feedback|@dumpParams}</td>
		</tr>
	{/foreach}
	</tbody>
</table>

<script type="text/javascript">{literal}
$(function() {
	function unreg(params) {
		params.jobID = circus.jobID;
		$.webapi({
			action: 'unregisterFeedback',
			params: params,
			onSuccess: function() { $.alert('Succeeded. Reload the browser to see the effect.'); }
		});
	}

	function close() { $('#inspector-feedback-unreg').dialog('close'); }

	$('.inspector-unreg').each(function() {
		var tr = $(this).closest('tr');
		var type = $('td:eq(0)', tr).text() == 'Consensual' ? 'consensual' : 'personal';
		var user = $('td:eq(1)', tr).text();
		$(this).click(function() {
			var params = { feedbackMode: type };
			if (type == 'personal') params.user = user;
			$.choice(
				'Do you really want to unregister feedback?',
				['Cancel', 'Delete Completely', 'Unregister (edit again)'],
				function(choice) {
					if (choice == 1) { params.delete = 1; unreg(params); }
					if (choice == 2) { unreg(params); }
				},
				{ width: '40em' }
			);
		});
	});
});
{/literal}</script>