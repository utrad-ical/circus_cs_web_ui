<?php

/**
 * Exports HTML for lesion CAD block content.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class LesionCADBlockContent extends BlockContent
{
	function display($smarty)
	{
		parent::display($smarty);
		return $smarty->fetch('cad_results/lesion_cad_block_content.tpl');
	}

}

?>