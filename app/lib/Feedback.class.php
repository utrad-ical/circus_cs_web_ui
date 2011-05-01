<?php

/**
 * Feedback represents the set of feedback data for one CAD result.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class Feedback extends Model
{
	protected static $_table = 'feedback_list';
	protected static $_primaryKey = 'fb_id';
	protected static $_belongsTo = array(
		'CADResult' => array('key' => 'job_id')
	);

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