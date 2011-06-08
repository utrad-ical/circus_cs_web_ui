<?php

class BlockSorter extends CadResultExtension
{
	public function requiringFiles()
	{
		return array('js/cad_result_sorter.js');
	}

	public function beforeBlocks()
	{
		if ($this->params['visible']) {
			$this->smarty->assign('sorter', $this->params);
			return $this->smarty->fetch('cad_results/cad_result_sorter.tpl');
		}
		else
			return '';
	}
}

?>