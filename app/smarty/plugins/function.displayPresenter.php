<?php

/**
 * Custom plugin function for smarty, for printing display content HTML.
 */
function smarty_function_displayPresenter ($param, $smarty)
{
	$presenter = $smarty->get_template_vars('displayPresenter');
	return $presenter->show($smarty);
}

?>