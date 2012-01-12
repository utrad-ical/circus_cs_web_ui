<?php

global $WEB_UI_ROOT;
require_once($WEB_UI_ROOT . '/app/vendor/Smarty-2.6.26/Smarty.class.php');

/**
 * SmartyEx subclasses Smarty, and does CIRCUS-specific initialization.
 * Requires Smarty 2.6 but Smarty 3.x is not supported.
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
		$rootPath = $WEB_UI_ROOT . $DIR_SEPARATOR . 'app' . $DIR_SEPARATOR . 'smarty' . $DIR_SEPARATOR;
		$this->template_dir  = $rootPath . 'templates/';
		$this->compile_dir   = $rootPath . 'templates_c/';
		$this->config_dir    = $rootPath . 'configs/';
		$this->cache_dir     = $rootPath . 'cache/';
		$this->plugins_dir[] = $rootPath . 'plugins/';

		$this->assign('currentUser', Auth::currentUser());

		// Find web root directory (where home.php exists) as a relative path
		do
		{
			$rp = str_repeat('../', $step);
			if (file_exists($rp . 'home.php'))
				break;
		} while ($step++ < 10);
		if ($step >= 10)
			throw new Exception('Web root cannot be resolved');
		$this->assign('totop', $rp);

	}
}

