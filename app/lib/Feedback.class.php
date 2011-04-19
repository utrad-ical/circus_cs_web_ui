<?php

/**
 * Feedback represents the set of feedback data for one CAD result.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class Feedback {
	/**
	 * The block-based feedbacks.
	 * keys are the lesion_id's, and values are feedback data for which
	 * the associated evaluation listener can recognize.
	 * @var array
	 */
	public $blockFeedback;

	/**
	 * Additional feedback is defined as key-value pairs of
	 *
	 * @var unknown_type
	 */
	public $additional;

	/**
	 * If true, this data set is consensual feedback.
	 * If false, this data set is personal feedback.
	 * @var bool
	 */
	public $is_consensual = false;

	/**
	 * The creator of this feedback data.
	 */
	public $entered_by;

	/**
	 * The status of the feedback data.
	 * Valid values are: (pending)
	 * @var int
	 */
	public $status;

	/**
	 * Feedback ID in the database. Null if unsaved.
	 * @var unknown_type
	 */
	private $fb_id = null;

	/**
	 * Save the feedback into the database.
	 */
	public function save()
	{
		// DB begin
		if ($fb_id === null)
		{
			// Insert a new record into feedback_list table and fetch ID
		}
		else
		{
			// Delete existing block feedbacks
		}
		// EvalListeners should save the evaluations of each block
		foreach ($blockFeedback as $id => $feedback)
		{
			// Save block feedback
		}
		// DB commit
	}

	/**
	 * Delete this feedback data from the database.
	 */
	public function delete()
	{
		if ($fb_id === null)
		{
			return;
		}
		// DB Delete: hopefully 'on delete cascade' can do most of the job
	}
}

?>