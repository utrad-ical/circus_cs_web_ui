<?php

session_cache_limiter('none');
session_start();

include("../common.php");

//------------------------------------------------------------------------------
// Import and validate $_POST data
//------------------------------------------------------------------------------

$validator = new FormValidator();
$validator->addRules(array(
	'jobID' => array(
		"type" => "int",
		"required" => false, // true, // transient
		"min" => 1,
		"errorMes" => "[ERROR] CAD ID is invalid."
	),
	'feedbackMode' => array(
		"type" => "select",
		"required" => false, // true, // transient
		"options" => array("personal", "consensual"),
		"errorMes" => "[ERROR] 'Feedback mode' is invalid."
	)
));
if ($validator->validate($_POST))
{
	$params = $validator->output;
}

show_cad_results($job_id, $feedbackMode);

/**
 * Displays CAD Result
 */
function show_cad_results($job_id, $feedbackMode) {
	// Retrieve the CAD Result
	$cadResult = new CADResult($jobID);

	// Assigning the result to Smarty
	$smarty = new SmartyEx();
	$params['toTopDir'] = '../';
	$smarty->assign(array(
		'feedbackMode' => $feedbackMode,
		'cadResult' => $cadResult,
		'blocks' => $cadResult->getBlocks(),
		'attr' => $cadResult->getAttributes(),
		'blockContent' => prepare_block_content(),
		'evalListener' => prepare_eval_listener(),
		'params' => array('toTopDir' => '../')
	));
	$smarty->display('cad_results/cad_result.tpl');
}

function prepare_eval_listener()
{
	$listener = new SelectionEvalListener();
	$listener->setParameter(array(
		"selections" => array(
			array(
				"label" => 'TP',
				"value" => 1
			),
			array(
				"label" => 'FP',
				"value" => 2
			),
			array(
				"label" => 'pending',
				"value" => 3
			)
		)
	));
	$js = 'js/selection_eval_listener.js';
	return $listener;
}

function prepare_block_content()
{
	$content = new LesionCADBlockContent();
	return $content;
}

?>