<?php

/**
 * TextEvalListener, subclass of EvalListener, provides a text input box
 * for collecting feedback data.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class TextEvalListener extends EvalListener
{
	function requiringFiles()
	{
		return 'text_eval_listener.js';
	}

	function display($smarty)
	{
		parent::display($smarty);
		return '<input type="text" class="evaluation-text">';
	}
}

?>