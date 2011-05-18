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
		// For plug-in execution block
		//--------------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT COUNT(*), MIN(executed_at) FROM executed_plugin_list WHERE status = ?";
		$result = DBConnector::query($sqlStr, $PLUGIN_SUCESSED, 'ARRAY_NUM');

		$executionNum = $result[0];
		$oldestExecDate = substr($result[1], 0, 10);

		if($executionNum > 0)
		{
			$sqlStr = "SELECT pm.plugin_name, pm.version, COUNT(el.job_id) as cnt"
					. " FROM executed_plugin_list el, plugin_master pm"
					. " WHERE pm.plugin_id = el.plugin_id"
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

			$sqlStr = "SELECT el.job_id, el.plugin_id, cc.candidate_id "
					. " FROM executed_plugin_list el, feedback_list fl, candidate_classification cc"
					. " WHERE el.job_id=fl.job_id AND fl.fb_id = cc.fb_id";

			if($_SESSION['showMissed']=='own')  $sqlStr .= " AND fl.entered_by=?";

			$sqlStr .= " AND fl.is_consensual='f'"
					.  " AND cc.evaluation = 2"
					.  " ORDER BY fl.registered_at DESC LIMIT 3;";

			$stmt = $pdo->prepare($sqlStr);
			if($_SESSION['showMissed']=='own')  $stmt->bindValue(1, $_SESSION['userID']);
			$stmt->execute();

			if($stmt->rowCount() > 0)
			{
				$idList = $stmt->fetchAll(PDO::FETCH_ASSOC);

				foreach($idList as $idSet)
				{
					$sqlStr = "SELECT pm.plugin_name, pm.version, cm.result_table"
							. " FROM plugin_master pm, plugin_cad_master cm"
							. " WHERE pm.plugin_id=? AND cm.plugin_id=pm.plugin_id";
					$pluginParams = DBConnector::query($sqlStr, $idSet['plugin_id'], 'ARRAY_ASSOC');

					$sqlStr = "SELECT pt.patient_id, pt.patient_name, st.study_date, st.study_time,"
							. " sr.study_instance_uid, sr.series_instance_uid, sm.storage_id, sm.path"
							. " FROM patient_list pt, study_list st, series_list sr,"
							. " storage_master sm, executed_series_list es"
							. " WHERE es.job_id=? AND es.series_id=0 AND sr.sid=es.series_sid"
							. " AND st.study_instance_uid = sr.study_instance_uid"
							. " AND pt.patient_id=st.patient_id"
							. " AND sm.storage_id=sr.storage_id";
					$seriesParams = DBConnector::query($sqlStr, $idSet['job_id'], 'ARRAY_ASSOC');

					$sqlStr = "SELECT location_x as x, location_y as y, location_z as z"
							. " FROM " . $pluginParams['result_table']
							. " WHERE job_id=? AND sub_id=?";
					$candPos = DBConnector::query($sqlStr, array($idSet['job_id'], $idSet['candidate_id']), 'ARRAY_ASSOC');

					$sqlStr = "SELECT key, value FROM executed_plugin_attributes WHERE job_id=?";
					$stmt = $pdo->prepare($sqlStr);
					$stmt->bindValue(1, $idSet['job_id']);
					$stmt->execute();
					$attributes = array();

					foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $item)
					{
						$attributes[$item['key']] = $item['value'];
					}

					// Set parameters
					$seriesDir = $seriesParams['path'] . $DIR_SEPARATOR . $seriesParams['patient_id']
							   . $DIR_SEPARATOR . $seriesParams['study_instance_uid']
					           . $DIR_SEPARATOR . $seriesParams['series_instance_uid'];
					$webPathOfseriesDir = 'storage/' . $seriesParams['storage_id']
									    . '/' . $seriesParams['patient_id']
										. '/' . $seriesParams['study_instance_uid']
										. '/' . $seriesParams['series_instance_uid'];
										

					$sqlStr = 'SELECT el.storage_id, sm.path FROM executed_plugin_list el, storage_master sm'
							. ' WHERE el.job_id=? AND sm.storage_id=el.storage_id';
					$result = DBConnector::query($sqlStr, $idSet['job_id'], 'ARRAY_NUM');

					$pathOfCADReslut = $result[1] . $DIR_SEPARATOR . $idSet['job_id'];
					$webPathOfCADReslut = './storage/' . $result[0] . '/' . $idSet['job_id'];

					$dstFname = sprintf("%s%sresult%03d.png", $pathOfCADReslut, $DIR_SEPARATOR, $idSet['candidate_id']);
					$dstFnameWeb = sprintf("%s/result%03d.png", $webPathOfCADReslut, $idSet['candidate_id']);

					if(!is_file($dstFname))
					{
						DcmExport::dcm2png($dstFname, $candPos['z'], $attributes['window_level'], $attributes['window_width']);
					}

					$dispWidth = 256;
					$dispHeight = (int)((real)$attributes['crop_height'] * ($dispWidth / (real)$attributes['crop_width']) + 0.5);
					$scale = $dispWidth/(real)$attributes['crop_width'];

					$img = @imagecreatefrompng($dstFname);
	
				    if($img)
					{
						$width  = imagesx($img);
						$height = imagesy($img);
						imagedestroy($img);
					}

					// Create HTML statement
					$latestHtml .= '<div class="result-record-3cols al-c">'
								.  '<div class="al-l" style="font-size:12px;">';

					if($_SESSION['anonymizeFlg'] == 1)
					{
						$latestHtml .= '<b>&nbsp;Pt.: </b>' . PinfoScramble::scramblePtName()
									.  ' ('
									.  PinfoScramble::encrypt($seriesParams['patient_id'], $_SESSION['key'])
									.  ')<br/>';
					}
					else
					{
						$latestHtml .= '<b>&nbsp;Pt.: </b>'
									.  $seriesParams['patient_name']
									. ' (' . $seriesParams['patient_id'] . ')<br/>';
					}

					$latestHtml .= '<b>&nbsp;St.: </b>' . $seriesParams['study_date']
								.  '&nbsp;' . $seriesParams['study_time'] . '<br/>'
								.  '<b>&nbsp;CAD: </b>' . $pluginParams['plugin_name'] . ' v.' . $pluginParams['version']
								.  '<input name="" type="button" value="detail" class="form-btn"'
								.  ' onclick="location.href=\'cad_results/show_cad_results.php?jobID=' . $idSet['job_id']
								.  '&feedbackMode=personal&remarkCand=' . $idSet['candidate_id'] . '&sortKey=confidence'
								.  '&sortOrder=DESC\';" style="margin-left:70px;"><br/>'
								.  '<b>&nbsp;Candidate ID: </b>' . $idSet['candidate_id'] . '<br/>'
								.  '</div>'
								.  '<div style="width:' . $dispWidth . 'px; height:' .  $dispHeight . 'px;'
								.  ' overflow:hidden; position:relative; margin-bottom:7px;" class="block-al-c">'
								.  '<img class="transparent" src="cad_results/images/magenta_circle.png"'
								.  ' style="position:absolute;'
								.  ' left:' .  (($candPos['x']-(int)$attributes['crop_org_x'])*$scale-12) . 'px;'
								.  ' top:' .(($candPos['y']-(int)$attributes['crop_org_y'])*$scale-12) . 'px; z-index:2;">'
								.  '<img src="' . $dstFnameWeb . '" width=' . $width*$scale . ' height=' . $height*$scale
								.  ' style="position:absolute; left:' . (-(int)$attributes['crop_org_x']*$scale) . 'px;'
								.  ' top:' . (-(int)$attributes['crop_org_y']*$scale) . 'px;'
								.  ' z-index:1;">'
								.  '</div>'
								.  '</div>';
				}
			}
		}
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//--------------------------------------------------------------------------------------------------------------
		$smarty = new SmartyEx();

		$smarty->assign('newsData',         $newsData);
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
