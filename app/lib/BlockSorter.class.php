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
				'useUserPreference' => false
			)
		);
	}

	public function requiringFiles()
	{
		return array('js/cad_result_sorter.js', 'css/cad_result_sorter.css');
	}

	private function sorterHtml($class)
	{
		$this->smarty->assign('sorter', $this->params);
		$this->smarty->assign('sorterClass', $class);
		return $this->smarty->fetch('cad_results/cad_result_sorter.tpl');
	}

	public function head()
	{
		$p = $this->params;
		if ($p['useUserPreference'])
		{
			$pref = $this->owner->userPreference();
			if (isset($pref['sortKey']) && isset($pref['sortOrder']))
			{
				$initials = array(
					'key' => $pref['sortKey'],
					'order' => strtolower($pref['sortOrder'])
				);
			}
			else
			{
				throw new CadPresentationException(
					'"useUserPreference" is true but there is not such preference value.');
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
}
