<?php

/**
 * LesionCandDisplayPresenter provides functionality which are
 * special for lesion detectors.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class LesionCandDisplayPresenter extends DisplayPresenter
{
	public function requiringFiles()
	{
		return 'js/lesion_cand_display_presenter.js';
	}

	protected function defaultParams()
	{
		return array_merge(
			parent::defaultParams(),
			array(
				'caption' => 'Lesion Classification',
				'scrollRange' => 5,
				'noResultMessage' => 'No lesion candidates found.',
				'maxDispNum' => 5,
				'confidenceThreshold' => 0,
				'askMaxDispNum' => false,
				'askConfidenceThreshold' => false
			)
		);
	}

	public function show()
	{
		return $this->executeTemplate('lesion_cand_display_presenter.tpl');
	}

	/**
	 * Overrides DisplayPresenter::extractDisplays() and extracts
	 * displays for lesion detector more gracefully.
	 * The CAD plugin using this presenter must provide diplays
	 * with location_x/y/z and confidence properties.
	 * @see DisplayPresenter::extractDisplays()
	 */
	public function extractDisplays($input)
	{
		global $DEFAULT_CAD_PREF_USER;

		$result = array();
		$count = 0;
		$pref = $this->owner->userPreference();

		// Legacy: Default preference is used for the time being
		$default_pref = $this->owner->userPreference($DEFAULT_CAD_PREF_USER);
		$pref = array_merge($default_pref, $pref);

		$max_disp_num = $this->params['maxDispNum'];
		if ($this->params['askMaxDispNum'] && isset($pref['maxDispNum'])) {
			$max_disp_num = intval($pref['maxDispNum']);
		}

		$confidence_threshold = $this->params['confidenceThreshold'];
		if ($this->params['askConfidenceThreshold'] && isset($pref['confidenceThreshold'])) {
			$confidence_threshold = $pref['confidenceThreshold'];
		}

		if (count($input) < 1)
			return $result;

		$key = $this->findDisplayIdField($input[0]);
		if (!$key)
			throw new Exception('Rows must be discriminated by display ID.');

		usort(
			$input,
			function($a, $b) use ($key) {
				$aa = (float)$a['confidence'];
				$bb = (float)$b['confidence'];
				if ($aa == $bb) return (int)$a[$key] - (int)$b[$key]; // display_id ASC
				if ($aa < $bb) return 1; else return -1; // confidence DESC
			}
		);
		$required_fields = array('location_x', 'location_y', 'location_z',
			'confidence');

		foreach ($input as $rec)
		{
			foreach ($required_fields as $req)
				if (!isset($rec[$req]))
					throw new Exception("Required field ($req) not defined");
			$key = $this->findDisplayIdField($rec);
			$display_id = $rec[$key];
			if ($rec['confidence'] < $confidence_threshold) {
				continue;
			}
			$rec['display_id'] = $display_id;
			if ($max_disp_num > 0 && ++$count > $max_disp_num)
			{
				$rec['_hidden'] = true;
			}
			$rec['volume_z'] = $rec['location_z'];
			$rec['location_z'] = $this->cadResult->volumeToSliceNum($rec['location_z'], 0);
			$result[$display_id] = $rec;
		}
		return $result;
	}

	public function preferenceForm()
	{
		$result = '';
		if ($this->params['askMaxDispNum']) {
			$result .=
				'<tr><th>Maximum display candidates</th>'.
				'<td><input type="text" name="maxDispNum" style="text-align: right;"/> (0: unlimited)</td>' .
				"</tr>\n";
		}
		if ($this->params['askConfidenceThreshold']) {
			$result .=
				'<tr><th>Confidence Threshold</th>'.
				'<td><input type="text" name="confidenceThreshold" style="text-align: right;"/> (0: unlimited)</td>' .
				"</tr>\n";
		}
		return $result ?: null;
	}

	public function preferenceValidationRule()
	{
		$result = array();
		if ($this->params['askMaxDispNum']) {
			$result['maxDispNum'] = 'int';
		}
		if ($this->params['askConfidenceThreshold']) {
			$result['confidenceThreshold'] = 'numeric';
		}
		return $result;
	}

	public function sortKeys()
	{
		return array(
			array('label' => 'Volume Size', 'key' =>  'volume_size'),
			array('label' => 'Image No.', 'key' => 'location_z'),
			array('label' => 'Confidence', 'key' => 'confidence')
		);
	}
}
