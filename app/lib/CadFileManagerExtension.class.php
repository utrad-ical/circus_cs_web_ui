<?php

class CadFileManagerExtension extends CadResultExtension
{
	private $_enabled = false;

	public function defaultParams()
	{
		return array_merge(
			parent::defaultParams(),
			array(
				'position' => 'after',
				'filesMatch' => '/(\\.(jpe?g|png|gif)$|^attachment\\/)/i',
				'enableUpload' => false,
				'enablePreview' => true
				// 'title' => 'Download'
			)
		);
	}

	public function requiringFiles()
	{
		$result = array(
			'css/cad_dir_inspector.css',
			'jq/jquery.jplayer.min.js',
			'js/cad_dir_inspector.js'
		);
		if ($this->params['enableUpload'])
			$result[] = 'jq/jquery.upload-1.0.2.min.js';
		return $result;
	}

	public function beforeBlocks()
	{
		if (!$this->_enabled) return '';
		if ($this->params['position'] == 'before')
		{
			return $this->export();
		} else {
			return '';
		}
	}

	public function afterBlocks()
	{
		if (!$this->_enabled) return '';
		if ($this->params['position'] == 'after')
		{
			return $this->export();
		} else {
			return '';
		}
	}

	public function tabs()
	{
		$visible_groups = $this->params['visibleGroups'];
		if (is_string($visible_groups))
		{
			$groups = preg_split('/\s*\,\s*/', $visible_groups);
			foreach (Auth::currentUser()->Group as $gp)
			{
				if (array_search($gp->group_id, $groups) !== false)
				{
					$this->_enabled = true;
					break;
				}
			}
		}
		if (!$this->_enabled) return array();

		$this->smarty->assign('cad_file_manager_title', $this->params['title']);
		if ($this->params['position'] == 'tab')
		{
			return array(array(
				label => $this->params['title'] ?: 'Download',
				template => 'cad_results/cad_file_manager.tpl'
			));
		}
		else
		{
			return array();
		}
	}

	protected function export()
	{
		return $this->smarty->fetch('cad_results/cad_file_manager.tpl');
	}
}