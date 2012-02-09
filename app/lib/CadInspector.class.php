<?php

/**
 * CadInspector is a extension used in a CAD result page
 * that adds a tab to display various useful information about the job.
 * Handy when developing and debugging a plugin.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class CadInspector extends CadResultExtension
{
	private $enabled = false;
	private $modules = array();

	private static $avail_modules = array(
		'basic' => 'inspector_basic.tpl',
		'series' => 'inspector_series.tpl',
		'presentation' => 'inspector_presentation.tpl',
		'feedback' => 'inspector_feedback.tpl',
		'files' => 'inspector_files.tpl',
		'attributes' => 'inspector_attributes.tpl',
		'displays' => 'inspector_displays.tpl'
	);

	public function requiringFiles()
	{
		return array('css/cad_inspector.css');
	}

	public function defaultParams()
	{
		return array_merge(parent::defaultParams(), array(
			'modules' => 'all',
			'visible_groups' => null
		));
	}

	public function head()
	{
		if (!$this->enabled)
			return;
		$params = $this->getParameter();
		$module_names = preg_split('/\s*\,\s*/', $params['modules']);
		$this->modules = array();
		foreach ($module_names as $module)
		{
			if ($module == 'all')
			{
				$this->modules = self::$avail_modules;
				break;
			}
			else if ($m = self::$avail_modules[$module])
			{
				if (array_search($m, $this->modules) === false)
					$this->modules[$module] = $m;
			}
		}
		$this->smarty->assign('inspector_modules', $this->modules);

		if ($this->modules['feedback'])
			$this->assignFeedback();
		if ($this->modules['files'])
			$this->assignFiles();

		return '';
	}

	protected function assignFeedback()
	{
		$entries = $this->cadResult->queryFeedback();
		foreach ($entries as $entry)
		{
			$entry->loadFeedback();
			$inspector_feedback[] = array(
				'type' => $entry->is_consensual ? 'Consensual' : 'Personal',
				'registerer' => $entry->entered_by,
				'feedback' => array(
					'block feedback' => $entry->blockFeedback,
					'additional feedback' => $entry->additionalFeedback
				)
			);
		}

		$this->smarty->assign('inspector_feedback', $inspector_feedback);
	}

	protected function assignFiles()
	{
		$path = $this->cadResult->pathOfCadResult();
		$entries = @scandir($path);
		if (!$entries) return;
		foreach ($entries as $entry)
		{
			if ($entry == '.' || $entry == '..') continue;
			$file = "$path/$entry";
			$size = filesize($file);
			$type = filetype($file);
			$inspector_files[] = array(
				'file' => $entry,
				'size' => $size,
				'type' => $type
			);
		}
		$this->smarty->assign('inspector_files', $inspector_files);
	}

	public function tabs()
	{
		$visible_groups = $this->params['visibleGroups'];
		if (is_string($visible_groups))
		{
			$groups = preg_split('/\s*\,\s*/', $visible_groups);
			$this->enabled = false;
			foreach (Auth::currentUser()->Group as $gp)
			{
				if (array_search($gp->group_id, $groups) !== false)
				{
					$this->enabled = true;
					break;
				}
			}
		}
		else
		{
			$this->enabled = true;
		}

		if ($this->enabled)
			return array(
				array (
					'label' => 'Inspector',
					'template' => 'cad_inspector.tpl'
				)
			);
		else
			return array();
	}
}
