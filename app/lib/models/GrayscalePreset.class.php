<?php

/**
 * Model class for grayscale presets.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class GrayscalePreset extends Model
{
	protected static $_table = 'grayscale_preset';

	/**
	 * Fetches the preset list for a specified modality.
	 * @param string $modality The modality string
	 * @return array Array of GrayscalePreset objects
	 */
	public static function findPresets($modality)
	{
		return GrayscalePreset::select(
			array('modality' => $modality),
			array('order' => array('priority'))
		);
	}

	/**
	 * Fetches the preset list for a specified modality and returns
	 * the result as an array of associative array (not the instances of
	 * GrayscalePreset).
	 * Handy for passing to imageviewer jQuery widget.
	 * @param string $modality The modality string
	 * @return array Array of associative array
	 */
	public static function findPresetsAssoc($modality)
	{
		$list = self::findPresets($modality);
		$result = array();
		foreach ($list as $obj)
			$result[] = array(
				'label' => $obj->preset_name,
				'wl' => $obj->window_level,
				'ww' => $obj->window_width
			);
		return $result;
	}
}

?>