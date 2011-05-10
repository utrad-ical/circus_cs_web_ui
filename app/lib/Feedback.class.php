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
	 * keys are the display_id's, and values are block feedback data for which
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
	 * Save the feedback data into the database.
	 * @return bool true if succeeds.
	 */
	public function saveFeedback($job_id, $blockFeedback,
		$additional, $user_id, $is_consensual)
	{
		$pdo = DBConnector::getConnection();
		$cadResult = new CadResult($job_id);
		$listener = $cadResult->feedbackListener();

		try {
			$pdo->beginTransaction();
			$now = date('Y-m-d H:i:s');
			$fb_id = $pdo->query("SELECT nextval('feedback_list_fb_id_seq')")->fetchColumn();
			$sqlStr = 'INSERT INTO feedback_list(fb_id, job_id, entered_by, is_consensual, status, registered_at) ' .
				'VALUES (?, ?, ?, ?, ?, ?)';
			$sth = $pdo->prepare($sqlStr);
			$sth->execute(array($fb_id, $job_id, $user, $is_consensual, 1, $now));

			$listener->prepareSaveBlockFeedback();
			foreach ($blockFeedback as $display_id => $block_fb)
			{
				$listener->saveBlockFeedback($fb_id, $display_id, $block_fb);
			}
			// $pdo->commit();
			$pdo->rollBack();
			$this->_data['fb_id'] = $fb_id;
			$this->_data['job_id'] = $job_id;
			$this->_data['entered_by'] = $user_id;
			$this->_data['status'] = 1;
			$this->_data['registered_at'] = $now;
		} catch (Exception $e) {
			$pdo->rollBack();
			return false;
		}
		return true;
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