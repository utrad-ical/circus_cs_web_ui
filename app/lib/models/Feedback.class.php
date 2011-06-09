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
	 * Feedback status is 'registered'.
	 * @var int
	 */
	const REGISTERED = 1;

	/**
	 * Feedback status is 'temporary' (not registered).
	 * @var int
	 */
	const TEMPORARY = 0;

	/**
	 * The block-based feedbacks.
	 * keys are the display_id's, and values are block feedback data for which
	 * the associated evaluation listener can recognize.
	 * @var array
	 */
	public $blockFeedback;

	/**
	 * Additional feedback.
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

		// insert block feedback
		$listener->saveFeedback($this, $data['blockFeedback']);

		// insert additional feedback
		$extensions = $cadResult->buildExtensions(null);
		foreach ($extensions as $ext)
		{
			if (!($ext instanceof IFeedbackListener))
				continue;
			$id = $ext->additionalFeedbackID();
			if (!isset($data['additionalFeedback'][$id]))
				continue;
			$ext->saveFeedback($this, $data['additionalFeedback'][$id]);
		}

		$pdo->commit();
		$this->blockFeedback = $data['blockFeedback'];
		$this->additionalFeedback = $data['additionalFeedback'];
		return true;
	}

	public function loadFeedback()
	{
		$cadResult = $this->CadResult;
		$listener = $cadResult->feedbackListener();
		$this->blockFeedback = $listener->loadFeedback($this);

		$extensions = $cadResult->buildExtensions(null);
		$this->additionalFeedback = array();
		foreach ($extensions as $ext)
		{
			if ($ext instanceof IFeedbackListener)
			{
				$id = $ext->additionalFeedbackID();
				$this->additionalFeedback[$id] = $ext->loadFeedback($this);
			}
		}
	}
}

?>