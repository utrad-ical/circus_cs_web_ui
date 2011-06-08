<?php

/**
 * TextEvalListener, subclass of FeedbackListener, provides a text input box
 * for collecting feedback data.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class TextFeedbackListener extends FeedbackListener
{
	public function requiringFiles()
	{
		return array(
			'js/text_feedback_listener.js',
			'css/text_feedback_listener.css'
		);
	}

	public function show()
	{
		parent::show();
		return '<div class="evaluation-text-container"><input type="text" class="evaluation-text" /></div>';
	}

	public function saveFeedback($fb_id, $data)
	{
		// TODO: implement
	}

	public function loadFeedback($fb_id)
	{
		// TODO: implement
	}

	public function integrateConsensualFeedback($personal_fb_list)
	{
		// TODO: implement
	}
}

?>