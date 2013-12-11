<?php
/**
 * BlockSorter extension provides sorting of display blocks.
 * Additionally, it displays a <select> element for choosing the sort key
 * and the sorting order.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class BlockSorter extends CadResultExtension
{
	public function defaultParams()
	{
		return array_merge(
			parent::defaultParams(),
			array(
				'visible' => false,
				'position' => 'before',
				'label' => 'Sort:',
				'useUserPreference' => true,
				'options' => 'auto'
			)
		);
	}

	public function requiringFiles()
	{
		return array('js/cad_result_sorter.js', 'css/cad_result_sorter.css');
	}

	private function sorterHtml($class)
	{
		$this->smarty->assign('sorter', $this->getSortKeys());
		$this->smarty->assign('sorterClass', $class);
		return $this->smarty->fetch('cad_results/cad_result_sorter.tpl');
	}

	private function getSortKeys()
	{
		$p = $this->params;
		// sort keys can be defined by presentation.json file.
		if (is_array($p['options'])) return $p['options'];
		// or default keys is determined according to the display presenter.
		return $this->owner->presentation()->displayPresenter()->sortKeys();
	}

	public function head()
	{
		global $DEFAULT_CAD_PREF_USER;
		$p = $this->params;
		if ($p['useUserPreference'])
		{
			$pref = $this->owner->userPreference();
			// Legacy: Default preference is used for the time being
			$default_pref = array('sortKey'   => $p['defaultKey'],
								  'sortOrder' => $p['defaultOrder']);
			$pref = array_merge($default_pref, $pref);

			if (isset($pref['sortKey']) && isset($pref['sortOrder']))
			{
				$initials = array(
					'key' => $pref['sortKey'],
					'order' => strtolower($pref['sortOrder'])
				);
			}
			else
			{
				throw new CadPresentationException(var_dump($p));
					//'"useUserPreference" is true but there is not such preference value.');
			}
		}
		else
		{
			if (!isset($p['defaultKey']))
				throw new CadPresentationException(
					'"defaultKey" must be specified as BlockSorter option.');
			$order = $p['defaultOrder'] == 'desc' ? 'desc' : 'asc';
			$initials = array('key' => $p['defaultKey'], 'order' => $order);
		}
		$result = '<script type="text/javascript">' . "\n" .
			"circus.cadresult.presentation.extensions.BlockSorter.initials = " .
			json_encode($initials) . ";\n" .
			'</script>';
		return $result;
	}

	public function beforeBlocks()
	{
		$p = $this->params;
		if ($p['visible'] && ($p['position'] == 'before' || $p['position'] == 'both'))
			return $this->sorterHtml('sorter-area-before');
		else
			return '';
	}

	public function afterBlocks()
	{
		$p = $this->params;
		if ($p['visible'] && ($p['position'] == 'after' || $p['position'] == 'both'))
			return $this->sorterHtml('sorter-area-after');
		else
			return '';
	}

	public function preferenceForm()
	{
		if (!$this->params['useUserPreference']) return null;
		$opts = '';
		$keys = $this->getSortKeys();
		foreach ($keys as $item) {
			$opts .= "<option value='$item[key]'>$item[label]</option>\n";
		}

		return <<<EOL
<tr>
<th>Sort</th>
<td>
<select name="sortKey">
$opts
</select>
<label><input type="radio" name="sortOrder" value="ASC" />Asc.</label>
<label><input type="radio" name="sortOrder" value="DESC" />Desc.</label>
</td>
</tr>
EOL;
	}

	public function preferenceValidationRule()
	{
		if (!$this->params['useUserPreference']) return array();
		$keys = array();
		foreach ($this->getSortKeys() as $item) $keys[] = $item['key'];
		return array(
			'sortKey' => array('type' => 'select', 'options' => $keys),
			'sortOrder' => '[ASC|DESC]'
		);
	}
}
