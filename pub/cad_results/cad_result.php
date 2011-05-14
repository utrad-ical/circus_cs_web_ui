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
		"default" => 'personal',
		"options" => array("personal", "consensual"),
		"errorMes" => "[ERROR] 'Feedback mode' is invalid."
	)
));
if ($validator->validate($_REQUEST))
{
	$params = $validator->output;
}

$tabs = array();
show_cad_results($params['jobID'], $params['feedbackMode']);


function register_result_tab($label, CadResultTab $content)
{
	global $tabs;
	if ($content)
		$tabs[] = array('label' => $label, 'content' => $content);
}

/**
 * Displays CAD Result
 */
function show_cad_results($jobID, $feedbackMode) {
	global $DIR_SEPARATOR, $tabs;

	// Retrieve the CAD Result
	$cadResult = new CadResult($jobID);

	// Assigning the result to Smarty
	$smarty = new SmartyEx();

	$params['toTopDir'] = '../';
	$sort = $cadResult->sorter();
	$user = $_SESSION['userID'];

	if ($feedbackMode == 'personal')
	{
		$feedback = $cadResult->queryFeedback('user', $_SESSION['userID']);
	}
	else
	{
		$feedback = $cadResult->queryFeedback('consensual');
	}
	if (is_array($feedback) && count($feedback) > 0)
	{
		$feedback = array_shift($feedback);
		$feedback->loadFeedback();
	}
	else
	{
		$feedback = null;
	}

	// Enabling plugin-specific template directory
	$td = $smarty->template_dir;
	$smarty->template_dir = array(
		$cadResult->pathOfPluginWeb(),
		$td . $DIR_SEPARATOR . 'cad_results',
		$td
	);

	$displayPresenter = $cadResult->displayPresenter();
	$displayPresenter->prepare($smarty);
	$feedbackListener = $cadResult->feedbackListener();
	$feedbackListener->prepare($smarty);

	register_result_tab('CAD Detail', new CadDetailTab());
	register_result_tab('FN Input', new CadDetailTab());

	$smarty->assign(array(
		'feedbackMode' => $feedbackMode,
		'cadResult' => $cadResult,
		'displays' => $cadResult->getDisplays(),
		'attr' => $cadResult->getAttributes(),
		'series' => $cadResult->Series[0],
		'displayPresenter' => $displayPresenter,
		'feedbackListener' => $feedbackListener,
		'feedbacks' => $feedback,
		'params' => $params,
		'sorter' => $sort,
		'tabs' => $tabs,
		'sort' => array('key' => $sort['defaultKey'], 'order' => $sort['defaultOrder'])
	));

	// Render using Smarty
	$smarty->display('cad_result.tpl');
}

?>