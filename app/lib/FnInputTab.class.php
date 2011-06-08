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

	public function saveFeedback($fb_id, $data)
	{
		if (!is_array($data))
			throw new LogicException('Invalid FN list data.');
		$pdo = DBConnector::getConnection();
		$sth = $pdo->prepare(
			'INSERT INTO fn_location(fb_id, location_x, ' .
			'location_y, location_z, nearest_lesion_id, integrate_fn_id) ' .
			'VALUES (?, ?, ?, ?, ?, ?)'
		);
		foreach ($data as $fn)
		{
			if (!is_array($fn))
				throw new LogicException('Invalid FN data.');
			$sth->execute(array(
				$fb_id,
				$fn['location_x'],
				$fn['location_y'],
				$fn['location_z'],
				0,
				0
			));
		}
		DBConnector::query(
			'INSERT INTO fn_count(fb_id, fn_num) VALUES(?, ?)',
			array($fb_id, count($data)),
			'SCALAR'
		);
	}

	public function loadFeedback($fb_id)
	{
		$rows = DBConnector::query(
			'SELECT * FROM fn_location WHERE fb_id=?',
			array($fb_id),
			'ALL_ASSOC'
		);
		$result = array();
		foreach ($rows as $row)
		{
			$result[] = array(
				'location_x' => $row['location_x'],
				'location_y' => $row['location_y'],
				'location_z' => $row['location_z'],
				'nearest_lesion_id' => $row['nearest_lesion_id'],
				'integrate_fn_id' => $row['integrate_fn_id']
			);
		}
		return $result;
	}

	public function integrateConsensualFeedback(array $personal_fb_list)
	{
		//
	}

	public function additionalFeedbackID()
	{
		return self::FEEDBACK_ID;
	}
}

?>