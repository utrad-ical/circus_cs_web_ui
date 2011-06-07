<?php

/**
 * FeedbackListener is a base class for any lesion evaluation listeners.
 * An evaluation listener can gather feedback information for each block.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
abstract class FeedbackListener extends CadBlockElement
{
	/*
	 * Returns the HTML that renders this evaluation listener.
	 * For evaluation listeners, this method should return mere skeleton
	 * HTML. Writing or reading the feedback data on this HTML will be
	 * done by the supporting JavaScript file.
	 */
	public function show()
	{
		$this->smarty->assign('feedbackListenerParams', $this->params);
	}

	/**
	 * Called before saveBlockFeedback() loop.
	 */
	public function prepareSaveBlockFeedback() { }

	/**
	 * Inserts one block feedback.
	 * This will be called multiple times by Feedback class.
	 * @param int $fb_id
	 * @param int $display_id
	 * @param mixed $feedback
	 */
	abstract public function saveBlockFeedback($fb_id, $display_id, $block_feedback);

	/**
	 * Loads block feedbacks.
	 * @param int $fb_id The feedback ID.
	 * @return array The list of block feedback. Each key holds a display ID.
	 */
	abstract public function loadBlockFeedback($fb_id);

	/**
	 * Create the initial consensual feedback data from the given list of
	 * persoanl feedback.
	 * @param array $personal_fb_list The list of Feedback instance.
	 * @return array The array of block feedback. The keys are
	 * display IDs, and the values are the block feedback data.
	 * If personal/consensual integration is not supported, returns null.
	 */
	public function integrateConsensualFeedback(array $personal_fb_list)
	{
		return null;
	}
}

?>