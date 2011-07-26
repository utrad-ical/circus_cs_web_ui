{capture name="require"}
jq/ui/jquery-ui.min.js
jq/jquery.multiselect.min.js
jq/ui/theme/jquery-ui.custom.css
css/jquery.multiselect.css
{/capture}
{capture name="extra"}
<script type="text/javascript">
var data = {$policyList|@json_encode};

{literal}

$(function () {
	var cancel = function (time) {
		$('#editor').hide(time);
		$('#policies tr').removeClass('editing');
	}

	$('.edit-button').click(function (event) {
		cancel();
		var editor = $('#editor');
		var tr = $(event.target).closest('tr.policy');
		var pol_id = $('.pol-id', tr).val();
		var pol_data = data[pol_id];
		$('#editor-header', editor).text('Editing: ' + pol_data.policy_name);
		$('#target-policy', editor).val(pol_id);
		for (var key in pol_data)
		{
			$('input[name=' + key + ']', editor).val(pol_data[key]);
			$('select[name=' + key + '\\[\\]]', editor).each(function() {
				var items =
					typeof(pol_data[key]) == "string" ?
					pol_data[key].split(',') : [];
				var hash = {};
				for (var i = 0; i < items.length; i++) {
					hash[items[i]] = true;
				}
				$('option', this).each(function() {
					if (hash[$(this).attr('value')])
						$(this).attr('selected', 'selected');
					else
						$(this).removeAttr('selected');
				});
			})
			.multiselect('refresh');
			$('#auto-cons').val(1)
				.attr('checked', pol_data.automatic_consensus ? 'checked' : '');
		}
		tr.addClass('editing');
		editor.show(300);
	});

	$('#allow-reference, #allow-personal, #allow-consensual').multiselect({
		header: false,
		noneSelectedText: '(all)',
		selectedList: 10
	});

	$('#add-policy-button').click(function () {
		cancel();
		$('#editor-header', editor).text('Add New CAD Result Policy');
		$('#target-policy', editor).val('');
		$('input[type=text]', editor).val('').removeAttr('disabled');
		$('select option', editor).removeAttr('selected');
		$('select', editor).multiselect('refresh');
		$('input.num', editor).val('0');
		var editor = $('#editor');
		editor.show(300);
	});

	$('#cancel-button').click(function () {
		$('#groups tr').removeClass('editing');
		cancel(300);
	});

	$('#save-button').click(function () {
		if (!$('#policy-name').val()) {
			alert('Specify the policy name.');
			return;
		}
		$('#editor-form').submit();
	});

});

</script>

<style type="text/css">

#message { margin: 1em 0; padding: 1em; font-weight: bold; color: red; }

#policies td.allow { }
#policies div.al {
	text-align: left;
	width: 150px;
	overflow: hidden;
	text-overflow: ellipsis;
}
#policies tr.editing td.pol-name { background-color: salmon; }

#editor { margin: 2em 0 0 0; background-color: #f9f9f9; }
#editor #allow-reference, #editor #allow-personal, #editor #allow-consensual {
	width: 20em;
}
#editor input.num { text-align: right; width: 5em; }


#panel { margin: 0.5em; }

</style>
{/literal}
{/capture}
{include file="header.tpl" body_class="spot"
	head_extra=$smarty.capture.extra require=$smarty.capture.require}

<h2>CAD Result Policy Configuration</h2>

<div id="message">{$message|escape}</div>

<table class="col-tbl" id="policies">
	<tr>
		<th rowspan="2">Policy</th>
		<th colspan="3">Allow</th>
		{* <th rowspan="2">PFB Freeze</th> *}
		<th rowspan="2">Max PFBs</th>
		<th rowspan="2">Min PFB to CFB</th>
		{* <th rowspan="2">Auto CFB</th> *}
		<th rowspan="2">Operation</th>
	</tr>
	<tr>
		<th>Result Reference</th>
		<th>Personal Feedback</th>
		<th>Consensual Feedback</th>
	</tr>

	{foreach from=$policyList item=pol}
	<tr class="policy">
		<td class="pol-name">{$pol.policy_name|escape}
		<input type="hidden" class="pol-id" value="{$pol.policy_id|escape}" /></td>
		<td class="allow-reference allow"><div class="al">{$pol.allow_result_reference|escape}</div></td>
		<td class="allow-personal allow"><div class="al">{$pol.allow_personal_fb|escape}</div></td>
		<td class="allow-consensual allow"><div class="al">{$pol.allow_consensual_fb|escape}</div></td>
		{* <td class="num">{$pol.time_to_freeze_personal_fb|escape}</td> *}
		<td class="num">{$pol.max_personal_fb|escape}</td>
		<td class="num">{$pol.min_personal_fb_to_make_consensus|escape}</td>
		{* <td>{$pol.automatic_consensus|OorMinus}</td> *}
		<td class="operation"><input type="button" class="edit-button form-btn" value="Edit" /></td>
	</tr>
	{/foreach}
</table>

<div id="panel">
	<input type="button" class="form-btn" value="Add new policy" id="add-policy-button" />
</div>

<div id="editor" style="display: none">
<form id="editor-form" method="post">
	<input type="hidden" name="mode" value="set" />
	<input type="hidden" id="target-policy" name="target" />
	<input type="hidden" name="ticket" value="{$ticket|escape}" />
	<h3 id="editor-header"></h3>
	<table class="detail-tbl" id="privs-table">
		<tr>
			<th>Policy name</th>
			<td><input type="text" name="policy_name" id="policy-name" /></td>
		</tr>
		<tr>
			<th>Allow result reference</th>
			<td>
				<select name="allow_result_reference[]" id="allow-reference" multiple="multiple">
				{foreach from=$groups item=group}
					<option value="{$group|escape}">{$group|escape}</option>
				{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<th>Allow personal feedback</th>
			<td>
				<select name="allow_personal_fb[]" id="allow-personal" multiple="multiple">
				{foreach from=$groups item=group}
					<option value="{$group|escape}">{$group|escape}</option>
				{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<th>Allow consensual feedback</th>
			<td>
				<select name="allow_consensual_fb[]" id="allow-consensual"  multiple="multiple">
				{foreach from=$groups item=group}
					<option value="{$group|escape}">{$group|escape}</option>
				{/foreach}
				</select>
			</td>
		</tr>
		<tr style="display: none">
			<th>Time to freeze personal feedback (min.)</th>
			<td><input type="text" name="time_to_freeze_personal_fb" id="freeze-time" class="num" /> (0: freeze at once)</td>
		</tr>
		<tr>
			<th>Max personal feedback</th>
			<td><input type="text" name="max_personal_fb" id="max-pfb" class="num" /> (0: unlimited)</td>
		</tr>
		<tr>
			<th>Min personal feedback to make consensus</th>
			<td><input type="text" name="min_personal_fb_to_make_consensus" id="min-cons" class="num" /> (0: unlimited)</td>
		</tr>
		<tr style="display none">
			<th>Automatic consensus</th>
			<td><input type="checkbox" name="automatic_consensus" id="auto-cons" value="1" /><label for="auto-cons">Enabled</label></td>
		</tr>
	</table>
	<div id="editor-commit">
		<input type="button" id="save-button" class="form-btn" value="Save" />
		<input type="button" id="cancel-button" class="form-btn" value="Cancel" />
	</div>
</form>
</div>

{include file="footer.tpl"}
