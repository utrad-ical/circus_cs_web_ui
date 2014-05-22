<?php

/**
 * Custom plugin function for smarty, for printing feedback listener.
 */
function smarty_function_feedbackListener ($param, $smarty)
{
	$listener = $smarty->getTemplateVars('feedbackListener');
	return $listener->show($smarty);
}
