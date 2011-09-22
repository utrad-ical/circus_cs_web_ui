<?php

include('../../pub/plugin/CAD-Summarizer_v.1/drawRocCurve.php');
include("../../pub/cad_results/lesion_candidate_display_private.php");

$data = array();

//------------------------------------------------------------------------------------------------------------------
// Load base file
//------------------------------------------------------------------------------------------------------------------
$fp = fopen($params['resPath']."CAD-SummarizerResult_0_base.txt", "r");

$data['caseNum']      = rtrim(fgets($fp));
$data['undispTpNum']  = rtrim(fgets($fp));
$data['dispTpNum']    = rtrim(fgets($fp));
$data['undispFpNum']  = rtrim(fgets($fp));
$data['dispFpNum']    = rtrim(fgets($fp));
$data['fnNum']        = rtrim(fgets($fp));
$data['underRocArea'] = sprintf("%.3f",rtrim(fgets($fp)));

$data['totalTpNum'] = $data['dispTpNum'] + $data['undispTpNum'];
$data['totalFpNum'] = $data['dispFpNum'] + $data['undispFpNum'];

fclose($fp);
//------------------------------------------------------------------------------------------------------------------

//------------------------------------------------------------------------------------------------------------------
// Load dispTp file
//------------------------------------------------------------------------------------------------------------------
$fp = fopen($params['resPath']."CAD-SummarizerResult_0_dispTp.txt", "r");

$totalTP = (int)($data['dispTpNum']) + (int)($data['undispTpNum']) + (int)($data['fnNum']);

$maxDispTp = (int)(rtrim(fgets($fp)));
$sensitivityArr = array();

for($i=0; $i<$maxDispTp; $i++)
{
	$tmp = (int)(rtrim(fgets($fp)));

	$sensitivityArr[$i][0] = $i+1;
	$sensitivityArr[$i][1] = sprintf("%.1f", (double)$tmp/$totalTP*100.0);
}

fclose($fp);
//------------------------------------------------------------------------------------------------------------------

//------------------------------------------------------------------------------------------------------------------
// Create examples of TP / FP / pending
//------------------------------------------------------------------------------------------------------------------
$listName = array('TP', 'FP', 'pending', 'FN');
$listHtml = array();
$dispNum = 5;

$cadName = "";
$version = "";
$pluginID = 0;
$resultTableName = "";

for($n = 0; $n < 4; $n++)
{
	$fp = fopen($params['resPath']."CAD-SummarizerResult_" . $listName[$n] . "List.txt", "r");

	$listCnt = (int)(rtrim(fgets($fp)));
	$candList= array();

	for($i = 0; $i < $listCnt; $i++)
	{
		$tmpArray =explode(",", rtrim(fgets($fp)));
		array_push($candList, $tmpArray);
	}
	fclose($fp);

	if($n==0)
	{
		$sqlStr = "SELECT pm.plugin_name, pm.version, pm.plugin_id"
				. " FROM executed_plugin_list el, plugin_master pm"
				. " WHERE el.job_id=? AND pm.plugin_id=el.plugin_id";
		$result = DBConnector::query($sqlStr, $candList[0][0], 'ARRAY_NUM');

		$cadName  = $result[0];
		$version  = $result[1];
		$pluginID = $result[2];

		$sqlStr = "SELECT result_table FROM plugin_cad_master WHERE plugin_id=?";
		$resultTableName = DBConnector::query($sqlStr, $pluginID, 'SCALAR');
	}

	$listHtml[$n] = '<table class="mt10 ml20"><tr>';

	for($k = 0; $k < min(5, $listCnt); $k++)
	{
		$stmt = $pdo->prepare("SELECT key, value FROM executed_plugin_attributes WHERE job_id=?");
		$stmt->bindParam(1, $candList[$k][0]);
		$stmt->execute();
		$result = array();

		foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $item)
		{
			$result[$item['key']] = $item['value'];
		}

		$windowLevel  = $result['window_level'];
		$windowWidth  = $result['window_width'];

		if($n==3)
		{
			$sqlStr = 'SELECT st.patient_id, st.study_instance_uid, sr.series_instance_uid,'
					. ' sm.storage_id, sm.path, fn.location_x, fn.location_y, fn.location_z'
					. ' FROM study_list st, series_list sr, storage_master sm, executed_plugin_list el,'
					. ' executed_series_list es, feedback_list fl, fn_location fn'
					. ' WHERE el.job_id=?  AND fl.job_id=el.job_id AND fn.fb_id=fl.fb_id AND fn.fn_id=?'
					. ' AND es.job_id=el.job_id AND es.volume_id=0'
					. ' AND sr.sid=es.series_sid'
					. ' AND st.study_instance_uid=sr.study_instance_uid '
					. ' AND sm.storage_id=sr.storage_id';
		}
		else
		{
			$sqlStr = 'SELECT st.patient_id, st.study_instance_uid, sr.series_instance_uid,'
					. ' sm.storage_id, sm.path, cad.location_x, cad.location_y, cad.location_z'
					. ' FROM study_list st, series_list sr, storage_master sm,'
					. ' executed_plugin_list el, executed_series_list es, "' . $resultTableName . '" cad'
					. ' WHERE el.job_id=?  AND cad.job_id=el.job_id AND cad.sub_id=?'
					. ' AND es.job_id=el.job_id AND es.volume_id=0'
					. ' AND sr.sid=es.series_sid '
					. ' AND st.study_instance_uid=sr.study_instance_uid '
					. ' AND sm.storage_id=sr.storage_id';
		}

		$result = DBConnector::query($sqlStr, array($candList[$k][0], $candList[$k][1]), 'ARRAY_NUM');

		$seriesDir = $result[4] . $DIR_SEPARATOR . $result[0] . $DIR_SEPARATOR
				   . $result[1] . $DIR_SEPARATOR . $result[2];

		$posX = $result[5];
		$posY = $result[6];
		$posZ = $result[7];
		
		$baseName = sprintf('%s_%d_%d_%d_0_0.jpg',
							$result[1],
							$result[7],
							$windowLevel,
							$windowWidth);

		$dumpFname = sprintf("%s_%d.txt", $result[2], $posZ);
		
		// Get path of web cache 
		$sqlStr = "SELECT storage_id, path FROM storage_master WHERE type=3 AND current_use='t'";
		$result =  DBConnector::query($sqlStr, NULL, 'ARRAY_NUM');		

		$pathOfCache = $result[1] . $DIR_SEPARATOR;
		$webPathOfCache = '../storage/' . $result[0] . '/';

		$dstFname    = $pathOfCache . $baseName;
		$dstFnameWeb = $webPathOfCache . $baseName;

		if(!is_file($dstFname))
		{
			$srcFname = sprintf("%s%s%08d.dcm", $seriesDir, $DIR_SEPARATOR, $posZ);
			$dumpFname = $pathOfCache . $dumpFname;

			$dcmResult = DcmExport::createThumbnailJpg(
							$srcFname, $dstFname, $dumpFname, 100,
							$windowLevel, $windowWidth, 0, 0);
		}

		$img = @imagecreatefromjpeg($dstFname);
		$width  = imagesx($img);
		$height = imagesy($img);
		imagedestroy($img);

		$listHtml[$n] .= '<td style="padding:3px 10px;">'
					  .  '<a href="../cad_results/cad_result.php?jobID=' . $candList[$k][0]
					  .  '&feedbackMode=personal&remarkCand=' . $candList[$k][1] . '&sortKey=confidence&sortOrder=DESC"'
					  .  ' title="ID:'. $candList[$k][0];
		if($n!=3)	$listHtml[$n] .= ', rank:'.$candList[$k][1].' (confidence:'.sprintf("%.3f", $candList[$k][2]).')';
		$listHtml[$n] .= '">'
					  .  '<div class="imgArea" style="width:101px; height:101px; position:relative; top:0px; left:0px;">'
					  .  '<img src="../cad_results/images/magenta_cross_enlarge.png"'
					  .  ' style="position:absolute; left:0px; top:0px; z-index:2;">'
					  .  '<img src="' . $dstFnameWeb . '" width=' . $width . ' height=' . $height
					  .  ' style="position:absolute; left:'.(-$posX+50).'px; top:'.(-$posY+50).'px; z-index:1;">'
					  .  '</div>'
					  .  '</a>'
				      .  '</td>';
	}
	$listHtml[$n] .= "</tr></table>";
}

//var_dump($tpList);
//------------------------------------------------------------------------------------------------------------------

$tmpFname = 'ROC' . $params['jobID'] . '_' . microtime(true) . '.png';

$curveFname = $params['cachePath'] . $tmpFname;
$curveFnameWeb = $params['cachePathWeb'] . $tmpFname;

CreateRocCurve(0, 0, $params['resPath'], $curveFname);

$params['resPath'] = addslashes($params['resPath']);

//------------------------------------------------------------------------------------------------------------------
// Settings for Smarty
//------------------------------------------------------------------------------------------------------------------
$smarty = new SmartyEx();

$smarty->assign('params',         $params);
$smarty->assign('data',           $data);
$smarty->assign('curveFnameWeb',  $curveFnameWeb);

$smarty->assign('sensitivityArr', $sensitivityArr);

$smarty->assign('tpListHtml',      $listHtml[0]);
$smarty->assign('fpListHtml',      $listHtml[1]);
$smarty->assign('pendingListHtml', $listHtml[2]);
$smarty->assign('fnListHtml',      $listHtml[3]);

$smarty->display($WEB_UI_ROOT.'/plugin/'.$params['pluginName'].'_v.'.$params['version'].'/research_result.tpl');
//------------------------------------------------------------------------------------------------------------------

?>
