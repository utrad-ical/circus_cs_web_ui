<?php

/**
 * TextFeedbackListener, subclass of FeedbackListener, provides a text input box
 * for collecting feedback data.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class TextFeedbackListener extends ScalarFeedbackListener
{
	/**
	 * (non-PHPdoc)
	 * @see CadResultElement::requiringFiles()
	 */
	public function requiringFiles()
	{
		return array(
			'js/text_feedback_listener.js',
			'css/text_feedback_listener.css'
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
				'minLength' => 1,
				'maxLength' => 0,
				'opinionSeparator' => ',',
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
		return '<div class="evaluation-text-container"><input type="text" class="evaluation-text" /></div>';
	}

	/**
	 * (non-PHPdoc)
	 * @see IFeedbackListener::integrateConsensualFeedback()
	 */
	public function integrateConsensualFeedback(array $personal_fb_list)
	{
		$buf = array();
		$result = array();
		foreach ($personal_fb_list as $pfb)
		{
			foreach ($pfb->blockFeedback as $display_id => $text)
			{
				if (!is_array($buf[$display_id]))
					$buf[$display_id] = array();
				$buf[$display_id][$text] = true;
			}
		}
		foreach ($buf as $display_id => $bfb)
		{
			$result[$display_id] = implode(
				$this->params['opinionSeparator'],
				array_keys($bfb)
			);
		}
		return $result;
	}
}
