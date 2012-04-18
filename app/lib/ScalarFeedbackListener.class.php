<?php

/**
 * ScalarFeedbackListener is a base class of feedback listeners that
 * takes a single string as a block feedback.
 * This base class uses 'feedback_attributes' as the result table name
 * and stores the feedback data there.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
abstract class ScalarFeedbackListener extends FeedbackListener
{
	/**
	 * (non-PHPdoc)
	 * @see IFeedbackListener::loadFeedback()
	 */
	public function loadFeedback(Feedback $fb)
	{
		$sql = 'SELECT * FROM feedback_attributes WHERE fb_id=?';
		$rows = DBConnector::query($sql, array($fb->fb_id), 'ALL_ASSOC');
		$result = array();
		foreach ($rows as $row)
		{
			$key = intval($row['key']);
			$result[$key] = $row['value'];
		}
		return $result;
	}

	/**
	 * (non-PHPdoc)
	 * @see IFeedbackListener::saveFeedback()
	 */
	public function saveFeedback(Feedback $fb, $data)
	{
		$pdo = DBConnector::getConnection();
		$sth = $pdo->prepare(
				'INSERT INTO feedback_attributes(fb_id, key, value) ' .
				'VALUES(?, ?, ?)'
		);
		foreach ($data as $display_id => $value)
		{
			$sth->execute(array(
				$fb->fb_id,
				$display_id,
				$value,
			));
		}
	}
}