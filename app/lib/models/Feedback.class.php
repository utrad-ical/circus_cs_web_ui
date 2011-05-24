<?php

/**
 * Model class for feedback.
 * This class represents the set of feedback data from one CAD result.
 * This class can load block feedback and additional feedback.
 * You must call loadFeedback() manually after creating the instance of
 * this class.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class Feedback extends Model
{
	protected static $_table = 'feedback_list';
	protected static $_sequence = 'feedback_list_fb_id_seq';
	protected static $_primaryKey = 'fb_id';
	protected static $_belongsTo = array(
		'CadResult' => array('key' => 'job_id')
	);

	/**
	 * The block-based feedbacks.
	 * keys are the display_id's, and values are block feedback data for which
	 * the associated evaluation listener can recognize.
	 * @var array
	 */
	public $blockFeedback;

	/**
	 * Additional feedback is defined as key-value pairs of
	 *
	 * @var array
	 */
	public $additionalFeedback;

	/**
	 * Save the feedback data into the database.
	 * @return bool true if succeeds.
	 */
	public function save($data)
	{
		$job_id = $data['Feedback']['job_id'];
		$cadResult = new CadResult($job_id);
		$listener = $cadResult->feedbackListener();
		$pdo = DBConnector::getConnection();

		$pdo->beginTransaction();
		parent::save($data);

		$listener->prepareSaveBlockFeedback();
		foreach ($data['blockFeedback'] as $display_id => $block_fb)
		{
			$listener->saveBlockFeedback($this->fb_id, $display_id, $block_fb);
		}
		$pdo->commit();
		// $pdo->rollBack();
		$this->blockFeedback = $data['blockFeedback'];
		$this->additionalFeedback = $data['additionalFeedback'];
		return true;
	}

	public function loadFeedback()
	{
		$cadResult = $this->CadResult;
		$listener = $cadResult->feedbackListener();
		$this->blockFeedback = $listener->loadBlockFeedback($this->fb_id);
		$this->additionalFeedback = array();
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