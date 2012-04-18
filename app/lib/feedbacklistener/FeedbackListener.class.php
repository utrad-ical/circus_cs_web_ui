<?php

/**
 * FeedbackListener is a base class for block feedback listeners.
 * Gathers feedback information for each block.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
abstract class FeedbackListener extends CadBlockElement implements IFeedbackListener
{
	/**
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
	 * Always returns null for block feedback listeners.
	 * @see IFeedbackListener::additionalFeedbackID()
	 */
	public function additionalFeedbackID()
	{
		return null;
	}
}
