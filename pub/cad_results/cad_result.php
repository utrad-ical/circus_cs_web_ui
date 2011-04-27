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

	$presentation = new CADPluginPresentation();
	$presentation->plugin_name = 'MRA-CAD_v2';
	$presenter = $presentation->buildDisplayPresenter();
	$listener  = $presentation->buildFeedbackListener();

	$smarty->assign(array(
		'feedbackMode' => $feedbackMode,
		'cadResult' => $cadResult,
		'displays' => $cadResult->getDisplays(),
		'attr' => $cadResult->getAttributes(),
		'displayPresenter' => $presenter,
		'feedbackListener' => $listener,
		'params' => array('toTopDir' => '../')
	));
	$smarty->display('cad_results/cad_result.tpl');
}

?>