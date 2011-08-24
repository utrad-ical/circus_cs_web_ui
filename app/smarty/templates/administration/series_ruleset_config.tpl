{capture name="require"}
jq/ui/jquery-ui.min.js
jq/ui/theme/jquery-ui.custom.css
{/capture}
{capture name="extra"}
{literal}
<script type="text/javascript">
$(function() {

	var targetPlugin = null;

	var pluginRuleSetsData = null;
	var currentRuleSets = null;
	var currentRuleSet = null;

	var hoveringElement = null;

	var op = [
		{op: '=', label: 'is'},
		{op: '>', label: '>'},
		{op: '<', label: '<'},
		{op: '>=', label: '>='},
		{op: '<=', label: '<='},
		{op: '!=', label: 'is not'},
		{op: '*=', label: 'contains'},
		{op: '^=', label: 'begins with'},
		{op: '$=', label: 'ends with'}
	];
	var oph = {};
	for (var i = 0; i < op.length; i++) oph[op[i].op] = op[i];

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

	/**
	 * Converts the given filter node into human-readable format.
	 */
	function stringifyNode(node)
	{
		var depth = arguments[1] ? arguments[1] : 0;

		function stringifyGroupNode(node)
		{
			var result = $('<span>').addClass('group-text');
			for (var i = 0; i < node.members.length; i++)
			{
				if (i > 0)
					result.append(
						' ',
						$('<span>').addClass('group-type-text').text(node.group),
						' '
					);
				result.append(stringifyNode(node.members[i], depth + 1));
			}
			if (depth)
			{
				result.prepend($('<span class="paren">(</span>'));
				result.append($('<span class="paren">)</span>'));
			}
			return result;
		}

		function stringifyComparisonNode(node)
		{
			return $('<span>').addClass('comparison-text').append(
				$('<span>').addClass('key-text').text(node.key),
				' ',
				$('<span>').addClass('condition-text').text(oph[node.condition].label),
				' ',
				$('<span>').addClass('value-text').text(node.value)
			);
		}

		if (node.members instanceof Array)
			return stringifyGroupNode(node);
		else if (node.key !== undefined)
			return stringifyComparisonNode(node);
		else
			throw "exception";
	}

	var keySelect = $('<select>').addClass('key-select');
	for (var i = 0; i < keys.length; i++)
	{
		$('<option>').attr('value', keys[i].value)
			.text(keys[i].label).appendTo(keySelect);
	};

	var opSelect = $('<select>').addClass('operation-select');
	for (i = 0; i < op.length; i++)
	{
		$('<option>').attr('value', op[i].op)
			.text(op[i].label).appendTo(opSelect);
	}

	var groupSelect = $('<select class="group-select"><option>and</option><option>or</option></select>');

	function ruleSetChanged() {
		currentRuleSet.filter = createNodeFromElement($('#condition > div'));
		$('#rule').empty().append(stringifyNode(currentRuleSet.filter));
		$('#condition-tools').hide();
	}

	function refreshRuleSet()
	{
		$('#condition').empty();

		if (currentRuleSet)
		{
			var node = createElementFromNode(currentRuleSet.filter);
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
			.change(ruleSetChanged)
			.keyup(ruleSetChanged)
			.appendTo('#condition');
		}
		else
		{
			$('#condition').text('Select plugin and volume ID');
			$('#rule').text('');
		}
	}

	function refreshRuleSets()
	{
		$('#rulesets').empty();
		if (!(currentRuleSets instanceof Array))
			return;
		currentRuleSet = null;
		$.each(currentRuleSets, function(index, item) {
			var li = $('<li>');
			$('<div>').addClass('rule-no').text('Rule Set: #' + (index + 1)).appendTo(li);
			$('<div>').addClass('rule-filter').append(stringifyNode(item.filter)).appendTo(li);
			li.appendTo('#rulesets');
		});
		refreshRuleSet();
	}

	// Change active ruleset
	$('#rulesets').click(function(event) {
		var li = $(event.target).closest('li');
		var index = $('#rulesets li').index(li);
		$('#rulesets li').removeClass('active');
		li.addClass('active');
		currentRuleSet = currentRuleSets[index];
		refreshRuleSet();
	});

	// Create new ruleset
	$('#add-ruleset').click(function() {
		rulesets.push({
			filter: {},
			rule: []
		});
		refreshRuleSets();
	});

	$('#plugin-select').change(function() {
		// TODO: save dialog
		var targetPlugin = $('#plugin-select').val();
		$.get(
			'series_ruleset_config.php',
			{ plugin_id: targetPlugin, mode: 'get_rulesets' },
			function(data) {
				var obj = JSON.parse(data);
				pluginRuleSetsData = obj.result;
				console.log(pluginRuleSetsData);
				var v = $('#volume-id-select');
				v.children().remove();
				$.each(pluginRuleSetsData, function(volume_id) {
					$('<option>').text(volume_id).appendTo(v);
				});
				v.change();
			},
			'text'
		);
	});

	$('#volume-id-select').change(function() {
		if (!pluginRuleSetsData)
			return;
		var index = $('#volume-id-select').val();
		currentRuleSets = pluginRuleSetsData[index];
		refreshRuleSets();
	});

	$('#enable-clip').change(function() {
		var enabled = $('#enable-clip').is(':checked');
		$('#start-image-num, #end-image-num').enable(enabled);
	});

	// Set up condition tools
	(function() {
		var newCondition = { key: 'modality', condition: '>', value: 'CT'};

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
			var newElement = createElementFromNode(newCondition);
			if (hoveringElement.is('.group-node'))
				newElement.appendTo(hoveringElement);
			else
				newElement.insertAfter(hoveringElement);
			ruleSetChanged();
		});
		$('#condition-addgroup').button({icons: { primary: 'ui-icon-folder-open' }}).click(function() {
			if (!hoveringElement)
				return;
			var newElement = createElementFromNode({ group: 'and', members: [newCondition]});
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

	// Initialize
	$('#plugin-select').change();
});
</script>

<style type="text/css">
#selector-pane {
	width: 300px;
	background-color: silver;
	float: left;
	padding: 5px;
}

#editor-pane {
	margin-left: 320px;
}

#condition {  }

#rule {
	font-size: 80%; color: gray;
	margin: 10px 0 30px 10px;
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
	border-left: 1px solid silver;
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

#rulesets li {
	border: 1px solid gray;
	border-radius: 0 5px 5px 5px;
	background-color: #eee;
	margin: 3px;
}

#rulesets li div.rule-no {
	font-weight: bold;
	float: left;
	background-color: gray;
	color: white;
	margin-right: 2em;
}

#rulesets li.active {
	background-color: yellow;
}

.group-text { color: green; }
.group-text .group-text { color: brown; }
.group-text .group-text .group-text { color: orange; }
.key-text { color: blue; }
.value-text { color: black; font-weight: bold; }
.condition-text { color: purple; }

#condition-tools { width: 115px; height: 18px; position: absolute; }
.condition-toolbutton { width: 18px; height: 18px; margin: 0; }
.condition-toolbutton span.ui-button-icon-primary { left: 0; }

</style>

{/literal}

<script type="text/javascript">
var keys = {$keys|@json_encode};
</script>

{/capture}
{include file="header.tpl" head_extra=$smarty.capture.extra
require=$smarty.capture.require body_class="spot"}

<h2>Series Ruleset Configuration</h2>

<div id="selector-pane">
	<div>
		Plugin:<br />
		<select id="plugin-select">
		{foreach from=$plugins item=item}
		  <option value="{$item.id|escape}">{$item.name|escape}</option>
		{/foreach}
		</select>
	</div>

	<div>
		Volume ID:<br />
		<select id="volume-id-select">
		</select>
	</div>

	<ul id="rulesets">
	</ul>
	<input type="button" class="form-btn" value="Add" id="add-ruleset" />
	<input type="button" class="form-btn" value="Delete" id="delete-ruleset" />
</div>
<div id="editor-pane">
	<h3>Condition</h3>
	<div id="condition"></div>
	<div id="rule"></div>

	<h3>Rule</h3>

	<div class="rule-box">
		<input type="checkbox" id="enable-clip" />
		Clip images<br />
		Start: <input type="text" id="start-image-num" disabled="disabled" />
		End: <input type="text" id="end-image-num" disabled="disabled" />
	</div>
</div>

<div id="condition-tools" style="display: none">
	<button id="move-up" class="condition-toolbutton"></button>
	<button id="move-down" class="condition-toolbutton"></button>
	<button id="condition-add" class="condition-toolbutton"></button>
	<button id="condition-addgroup" class="condition-toolbutton"></button>
	<button id="condition-delete" class="condition-toolbutton"></button>
</div>

{include file="footer.tpl"}