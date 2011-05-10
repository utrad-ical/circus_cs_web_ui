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
		return array(
			'js/text_feedback_listener.js',
			'css/text_feedback_listener.css'
		);
	}

	function show($smarty)
	{
		parent::show($smarty);
		return '<div class="evaluation-text-container"><input type="text" class="evaluation-text" /></div>';
	}
}

?>