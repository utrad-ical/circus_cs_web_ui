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

	/**
	 * Custom plugin function for smarty, for printing link and
	 * script tags easily.
	 */
	function func_print_requirements ($param, $smarty)
	{
		$requires = explode("\n", $param['require']);
		$results = array();
		foreach ($requires as $req)
		{
			$req = trim($req);
			$root = $smarty->get_template_vars('root');
			if ($root)
				$req = "$root/$req";
			if (preg_match("/\\.css$/i", $req))
			{
				$results[] = '<link href="' . $req . '" rel="stylesheet" ' .
					'type="text/css" media="all" />';
			}
			else if (preg_match("/\\.js$/i", $req))
			{
				$results[] = '<script language="javascript" ' .
					'type="text/javascript" src="' . $req . '"></script>';
			}
		}
		return implode("\n", $results);
	}

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
			$this->register_function('require', 'func_print_requirements');
		}
	}
?>
