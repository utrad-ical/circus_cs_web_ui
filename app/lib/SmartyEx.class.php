<?php

global $WEB_UI_ROOT;
require_once($WEB_UI_ROOT . '/app/vendor/Smarty-3.1.18/Smarty.class.php');

/**
 * SmartyEx subclasses Smarty, and does CIRCUS-specific initialization.
 * Requires Smarty 3.1.x
 */
class SmartyEx extends Smarty
{
	/**
	 * Constructor.
	 * Initializes directories.
	 */
	public function __construct()
	{
		global $BASE_DIR, $DIR_SEPARATOR, $WEB_UI_ROOT;
		parent::__construct();
		$rootPath  = $WEB_UI_ROOT . $DIR_SEPARATOR . 'app' . $DIR_SEPARATOR . 'smarty' . $DIR_SEPARATOR;
		$cachePath = $WEB_UI_ROOT . $DIR_SEPARATOR . 'cache';

		$this->setTemplateDir($rootPath . 'templates')
			->setCompileDir($cachePath)
			->setCacheDir($cachePath)
			->setConfigDir($rootPath . 'configs')
			->addPluginsDir($rootPath . 'plugins');

		$this->registerPlugin('modifier', 'status_str', array('Job', 'codeToStatusName'));

		$this->assign('currentUser', Auth::currentUser());

		$this->assign('totop', relativeTopDir());
	}
}

