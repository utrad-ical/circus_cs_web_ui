<?php

/**
 * CadDetailTab adds 'CAD Detail' tab and
 * sets double-click handler to each display presentation.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class CadDetailTab extends CadResultExtension
{
	public function requiringFiles()
	{
		return array(
			'js/jquery.imageviewer.js',
			'js/cad_detail.js'
		);
	}

	public function tabs()
	{
		return array(
			array (
				'label' => 'CAD Detail',
				'content' => 'Hello!'
			)
		);
	}
}