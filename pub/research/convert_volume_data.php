<?php
$params = array('toTopDir' => "../");
include_once("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(Auth::RESEARCH_EXEC);

//------------------------------------------------------------------------------------------------------------------
// Import $_POST variables and validation
//------------------------------------------------------------------------------------------------------------------
$params = array();
$validator = new FormValidator();

$validator->addRules(array(
	"seriesInstanceUID" => array(
		"type" => "uid",
		"required" => true,
		"errorMes" => "[ERROR] Parameter of URL (seriesInstanceUID) is invalid.")
	));

if($validator->validate($_POST))
{
	$params = $validator->output;
	$params['message'] = "";
}
else
{
	$params = $validator->output;
	$params['message'] = implode('<br/>', $validator->errors);
}

$params['toTopDir'] = "../";
//-----------------------------------------------------------------------------------------------------------------

if($params['message'] == "")
{
	try
	{

		$pdo = DBConnector::getConnection();

		$sqlStr = "SELECT patient_id, study_instance_uid,"
				. " series_date, series_time, modality, series_description"
				. " FROM series_join_list"
				. " WHERE series_instance_uid=?";

		$result = DBConnector::query($sqlStr, $params['seriesInstanceUID'], 'ARRAY_NUM');

		if(!is_array($result))
		{
			$params['message'] = "[Error] DICOM series is unspecified!!";
		}
		else
		{
			$params['patientID']         = $result[0];
			$params['studyInstanceUID']  = $result[1];
			$params['seriesTime']        = $result[2] . ' ' . $result[3];
			$params['modality']          = $result[4];
			$params['seriesDescription'] = $result[5];

			if($_SESSION['anonymizeFlg'] == 1)
			{
				$params['encryptedPtID'] =  PinfoScramble::encrypt($params['patientID'], $_SESSION['key']);
			}
		}

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		$smarty = new SmartyEx();

		$smarty->assign('params', $params);

		$smarty->display('research/convert_volume_data.tpl');
		//--------------------------------------------------------------------------------------------------------------
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;
}

