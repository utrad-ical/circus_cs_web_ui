<?php
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
	critical_error(
		$e->getMessage(),
		is_a($e, 'CadPresentationException') ? 'Configuration Error' : 'Error'
	);
}


/**
 * Displays CAD Result
 */
function show_cad_results($jobID, $feedbackMode) {
	global $DIR_SEPARATOR;

	// Retrieve the CAD Result
	$cadResult = new CadResult($jobID);
	if (!isset($cadResult->job_id))
		critical_error('The CAD result for this ID was not found.', 'Not Found');
	if ($cadResult->status == Job::JOB_FAILED)
		critical_error('This CAD job did not finish correctly.', 'Execution Error');
	if ($cadResult->status != Job::JOB_SUCCEEDED)
		critical_error('This CAD job has not finished yet.', 'Not Finished');
	set_include_path(get_include_path() . PATH_SEPARATOR . $cadResult->Plugin->configurationPath());

	// Assigning the result to Smarty
	$smarty = new SmartyEx();

	$user = Auth::currentUser();
	$user_id = $user->user_id;

	if (!$cadResult->checkCadResultAvailability($user->Group))
		critical_error('You do not have privilege to see this CAD result.');

	// Enabling plugin-specific template directory
	$td = $smarty->template_dir;
	$smarty->template_dir = array(
		$cadResult->Plugin->configurationPath(),
		$td . $DIR_SEPARATOR . 'cad_results',
		$td
	);

	$displayPresenter = $cadResult->Plugin->presentation()->displayPresenter();
	$displayPresenter->setSmarty($smarty);
	$displayPresenter->setCadResult($cadResult);
	$displayPresenter->prepare();

	$feedbackListener = $cadResult->Plugin->presentation()->feedbackListener();
	$feedbackListener->setSmarty($smarty);
	$feedbackListener->setCadResult($cadResult);
	$feedbackListener->prepare();

	$noFeedback = $feedbackListener instanceof NullFeedbackListener;

	$extensions = $cadResult->Plugin->presentation()->extensions();
	foreach ($extensions as $ext)
	{
		$ext->setCadResult($cadResult);
		$noFeedback = $noFeedback && !($ext instanceof IFeedbackListener);
	}

	// Prepare feedback data
	$personalOpinions = array();
	if ($feedbackMode == 'personal')
	{
		$feedback = $cadResult->queryFeedback('user', $user_id, false);
	}
	else
	{
		$feedback = $cadResult->queryFeedback('consensual', null, false);
		$opinions = $cadResult->queryFeedback('personal', null, true);
		foreach ($opinions as $item)
		{
			$item->loadFeedback();
			$personalOpinions[] = array(
				'entered_by' => $item->entered_by,
				'blockFeedback' => $item->blockFeedback,
				'additionalFeedback' => $item->additionalFeedback
			);
		}
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
			$feedback = $cadResult->buildInitialConsensualFeedback($opinions);
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

	$url_pub  = $cadResult->webPathOfPluginPub() . '/';
	$path_pub = $cadResult->pathOfPluginPub() . '/';
	if (file_exists($path_pub . 'cad_result.css'))
	{
		$requiringFiles[] = $url_pub . 'cad_result.css';
	}
	if (file_exists($path_pub . 'cad_result.js'))
	{
		$requiringFiles[] = $url_pub . 'cad_result.js';
	}
	$requiringFiles = array_unique($requiringFiles); // keys preserved

	if ($user->anonymized || !$user->hasPrivilege(Auth::PERSONAL_INFO_VIEW))
		Patient::$anonymizeMode = true;

	$seriesList = array();
	foreach ($cadResult->ExecutedSeries as $es)
	{
		$series = $es->Series;
		$vid = (int)($es->volume_id);
		$seriesList[$vid] = array(
			'volumeID' => $vid,
			'studyUID' => $series->Study->study_instance_uid,
			'seriesUID' => $series->series_instance_uid,
			'numImages' => $series->image_number,
			'start_img_num' => $es->start_img_num,
			'image_delta' => $es->image_delta,
			'image_count' => $es->image_count
		);
	}
	ksort($seriesList, SORT_NUMERIC);

	$smarty->assign(array(
		'noFeedback' => $noFeedback,
		'feedbackMode' => $feedbackMode,
		'feedbackStatus' => $feedback_status,
		'avail_pfb' => $avail_pfb,
		'avail_cfb' => $avail_cfb,
		'avail_cfb_reason' => $avail_cfb_reason,
		'avail_pfb_reason' => $avail_pfb_reason,
		'requiringFiles' => implode("\n", $requiringFiles),
		'cadResult' => $cadResult,
		'displays' => $cadResult->getDisplays(),
		'attr' => $cadResult->getAttributes(),
		'series' => $cadResult->Series[0],
		'seriesList' => $seriesList,
		'displayPresenter' => $displayPresenter,
		'feedbackListener' => $feedbackListener,
		'presentationParams' => $presentationParams,
		'feedbacks' => $feedback,
		'personalOpinions' => $personalOpinions,
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
		'message' => $message,
		'errorTitle' => $title
	));
	$smarty->display('critical_error.tpl');
	exit();
}
