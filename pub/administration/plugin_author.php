<?php

require("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(AUTH::SERVER_SETTINGS);

$keys = array(
	array('value' => 'modality'),
	array('value' => 'manufacturer'),
	array('value' => 'model_name'),
	array('value' => 'station_name'),
	array('value' => 'patient_id'),
	array('value' => 'sex'),
	array('value' => 'age'),
	array('value' => 'study_date'),
	array('value' => 'series_date'),
	array('value' => 'body_part'),
	array('value' => 'image_width'),
	array('value' => 'image_height'),
	array('value' => 'series_description'),
	array('value' => 'image_number', 'label' => 'number of images')
);


$smarty = new SmartyEx();
$smarty->assign('keys', $keys);
$smarty->display('administration/plugin_author.tpl');