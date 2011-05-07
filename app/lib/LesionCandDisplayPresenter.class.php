<?php

/**
 * Exports HTML for lesion CAD display.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class LesionCandDisplayPresenter extends DisplayPresenter
{
	public $imageWidth;
	public $imageHeight;

	protected function getImageSize($display_id)
	{
		global $DIR_SEPARATOR;
		$imgfile = $this->owner->pathOfCadResult() . $DIR_SEPARATOR .
			sprintf('result%03d.png', $display_id);
		$img = @imagecreatefrompng($imgfile);
	    if($img)
		{
			$this->imageWidth  = imagesx($img);
			$this->imageHeight = imagesy($img);
			imagedestroy($img);
		}
	}

	public function resultImage($display_id)
	{
		global $DIR_SEPARATOR_WEB;
		return
			$this->owner->webPathOfCadResult() . $DIR_SEPARATOR_WEB .
			sprintf('result%03d.png', $display_id);
	}

	function show($smarty)
	{
		return $this->executeTemplate(
			$smarty, 'lesion_cand_display_presenter.tpl');
	}

	function extractDisplays($input)
	{
		$result = array();
		foreach ($input as $rec)
		{
			if (!$this->imageWidth)
				$this->getImageSize($rec['sub_id']);
			$item = array(
				'display_id' => $rec['sub_id'],
				'location_x' => $rec['location_x'],
				'location_y' => $rec['location_y'],
				'location_z' => $rec['location_z'],
				'slice_location' => $rec['slice_location'],
				'volume_size' => $rec['volume_size'],
				'confidence' => $rec['confidence']
			);
			$result[$rec['sub_id']] = $item;
		}
		return $result;
	}
}

?>