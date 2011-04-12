<?php

	include('drawRocCurve_v1.1.php');
	include("../cad_results/lesion_candidate_display_private.php");

	$data = array();

	//------------------------------------------------------------------------------------------------------------------
	// Load base file
	//------------------------------------------------------------------------------------------------------------------
	$fp = fopen($params['resPath']."CAD-SummarizerResult_base.txt", "r");

	$data['caseNum']      = rtrim(fgets($fp));
	$data['totalTpNum']   = rtrim(fgets($fp));
	$data['totalFpNum']   = rtrim(fgets($fp));
	$data['fnNum']        = rtrim(fgets($fp));
	$data['underRocArea'] = sprintf("%.3f",rtrim(fgets($fp)));

	fclose($fp);
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Load dispTp file
	//------------------------------------------------------------------------------------------------------------------
	$fp = fopen($params['resPath']."CAD-SummarizerResult_dispTp.txt", "r");

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
	$resultTableName = "";

	for($n=0; $n<4; $n++)
	{
		$fp = fopen($params['resPath']."CAD-SummarizerResult_" . $listName[$n] . "List.txt", "r");

		$listCnt = (int)(rtrim(fgets($fp)));
		$candList= array();

		for($i=0; $i<$listCnt; $i++)
		{
			$tmpArray =explode(",", rtrim(fgets($fp)));
			array_push($candList, $tmpArray);
		}
		fclose($fp);

		if($n==0)
		{
			$sqlStr = "SELECT plugin_name, version FROM executed_plugin_list WHERE job_id=?";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindValue(1, $candList[0][0]);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_NUM);

			$cadName = $result[0];
			$version = $result[1];

			$stmt = $pdo->prepare("SELECT result_table FROM cad_master WHERE plugin_name=? AND version=?");
			$stmt->execute(array($cadName, $version));
			$resultTableName = $stmt->fetchColumn();
		}

		$listHtml[$n] = '<table class="mt10 ml20"><tr>';

		for($k = 0; $k < min(5, $listCnt); $k++)
		{
			$stmt = $pdo->prepare("SELECT * FROM param_set WHERE job_id=?");
			$stmt->bindParam(1, $candList[$k][0]);
			$stmt->execute();

			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$windowLevel  = $result['window_level'];
			$windowWidth  = $result['window_width'];

			$sqlStr = "";

			if($n==3)
			{
				$sqlStr = 'SELECT st.patient_id, st.study_instance_uid, sr.series_instance_uid, sm.path, sm.apache_alias,'
						. ' fn.location_x, fn.location_y, fn.location_z'
						. ' FROM study_list st, series_list sr, storage_master sm,'
						. ' executed_plugin_list el, executed_series_list es, false_negative_location fn'
						. ' WHERE el.job_id=?  AND fn.job_id=el.job_id AND fn.location_id=?'
						. ' AND es.job_id=el.job_id AND es.series_id=0'
						. ' AND st.study_instance_uid=es.study_instance_uid '
						. ' AND sr.series_instance_uid=es.series_instance_uid '
						. ' AND sm.storage_id=sr.storage_id';
			}
			else
			{
				$sqlStr = 'SELECT st.patient_id, st.study_instance_uid, sr.series_instance_uid, sm.path, sm.apache_alias,'
						. ' cad.location_x, cad.location_y, cad.location_z'
						. ' FROM study_list st, series_list sr, storage_master sm,'
						. ' executed_plugin_list el, executed_series_list es, "' . $resultTableName . '" cad'
						. ' WHERE el.job_id=?  AND cad.job_id=el.job_id AND cad.sub_id=?'
						. ' AND es.job_id=el.job_id AND es.series_id=0'
						. ' AND st.study_instance_uid=es.study_instance_uid '
						. ' AND sr.series_instance_uid=es.series_instance_uid '
						. ' AND sm.storage_id=sr.storage_id';
			}

			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindParam(1, $candList[$k][0]);
			$stmt->bindParam(2, $candList[$k][1]);
			$stmt->execute();

			$result = $stmt->fetch(PDO::FETCH_NUM);

			$seriesDir = $result[3] . $DIR_SEPARATOR . $result[0] . $DIR_SEPARATOR
					   . $result[1] . $DIR_SEPARATOR . $result[2];
			$seriesDirWeb = $result[4] . $result[0]
						  . $DIR_SEPARATOR_WEB . $result[1] . $DIR_SEPARATOR_WEB . $result[2];

			$pathOfCADReslut = $seriesDir . $DIR_SEPARATOR . $SUBDIR_CAD_RESULT . $DIR_SEPARATOR . $cadName . '_v.' . $version;
			$webPathOfCADReslut = $seriesDirWeb . $DIR_SEPARATOR_WEB . $SUBDIR_CAD_RESULT . $DIR_SEPARATOR_WEB . $cadName
			                    . '_v.' . $version;

			$posX = $result[5];
			$posY = $result[6];
			$posZ = $result[7];

			$srcFname = sprintf("%s%sresult%03d.png", $pathOfCADReslut, $DIR_SEPARATOR, $candList[$k][1]);
			$srcFnameWeb = sprintf("../%s%sresult%03d.png", $webPathOfCADReslut, $DIR_SEPARATOR_WEB, $candList[$k][1]);

			if($n==3)
			{
				$srcFname = sprintf("%s%sfnslice%03d.png", $pathOfCADReslut, $DIR_SEPARATOR, $posZ);
				$srcFnameWeb = sprintf("../%s%sfnslice%03d.png", $webPathOfCADReslut, $DIR_SEPARATOR_WEB, $posZ);
			}

			if(!is_file($srcFname))
			{
				DcmExport::dcm2png($srcFname, $posZ, $windowLevel, $windowWidth);
			}

			$img = @imagecreatefrompng($srcFname);
			$width  = imagesx($img);
			$height = imagesy($img);
			imagedestroy($img);

			$listHtml[$n] .= '<td style="padding:3px 10px;">'
						  .  '<a href="../cad_results/show_cad_results.php?jobID=' . $candList[$k][0]
						  .  '&remarkCand=' . $candList[$k][1] . '&sortKey=confidence&sortOrder=DESC"'
						  .  ' title="ID:'. $candList[$k][0];
			if($n!=3)	$listHtml[$n] .= ', rank:'.$candList[$k][1].' (confidence:'.sprintf("%.3f", $candList[$k][2]).')';
			$listHtml[$n] .= '">'
						  .  '<div class="imgArea" style="width:101px; height:101px; position:relative; top:0px; left:0px;">'
						  .  '<img src="../cad_results/images/magenta_cross_enlarge.png"'
						  .  ' style="position:absolute; left:0px; top:0px; z-index:2;">'
						  .  '<img src="' . $srcFnameWeb . '" width=' . $width . ' height=' . $height
						  .  ' style="position:absolute; left:'.(-$posX+50).'px; top:'.(-$posY+50).'px; z-index:1;">'
						  .  '</div>'
						  .  '</a>'
						  .  '</td>';
		}
		$listHtml[$n] .= "</tr></table>";
	}

	//var_dump($tpList);
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Create ROC(FROC) curve as PNG file
	//------------------------------------------------------------------------------------------------------------------
	$tmpFname = 'ROC' . $params['jobID'] . '_' . microtime(true) . '.png';

	$curveFname    = $WEB_UI_ROOT . $DIR_SEPARATOR . 'pub' . $DIR_SEPARATOR . 'tmp'
	               . $DIR_SEPARATOR . $tmpFname;
	$curveFnameWeb = '../tmp/' . $tmpFname;

	CreateRocCurve(0, $params['resPath'], $curveFname);

	$params['resPath'] = addslashes($params['resPath']);
	//------------------------------------------------------------------------------------------------------------------

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

	$smarty->display('research/cad_summarizer_v1.1.tpl');
	//------------------------------------------------------------------------------------------------------------------

?>