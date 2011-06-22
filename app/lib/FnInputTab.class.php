<?php

/**
 * FnInputTab adds 'FN Input' tab and provides 'fn_location'
 * additional feedback mechanism.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class FnInputTab extends CadResultExtension implements IFeedbackListener
{
	const FEEDBACK_ID = 'fn_input';

	public function requiringFiles()
	{
		return array(
			'jq/ui/jquery-ui-1.7.3.min.js',
			'jq/ui/css/jquery-ui-1.7.3.custom.css',
			'jq/jquery.mousewheel.min.js',
			'js/jquery.imageviewer.js',
			'js/fn_input_tab.js'
		);
	}

	public function head()
	{
		$cadResult = $this->owner;
		$series = $cadResult->Series[0];
		$modality = $series->modality;
		$presets = GrayscalePreset::findPresetsAssoc($modality);
		$result = '<script type="text/javascript">'
			. 'circus.cadresult.fnInputGrayscalePresets = ' . json_encode($presets)
			. '</script>';
		return $result;
	}

	public function tabs()
	{
		return array(
			array (
				'label' => 'FN Input',
				'template' => 'fn_input_tab.tpl'
			)
		);
	}

	protected function defaultParams()
	{
		return array_merge(
			parent::defaultParams(),
			array('distThreshold' => 5)
		);
	}

	public function afterBlocks()
	{
		return $this->smarty->fetch('fn_input_afterblocks.tpl');
	}

	public function saveFeedback(Feedback $fb, $data)
	{
		if (!is_array($data))
			throw new LogicException('Invalid FN list data.');
		$pdo = DBConnector::getConnection();
		$sth = $pdo->prepare(
			'INSERT INTO fn_location(fb_id, location_x, ' .
			'location_y, location_z, nearest_lesion_id, integrated_from) ' .
			'VALUES (?, ?, ?, ?, ?, ?)'
		);
		foreach ($data as $fn)
		{
			if (!is_array($fn))
				throw new LogicException('Invalid FN data.');
			$sth->execute(array(
				$fb->fb_id,
				$fn['location_x'],
				$fn['location_y'],
				$fn['location_z'],
				$fn['nearest_lesion_id'] > 0 ? $fn['nearest_lesion_id'] : 0,
				0
			));
		}
		DBConnector::query(
			'INSERT INTO fn_count(fb_id, fn_num) VALUES(?, ?)',
			array($fb->fb_id, count($data)),
			'SCALAR'
		);
	}

	public function loadFeedback(Feedback $fb)
	{
		$rows = DBConnector::query(
			'SELECT * FROM fn_location WHERE fb_id=?',
			array($fb->fb_id),
			'ALL_ASSOC'
		);
		$result = array();
		foreach ($rows as $row)
		{
			$result[] = array(
				'location_x' => $row['location_x'],
				'location_y' => $row['location_y'],
				'location_z' => $row['location_z'],
				'nearest_lesion_id' => $row['nearest_lesion_id'] ?: null,
				'entered_by' => $fb->entered_by
			);
		}
		return $result;
	}

	public function integrateConsensualFeedback(array $personal_fb_list)
	{
		// Actual integration will be done by browser-side.
		// So we will just create the list of FN location input as
		// personal feedback.
		$displays = $this->owner->getDisplays();
		$result = array();
		foreach ($personal_fb_list as $pfb)
		{
			$fns = $pfb->additionalFeedback['fn_input'];
			foreach ($fns as $fn)
			{
				$result[] = array(
					'location_x' => $fn['location_x'],
					'location_y' => $fn['location_y'],
					'location_z' => $fn['location_z'],
					'entered_by' => $pfb->entered_by
				);
			}
		}
		return array('to_integrate' => $result);
	}

	public function additionalFeedbackID()
	{
		return self::FEEDBACK_ID;
	}
}

?>