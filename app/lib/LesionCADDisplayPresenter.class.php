<?php

/**
 * Exports HTML for lesion CAD display.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class LesionCADDisplayPresenter extends DisplayPresenter
{
	function display($smarty)
	{
		parent::display($smarty);
		return $smarty->fetch('cad_results/lesion_cad_display_presenter.tpl');
	}

}

?>