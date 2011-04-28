<?php

/**
 * Exports HTML for lesion CAD display.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class LesionCADDisplayPresenter extends DisplayPresenter
{
	function display($smarty)
	{
		parent::display($smarty);
		return $smarty->fetch('cad_results/lesion_cad_display_presenter.tpl');
	}

	function extractDisplays($input)
	{
		$result = array();
		foreach ($input as $rec)
		{
			$item = array(
				'display_id' => $rec['sub_id'],
				'location_x' => $rec['location_x'],
				'location_y' => $rec['location_y'],
				'location_z' => $rec['location_z'],
				'slice_location' => $rec['slice_location'],
				'volume_size' => $rec['volue_size'],
				'confidence' => $rec['confidence']
			);
			$result[] = $item;
		}
		return $result;
	}
}

?>