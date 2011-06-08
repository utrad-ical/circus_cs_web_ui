<?php

/**
 * FnInputTab adds 'FN Input' tab and provides 'fn_location'
 * additional feedback mechanism.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class FnInputTab extends CadResultExtension implements IFeedbackListener
{
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

	public function afterBlocks()
	{
		return $this->smarty->fetch('fn_input_afterblocks.tpl');
	}

	public function saveFeedback($fb_id, $data)
	{
		if (!is_array($data) || !is_array($data['fnInput']))
			return;
		$fns = array();
		$no = 0;
		foreach ($data['fnInput'] as $fn)
		{
			$fns[] = array(
				'fn_id' => $no++,
				'fb_id' => 0,
				'location_x' => $fn['location_x'],
				'location_y' => $fn['location_y'],
				'location_z' => $fn['location_z'],
				'nearest_lesion_id' => 0,
				'integrate_fn_id' => ''
			);
		}
		$pdo = DBConnector::getConnection();
		// $sth = $pdo->prepare('INSERT INTO fn_location');
	}

	public function loadFeedback($fb_id)
	{
		//
	}

	public function integrateConsensualFeedback(array $personal_fb_list)
	{
		//
	}

	public function additionalFeedbackID()
	{
		return 'fn_input';
	}
}

?>