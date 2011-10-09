<?php

/**
 * SliderFeedbackListener, subclass of FeedbackListener, provides
 * horizontal sliders for retrieving feedback.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class SliderFeedbackListener extends FeedbackListener
{
	/**
	 * (non-PHPdoc)
	 * @see CadResultElement::requiringFiles()
	 */
	public function requiringFiles()
	{
		return array(
			'js/slider_feedback_listener.js',
			'css/slider_feedback_listener.css'
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see CadResultElement::defaultParams()
	 */
	public function defaultParams()
	{
		return array_merge(
			parent::defaultParams(),
			array(
				'min' => 0,
				'max' => 100,
				'initial' => 50,
				'step' => 1,
				'showValue' => 'never' // one of 'never', 'always', 'active'
			)
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see FeedbackListener::show()
	 */
	public function show()
	{
		parent::show();
		return '<div class="evaluation-slider-container">' .
			'<div class="evaluation-slider" /></div></div>';
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
	 * Create initial consensus values from personal feedback.
	 * The initial values are simple average.
	 * @see IFeedbackListener::integrateConsensualFeedback()
	 */
	public function integrateConsensualFeedback(array $personal_fb_list)
	{
		$sums = array();
		$result = array();
		$count = count($personal_fb_list);
		foreach ($personal_fb_list as $pfb)
		{
			foreach ($pfb->blockFeedback as $display_id => $value)
			{
				$sums[$display_id] += $value;
			}
		}
		foreach ($sums as $display_id => $sum)
		{
			$result[$display_id] = $sum / $count;
		}
		return $result;
	}
}

?>