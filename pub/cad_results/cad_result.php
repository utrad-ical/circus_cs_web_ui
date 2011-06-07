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
		'label' => 'Job ID',
		'type' => 'int',
		'required' => true,
		'min' => 1
	),
	'feedbackMode' => array(
		'label' => 'feedback mode',
		'type' => 'select',
		'required' => false,
		'default' => 'personal',
		'options' => array('personal', 'consensual'),
	)
));

try
{
	if ($validator->validate($_REQUEST))
	{
		$params = $validator->output;
	}
	else
	{
		throw new Exception(implode("\n", $validator->errors));
	}
	show_cad_results($params['jobID'], $params['feedbackMode']);
}
catch (Exception $e)
{
	critical_error($e->getMessage(), 'Error');
}


/**
 * Displays CAD Result
 */
function show_cad_results($jobID, $feedbackMode) {
	global $DIR_SEPARATOR;

	// Retrieve the CAD Result
	$cadResult = new CadResult($jobID);
	if (!isset($cadResult->job_id))
	{
		critical_error('The CAD result for this ID was not found.', 'Not Found');
	}

	// Assigning the result to Smarty
	$smarty = new SmartyEx();

	$params['toTopDir'] = '../';
	$sort = $cadResult->sorter();
	$user = Auth::currentUser();
	$user_id = $user->user_id;

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
	$displayPresenter->setSmarty($smarty);
	$displayPresenter->prepare();
	$feedbackListener = $cadResult->feedbackListener();
	$feedbackListener->setSmarty($smarty);
	$feedbackListener->prepare();

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
		if ($feedbackMode == 'consensual')
		{
			$pfbs = $cadResult->queryFeedback('personal');
			foreach ($pfbs as $pfb) $pfb->loadFeedback();
			$feedback = array(
				'blockFeedback' => $feedbackListener->integrateConsensualFeedback($pfbs)
			);
		}
	}

	$avail_pfb = $cadResult->feedbackAvailability('personal', $user);
	$avail_cfb = $cadResult->feedbackAvailability('consensual', $user);
	if ($avail_cfb == 'locked' && $feedbackMode == 'consensual')
		critical_error('You can not enter consensual mode.');
	$feedback_status = $feedbackMode == 'personal' ? $avail_pfb : $avail_cfb;

	$requiringFiles = array();
	array_splice($requiringFiles, -1, 0, $displayPresenter->requiringFiles());
	array_splice($requiringFiles, -1, 0, $feedbackListener->requiringFiles());

	$extensions = $cadResult->buildExtensions();

	$tabs = array();
	foreach ($extensions as $ext)
	{
		$ext->setSmarty($smarty);
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
		'feedbackStatus' => $feedback_status,
		'avail_cfb' => $avail_cfb,
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
		'errorTitle' => $title
	));
	$smarty->display('critical_error.tpl');
	exit();
}

?>