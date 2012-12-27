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
				'visibleGroups' => 'admin',
				'filesMatch' => '/(\\.(jpe?g|png|gif)$|^attachment\\/)/i',
				'uploadableGropus' => 'admin',
				'deleteFilesMatch' => '{^attachment/.}',
				'enablePreview' => true,
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
		if ($this->checkUploadableGroups(Auth::currentUser()->Group))
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

	public function checkVisibleGroups(array $group)
	{
		return $this->checkGroups($group, $this->params['visibleGroups']);
	}

	public function checkUploadableGroups(array $group)
	{
		return $this->checkGroups($group, $this->params['uploadableGroups']);
	}

	public function checkDeletableGroups(array $group)
	{
		return $this->checkGroups($group, $this->params['deletableGroups']);
	}

	public function tabs()
	{
		$current_groups = Auth::currentUser()->Group;
		$this->_enabled = $this->checkVisibleGroups($current_groups);
		if (!$this->_enabled) return array();

		$this->smarty->assign('cad_file_manager_title', $this->params['title']);
		$this->smarty->assign('cad_file_manager_uploadable', $this->checkUploadableGroups($current_groups));
		if ($this->params['position'] == 'tab')
		{
			return array(array(
				'label' => $this->params['title'] ?: 'Download',
				'template' => 'cad_results/cad_file_manager.tpl',
			));
		}
		else
		{
			return array();
		}
	}

	protected function checkGroups(array $group, $access_string)
	{
		if (is_string($access_string))
		{
			$accessible = preg_split('/\s*\,\s*/', $access_string);
			foreach ($group as $gp)
			{
				if (array_search($gp->group_id, $accessible) !== false)
				{
					return true;
				}
			}
		}
		return false;
	}

	protected function export()
	{
		return $this->smarty->fetch('cad_results/cad_file_manager.tpl');
	}
}