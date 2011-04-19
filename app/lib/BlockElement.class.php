<?php

/**
 * BlockElement is the common base class for EvalListener
 * and BlockContent classes.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
abstract class BlockElement
{
	protected $params;

	/**
	 * Returns the HTML of the element.
	 * @param Smarty The Smarty instance
	 */
	abstract function display($smarty);

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
	 * (for instance, selections for EnumEvalListener, or min/max value for
	 * SliderEvalListener).
	 * This information will be defined by pluguin's evaluation definiton
	 * XML file (not implemented).
	 * @param array $params
	 */
	function setParameter($params)
	{
		$this->params = $params;
	}
}

?>