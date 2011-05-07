<?php

/**
 * BlockElement is the common base class for FeedbackListener
 * and DisplayPresenter classes.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
abstract class BlockElement
{
	/**
	 * @var array
	 */
	protected $params;

	/**
	 * @var CADResult
	 */
	protected $owner;

	function __construct($owner)
	{
		$this->owner = $owner;
	}

	/**
	 * Returns the HTML of the element.
	 * @param Smarty The Smarty instance
	 */
	abstract function show($smarty);

	/**
	 * Returns the javascript and CSS files for making this evaluator work.
	 * @return array|string the name, or the array of names, of the
	 * supporting .js/.css file(s).
	 */
	function requiringFiles()
	{
		return null; // nothing
	}

	/**
	 * Provides additional information to set up this evaluation listener
	 * (for instance, selections for SelectionFeedbackListener,
	 * or min/max value for SliderFeedbackListener).
	 * This information will be defined by plugin's presentation.json file.
	 * @param array $params
	 */
	function setParameter($params)
	{
		$this->params = $params;
	}
}

?>