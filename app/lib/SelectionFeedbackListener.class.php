<?php

/**
 * SelectionFeedbackListener subclasses FeedbackListener, and provides the
 * array of toggle buttons. Users will click one of the selections
 * to give feedback.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class SelectionFeedbackListener extends FeedbackListener
{
	private $_sth;

	/**
	 * (non-PHPdoc)
	 * @see BlockElement::requiringFiles()
	 */
	public function requiringFiles()
	{
		return 'js/selection_feedback_listener.js';
	}

	/**
	 * (non-PHPdoc)
	 * @see FeedbackListener::show()
	 */
	public function show($smarty)
	{
		parent::show($smarty);
		return $smarty->fetch('cad_results/selection_feedback_listener.tpl');
	}

	/**
	 * (non-PHPdoc)
	 * @see BlockElement::defaultParams()
	 */
	protected function defaultParams()
	{
		return array(
			'personal' => array(
				array('value' => 'TP', 'label' => 1),
				array('value' => 'FP', 'label' => -1),
				array('value' => 'pending', 'label' => 0)
			)
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see FeedbackListener::prepareSaveBlockFeedback()
	 */
	function prepareSaveBlockFeedback()
	{
		$pdo = DBConnector::getConnection();
		$this->_sth = $pdo->prepare(
			'INSERT INTO candidate_classification(candidate_id, evaluation, fb_id) ' .
			'VALUES(?, ?, ?)'
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see FeedbackListener::saveBlockFeedback()
	 */
	function saveBlockFeedback($fb_id, $display_id, $block_feedback)
	{
		$this->_sth->execute(array(
			$display_id,
			$block_feedback['selection'],
			$fb_id
		));
	}

	/**
	 * (non-PHPdoc)
	 * @see FeedbackListener::loadBlockFeedback()
	 */
	function loadBlockFeedback($fb_id)
	{
		$sql = 'SELECT * FROM candidate_classification WHERE fb_id=?';
		$rows = DBConnector::query($sql, array($fb_id), 'ALL_ASSOC');
		$result = array();
		foreach ($rows as $row)
		{
			$result[$row['candidate_id']] = array(
				'selection' => $row['evaluation']
			);
		}
		return $result;
	}
}

?>