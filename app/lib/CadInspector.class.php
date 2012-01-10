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

	private $avail_modules = array(
		'basic' => 'inspector_basic.tpl',
		'series' => 'inspector_series.tpl',
		'presentation' => 'inspector_presentation.tpl',
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
		$modules = array();
		foreach ($module_names as $module)
		{
			if ($module == 'all')
			{
				$modules = array_values($this->avail_modules);
				break;
			}
			else if ($m = $this->avail_modules[$module])
			{
				if (array_search($m, $modules) === false)
					$modules[] = $m;
			}
		}
		$this->smarty->assign('inspector_modules', $modules);
		return '';
	}

	public function tabs()
	{
		$visible_groups = $this->params['visible_groups'];
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
