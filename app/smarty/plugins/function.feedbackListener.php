<?php

/**
 * Custom plugin function for smarty, for printing feedback listener.
 */
function smarty_function_feedbackListener ($param, $smarty)
{
	$listener = $smarty->get_template_vars('feedbackListener');
	return $listener->display($smarty);
}

?>