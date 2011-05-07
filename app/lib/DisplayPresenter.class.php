<?php

/**
 * DisplayPresenter renders the main part of the CAD result. It defines
 * how 'CAD displays' are visualized in web browsers.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class DisplayPresenter extends BlockElement
{
	/**
	 * Protected method to execute Smarty.
	 * This method first looks at the plugins directory.
	 * If there is not template file of such name, it uses default
	 * template directory of CIRCUS CS.
	 * @param Smarty $smarty
	 * @param string $template The name of the template file.
	 */
	protected function executeTemplate($smarty, $template)
	{
		$smarty->assign('displayPresenterParams', $this->params);
		$web_path = $this->owner->pathOfPluginWeb();
		if ($smarty->template_exists("file:$web_path/display_presenter.tpl"))
		{
			return $smarty->fetch("file:$web_path/display_presenter.tpl");
		}
		elseif ($smarty->template_exists("file:$web_path/$template"))
		{
			return $smarty->fetch("file:$web_path/$template");
		}
		else
		{
			return $smarty->fetch("cad_results/$template");
		}
	}

	/**
	 * Returns the HTML that describes one given CAD display.
	 * @param Smarty the Smarty instance
	 */
	public function show($smarty)
	{
		return $this->executeTemplate($smarty, 'display_presenter.tpl');
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