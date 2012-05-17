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
			'js/fn_input_tab.js'
		);
	}

	public function head()
	{
		$series = $this->cadResult->Series[0];
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
			if (!isset($fn['location_x']) || !isset($fn['location_y']) ||
				!isset($fn['location_z']))
			{
				continue;
			}
			if (count($fn['personal_fn_id']) > 0)
			{
				$integrated_from = implode(',', $fn['personal_fn_id']);
			}
			else
			{
				$integrated_from = null;
			}
			$volume_z = $this->cadResult->sliceNumToVolume($fn['location_z'], 0);

			$sth->execute(array(
				$fb->fb_id,
				$fn['location_x'],
				$fn['location_y'],
				$volume_z,
				$fn['nearest_lesion_id'] > 0 ? $fn['nearest_lesion_id'] : 0,
				$integrated_from
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
		$cr = $this->cadResult;
		foreach ($rows as $row)
		{
			$result[] = array(
				'fn_id' => $row['fn_id'],
				'location_x' => $row['location_x'],
				'location_y' => $row['location_y'],
				'location_z' => $cr->volumeToSliceNum($row['location_z'], 0),
				'nearest_lesion_id' => $row['nearest_lesion_id'] ?: null,
				'entered_by' => $fb->entered_by
			);
		}
		return $result;
	}

	/**
	 * Integrates the personal FN list and make FN list for consensual FB.
	 * While integrating, all personal FN's are snapped to their nearest
	 * lesion candidates (within the distance of specified threshold).
	 * Note that this method is run once to create 'initial' state of the
	 * consensual FN locations. After the 'FN Input' tab is displayed,
	 * location processing is done in JavaScript. So fn_input_tab.js file has
	 * similar methods.
	 * @see IFeedbackListener::integrateConsensualFeedback()
	 */
	public function integrateConsensualFeedback(array $personal_fb_list)
	{
		$displays = $this->cadResult->getDisplays();
		$result = array();
		foreach ($personal_fb_list as $pfb)
		{
			$fns = $pfb->additionalFeedback['fn_input'];
			foreach ($fns as $fn)
			{
				$item = array(
					'location_x' => $fn['location_x'],
					'location_y' => $fn['location_y'],
					'location_z' => $fn['location_z'],
					'entered_by' => $pfb->entered_by,
					'personal_fn_id' => array($fn['fn_id'])
				);
				$item = $this->snapToNearestHiddenCand($displays, $item);
				$result[] = $item;
			}
		}
		return $this->makeUnique($result);
	}

	protected function distance2($a, $b)
	{
		$dx = $a['location_x'] - $b['location_x'];
		$dy = $a['location_y'] - $b['location_y'];
		$dz = $a['location_z'] - $b['location_z'];
		return $dx * $dx + $dy * $dy + $dz * $dz;
	}

	protected function findNearestHiddenCand(array $displays, $item)
	{
		$distTh = $this->params['distThreshold'];
		$distTh = $distTh * $distTh;
		$distMin = 1000000;
		$ret = null;
		foreach ($displays as $id => $display)
		{
			$dist = $this->distance2($display, $item);
			if($dist < $distMin)
			{
				$distMin = $dist;
				if($distMin < $distTh)
					$ret = $id;
			}
		}
		return $ret;
	}

	protected function snapToNearestHiddenCand(array $displays, $fn)
	{
		$nearest = $this->findNearestHiddenCand($displays, $fn);
		if ($nearest != null)
		{
			$item = $displays[$nearest];
			$fn['location_x'] = $item['location_x'];
			$fn['location_y'] = $item['location_y'];
			$fn['location_z'] = $item['location_z'];
			$fn['nearest_lesion_id'] = $nearest;
		}
		return $fn;
	}

	protected function makeUnique(array $fn_list)
	{
		$buf = array();
		$result = array();
		foreach ($fn_list as $fn)
		{
			$key = "$fn[location_x],$fn[location_y],$fn[location_z]";
			$buf[$key] = $buf[$key] ?: array(
				'nearest_lesion_id' => $fn['nearest_lesion_id'],
				'entered_by' => array(),
				'location_x' => $fn['location_x'],
				'location_y' => $fn['location_y'],
				'location_z' => $fn['location_z'],
				'personal_fn_id' => array()
			);
			$buf[$key]['entered_by'][$fn['entered_by']] = 1;
			array_splice($buf[$key]['personal_fn_id'], -1, 0, $fn['personal_fn_id']);
		}
		foreach ($buf as $key => $item)
		{
			$joined = implode(',', array_keys($item['entered_by']));
			$result[] = array(
				'location_x' => $item['location_x'],
				'location_y' => $item['location_y'],
				'location_z' => $item['location_z'],
				'nearest_lesion_id' => $item['nearest_lesion_id'],
				'entered_by' => $joined,
				'personal_fn_id' => $item['personal_fn_id']
			);
		}
		return $result;
	}

	public function additionalFeedbackID()
	{
		return self::FEEDBACK_ID;
	}
}
