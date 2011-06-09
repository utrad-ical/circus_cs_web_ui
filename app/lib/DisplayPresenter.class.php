<?php

/**
 * DisplayPresenter renders the main part of the CAD result. It defines
 * how 'CAD displays' are visualized in web browsers.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class DisplayPresenter extends CadBlockElement
{
	/**
	 * Protected method to execute Smarty.
	 * When there is 'display_presenter.tpl' template in the plugin's web
	 * configuration directory, always use it instead of built-in templates.
	 * @param Smarty $smarty
	 * @param string $template The name of the template file.
	 */
	protected function executeTemplate($template)
	{
		$smarty = $this->smarty;
		$smarty->assign('displayPresenterParams', $this->params);
		if ($smarty->template_exists("display_presenter.tpl"))
		{
			return $smarty->fetch("display_presenter.tpl");
		}
		else
		{
			return $smarty->fetch($template);
		}
	}

	protected function defaultParams()
	{
		return array('caption' => 'Block Feedback');
	}

	/**
	 * Returns the HTML that describes one given CAD display.
	 * @param Smarty the Smarty instance
	 */
	public function show()
	{
		return $this->executeTemplate('default_display_presenter.tpl');
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
		ksort($result, SORT_NUMERIC);
		return $result;
	}
}

?>