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
	function requiringFiles()
	{
		return 'js/selection_feedback_listener.js';
	}

	function show($smarty)
	{
		parent::show($smarty);
		return $smarty->fetch('cad_results/selection_feedback_listener.tpl');
	}
}

?>