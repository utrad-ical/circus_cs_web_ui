<?php

/**
 * CadResultExtension is the base class which adds some functionality
 * to the CAD result page.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class CadResultExtension extends CadResultElement
{
	/**
	 * (non-PHPdoc)
	 * @see CadResultElement::requiringFiles()
	 */
	public function requiringFiles() { return null; }

	public function head() { return ''; }
	public function beforeBlocks() { return ''; }
	public function afterBlocks() { return ''; }
	public function tabs() { return array(); }
	public function saveAdditionalFeedback($data) { return; }
}

?>