/**
 * Series ruleset stringify routines.
 */

if (typeof circus == 'undefined') circus = {};

circus.ruleset = (function() {

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

	function stringifyRule(rule)
	{
		var results = [];
		if ('start_img_num' in rule && 'end_img_num' in rule)
		{
			results.push('Clip(' + rule.start_img_num + ' - ' + rule.end_img_num + ')');
		}
		if ('required_private_tags' in rule && rule.required_private_tags.length > 0)
		{
			results.push('Require private tags(' + rule.required_private_tags + ')');
		}
		return results.join(', ');
	}

	return {
		op: op,
		stringifyNode: stringifyNode,
		stringifyRule: stringifyRule
	};
})();