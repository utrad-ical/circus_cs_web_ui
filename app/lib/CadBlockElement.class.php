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
	 * @var CADResult
	 */
	protected $owner;

	public function __construct($owner)
	{
		$this->owner = $owner;
	}

	/**
	 * Called just after setting up this block element.
	 */
	public function prepare()
	{
		return null; // nothing
	}

	/**
	 * Returns the HTML of the element.
	 * @param Smarty $smarty The Smarty instance
	 */
	abstract public function show();


}
