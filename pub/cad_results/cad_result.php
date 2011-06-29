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
	set_include_path(get_include_path() . PATH_SEPARATOR . $cadResult->pathOfPluginWeb());

	// Assigning the result to Smarty
	$smarty = new SmartyEx();

	$params['toTopDir'] = '../';
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
	$extensions = $cadResult->buildExtensions();

	if ($feedbackMode == 'personal')
	{
		$feedback = $cadResult->queryFeedback('user', $user_id, false);
	}
	else
	{
		$feedback = $cadResult->queryFeedback('consensual', null, false);
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
			foreach ($extensions as $ext)
			{
				if (!($ext instanceof IFeedbackListener))
					continue;
				$type = $ext->additionalFeedbackID();
				$additionalFeedback[$type] = $ext->integrateConsensualFeedback($pfbs);
			}
			$feedback = array(
				'blockFeedback' => $feedbackListener->integrateConsensualFeedback($pfbs),
				'additionalFeedback' => $additionalFeedback ?: array()
			);
		}
	}

	$avail_pfb_reason = '';
	$avail_pfb = $cadResult->feedbackAvailability('personal', $user, $avail_pfb_reason);
	$avail_cfb_reason = '';
	$avail_cfb = $cadResult->feedbackAvailability('consensual', $user, $avail_cfb_reason);
	if ($avail_cfb == 'locked' && $feedbackMode == 'consensual')
		critical_error($avail_cfb_reason);
	$feedback_status = $feedbackMode == 'personal' ? $avail_pfb : $avail_cfb;

	$requiringFiles = array();
	array_splice($requiringFiles, -1, 0, $displayPresenter->requiringFiles());
	array_splice($requiringFiles, -1, 0, $feedbackListener->requiringFiles());

	$tabs = array();
	$extParameters = array();
	foreach ($extensions as $ext)
	{
		$ext->setSmarty($smarty);
		array_splice($requiringFiles, -1, 0, $ext->requiringFiles());
		foreach ($ext->tabs() as $tab)
			array_push($tabs, $tab);
		$extParameters[get_class($ext)] = $ext->getParameter();
	}

	$presentationParams = array(
		'displayPresenter' => $displayPresenter->getParameter(),
		'feedbackListener' => $feedbackListener->getParameter(),
		'extensions' => $extParameters
	);

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
		'avail_cfb_reason' => $avail_cfb_reason,
		'avail_pfb_reason' => $avail_pfb_reason,
		'requiringFiles' => implode("\n", $requiringFiles),
		'cadResult' => $cadResult,
		'displays' => $cadResult->getDisplays(),
		'attr' => $cadResult->getAttributes(),
		'series' => $cadResult->Series[0],
		'displayPresenter' => $displayPresenter,
		'feedbackListener' => $feedbackListener,
		'presentationParams' => $presentationParams,
		'feedbacks' => $feedback,
		'params' => $params,
		'tabs' => $tabs,
		'extensions' => $extensions,
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