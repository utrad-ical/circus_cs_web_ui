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
	 * @return array The list of displays.
	 */
	public function extractDisplays($input)
	{
		foreach ($input as &$rec)
		{
			$disp_id = $rec['display_id'] ?: $rec['sub_id'] ?: $rec['id'];
			$rec['display_id'] = $disp_id;
		}
		return $input;
	}
}

?>