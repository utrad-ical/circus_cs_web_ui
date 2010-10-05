<?php
	require_once('c:/php5/lib/Smarty/Smarty.class.php');

	//----------------------------------------------------------------------------------------------
	// Extended class of Smarty
	//----------------------------------------------------------------------------------------------
	
	class SmartyEx extends Smarty {

		// Constructor
		public function __construct()
		{
			parent::__construct(); // initalization of parent class

			$rootPath = 'C:/apache2/htdocs/CIRCUS-CS/smarty/';
			//$rootPath = 'C:/apache2/htdocs/CIRCUS-CS_1.0RC1/smarty/';

			$this->template_dir = $rootPath . 'templates/';
			$this->compile_dir  = $rootPath . 'templates_c/';
			$this->config_dir   = $rootPath . 'configs/';
			$this->cache_dir    = $rootPath . 'cache/';
			
			// define default variables (if required)
			//$this->assign('TEST', 'TEST');
		}
	}
?>
