{capture name="require"}
jq/ui/jquery-ui.min.js
jq/ui/theme/jquery-ui.custom.css
js/series_ruleset.js
{/capture}
{capture name="extra"}
{literal}
<script type="text/javascript">
$(function() {

	var targetPlugin = null;
	var targetPluginName = null;

	var pluginRuleSetsData = null;
	var labels = null;
	var currentRuleSet = null;

	var hoveringElement = null;

	var modified = false;

	var op = circus.ruleset.op;
	var oph = {};
	for (var i = 0; i < op.length; i++) oph[op[i].op] = op[i];

	function exitEdit()
	{
		$('#selector-pane, #editor-pane').hide();
		$('#plugin-selector-pane').show();
		targetPlugin = null;
		modified = false;
	}

	function enterEdit()
	{
		$('#selector-pane, #editor-pane').show();
		$('#selected-plugin-name').text(targetPluginName);
		$('#plugin-selector-pane').hide();
		$('#plugin-select').val(['']);
		modified = false;
		$('#save-button').disable();
	}

	/**
	 * Creates a new HTML div element (as a jQuery object) representing the
	 * given filter node.
	 */
	function createElementFromNode(node)
	{
		function createElementFromGroupNode(node)
		{
			var elem = $('<div>').addClass('group-node node');
			var max = node.members.length;
			groupSelect.clone().val(node.group).appendTo(elem);
			for (var i = 0; i < max; i++)
			{
				var child = createElementFromNode(node.members[i]);
				child.appendTo(elem);
			}
			elem.sortable({
				axis: 'y',
				containment: 'parent',
				update: ruleSetChanged
			});
			return elem;
		}

		function createElementFromComparisonNode(node)
		{
			var elem = $('<div>').addClass('comparison-node node');
			var value = $('<input type="text" class="value">').val(node.value);
			var tmpKey = keySelect.clone().val([node.key]);
			var tmpOp = opSelect.clone().val([node.condition]);
			elem.append(tmpKey, tmpOp, value);
			return elem;
		}

		if (node.members instanceof Array)
			return createElementFromGroupNode(node);
		else if (node.key !== undefined)
			return createElementFromComparisonNode(node);
		else
			throw new Exception();
	}

	/**
	 * Creates a node object from the given div element (as the jQuery object)
	 * (opposite of createElementFromNode)
	 */
	function createNodeFromElement(element)
	{
		function createNodeFromGroupElement(element)
		{
			var members = [];
			element.children('.node').each(function() {
				var item = createNodeFromElement($(this));
				if (item != null)
					members.push(item);
			});
			var groupType = $('.group-select', element).val();
			if (members.length > 0)
				return { group: groupType, members: members };
			else
				return null;
		}

		function createNodeFromComparisonElement(element)
		{
			return {
				key: element.find('.key-select').val(),
				condition: element.find('.operation-select').val(),
				value: element.find('.value').val()
			};
		}

		if (element.is('.group-node'))
			return createNodeFromGroupElement(element);
		else if (element.is('.comparison-node'))
			return createNodeFromComparisonElement(element);
		else
			throw "exception";
	}

	function createRuleFromElement()
	{
		var rule = {};
		rule.start_img_num = Math.max(parseInt($('#start-img-num').val()) || 0, 0);
		rule.end_img_num = Math.max(parseInt($('#end-img-num').val()) || 0, 0);
		rule.required_private_tags = $('#required-private-tags').val();
		rule.image_delta = parseInt($('#image-delta').val()) || 0;
		rule.environment = $('#environment').val();
		return rule;
	}

	var keySelect = $('<select>').addClass('key-select');
	for (var i = 0; i < keys.length; i++)
	{
		var label = keys[i].value.replace('_', ' ');
		if ('label' in keys[i]) label = keys[i].label;
		$('<option>').attr('value', keys[i].value)
			.text(label).appendTo(keySelect);
	};

	var opSelect = $('<select>').addClass('operation-select');
	for (i = 0; i < op.length; i++)
	{
		$('<option>').attr('value', op[i].op)
			.text(op[i].label).appendTo(opSelect);
	}

	var groupSelect = $('<select class="group-select"><option>and</option><option>or</option></select>');

	function newRuleSetListContent(item)
	{
		var div = $('<div>').addClass('content');
		$('<div>').addClass('rule-filter')
			.append(circus.ruleset.stringifyNode(item.filter)).appendTo(div);
		var rule = circus.ruleset.stringifyRule(item.rule);
		if (rule)
		{
			var icon = $('<div class="rule-rule ui-icon ui-icon-circle-arrow-e">');
			$('<div>').text(rule).prepend(icon).appendTo(div);
		}
		return div;
	}

	function modify()
	{
		modified = true;
		$('#save-button').enable();
	}

	function ruleSetChanged(event) {
		if (!currentRuleSet)
			return;
		modify();
		var conditiondiv = $('#condition > div');
		if (conditiondiv.data('targetRuleSet') != currentRuleSet)
			return;
		currentRuleSet.filter = createNodeFromElement(conditiondiv);
		currentRuleSet.rule = createRuleFromElement();
		$('#rulesets-list li.active .content').replaceWith(
			newRuleSetListContent(currentRuleSet)
		);
		$('#condition-tools').hide();
	}

	function ruleSetListChanged() {
		var stage = $('#rulesets-list');
		var result = [];
		$('#rulesets-list div.volume-group').each(function() {
			var grp = $(this);
			var rulesets = [];
			$('ul.rulesets li', grp).each(function() {
				rulesets.push($(this).data('item'));
			});
			result.push(rulesets);
		});
		pluginRuleSetsData = result;
		modify();
	}

	function refreshRuleSet()
	{
		$('#condition-tools').appendTo($('body'));
		$('#condition').empty();

		if (currentRuleSet)
		{
			var node = createElementFromNode(currentRuleSet.filter);
			node.data('targetRuleSet', currentRuleSet);
			node.mousemove(function(event) {
				var element = $(event.target);
				if (element != hoveringElement && element.is('.node'))
				{
					if (hoveringElement) hoveringElement.removeClass('hover-node');
					if (element.parents('.node').length == 0)
					{
						// top level group cannot be changed
						$('#condition-tools').hide();
						hoveringElement = null;
					}
					else
					{
						$('#condition-tools').appendTo(element).show().position({
							of: element, at: 'right top', my: 'right top', offset: '0 3'
						});
						hoveringElement = element;
						hoveringElement.addClass('hover-node');
					}
				}
			})
			.mouseleave(function() {
				$('#condition-tools').hide();
			})
			.appendTo('#condition');

			$('#start-img-num').val(currentRuleSet.rule.start_img_num);
			$('#end-img-num').val(currentRuleSet.rule.end_img_num);
			$('#required-private-tags').val(currentRuleSet.rule.required_private_tags);
			$('#image-delta').val(currentRuleSet.rule.image_delta);
			$('#environment').val(currentRuleSet.rule.environment);

			$('#select-help').hide();
			$('#editor-contents').show();
			$('#editor-pane').addClass('active');
		}
		else
		{
			$('#select-help').show();
			$('#editor-contents').hide();
			$('#editor-pane').removeClass('active');
			$('#rule').text('');
			hoveringElement = null;
		}
	}

	function addRuleSetClicked(event)
	{
		var grp = $(event.target).closest('.volume-group');
		if (grp.length == 0)
			return;
		var dum = {
			filter: { group: 'and', members: [$.extend({}, dummyCondition)] },
			rule: {}
		};
		var volume_id = grp.data('volume-id');
		pluginRuleSetsData[grp.data('volume-id')].push(dum);
		refreshRuleSets();
	}

	function removeRuleSetClicked(event)
	{
		var grp = $(event.target).closest('.volume-group');
		var removed = false;
		$('#rulesets-list ul.rulesets li').each(function() {
			var item = $(this).data('item');
			if (item != null && item == currentRuleSet)
			{
				$(this).remove();
				removed = true;
			}
		})
		if (removed)
		{
			ruleSetListChanged();
			refreshRuleSets();
		}
	}

	function refreshRuleSets()
	{
		var stage = $('#rulesets-list').empty();
		$.each(pluginRuleSetsData, function(volume_id, rulesets) {
			var label = labels[volume_id];
			var grp = $('<div class="volume-group">')
				.data('volume-id', volume_id)
				.appendTo(stage);
			var t = 'Volume ID: ' + volume_id;
			var h = $('<div class="vol-id">').text(t).appendTo(grp);
			if (typeof(label) == 'string')
				h.append($('<span class="volume-label">').text(' (' + label + ')'));

			if (rulesets.length > 0)
			{
				var ul = $('<ul class="rulesets">').appendTo(grp);
				$.each(rulesets, function(index, item) {
					var li = $('<li>').appendTo(ul).data('item', item);
					$('<div>').addClass('rule-no').text('Rule Set: #' + (index + 1)).appendTo(li);
					newRuleSetListContent(item).appendTo(li);
				});
				ul.sortable({
					axis: 'y',
					containment: 'parent',
					update: function() { ruleSetListChanged(); refreshRuleSets(); }
				});
			}
			else
			{
				$('<p>').text('No rule set (any series will match)').appendTo(grp);
			}
			var tools = $('<div class="ruleset-tools">').appendTo(grp);
			$('<button class="ruleset-toolbutton">')
			.button({icons: { primary: 'ui-icon-minusthick' }})
			.click(removeRuleSetClicked).appendTo(tools);
			$('<button class="ruleset-toolbutton">')
				.button({icons: { primary: 'ui-icon-plusthick' }})
				.click(addRuleSetClicked).appendTo(tools);

		});
		currentRuleSet = null;
		refreshRuleSet();
	}

	// Change active ruleset
	$('#rulesets-list').click(function(event) {
		var li = $(event.target).closest('li');
		$('#rulesets-list li').removeClass('active');
		li.addClass('active');
		currentRuleSet = li.data('item');
		refreshRuleSet();
	});

	$('#plugin-select').change(function() {
		targetPlugin = $('#plugin-select').val();
		targetPluginName = $('#plugin-select option:selected').text();
		pluginRuleSetsData = [];
		labels = [];
		if (targetPlugin)
		{
			$.webapi({
				action: 'seriesRuleset',
				params: {
					plugin_id: targetPlugin,
					mode: 'get'
				},
				onSuccess: function(result) {
					for (var volume_id in result)
					{
						pluginRuleSetsData[volume_id] = result[volume_id].ruleset;
						labels[volume_id] = result[volume_id].label;
					}
					enterEdit();
					refreshRuleSets();
				}
			});
		}
		else
			exitEdit();
	});

	var dummyCondition = { key: 'modality', condition: '=', value: 'CT'};

	// Set up condition tools
	(function() {
		$('#move-up').button({icons: { primary: 'ui-icon-carat-1-n' }}).click(function(event) {
			if (!hoveringElement)
				return;
			var prev = hoveringElement.prev('.node');
			if (prev)
			{
				hoveringElement.insertBefore(prev);
				ruleSetChanged();
			}
		});
		$('#move-down').button({icons: { primary: 'ui-icon-carat-1-s' }}).click(function() {
			if (!hoveringElement)
				return;
			var next = hoveringElement.next('.node');
			if (next)
			{
				hoveringElement.insertAfter(next);
				ruleSetChanged();
			}
		});
		$('#condition-add').button({icons: { primary: 'ui-icon-plusthick' }}).click(function() {
			if (!hoveringElement)
				return;
			var newElement = createElementFromNode(dummyCondition);
			if (hoveringElement.is('.group-node'))
				newElement.appendTo(hoveringElement);
			else
				newElement.insertAfter(hoveringElement);
			ruleSetChanged();
		});
		$('#condition-addgroup').button({icons: { primary: 'ui-icon-folder-open' }}).click(function() {
			if (!hoveringElement)
				return;
			var newElement = createElementFromNode({ group: 'and', members: [dummyCondition]});
			if (hoveringElement.is('.group-node'))
				newElement.appendTo(hoveringElement);
			else
				newElement.insertAfter(hoveringElement);
			ruleSetChanged();
		});
		$('#condition-delete').button({icons: { primary: 'ui-icon-minusthick' }}).click(function() {
			if (!hoveringElement)
				return;
			$('#condition-tools').hide().appendTo('body');
			hoveringElement.remove();
			ruleSetChanged();
		});
	})();


	$('#close-button').click(function() {
		if (modified)
		{
			$('#close-confirm').dialog({
				autoOpen: true,
				modal: true,
				buttons: {
					"Don't Save": function() {
						$(this).dialog('close');
						exitEdit();
					},
					"Cancel": function() { $(this).dialog('close'); }
				}
			});
		}
		else
		{
			exitEdit();
		}
	});

	$('#save-button').click(function() {
		$('#save-button').disable();
		if (targetPlugin)
		{
			$.webapi({
				action: 'seriesRuleset',
				params: {
					plugin_id: targetPlugin,
					mode: 'set',
					pluginRuleSetsData: JSON.stringify(pluginRuleSetsData)
				},
				onSuccess: function(data) {
					alert('Saved');
					exitEdit();
				}
			});
		}
	});

	$('#editor-pane').change(ruleSetChanged).keyup(ruleSetChanged);

	$('#plugin-select').change();

});
</script>

<style type="text/css">

h3 { margin-bottom: 15px; }

#content div.vol-id {
	border-top: 1px solid gray;
	font-weight: bold;
	margin: 0 5px 3px 0;
}

#content div.vol-id .volume-label {
	color: #8a3b2b;
}

#selected-plugin-pane {
	margin: 0 0 15px; 0;
}

#selector-pane {
	width: 300px;
	background-color: white;
	float: left;
	min-height: 510px;
	word-wrap: break-word;
}

#editor-pane {
	margin-left: 300px;
	border: 5px solid #eee;
	padding: 5px;
	min-height: 500px;
}

#editor-pane.active {
	border-color: #ebbe8c;
}

#rule {
	font-size: 80%; color: gray;
	margin: 10px 0 30px 10px;
}

#select-help {
	margin: 50px;
	text-align: center;
}

.group-select { font-weight: bold; margin-left: 3px; }

.group-node {
	border: 1px solid silver;
}

.group-node .group-node {
	margin-left: 15px;
	border-top: none;
	border-bottom: none;
	border-right: none;
	border-left: 3px solid silver;
}

.comparison-node {
	padding: 2px 2px 2px 15px;
}

.hover-node {
	background-color: #ffc;
}

.comparison-node .value {
	width: 250px;
}

.rulesets {
	margin-bottom: 2px;
}

.rulesets li {
	margin: 5px 0;
	background-color: #eee;
	cursor: pointer;
	border-right: 5px solid white;
}

.rulesets li div.rule-no {
	font-weight: bold;
	float: left;
	background-color: gray;
	color: white;
	margin-right: 1em;
}

.rulesets li .rule-rule {
	float: left;
}

.rulesets li:hover {
	background-color: #ffddae;
}

.rulesets li.active {
	background-color: #ebbe8c;
	border-color: #ebbe8c;
}

.rulesets li.active div.rule-no {
	background-color: #8a3b2b;
}

.ruleset-tools { text-align: right; margin: 0 5px 15px 0; }
.ruleset-toolbutton { width: 18px; height: 18px; margin: 0 3px; }
.ruleset-toolbutton span.ui-button-icon-primary { left: 0; }

#condition-tools { width: 115px; height: 18px; position: absolute; }
.condition-toolbutton { width: 18px; height: 18px; margin: 0; }
.condition-toolbutton span.ui-button-icon-primary { left: 0; }

.rule-box { margin-top: 10px; }
.rule-box th { font-weight: bold; padding: 5px 15px; }

#down { font-size: 20px; text-align: center; }

#save-pane { text-align: right; margin: 30px 5px 0 0; border-top: 1px solid gray; padding: 10px; }
#save-button { padding: 0.5em 2em; }

</style>

{/literal}

<script type="text/javascript">
var keys = {$keys|@json_encode};
</script>

{/capture}
{include file="header.tpl" head_extra=$smarty.capture.extra
require=$smarty.capture.require body_class="spot"}

<h2>Series Ruleset Configuration</h2>

<div id="plugin-selector-pane">
	<b>Plugin:</b>&nbsp;
	<select id="plugin-select">
		<option value="">Select Plugin</option>
	{foreach from=$plugins item=item}
		<option value="{$item.id|escape}">{$item.name|escape}</option>
	{/foreach}
	</select>
</div>

<div id="selector-pane">
	<div id="selected-plugin-pane"><b>Plugin:</b>&nbsp;<span id="selected-plugin-name"></span></div>
	<div id="rulesets-list"></div>
	<div id="save-pane">
		<a href="#" id="close-button">Close</a>&nbsp;
		<input type="button" class="form-btn" id="save-button"
		value="Save settings" />
	</div>
</div>
<div id="editor-pane">
	<div id="select-help">Select Rule Set</div>

	<div id="editor-contents">
		<h3>Condition</h3>
		<div id="condition"></div>
		<div id="rule"></div>

		<div id="down">&downarrow;</div>

		<h3>Rule</h3>

		<table class="rule-box">
			<tbody>
				<tr>
					<th>Start image number</th>
					<td><input type="text" id="start-img-num" /></td>
				</tr>
				<tr>
					<th>End image number</th>
					<td><input type="text" id="end-img-num" /></td>
				</tr>
				<tr>
					<th>Required private DICOM tags</th>
					<td><input type="text" id="required-private-tags" size="30" /></td>
				</tr>
				<tr>
					<th>Direction</th>
					<td>
						<select id="image-delta">
							<option value="0">auto (head to foot)</option>
							<option value="1">forward</option>
							<option value="-1">reverse</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>Provide environment</th>
					<td><input type="text" id="environment" /></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<div id="condition-tools" style="display: none">
	<button id="move-up" class="condition-toolbutton"></button>
	<button id="move-down" class="condition-toolbutton"></button>
	<button id="condition-add" class="condition-toolbutton"></button>
	<button id="condition-addgroup" class="condition-toolbutton"></button>
	<button id="condition-delete" class="condition-toolbutton"></button>
</div>

<div id="close-confirm" title="Confirm" style="display: none">Exit without saving?</div>

{include file="footer.tpl"}