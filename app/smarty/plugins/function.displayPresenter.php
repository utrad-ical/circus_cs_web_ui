<?php

/**
 * Custom plugin function for smarty, for printing display content HTML.
 */
function smarty_function_displayPresenter ($param, $smarty)
{
	$presenter = $smarty->getTemplateVars('displayPresenter');
	return $presenter->show($smarty);
}
