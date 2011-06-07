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
	public function show()
	{
		parent::show();
		return $this->smarty->fetch('selection_feedback_listener.tpl');
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
	public function prepareSaveBlockFeedback()
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
	public function saveBlockFeedback($fb_id, $display_id, $block_feedback)
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
	public function loadBlockFeedback($fb_id)
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

	/**
	 * (non-PHPdoc)
	 * @see FeedbackListener::integrateConsensualFeedback()
	 */
	public function integrateConsensualFeedback(array $personal_fb_list)
	{
		if (!is_array($personal_fb_list))
			return array();
		$result = array();
		$map = array();

		// Build personal => consensual opinion map
		foreach ($this->params['personal'] as $p_selection)
		{
			$v = $p_selection['value'];
			if (isset($p_selection['consensualMapsTo']))
				$map[$v] = $p_selection['consensualMapsTo'];
			else
				$map[$v] = $v;
		}
		// Integrate personal opinions and make consensual opinion
		foreach ($personal_fb_list as $pfb)
		{
			foreach ($pfb->blockFeedback as $display_id => $block)
			{
				if (!isset($result[$display_id]))
					$result[$display_id] = array(
						'opinions' => array()
					);
				$opinion = $map[$block['selection']];
				$result[$display_id]['opinions'][$opinion][] = $pfb->entered_by;
			}
		}
		foreach ($result as &$block)
		{
			if (count($block['opinions']) == 1) // All opinions are the same
			{
				$selection = array_keys($block['opinions']);
				$block['selection'] = $selection[0];
			}
		}
		return $result;
	}
}

?>