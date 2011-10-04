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
				'scrollRange' => 5
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
		$result = array();
		$count = 0;
		$pref = $this->owner->userPreference();

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
			$rec['display_id'] = $display_id;
			if ($pref['maxDispNum'] && ++$count > $pref['maxDispNum'])
			{
				$rec['_hidden'] = true;
			}
			$result[$display_id] = $rec;
		}
		return $result;
	}
}

?>