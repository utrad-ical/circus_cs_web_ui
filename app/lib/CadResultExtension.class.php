<?php

/**
 * CadResultExtension
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class CadResultExtension
{
	protected $owner;
	protected $smarty;

	public $priority;

	public function __construct($owner, $smarty, $priority = 0)
	{
		$this->owner = $owner;
		$this->smarty = $smarty;
		$this->priority = $priority;
	}

	public function requiringFiles() { return null; }
	public function head() { return ''; }
	public function beforeBlocks() { return ''; }
	public function afterBlocks() { return ''; }
	public function tabs() { return array(); }
}

?>