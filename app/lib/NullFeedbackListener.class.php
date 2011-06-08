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
	public function show()
	{
		return '';
	}

	public function saveFeedback($fb_id, $data)
	{
		throw new BadMethodCallException('NullFeedbackListener does not support saving');
	}

	public function loadFeedback($fb_id)
	{
		throw new BadMethodCallException('NullFeedbackListener does not support loading');
	}

	public function integrateConsensualFeedback($personal_fb_list)
	{
		throw new BadMethodCallException('NullFeedbackListener does not support integrating');
	}
}

?>