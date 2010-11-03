<?php
	session_start();
	
	include_once('common.php');
	include_once("auto_logout.php");
	require_once('class/DcmExport.class.php');
	
	$data = array();

	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		//--------------------------------------------------------------------------------------------------------------
		// For news block
		//--------------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT plugin_name, version, install_dt FROM plugin_master ORDER BY install_dt DESC LIMIT 5";
		$newsData = PdoQueryOne($pdo, $sqlStr, null, 'ALL_ASSOC');
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		// For CAD execution block
		//--------------------------------------------------------------------------------------------------------------
		$sqlStr = "SELECT COUNT(*), MIN(executed_at) FROM executed_plugin_list";
		$result = PdoQueryOne($pdo, $sqlStr, null, 'ARRAY_NUM');

		$executionNum = $result[0];
		$oldestExecDate = substr($result[1], 0, 10);
		
		if($executionNum > 0)
		{
			$sqlStr = "SELECT plugin_name, version, COUNT(exec_id) as cnt FROM executed_plugin_list "
			        . " GROUP BY plugin_name, version ORDER BY COUNT(exec_id) DESC LIMIT 3";
			$cadExecutionData = PdoQueryOne($pdo, $sqlStr, null, 'ALL_ASSOC');
		}
		//--------------------------------------------------------------------------------------------------------------

		//--------------------------------------------------------------------------------------------------------------
		//// For latest missed TP
		//--------------------------------------------------------------------------------------------------------------
		$latestHtml = "";

		if($_SESSION['personalFBFlg']==1 && $_SESSION['latestResults']=='own')
		{
			include('cad_results/show_cad_results_private.php');
		
			$sqlStr = "SELECT lf.exec_id, lf.lesion_id, el.plugin_name, el.version, pt.patient_id, pt.patient_name,"
					. " st.study_date, st.study_time, es.study_instance_uid, es.series_instance_uid,"
					. " storage.path, storage.apache_alias, cm.result_table"
					. " FROM cad_master cm JOIN (patient_list pt JOIN (study_list st JOIN (storage_master storage JOIN"
					. " (series_list sr JOIN (executed_series_list es JOIN"
					. " (lesion_feedback lf JOIN executed_plugin_list el ON lf.exec_id=el.exec_id)"
					. " ON lf.exec_id=es.exec_id AND es.series_id=1) ON sr.series_instance_uid=es.series_instance_uid)"
					. " ON sr.storage_id=storage.storage_id) ON st.study_instance_uid=es.study_instance_uid)"
					. " ON pt.patient_id=st.patient_id) ON cm.cad_name=el.plugin_name AND cm.version=el.version"
					. " WHERE lf.entered_by=? AND lf.consensual_flg='f' AND lf.interrupt_flg='f'"
					. " AND lf.evaluation=2 ORDER BY lf.registered_at DESC LIMIT 3";
		
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindValue(1, $_SESSION['userID']);
			$stmt->execute();
			
			$missedTPNum = $stmt->rowCount();
		
			$missedTPData = $stmt->fetchAll(PDO::FETCH_ASSOC);
			//var_dump($missedTPData);
		
			for($i=0; $i<$missedTPNum; $i++)
			{
				$sqlStr = "SELECT cad.location_x, cad.location_y, cad.location_z,"
						. " param.crop_org_x as org_x, param.crop_org_y as org_y,"
						. " param.crop_width, param.crop_height, param.window_level, param.window_width"
						. " FROM param_set param JOIN " . $missedTPData[$i]['result_table'] . " cad"
						. " ON param.exec_id=cad.exec_id"
						. " WHERE cad.exec_id=? AND cad.sub_id=?";
					
				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindValue(1, $missedTPData[$i]['exec_id']);
				$stmt->bindValue(2, $missedTPData[$i]['lesion_id']);
				$stmt->execute();			
		
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				$missedTPData[$i] = array_merge($missedTPData[$i], $result);
			}
			
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
				$dispHeight = (int)($item['crop_height'] * ($dispWidth / $item['crop_width']) + 0.5);
				$scale = $dispWidth/$item['crop_width'];
			
				$img = new Imagick();
				$img->readImage($dstFname);			
				$width  = $img->getImageWidth();
				$height = $img->getImageHeight();
				$img->destroy();

				$latestHtml .= '<div class="result-record-3cols al-c">'
							.  '<div class="al-l" style="font-size:12px;">'
							.  '<b>&nbsp;Pt.: </b>' . $item['patient_name'] . ' (' . $item['patient_id'] . ')<br>'
							.  '<b>&nbsp;St.: </b>' . $item['study_date'] . '&nbsp;' . $item['study_time'] . '<br>'
							.  '<b>&nbsp;CAD: </b>' . $item['plugin_name'] . ' v.' . $item['version']
							.  '<input name="" type="button" value="detail" class="form-btn"'
							.  ' onclick="location.href=\'cad_results/show_cad_results.php?execID=' . $item['exec_id']
							.  '&feedbackMode=personal&remarkCand=' . $item['lesion_id'] . '&sortKey=0&sortOrder=t\';"'
							.  ' style="margin-left:70px;"><br>'
							.  '<b>&nbsp;Candidate ID: </b>' . $item['lesion_id'] . '<br>'
							.  '</div>'
							.  '<div style="width:' . $dispWidth . 'px; height:' .  $dispHeight . 'px;'
							.  ' overflow:hidden; position:relative; margin-bottom:7px;" class="block-al-c">'
							.  '<img class="transparent" src="cad_results/images/magenta_circle.png"'
							.  ' style="position:absolute; left:' .  (($item['location_x']-$item['org_x'])*$scale-12) . 'px;'
							.  ' top:' .(($item['location_y']-$item['org_y'])*$scale-12) . 'px; z-index:2;">'
							.  '<img src="' . $dstFnameWeb . '" width=' . $width*$scale . ' height=' . $height*$scale
							.  ' style="position:absolute; left:' . (-$item['org_x']*$scale) . 'px; top:' . (-$item['org_y']*$scale) . 'px;'
							.  ' z-index:1;">'
							.  '</div>'
							.  '</div>';
			}
		}
		//--------------------------------------------------------------------------------------------------------------
		
		//----------------------------------------------------------------------------------------------------
		// Settings for Smarty
		//----------------------------------------------------------------------------------------------------
		require_once('smarty/SmartyEx.class.php');
		$smarty = new SmartyEx();
		
		$smarty->assign('newsData', $newsData);
		
		$smarty->assign('executionNum',     $executionNum);
		$smarty->assign('oldestExecDate',   $oldestExecDate);
		$smarty->assign('cadExecutionData', $cadExecutionData);
		
		//$smarty->assign('colParam', $colParam);
		
		$smarty->assign('latestHtml', $latestHtml);
	
		$smarty->display('home.tpl');
		//----------------------------------------------------------------------------------------------------			
		

	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;

?>		