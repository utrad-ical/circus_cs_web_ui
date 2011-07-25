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
			'js/cad_detail.js'
		);
	}

	public function head()
	{
		$series = $this->cadResult->Series[0];
		$modality = $series->modality;
		$presets = GrayscalePreset::findPresetsAssoc($modality);
		$result = '<script type="text/javascript">'
			. 'circus.cadresult.cadDetailGrayscalePresets = ' . json_encode($presets)
			. '</script>';
		return $result;
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