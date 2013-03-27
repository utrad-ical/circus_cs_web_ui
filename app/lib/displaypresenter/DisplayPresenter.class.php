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
	 * @param string $template The name of the template file.
	 */
	protected function executeTemplate($template)
	{
		global $DIR_SEPARATOR;
		$smarty = $this->smarty;
		$smarty->assign('displayPresenterParams', $this->params);
		$path = $this->owner->configurationPath();
		if ($smarty->template_exists($path . $DIR_SEPARATOR . "display_presenter.tpl"))
		{
			return $smarty->fetch($path . $DIR_SEPARATOR . "display_presenter.tpl");
		}
		else
		{
			return $smarty->fetch($template);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see CadResultElement::defaultParams()
	 */
	protected function defaultParams()
	{
		return array(
			'caption' => 'Block Feedback',
			'displayExtractType' => 'row',
			'noResultMessage' => 'No Result.'
		);
	}

	/**
	 * Find the name of the key which can be used as display ID.
	 * @param $item array The input array.
	 */
	protected function findDisplayIdField(array $item)
	{
		if (isset($item['display_id']))
			return 'display_id';
		if (isset($item['sub_id']))
			return 'sub_id';
		if (isset($item['id']))
			return 'id';
		return null;
	}

	/**
	 * Returns the HTML that describes one given CAD display.
	 * This is a good point for subclasses to override.
	 * This method is called multiple times by the framework.
	 * In each call, the current display is assigned to the
	 * Smarty instance stored in $this->smarty.
	 * Call $this->smarty->get_template_vars('display') to get it.
	 * @return string The rendered HTML that describes the given CAD display.
	 */
	public function show()
	{
		return $this->executeTemplate('default_display_presenter.tpl');
	}

	/**
	 * Extract the displays from the input table.
	 * This is a good point for subclasses to override.
	 * You can add extra information to the extracted displays,
	 * or even create complete new display data sets.
	 * @param array $input The array of each table records.
	 * @return array The list of displays. Each key must hold a display id.
	 */
	public function extractDisplays(array $input)
	{
		switch ($this->params['displayExtractType'])
		{
			case 'all':
				$input['display_id'] = 1;
				$result = array(1 => $input);
				break;
			case 'row':
				$result = array();
				if (count($input) == 0) return $result;

				// auto-detect key used as display ID
				$head = $input[0];
				$key = $this->findDisplayIdField($head);

				if (count($input) > 1 && !$key)
					throw new Exception('Rows must be discriminated by display ID.');
				$index = 1;
				foreach ($input as $item)
				{
					$disp_id = $key ? $item[$key] : $index++;
					$item['display_id'] = $disp_id;
					$result[$disp_id] = $item;
				}
				ksort($result, SORT_NUMERIC);
				break;
			default:
				throw new Exception('displayExtractType is invalid.');
		}
		return $result;
	}
}
