<?php

/**
 * CADPluginPresentation is a factory class that builds appropreate
 * FeedbackListener and DisplayPresenter instances and configures them
 * using JSON configuration file.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class CADPluginPresentation
{
	private $_presentation;
	public $plugin_name;

	protected function defaultPresentation()
	{
		return array(
			'displayPresenter' => array(
				'type' => 'LesionCADDisplayPresenter'
			),
			'feedbackListener' => array(
				'type' => 'SelectionFeedbackListener'
			)
		);
	}

	protected function loadPresentationConfiguration()
	{
		global $WEB_UI_ROOT;
		if (is_array($this->_presentation))
			return;
		$result = $this->defaultPresentation();
		try {
			$json = file_get_contents(
				"$WEB_UI_ROOT/plugin/" . $this->plugin_name . "/presentation.json" );
			$tmp = json_decode($json, true);
			$result = array_merge($result, $tmp);
		} catch (Exception $e) {
			print ($e->getMessage());
		}
		$this->_presentation = $result;
	}

	public function buildDisplayPresenter()
	{
		$this->loadPresentationConfiguration();
		$presenter = new $this->_presentation['displayPresenter']['type'];
		$presenter->setParameter($this->_presentation['displayPresenter']['params']);
		return $presenter;
	}

	public function buildFeedbackListener()
	{
		$this->loadPresentationConfiguration();
		$listener = new $this->_presentation['feedbackListener']['type'];
		$listener->setParameter($this->_presentation['feedbackListener']['params']);
		return $listener;
	}
}