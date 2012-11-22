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

	$user = Auth::currentUser();
	$user_id = $user->user_id;

	if (!$cadResult->checkCadResultAvailability($user->Group))
		critical_error('You do not have privilege to see this CAD result.');

	// Automatically change to consensual mode
	$feedbackList = $cadResult->queryFeedback('all', null, false);
	$registerConsensualFeedbackFlg = 0;
	$enterOnwPersonalFeedbackFlg   = 0;

	foreach($feedbackList as $item)
	{
		$item->loadFeedback();
		
		if($item->is_consensual && $item->status == Feedback::REGISTERED)
		{
			$registerConsensualFeedbackFlg = 1;
		}
		else if(!$item->is_consensual && $item->entered_by == $user_id)
		{
			$enterOnwPersonalFeedbackFlg   = 1;
		}
	}

	if($feedbackMode == 'personal'
		&& $registerConsensualFeedbackFlg == 1
		&& $enterOnwPersonalFeedbackFlg == 0)
	{
		header('Location: ./cad_result.php?jobID=' . $jobID . '&feedbackMode=consensual');
	}

	// Assigning the result to Smarty
	$smarty = new SmartyEx();

	// Set smarty default template handler.
	$td = $smarty->template_dir;
	$smarty->template_id = md5($cadResult->Plugin->fullName());
	$smarty->template_dir = $cadResult->Plugin->configurationPath();
	$template_handler = function ($resource_type, $resource_name,
		&$template_source, &$template_timestamp, &$smarty_obj) use ($td)
	{
		global $DIR_SEPARATOR;
		if ($resource_type != 'file')
			return false;
		$cadtemplate = $td . $DIR_SEPARATOR . 'cad_results' . $DIR_SEPARATOR . $resource_name;
		$template = $td . $DIR_SEPARATOR . $resource_name;
		if (file_exists($cadtemplate))
		{
			$template_timestamp = time(); // always recompile
			$template_source = file_get_contents($cadtemplate);
		}
		else if (file_exists($template))
		{
			$template_timestamp = filemtime($template);
			$template_source = file_get_contents($template);
		}
		else return false;
		return true;
	};
	$smarty->default_template_handler_func = $template_handler;


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
	$feedback_temporary = false;
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
		$feedback_temporary = true;
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

	$url_pub  = $cadResult->webPathOfPluginPub(true) . '/';
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
			'z_org_img_num' => $es->z_org_img_num,
			'image_delta' => $es->image_delta,
			'image_count' => $es->image_count
		);
	}
	ksort($seriesList, SORT_NUMERIC);

	$smarty->assign(array(
		'noFeedback' => $noFeedback,
		'feedbackMode' => $feedbackMode,
		'feedbackStatus' => $feedback_status,
		'feedbackTemporary' => $feedback_temporary,
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
