<?php
	session_start();

	include_once('common.php');
	include_once("auto_logout.php");

	$data = array();

	try
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		//--------------------------------------------------------------------------------------------------------------
		// For news block
		//--------------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT plugin_name, version, install_dt FROM plugin_master ORDER BY install_dt DESC LIMIT 5";
		$newsData = DBConnector::query($sqlStr, null, 'ALL_ASSOC');
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// For CAD execution block
		//--------------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT COUNT(*), MIN(executed_at) FROM executed_plugin_list";
		$result = DBConnector::query($sqlStr, null, 'ARRAY_NUM');

		$executionNum = $result[0];
		$oldestExecDate = substr($result[1], 0, 10);

		if($executionNum > 0)
		{
			$sqlStr = "SELECT plugin_name, version, COUNT(job_id) as cnt FROM executed_plugin_list "
			        . " GROUP BY plugin_name, version ORDER BY COUNT(job_id) DESC LIMIT 3";
			$cadExecutionData = DBConnector::query($sqlStr, null, 'ALL_ASSOC');
		}
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		//// For latest missed TP
		//--------------------------------------------------------------------------------------------------------------
		$latestHtml = "";

		if($_SESSION['personalFBFlg']==1 && $_SESSION['showMissed']!='none')
		{
			include('cad_results/lesion_candidate_display_private.php');

			$sqlStr = "SELECT lf.job_id, lf.lesion_id, el.plugin_name, el.version, pt.patient_id, pt.patient_name,"
					. " st.study_date, st.study_time, es.study_instance_uid, es.series_instance_uid,"
					. " storage.path, storage.apache_alias, cm.result_table"
					. " FROM plugin_cad_master cm JOIN (plugin_master pm JOIN (patient_list pt JOIN"
					. " (study_list st JOIN (storage_master storage JOIN"
					. " (series_list sr JOIN (executed_series_list es JOIN"
					. " (lesion_classification lf JOIN executed_plugin_list el ON lf.job_id=el.job_id)"
					. " ON lf.job_id=es.job_id AND es.series_id=0) ON sr.series_instance_uid=es.series_instance_uid)"
					. " ON sr.storage_id=storage.storage_id) ON st.study_instance_uid=es.study_instance_uid)"
					. " ON pt.patient_id=st.patient_id) ON pm.plugin_name=el.plugin_name AND pm.version=el.version)"
					. " ON cm.plugin_id=pm.plugin_id";

			if($_SESSION['showMissed']=='own')  $sqlStr .= " WHERE lf.entered_by=? AND";
			else								$sqlStr .= " WHERE";

			$sqlStr .= " lf.is_consensual='f' AND lf.interrupted='f'"
					.  " AND lf.evaluation=2 ORDER BY lf.registered_at DESC LIMIT 3";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindValue(1, $_SESSION['userID']);
			$stmt->execute();

			$missedTPNum = $stmt->rowCount();

			$missedTPData = $stmt->fetchAll(PDO::FETCH_ASSOC);

			for($i=0; $i<$missedTPNum; $i++)
			{
				$sqlStr = "SELECT location_x, location_y, location_z FROM " . $missedTPData[$i]['result_table']
						. " WHERE job_id=? AND sub_id=?";
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute(array($missedTPData[$i]['job_id'], $missedTPData[$i]['lesion_id']));
				$lesionLocation = $stmt->fetch(PDO::FETCH_ASSOC);

				$missedTPData[$i] = array_merge($missedTPData[$i], $lesionLocation);

				$sqlStr = "SELECT key, value FROM executed_plugin_attributes WHERE job_id=?";
				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindValue(1, $missedTPData[$i]['job_id']);
				$stmt->execute();

				foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $item)
				{
					$missedTPData[$i][$item['key']] = $item['value'];
				}
			}

			//var_dump($missedTPData);

			foreach($missedTPData as $key => $item)
			{
				$seriesDir = $item['path'] . $DIR_SEPARATOR . $item['patient_id']
						   . $DIR_SEPARATOR . $item['study_instance_uid']
				           . $DIR_SEPARATOR . $item['series_instance_uid'];
				$webPathOfseriesDir = $item['apache_alias'] . $item['patient_id']
									. $DIR_SEPARATOR_WEB . $item['study_instance_uid']
									. $DIR_SEPARATOR_WEB . $item['series_instance_uid'];
				$pathOfCADReslut = $seriesDir . $DIR_SEPARATOR . $SUBDIR_CAD_RESULT . $DIR_SEPARATOR
				                 . $item['plugin_name'] . '_v.' . $item['version'];
				$webPathOfCADReslut = $webPathOfseriesDir . $DIR_SEPARATOR_WEB . $SUBDIR_CAD_RESULT . $DIR_SEPARATOR_WEB
									. $item['plugin_name'] . '_v.' . $item['version'];

				$dstFname = sprintf("%s%sresult%03d.png", $pathOfCADReslut, $DIR_SEPARATOR, $item['lesion_id']);
				$dstFnameWeb = sprintf("%s%sresult%03d.png", $webPathOfCADReslut, $DIR_SEPARATOR_WEB, $item['lesion_id']);

				if(!is_file($dstFname))
				{
					DcmExport::dcm2png($dstFname, $item['location_z'], $item['window_level'], $item['window_width']);
				}

				$dispWidth = 256;
				$dispHeight = (int)((real)$item['crop_height'] * ($dispWidth / (real)$item['crop_width']) + 0.5);
				$scale = $dispWidth/(real)$item['crop_width'];

				$img = new Imagick();
				$img->readImage($dstFname);
				$width  = $img->getImageWidth();
				$height = $img->getImageHeight();
				$img->destroy();

				$latestHtml .= '<div class="result-record-3cols al-c">'
							.  '<div class="al-l" style="font-size:12px;">';

				if($_SESSION['anonymizeFlg'] == 1)
				{
					$latestHtml .= '<b>&nbsp;Pt.: </b>' . PinfoScramble::scramblePtName()
								.  ' (' . PinfoScramble::encrypt($item['patient_id'], $_SESSION['key']) . ')<br/>';
				}
				else
				{
					$latestHtml .= '<b>&nbsp;Pt.: </b>' . $item['patient_name'] . ' (' . $item['patient_id'] . ')<br/>';
				}

				$latestHtml .= '<b>&nbsp;St.: </b>' . $item['study_date'] . '&nbsp;' . $item['study_time'] . '<br/>'
							.  '<b>&nbsp;CAD: </b>' . $item['plugin_name'] . ' v.' . $item['version']
							.  '<input name="" type="button" value="detail" class="form-btn"'
							.  ' onclick="location.href=\'cad_results/show_cad_results.php?jobID=' . $item['job_id']
							.  '&feedbackMode=personal&remarkCand=' . $item['lesion_id'] . '&sortKey=confidence'
							.  '&sortOrder=DESC\';" style="margin-left:70px;"><br/>'
							.  '<b>&nbsp;Candidate ID: </b>' . $item['lesion_id'] . '<br/>'
							.  '</div>'
							.  '<div style="width:' . $dispWidth . 'px; height:' .  $dispHeight . 'px;'
							.  ' overflow:hidden; position:relative; margin-bottom:7px;" class="block-al-c">'
							.  '<img class="transparent" src="cad_results/images/magenta_circle.png"'
							.  ' style="position:absolute;'
							.  ' left:' .  (($item['location_x']-(int)$item['crop_org_x'])*$scale-12) . 'px;'
							.  ' top:' .(($item['location_y']-(int)$item['crop_org_y'])*$scale-12) . 'px; z-index:2;">'
							.  '<img src="' . $dstFnameWeb . '" width=' . $width*$scale . ' height=' . $height*$scale
							.  ' style="position:absolute; left:' . (-(int)$item['crop_org_x']*$scale) . 'px;'
							.  ' top:' . (-(int)$item['crop_org_y']*$scale) . 'px;'
							.  ' z-index:1;">'
							.  '</div>'
							.  '</div>';
			}
		}
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		$smarty = new SmartyEx();

		$smarty->assign('newsData', $newsData);
		$smarty->assign('executionNum',     $executionNum);
		$smarty->assign('oldestExecDate',   $oldestExecDate);
		$smarty->assign('cadExecutionData', $cadExecutionData);
		$smarty->assign('latestHtml', $latestHtml);

		$smarty->display('home.tpl');
		//--------------------------------------------------------------------------------------------------------------

	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;

?>
