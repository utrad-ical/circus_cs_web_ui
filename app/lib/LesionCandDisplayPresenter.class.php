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
		foreach ($input as $rec)
		{
			$item = array(
				'display_id' => $rec['sub_id'],
				'location_x' => $rec['location_x'],
				'location_y' => $rec['location_y'],
				'location_z' => $rec['location_z'],
				'slice_location' => $rec['slice_location'],
				'volume_size' => $rec['volume_size'],
				'confidence' => $rec['confidence']
			);

			if ($pref['maxDispNum'] && ++$count > $pref['maxDispNum'])
			{
				$item['_hidden'] = true;
			}
			$result[$rec['sub_id']] = $item;
		}
		return $result;
	}
}

?>