<?php

/**
 * BlockElement is the common base class for FeedbackListener
 * and DisplayPresenter classes.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
abstract class CadBlockElement extends CadResultElement
{
	/**
	 * Returns the HTML of the element.
	 * @param Smarty $smarty The Smarty instance
	 */
	abstract public function show();
}
