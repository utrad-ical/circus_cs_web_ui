<?php

/**
 * CadResultElement is the base class for all objects
 * which are configured in presentation.json file and instanciated by
 * a factory method.
 * All instances have $params field, which holds the properties defined in
 * presentation.json file.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
abstract class CadResultElement
{
	/**
	 * @var array
	 */
	protected $params;

	/**
	 * @var Smarty
	 */
	protected $smarty;

	/**
	 * Returns the javascript and CSS files for making this element work.
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
	 * Provides additional information to set up this element, provided by
	 * 'params' from presentation.json file.
	 * @param array $params
	 */
	public function setParameter($params)
	{
		if (is_array($params))
			$this->params = array_merge($this->defaultParams(), $params);
		else
			$this->params = $this->defaultParams();
	}

	/**
	 * Returns the parameters.
	 * @return array The element parameters.
	 */
	public function getParameter()
	{
		return $this->params;
	}

	/**
	 * Provides a Smarty instance.
	 * Smarty is needed only if this class is instanciated for viewing something.
	 * @param Smarty $smarty
	 */
	public function setSmarty(Smarty $smarty)
	{
		$this->smarty = $smarty;
	}
}

?>