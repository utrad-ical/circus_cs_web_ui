<?php

/**
 * Custom plugin function for smarty, for printing evaluation listener.
 */
function smarty_function_evalListener ($param, $smarty)
{
	$listener = $smarty->get_template_vars('evalListener');
	return $listener->display($smarty);
}

?>