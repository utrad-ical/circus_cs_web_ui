<?php
	global $WEB_UI_ROOT;
	require_once($WEB_UI_ROOT . '/app/vendor/Smarty-2.6.26/Smarty.class.php');

	function modifier_TorF ($boolean)
	{
		if ($boolean) return 't'; else return 'f';
	}

	function modifier_OorMinus ($boolean)
	{
		if ($boolean) return 'O'; else return '-';
	}

	//function modifier_1or0 ($boolean)
	//{
	//	if ($boolean) return '1'; else return '0';
	//}

	/**
	 * SmartyEx subclasses Smarty, and does CIRCUS-specific initialization.
	 * Requires Smarty 2.6 but Smarty 3.x is not supported.
	 */
	class SmartyEx extends Smarty
	{
		/**
		 * Constructor.
		 * Initializes directories and several modifiers.
		 */
		public function __construct()
		{
			global $BASE_DIR, $DIR_SEPARATOR, $WEB_UI_ROOT;
			parent::__construct(); // initalization of parent class

			$rootPath = $WEB_UI_ROOT . $DIR_SEPARATOR . 'app' . $DIR_SEPARATOR . 'smarty' . $DIR_SEPARATOR;

			$this->template_dir = $rootPath . 'templates/';
			$this->compile_dir  = $rootPath . 'templates_c/';
			$this->config_dir   = $rootPath . 'configs/';
			$this->cache_dir    = $rootPath . 'cache/';

			$this->register_modifier('TorF',     'modifier_TorF');
			$this->register_modifier('OorMinus', 'modifier_OorMinus');
		}
	}
?>
