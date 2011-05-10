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

}

?>