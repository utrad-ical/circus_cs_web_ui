<?php

/**
 * SelectionEvalListener, subclass of EvalListener, provides the array of
 * toggle buttons. Users can click one of the selections to give feedback.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class SelectionEvalListener extends EvalListener
{
	function requiringFiles()
	{
		return 'enum_eval_listener.js';
	}

	function display($smarty)
	{
		parent::display($smarty);
		return $smarty->fetch('cad_results/selection_eval_listener.tpl');
	}
}

?>