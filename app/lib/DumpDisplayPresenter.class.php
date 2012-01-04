<?php

/**
 * DumpDisplayPresenter, subclass of DisplayPresenter, shows CAD displays
 * as simple 'table'. By default, each row contains column names and values of
 * the records of CAD result table.
 * This is convenient for debugging your CAD plugin.
 * This display presenter is also suitable for very simple 'measurement' type
 * CADs.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class DumpDisplayPresenter extends DisplayPresenter
{
	public function requiringFiles()
	{
		return 'css/dump_display_presenter.css';
	}

	public function show()
	{
		return $this->executeTemplate('dump_display_presenter.tpl');
	}
}
