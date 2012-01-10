<?php

/**
 * Modifier that dumps an array into unordered list.
 */
function smarty_modifier_dumpParams(array $items)
{
	$indent = function($item) use(&$indent)
	{
		if (is_array($item))
		{
			if (count($item) == 0)
				return '<em>(Empty)</em>';
			foreach ($item as $k => $v)
			{
				$result .= '<li><strong>' . htmlspecialchars($k) . '</strong>: ' .
					$indent($v) . '</li>';
			}
			return "<ul>$result</ul>";
		}
		else if (is_null($item))
			return '<em>null</em>';
		else
			return htmlspecialchars($item);
	};

	return '<ul>' . $indent($items) . '</ul>';
}
