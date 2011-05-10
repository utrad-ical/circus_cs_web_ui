<?php

/**
 * FeedbackListener is a base class for any lesion evaluation listeners.
 * An evaluation listener can gather feedback information for each block.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
abstract class FeedbackListener extends BlockElement
{
	/**
	 * @var PDO
	 */
	protected $pdo;

	/*
	 * Returns the HTML that renders this evaluation listener.
	 * For evaluation listeners, this method should return mere skeleton
	 * HTML. Writing or reading the feedback data on this HTML will be
	 * done by the supporting JavaScript file.
	 */
	function show($smarty)
	{
		$smarty->assign('feedbackListenerParams', $this->params);
	}

	/**
	 * Called before saveBlockFeedback() loop.
	 */
	abstract function prepareSaveBlockFeedback();

	/**
	 * Inserts one block feedback.
	 * @param int $fb_id
	 * @param int $display_id
	 * @param mixed $feedback
	 */
	abstract function saveBlockFeedback($fb_id, $display_id, $feedback);
}

?>