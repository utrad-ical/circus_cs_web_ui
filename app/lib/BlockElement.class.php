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

	public function __construct($owner)
	{
		$this->owner = $owner;
	}

	/**
	 * Returns the HTML of the element.
	 * @param Smarty The Smarty instance
	 */
	abstract public function show($smarty);

	/**
	 * Returns the javascript and CSS files for making this evaluator work.
	 * @return array|string the name, or the array of names, of the
	 * supporting .js/.css file(s).
	 */
	public function requiringFiles()
	{
		return null; // nothing
	}

	/**
	 * Protected method that defines the default parameter set of this element.
	 * The parameters from presentation.json file will be merged into this
	 * default parameter set.
	 * @return array The default parameter set of this element.
	 */
	protected function defaultParams()
	{
		return array();
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
		$this->params = array_merge($this->defaultParams(), $params);
	}
}

?>