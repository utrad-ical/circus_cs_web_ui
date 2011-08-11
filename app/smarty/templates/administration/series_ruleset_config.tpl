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
			var elem = $('<div>').addClass('group-node');
			var max = node.members.length;
			for (var i = 0; i < max; i++)
			{
				if (i > 1)
					$('<div>').addClass('group-type').text(node.group).appendTo(elem);
				else if (i > 0)
					groupSelect.clone().val(node.group).change(function (event) {
						var me = $(event.target);
						me.siblings('.group-type').text(me.val());
					}).appendTo(elem);
				var child = createElementFromNode(node.members[i]);
				child.appendTo(elem);
			}
			return elem;
		}

		function createElementFromComparisonNode(node)
		{
			var elem = $('<div>').addClass('comparison-node');
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
			element.children('.group-node, .comparison-node').each(function() {
				var item = createNodeFromElement($(this));
				members.push(item);
			});
			var groupType = $('.group-select', element).val();
			if (members.length > 1)
				return { group: groupType, members: members };
			else if (members.length > 0)
				return members[0];
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

	function refreshRuleSet()
	{
		$('#condition').empty();
		if (currentRuleSet)
		{
			$('#condition').append(createElementFromNode(currentRuleSet.filter));
			$('#condition').change(function() {
				currentRuleSet.filter = createNodeFromElement($('#condition > div'));
				$('#rule').empty().append(stringifyNode(currentRuleSet.filter));
			}).change();
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

	$('#rulesets').click(function(event) {
		var li = $(event.target).closest('li');
		var index = $('#rulesets li').index(li);
		$('#rulesets li').removeClass('active');
		li.addClass('active');
		currentRuleSet = currentRuleSets[index];
		refreshRuleSet();
	});

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

.group-type { font-weight: bold; }

.group-node {
	border: 1px solid silver;
	margin-top: 3px;
}

.group-node .group-node {
	margin: 3px 0 3px 15px;
}

.comparison-node {
	padding-left: 15px;
}
.comparison-node:hover {
	background-color: #eee;
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
</div>

{include file="footer.tpl"}