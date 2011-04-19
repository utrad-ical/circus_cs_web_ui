<?php

/**
 * EvalListener is a base class for any lesion evaluation listeners.
 * An evaluation listener can gather feedback information for each block.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
abstract class EvalListener extends BlockElement
{
	/*
	 * Returns the HTML that renders this evaluation listener.
	 * For evaluation listeners, this method should return mere skeleton
	 * HTML. Writing or reading the feedback data on this HTML will be
	 * done by the supporting JavaScript file.
	 */
	function display($smarty)
	{
		$smarty->assign('evalListenerParams', $this->params);
	}

	// abstract function saveBlockFeedback($id, $feedback);
}

?>