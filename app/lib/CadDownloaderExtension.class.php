<?php

class CadDownloaderExtension extends CadResultExtension
{
	private $_enabled = false;

	public function defaultParams()
	{
		return array_merge(
			parent::defaultParams(),
			array(
				'position' => 'after',
				'filesMatch' => '/\\.(jpe?g|png|gif)$/i',
				// 'title' => 'Download'
			)
		);
	}

	public function requiringFiles()
	{
		return array('css/cad_downloader.css');
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

		$path = $this->cadResult->pathOfCadResult();
		$wpath = $this->cadResult->webPathOfCadResult();
		$m = $this->params['filesMatch'];
		$matched = array();
		if ($handle = opendir($path)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry == "." && $entry == "..") continue;
				if (preg_match($m, $entry))
				{
					$size = filesize($path . DIRECTORY_SEPARATOR . $entry);
					$link = $entry;
					if (is_array($this->params['substitutes']))
					{
						foreach ($this->params['substitutes'] as $sub)
						{
							print_r($link);
							$cnt = 0;
							$link = preg_replace($sub[0], $sub[1], $link, -1, $cnt);
							if ($cnt > 0) break;
						}
					}
					$matched[] = array(
						"file" => $entry,
						"link" => $link,
						"url" => "$wpath/$entry",
						"size" => $size
					);
				}
			}
			closedir($handle);
		}
		$this->smarty->assign('cad_downloader_items', $matched);
		$this->smarty->assign('cad_downloader_title', $this->params['title']);
		if ($this->params['position'] == 'tab')
		{
			return array(array(
				label => $this->params['title'] ?: 'Download',
				template => 'cad_results/cad_downloader.tpl'
			));
		}
		else
		{
			return array();
		}
	}

	protected function export()
	{
		return $this->smarty->fetch('cad_results/cad_downloader.tpl');
	}
}