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
			'jq/ui/jquery-ui-1.7.3.min.js',
			'jq/ui/css/jquery-ui-1.7.3.custom.css',
			'jq/jquery.mousewheel.min.js',
			'js/jquery.imageviewer.js',
			'js/cad_detail.js'
		);
	}

	public function tabs()
	{
		return array(
			array (
				'label' => 'CAD Detail',
				'template' => 'cad_detail.tpl'
			)
		);
	}
}