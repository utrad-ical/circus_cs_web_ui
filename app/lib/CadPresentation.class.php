<?php

/**
 * PresentationParser is a loader class for 'presentation.json' file.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class CadPresentation
{
	private $_displayPresenter;
	private $_feedbackListener;
	private $_extensions = array();
	private $_owner;

	public function __construct($fileName, Plugin $owner)
	{
		$this->_owner = $owner;
		$this->load($fileName);
	}

	public function owner()
	{
		return $this->_owner;
	}

	public function displayPresenter()
	{
		return $this->_displayPresenter;
	}

	public function feedbackListener()
	{
		return $this->_feedbackListener;
	}

	public function extensions()
	{
		return $this->_extensions;
	}

	protected function instanciateOne($class, array $params = array())
	{
		$result = new $class($this->_owner);
		$result->setParameter($params);
		return $result;
	}

	protected function is_a($class, $check)
	{
		 return strcasecmp($class, $check) == 0
		 	|| @is_subclass_of($class, $check);
	}

	protected function load($fileName)
	{
		$errors = array();
		$settings = array();

		$lines = @file_get_contents($fileName);

		if ($lines !== false && strlen($lines) > 0)
		{
			$tmp = json_decode($lines, true);
			if (is_null($tmp))
				throw new CadPresentationException("Syntax error found in presentation.json file.");
			$settings = array_merge($settings, $tmp);
		}

		foreach ($settings as $class => $params)
		{
			if ($this->is_a($class, 'DisplayPresenter'))
			{
				if ($this->_displayPresenter)
					$errors[] = "You can not define more than one display presenter.";
				$this->_displayPresenter = $this->instanciateOne($class, $params);
			}
			else if ($this->is_a($class, 'FeedbackListener'))
			{
				if ($this->_feedbackListener)
					$errors[] = "You can not define more than one feedback listener.";
				$this->_feedbackListener = $this->instanciateOne($class, $params);
			}
			else if ($this->is_a($class, 'CadResultExtension'))
			{
				$this->_extensions[] = $this->instanciateOne($class, $params);
			}
			else
			{
				$errors[] = "The class '$class' is not defined";
			}
		}
		if (!$this->_displayPresenter)
			$this->_displayPresenter = $this->instanciateOne('DisplayPresenter');
		if (!$this->_feedbackListener)
			$this->_feedbackListener = $this->instanciateOne('NullFeedbackListener');
		if ($errors)
			throw new CadPresentationException(implode("\n", $errors));
	}
}

class CadPresentationException extends LogicException {}
