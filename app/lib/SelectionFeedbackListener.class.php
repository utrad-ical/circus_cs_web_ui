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
	/**
	 * (non-PHPdoc)
	 * @see CadResultElement::requiringFiles()
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
	 * @see CadResultElement::defaultParams()
	 */
	protected function defaultParams()
	{
		return array_merge(
			parent::defaultParams(),
			array(
				'personal' => array(
					array('value' => 'TP', 'label' => 1),
					array('value' => 'FP', 'label' => -1),
					array('value' => 'pending', 'label' => 0)
				)
			)
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see FeedbackListener::saveFeedback()
	 */
	public function saveFeedback(Feedback $fb, $data)
	{
		$pdo = DBConnector::getConnection();
		$sth = $pdo->prepare(
			'INSERT INTO candidate_classification(candidate_id, evaluation, fb_id) ' .
			'VALUES(?, ?, ?)'
		);
		foreach ($data as $display_id => $selection)
		{
			$sth->execute(array(
				$display_id,
				$selection,
				$fb->fb_id
			));
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see IFeedbackListener::loadFeedback()
	 */
	public function loadFeedback(Feedback $fb)
	{
		$sql = 'SELECT * FROM candidate_classification WHERE fb_id=?';
		$rows = DBConnector::query($sql, array($fb->fb_id), 'ALL_ASSOC');
		$result = array();
		foreach ($rows as $row)
		{
			$result[$row['candidate_id']] = $row['evaluation'];
		}
		return $result;
	}

	/**
	 * (non-PHPdoc)
	 * @see IFeedbackListener::integrateConsensualFeedback()
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
		$opinions = array();
		foreach ($personal_fb_list as $pfb)
		{
			foreach ($pfb->blockFeedback as $display_id => $selection)
			{
				$opinion = $map[$selection];
				$opinions[$display_id][$opinion] = true;
			}
		}
		foreach ($opinions as $display_id => $arr)
		{
			if (count($arr) == 1)
			{
				// All opinions matched for this display
				reset($arr);
				$result[$display_id] = key($arr);
			}
			else
			{
				$result[$display_id] = null;
			}
		}
		return $result;
	}
}
