<?php

/**
 * DisplayPresenter renders the main part of the CAD result. It difines
 * how 'CAD displays' are visualized in web browsers.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
abstract class DisplayPresenter extends BlockElement
{
	/**
	 * Returns the HTML that describes one given CAD display.
	 * @param Smarty the Smarty instance
	 */
	public function show($smarty)
	{
		$smarty->assign('displayPresenterParams', $this->params);
	}

	/**
	 * Extract the displays from the input table.
	 * @param array $input The array of each table records.
	 * @return array The list of displays, each key holds a display id.
	 */
	public function extractDisplays($input)
	{
		$result = array();
		foreach ($input as $rec)
		{
			$item = $rec;
			$disp_id = $item['display_id'] ?: $item['sub_id'] ?: $item['id'];
			$item['display_id'] = $disp_id;
			$result[$disp_id] = $item;
		}
		return $result;
	}
}

?>