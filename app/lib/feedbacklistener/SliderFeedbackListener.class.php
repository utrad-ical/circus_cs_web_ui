<?php

/**
 * SliderFeedbackListener, subclass of FeedbackListener, provides
 * horizontal sliders for retrieving feedback.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class SliderFeedbackListener extends ScalarFeedbackListener
{
	/**
	 * (non-PHPdoc)
	 * @see CadResultElement::requiringFiles()
	 */
	public function requiringFiles()
	{
		return array(
			'js/slider_feedback_listener.js',
			'js/sprintf-0.7-beta1.js',
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
				'format' => '%f',
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
