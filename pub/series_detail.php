<?php
include_once('common.php');
Auth::checkSession();
Auth::purgeUnlessGranted(Auth::LIST_SEARCH);

//------------------------------------------------------------------------------
// Import $_GET variables and validation
//------------------------------------------------------------------------------
$validator = new FormValidator();
$validator->addRules(array(
	'sid' => array(
		'type' => 'int',
		'min' => 1
	),
	'listTabName' => array(
		'type' => 'select',
		'options' => array("Today's series", "Series list"),
		'default' => 'Series list',
		'adjValue' => 'Series list'
	),
	'index' => array(
		'type' => 'int'
	)
));

try
{
	$currentUser = Auth::currentUser();

	if (!$validator->validate($_REQUEST))
		throw new Exception(implode("\n", $validator->errors));
	$req = $validator->output;

	$dum = new Series();
	$ss = $dum->find(array('sid' => $req['sid']));
	if (count($ss) != 1)
		throw new Exception('Specified series does not exist.');

	$data['series'] = $series = $ss[0];
	$data['study'] = $study = $series->Study;
	$data['patient'] = $patient = $study->Patient;

	$storage = new Storage($series->storage_id);

	if(!$currentUser->hasPrivilege('personalInfoView'))
	{
		$data['patientID'] = PinfoScramble::encrypt($data['patientID'], $_SESSION['key']);
		$data['patientName'] = PinfoScramble::encrypt($data['patientName'], $_SESSION['key']);
	}

	// Check for directories
	$seriesDir = $storage->path . '/' . $patient->patient_id . '/'
		. $study->study_instance_uid . '/' . $series->series_instance_uid;
	if(!is_dir($seriesDir))
		throw new Exception('Series directory does not exist.');

	// Viewer initialization
	$data['viewer'] = array(
		'min' => max(1, $series->min_image_number),
		'max' => max($series->image_number, $series->max_image_number),
		'wl' => 0,
		'ww' => 0,
		'index' => $req['pos'] ?: 1
	);

	// Grayscale Presets
	$preset_list = GrayscalePreset::findPresetsAssoc($series->modality);
	if (count($preset_list) > 0)
	{
		$data['viewer']['wl'] = $preset_list[0]['wl'];
		$data['viewer']['ww'] = $preset_list[0]['ww'];
	}
	$data['viewer']['grayscalePresets'] = $preset_list;
}
catch (Exception $e)
{
	$smarty = new SmartyEx();
	$smarty->assign('message', $e->getMessage());
	$smarty->display('critical_error.tpl');
	exit;
}

//------------------------------------------------------------------------------
// Settings for Smarty
//------------------------------------------------------------------------------
$smarty = new SmartyEx();
$smarty->assign($data);
$smarty->display('series_detail.tpl');
//------------------------------------------------------------------------------

?>
