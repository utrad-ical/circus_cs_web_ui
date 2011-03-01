<?php

/**
 * Custom plugin function for smarty, for printing link and
 * script tags easily.
 */
function smarty_function_require ($param, $smarty)
{
	$requires = explode("\n", $param['require']);
	$results = array();
	foreach ($requires as $req)
	{
		$req = trim($req);
		$root = $smarty->get_template_vars('root');
		if ($root)
			$req = "$root/$req";
		if (preg_match("/\\.css$/i", $req))
		{
			$results[] = '<link href="' . $req . '" rel="stylesheet" ' .
				'type="text/css" media="all" />';
		}
		else if (preg_match("/\\.js$/i", $req))
		{
			$results[] = '<script language="javascript" ' .
				'type="text/javascript" src="' . $req . '"></script>';
		}
	}
	return implode("\n", $results);
}

?>