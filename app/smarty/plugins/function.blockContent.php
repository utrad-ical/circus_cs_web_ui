<?php

/**
 * Custom plugin function for smarty, for printing block content HTML.
 */
function smarty_function_blockContent ($param, $smarty)
{
	$content = $smarty->get_template_vars('blockContent');
	return $content->display($smarty);
}

?>