<?php

/**
 * DisplayPresenter renders the main part of the CAD result.
 * Typically, this outputs a lesion candidate as a static image with a
 * circle marker, with some additional information (such as confidence).
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
abstract class DisplayPresenter extends BlockElement
{
	/**
	 * Returns the HTML of the element.
	 */
	public function display($smarty)
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