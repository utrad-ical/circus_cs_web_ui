<?php

/**
 * IFeedbackListener defines the interface for saving/loading/integrating for
 * both block feedback listener and additional feedback listener.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
interface IFeedbackListener
{
	/**
	 * Saves feedback.
	 * @param int $fb_id The feedback ID.
	 * @param mixed $data The data to store.
	 */
	public function saveFeedback(Feedback $fb, $data);

	/**
	 * Loads feedback.
	 * @param int $fb_id The feedback ID.
	 * @return mixed The feedback data.
	 */
	public function loadFeedback(Feedback $fb);

	/**
	 * Create the initial consensual feedback data from the given list of
	 * persoanl feedback.
	 * @param array $personal_fb_list The list of Feedback objects.
	 * @return mixed The integrated feedback data.
	 * If personal/consensual integration is not supported, just return null.
	 */
	public function integrateConsensualFeedback(array $personal_fb_list);

	/**
	 * Returns the additional feedback ID.
	 * @return mixed If the class implementing this interface is a block
	 * feedback listener, returns null. If the class is an additional feedback
	 * listener, then return the identifier of the additional feedback.
	 */
	public function additionalFeedbackID();
}
