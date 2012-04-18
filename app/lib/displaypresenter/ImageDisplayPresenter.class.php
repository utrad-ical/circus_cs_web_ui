<?php

/**
 * ImageDisplayPresenter, subclass of DisplayPresenter, simply shows
 * one image per block.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class ImageDisplayPresenter extends DisplayPresenter
{
	function defaultParams()
	{
		return array_merge(
			parent::defaultParams(),
			array(
				'file' => 'result%04d.jpg',
				'showID' => true
			)
		);
	}

	public function show()
	{
		return $this->executeTemplate('image_display_presenter.tpl');
	}
}
