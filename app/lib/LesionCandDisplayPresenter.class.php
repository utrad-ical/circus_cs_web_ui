<?php

/**
 * Exports HTML for lesion CAD display.
 *
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
				'caption' => 'Lesion Classification'
			)
		);
	}

	public function show()
	{
		return $this->executeTemplate('lesion_cand_display_presenter.tpl');
	}

	public function extractDisplays($input)
	{
		$result = array();
		$count = 0;
		$pref = $this->owner->Plugin->userPreference();
		usort(
			$input,
			function($a, $b) {
				$aa = (float)$a['confidence'];
				$bb = (float)$b['confidence'];
				if ($aa == $bb) return 0;
				if ($aa < $bb) return 1; else return -1;
			}
		);
		$required_fields = array('location_x', 'location_y', 'location_z',
			'confidence');
		foreach ($input as $rec)
		{
			if (!$key)
			{
				$key = $this->findDisplayIdField($rec);
				if (!$key)
					throw new Exception('Rows must be discriminated by display ID.');
			}
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