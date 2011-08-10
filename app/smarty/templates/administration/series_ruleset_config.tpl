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
			var result = '';
			for (var i = 0; i < node.members.length; i++)
			{
				if (i > 0)
					result += ' ' + node.group + ' ';
				result += stringifyNode(node.members[i], depth + 1);
			}
			if (depth)
				return '(' + result + ')';
			else
				return result;
		}

		function stringifyComparisonNode(node)
		{
			return node.key + ' ' + oph[node.condition].label + ' ' + node.value;
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
				$('#rule').text(stringifyNode(currentRuleSet.filter));
			}).change();
		}
		else
		{
			$('#condition').text('Select plugin and volume ID');
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
			$('<div>').addClass('rule-filter').text(stringifyNode(item.filter)).appendTo(li);
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
#condition { border: 1px solid red; margin: 1em;}

#rule { border: 1px solid green; margin: 1em; padding: 1em; }

.group-type { font-weight: bold; }

.group-node {
	border: 1px solid silver;
}

.group-node .group-node {
	margin-left: 15px;
}

.comparison-node {
	margin-left: 15px;
	border: 1px solid silver;
}

#rulesets li {
	border: 1px solid gray;
	border-radius: 5px;
	background-color: #eee;
	margin: 3px;
}

#rulesets li div.rule-no {
	font-weight: bold;
	width: 100px;
	float: left;
	background-color: gray;
	color: white;
	border-radius: 5px 0 0 5px;
}
#rulesets li div.rule-filter {
	color: navy;
	padding-left: 110px;
}

#rulesets li.active {
	background-color: yellow;
}

</style>

{/literal}

<script type="text/javascript">
var keys = {$keys|@json_encode};
</script>

{/capture}
{include file="header.tpl" head_extra=$smarty.capture.extra
require=$smarty.capture.require body_class="spot"}

<h2>Series Ruleset Config</h2>

Plugin:
<select id="plugin-select">
{foreach from=$plugins item=item}
  <option value="{$item.id|escape}">{$item.name|escape}</option>
{/foreach}
</select>
&gt;
Volume ID:
<select id="volume-id-select">
</select>

<ul id="rulesets">
</ul>

<input type="button" class="form-btn" value="Add" id="add-ruleset" />
<input type="button" class="form-btn" value="Delete" id="delete-ruleset" />

<div id="condition">a</div>

<div id="rule"></div>

{include file="footer.tpl"}