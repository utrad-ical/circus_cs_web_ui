<?php

/**
 * TextEvalListener, subclass of EvalListener, provides a text input box
 * for collecting feedback data.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class TextFeedbackListener extends FeedbackListener
{
	function requiringFiles()
	{
		return 'js/text_feedback_listener.js';
	}

	function display($smarty)
	{
		parent::display($smarty);
		return '<input type="text" class="evaluation-text">';
	}
}

?>