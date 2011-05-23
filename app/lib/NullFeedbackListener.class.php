<?php

/**
 * NullFeedbackListener, subclass of FeedbackListener, is a special
 * feedback listener, which actually collects no feedback, and prints
 * no feedback-related user interface.
 * Use this feedback listener when you do not like to collect feedback at all.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class NullFeedbackListener extends FeedbackListener
{
	public function requiringFiles()
	{
		return 'js/null_feedback_listener.js';
	}

	/**
	 * @see FeedbackListener::show()
	 */
	public function show($smarty)
	{
		return '';
	}

	function prepareSaveBlockFeedback()
	{
		throw new Exception('NullFeedbackListener does not support saving');
	}

	function saveBlockFeedback($fb_id, $display_id, $feedback)
	{
		throw new Exception('NullFeedbackListener does not support saving');
	}

	function loadBlockFeedback($fb_id)
	{
		throw new Exception('NullFeedbackListener does not support loading feedback');
	}
}

?>