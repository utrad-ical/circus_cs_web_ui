<?php

/**
 * Modifier that dumps an array into unordered list.
 */
function smarty_modifier_dumpParams(array $items)
{
	if (count($items) == 0) return "<em>(Empty)</em>";
	foreach ($items as $key => $val)
	{
		$result .= "<li><strong>" . htmlspecialchars($key) . "</strong>: ";
		if (is_array($val))
			$result .= htmlspecialchars(json_encode($val));
		else if (is_null($val))
			$result .= "<em>null</em>";
		else
			$result .= htmlspecialchars($val);
		$result .= "</li>";
	}
	return  "<ul>$result</ul>";
}