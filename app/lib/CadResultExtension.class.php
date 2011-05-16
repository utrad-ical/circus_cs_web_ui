<?php

/**
 * CadResultExtension
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class CadResultExtension
{
	protected $owner;
	protected $smarty;

	public function __construct($owner, $smarty)
	{
		$this->owner = $owner;
		$this->smarty = $smarty;
	}

	public function requiringFiles() { return null; }
	public function initialize() {}
	public function beforeBlocks() { return ''; }
	public function afterBlocks() { return ''; }
	public function tabs() { return array(); }
}

?>