<?php
	require_once('C:/CIRCUS-CS/web_ui/app/vendor/Smarty-2.6.26/Smarty.class.php');
	//require_once('../vendor/Smarty-3.0.6/Smarty.class.php');

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

	//----------------------------------------------------------------------------------------------
	// Extended class of Smarty
	//----------------------------------------------------------------------------------------------
	class SmartyEx extends Smarty
	{
		// Constructor
		public function __construct()
		{
			parent::__construct(); // initalization of parent class
			
			$rootPath = 'C:/CIRCUS-CS/web_ui/app/smarty/';
			
			$this->template_dir = $rootPath . 'templates/';
			$this->compile_dir  = $rootPath . 'templates_c/';
			$this->config_dir   = $rootPath . 'configs/';
			$this->cache_dir    = $rootPath . 'cache/';
			
			// for Smarty 2.x
			$this->register_modifier('TorF',     'modifier_TorF');
			$this->register_modifier('OorMinus', 'modifier_OorMinus');
			//$this->register_modifier('1or0',     'modifier_1or0');
			
			// for Smarty 3.x
			//$this->registerPlugin('modifier', 'TorF',     'modifier_TorF');
			//$this->registerPlugin('modifier', 'OorMinus', 'modifier_OorMinus');
			////$this->registerPlugin('modifier', '1or0',     'modifier_1or0');
			
			// define default variables (if required)
			//$this->assign('TEST', 'TEST');
		}
	}
	//----------------------------------------------------------------------------------------------
?>
