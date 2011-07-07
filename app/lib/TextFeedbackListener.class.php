<?php

/**
 * TextEvalListener, subclass of FeedbackListener, provides a text input box
 * for collecting feedback data.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class TextFeedbackListener extends FeedbackListener
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
			array('required' => true)
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
	 * @see IFeedbackListener::saveFeedback()
	 */
	public function saveFeedback(Feedback $fb, $data)
	{
		$pdo = DBConnector::getConnection();
		$sth = $pdo->prepare(
			'INSERT INTO feedback_attributes(fb_id, key, value) ' .
			'VALUES(?, ?, ?)'
		);
		foreach ($data as $display_id => $block_feedback)
		{
			if (!isset($block_feedback['text']))
				continue;
			$sth->execute(array(
				$fb->fb_id,
				$display_id,
				$block_feedback['text'],
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
			$result[$key] = array(
				'text' => $row['value']
			);
		}
		return $result;
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
			foreach ($pfb->block_feedback as $display_id => $block)
			{
				$text = $block['text'];
				if (!is_array($buf[$display_id]))
					$buf[$display_id] = array();
				$buf[$display_id][$text] = true;
			}
		}
		foreach ($buf as $display_id)
		{
			$result[$display_id] = implode(',', array_keys($buf[$display_id]));
		}
		return $result;
	}
}

?>