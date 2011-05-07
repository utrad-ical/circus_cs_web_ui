<?php

/**
 * TextEvalListener, subclass of FeedbackListener, provides a text input box
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

	function show($smarty)
	{
		parent::show($smarty);
		return '<input type="text" class="evaluation-text">';
	}
}

?>