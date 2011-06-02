<?php

$params['toTopDir'] = '../';
include("../common.php");
Auth::checkSession();

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

try
{
	show_cad_results($params['jobID'], $params['feedbackMode']);
}
catch (Exception $e)
{
	critical_error($e->getMessage(), get_class($e));
}


/**
 * Displays CAD Result
 */
function show_cad_results($jobID, $feedbackMode) {
	global $DIR_SEPARATOR;

	// Retrieve the CAD Result
	$cadResult = new CadResult($jobID);

	// Assigning the result to Smarty
	$smarty = new SmartyEx();

	$params['toTopDir'] = '../';
	$sort = $cadResult->sorter();
	$user = Auth::currentUser();
	$user_id = $user->user_id;

	if ($feedbackMode == 'personal')
	{
		$feedback = $cadResult->queryFeedback('user', $user_id);
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

	if (!$cadResult->checkCadResultAvailability($user->Group))
		critical_error('You do not have privilege to see this CAD result.');

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

	$requiringFiles = array();
	array_splice($requiringFiles, -1, 0, $displayPresenter->requiringFiles());
	array_splice($requiringFiles, -1, 0, $feedbackListener->requiringFiles());

	$extensions = array(
		new CadDetailTab($cadResult, $smarty, 1),
		new FnInputTab($cadResult, $smarty, 2)
	);
	usort($extensions, function ($a, $b) { return $b->priority - $a->priority; });

	$tabs = array();
	foreach ($extensions as $ext)
	{
		array_splice($requiringFiles, -1, 0, $ext->requiringFiles());
		foreach ($ext->tabs() as $tab)
			array_push($tabs, $tab);
	}

	$pop = $cadResult->webPathOfPluginPub() . '/';
	if (file_exists($pop . 'cad_result.css'))
	{
		$requiringFiles[] = $pop . 'cad_result.css';
	}
	if (file_exists($pop . 'cad_result.js'))
	{
		$requiringFiles[] = $pop . 'cad_result.js';
	}
	$requiringFiles = array_unique($requiringFiles); // keys preserved

	$smarty->assign(array(
		'feedbackMode' => $feedbackMode,
		'requiringFiles' => implode("\n", $requiringFiles),
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
		'extensions' => $extensions,
		'sort' => array('key' => $sort['defaultKey'], 'order' => $sort['defaultOrder'])
	));

	// Render using Smarty
	$smarty->display('cad_result.tpl');
}

function critical_error($message, $title = null)
{
	$smarty = new SmartyEx();
	$smarty->assign(array(
		'params' => array('toTopDir' => '../'),
		'message' => $message,
		'title' => $title
	));
	$smarty->display('critical_error.tpl');
	exit();
}

?>