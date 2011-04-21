<?php

	include_once("lesion_candidate_display_private.php");

	//--------------------------------------------------------------------------------------------------------
	// Get attributes from "executed_plugin_attributes" table
	//--------------------------------------------------------------------------------------------------------
	$stmt = $pdo->prepare("SELECT key, value FROM executed_plugin_attributes WHERE job_id=?");
	$stmt->bindParam(1, $params['jobID']);
	$stmt->execute();
	$result = array();

	foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $item)
	{
		$result[$item['key']] = $item['value'];
	}

	$params['orgX']         = $result['crop_org_x'];
	$params['orgY']         = $result['crop_org_y'];
	$params['cropWidth']    = $result['crop_width'];
	$params['cropHeight']   = $result['crop_height'];
	$params['pixelSize']    = $result['pixel_size'];
	$params['distSlice']    = $result['dist_slice'];
	$params['sliceOffset']  = $result['slice_offset'];
	$params['windowLevel']  = $result['window_level'];
	$params['windowWidth']  = $result['window_width'];
	//--------------------------------------------------------------------------------------------------------

	// Get main modality (1st series)
	$sqlStr = "SELECT modality FROM plugin_cad_series WHERE plugin_id=? AND series_id=0";
	$params['mainModality'] = DBConnector::query($sqlStr, $params['pluginID'], 'SCALAR');

	//--------------------------------------------------------------------------------------------------------
	$params['registMsg'] = "";
	$params['registTime'] = "";

	$sqlStr = "SELECT registered_at, entered_by FROM feedback_list WHERE job_id=? AND status=1";
	
	if($params['feedbackMode'] == "personal")  $sqlStr .= " AND entered_by=? AND is_consensual='f'";
	else                                       $sqlStr .= " AND is_consensual='t'";

	$stmt = $pdo->prepare($sqlStr);
	$stmt->bindparam(1, $params['jobID']);
	if($params['feedbackMode'] == "personal")  $stmt->bindParam(2, $userID);
	$stmt->execute();

	if($stmt->rowCount() >= 1)
	{
		$result = $stmt->fetch();
		$params['registTime'] = $result['registered_at'];
		$enteredBy  = $result['entered_by'];
		$consensualFBFlg = 1;

		$params['registMsg'] = 'registered at ' . $params['registTime'];
		if($params['feedbackMode'] == "consensual")
		{
			$params['registMsg'] .= ' (by ' . $result['entered_by']. ')';
		}
	}
	//else
	//{
	//	$sqlStr = substr_replace($sqlStr, "'t'", (strlen($sqlStr)-3));
	//	$stmt = $pdo->prepare($sqlStr);
	//	$stmt->bindparam(1, $params['jobID']);
	//	if($params['feedbackMode'] == "personal")  $stmt->bindParam(2, $userID);
	//	$stmt->execute();
	//	if($stmt->rowCount() >= 1)  $params['interruptFlg'] = 1;
	//}
	//--------------------------------------------------------------------------------------------------------

	//--------------------------------------------------------------------------------------------------------
	//
	//--------------------------------------------------------------------------------------------------------
	$params['candNum'] = 0;
	$candArr = array();

	$stmt = $pdo->prepare('SELECT COUNT(*) FROM "' . $params['resultTableName'] . '" WHERE job_id=?');
	$stmt->bindValue(1, $params['jobID']);
	$stmt->execute();

	$params['totalCandNum'] = $stmt->fetchColumn();

	if($params['maxDispNum'] <= 0)  $params['maxDispNum'] = $params['totalCandNum'];

	if($params['feedbackMode'] == "consensual")
	{
		$sqlStr = 'SELECT * FROM "' . $params['resultTableName'] . '"'
				. " WHERE job_id= :jobID"
				. " AND sub_id IN"
				. " (SELECT DISTINCT(cc.candidate_id) FROM feedback_list fl, candidate_classification cc"
				. " WHERE fl.job_id=:jobID AND cc.fb_id=fl.fb_id"
				. " AND fl.is_consensual='f' AND fl.status=1)"
				. ' ORDER BY ' . $params['sortKey'] . ' ' . $params['sortOrder'];

		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindValue(':jobID', $params['jobID']);
		$stmt->execute();
	}
	else // for personal
	{
		$sqlStr = 'SELECT * FROM (SELECT * FROM "' . $params['resultTableName'] . '"'
				. " WHERE job_id=? AND confidence >= ? ORDER BY confidence DESC  LIMIT ?) set1"
				. " ORDER BY set1." . $params['sortKey'] . ' ' . $params['sortOrder'];

		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute(array($params['jobID'], $params['confidenceTh'], $params['maxDispNum']));
		//var_dump($stmt->errorInfo());
	}

	$params['candNum'] = $stmt->rowCount();
	//--------------------------------------------------------------------------------------------------------

	//--------------------------------------------------------------------------------------------------------
	// Set parameters for candidate display
	//--------------------------------------------------------------------------------------------------------
	$params['resultColNum'] = 3;  // 3 candidates per row
	$params['dispWidth'] = 256;

	if($params['candNum'] < 5 && $params['mainModality'] == 'CT')   // for HIMEDIC
	//if($params['candNum'] < 5)					      			// for other case...
	{
		$params['resultColNum'] = 2;
		$params['dispWidth'] = 384;
	}

	$params['scale'] = $params['dispWidth']/$params['cropWidth'];
	$params['dispHeight'] = (int)($params['cropHeight'] * $params['scale'] + 0.5);
	//--------------------------------------------------------------------------------------------------------

	//--------------------------------------------------------------------------------------------------------
	// Set HTML statement for each lesion candidate
	//--------------------------------------------------------------------------------------------------------
	$fnConsCheck = 0;
	$candHtml = array();
	$k = 0;

	$params['lesionCheckCnt'] = 0;

	$dispSid = array();

	while($result = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		$candID     = $result['sub_id'];
		$confidence = $result['confidence'];
		$posX       = $result['location_x'];
		$posY       = $result['location_y'];
		$posZ       = $result['location_z'];

		$candArr[] = $candID;
		$dispSid[] = $result['sid'];

		$srcFname = sprintf("%s%sresult%03d.png", $params['pathOfCADReslut'], $DIR_SEPARATOR, $candID);
		$srcFnameWeb = sprintf("../%s%sresult%03d.png", $params['webPathOfCADReslut'], $DIR_SEPARATOR_WEB, $candID);

		if(!is_file($srcFname)) DcmExport::dcm2png($srcFname, $posZ, $params['windowLevel'], $params['windowWidth']);

		$img = @imagecreatefrompng($srcFname);
	
	    if($img)
		{
			$width  = imagesx($img);
			$height = imagesy($img);
			imagedestroy($img);
		}


		$candHtml[$k]  = '<div id="lesionBlock' . $candID . '" class="result-record-' . $params['resultColNum'] . 'cols al-c"';
		if($candID == $params['remarkCand'])  $candHtml[$k] .= ' style="border: 1px solid #F00;"';
		$candHtml[$k] .= '>';
		$candHtml[$k] .= '<div class="al-l">';
		$candHtml[$k] .= sprintf("<b>&nbsp;Image No.: </b>%d<br>", $posZ);
		$candHtml[$k] .= sprintf("<b>&nbsp;Slice location: </b>%.2f [mm]<br>", $result['slice_location']);
		$candHtml[$k] .= sprintf("<b>&nbsp;Volume: </b>%.2f [mm3]<br>", $result['volume_size']);
		if($params['dispConfidenceFlg'])  $candHtml[$k] .= sprintf("<b>&nbsp;Confidence: </b>%.3f<br>", $confidence);
		$candHtml[$k] .= '</div>';

		$candHtml[$k] .= '<div style="width:' . $params['dispWidth'] . 'px; height:' .  $params['dispHeight'] . 'px;'
		              .  ' overflow:hidden; position:relative; margin-bottom:7px;" class="block-al-c" ondblclick="ShowCADDetail(' . $posZ . ')">';

		if($confidence >= $params['doubleCircleTh'])
		{
		 	$candHtml[$k] .= '<img class="transparent" src="images/double_circle.png"'
		                  .  ' style="position:absolute; left:' . (($posX-$params['orgX'])*$params['scale']-15)
						  .  'px; top:' . (($posY-$params['orgY'])*$params['scale']-15) . 'px; z-index:2;">';
		}
		else if($confidence < $params['yellowCircleTh'])
		{
		 	$candHtml[$k] .= '<img class="transparent" src="images/yellow_circle.png"'
		                  .  ' style="position:absolute; left:' . (($posX-$params['orgX'])*$params['scale']-12)
						  .  'px; top:' . (($posY-$params['orgY'])*$params['scale']-12) . 'px; z-index:2;">';
		}
		else
		{
		 	$candHtml[$k] .= '<img class="transparent" src="images/magenta_circle.png"'
		                  .  ' style="position:absolute; left:' . (($posX-$params['orgX'])*$params['scale']-12)
						  .  'px; top:' . (($posY-$params['orgY'])*$params['scale']-12) . 'px; z-index:2;">';
		}

		$candHtml[$k] .= '<img src="' . $srcFnameWeb . '" width=' . $width*$params['scale'] . ' height=' . $height*$params['scale']
		              .  ' style="position:absolute; left:' . (-$params['orgX']*$params['scale']) . 'px; top:' . (-$params['orgY']*$params['scale']) . 'px; z-index:1;">'
					  .  '</div>';

		// MRA with axial MIP display
		//if($params['cadName'] == "MRA-CAD")
		//{
		//	$srcMIPFnameWeb = sprintf("../%s%sMIP_axial.png", $params['webPathOfCADReslut'], $DIR_SEPARATOR_WEB);
		//
		//	$candHtml[$k] .= '<div style="width:' . $params['dispWidth'] . 'px; height:' .  $params['dispHeight'] . 'px;'
		//	              .  ' overflow:hidden; position:relative; margin-bottom:7px;" class="block-al-c">'
		//	              .  '<img class="transparent" src="images/magenta_circle.png"'
		//	              .  ' style="position:absolute; left:' . (($posX-$params['orgX'])*$params['scale']-12) . 'px; top:' . (($posY-$params['orgY'])*$params['scale']-12) . 'px; z-index:2;">'
		//	              .  '<img src="' . $srcMIPFnameWeb . '" width=' . $width*$params['scale'] . ' height=' . $height*$params['scale']
		//	              .  ' style="position:absolute; left:' . (-$params['orgX']*$params['scale']) . 'px; top:' . (-$params['orgY']*$params['scale']) . 'px; z-index:1;">'
		//	              .  '</div>';
		//}

		if($_SESSION['personalFBFlg'] || $_SESSION['consensualFBFlg'])
		{
			$consensualFlg = ($params['feedbackMode'] == "consensual") ? 1 :0;

			$evalVal = -2;
			$checkFlg = 0;

			if($params['feedbackMode'] == "personal" || $params['feedbackMode'] == "consensual")
			{
				$sqlStr = "SELECT cc.evaluation FROM feedback_list fl, candidate_classification cc"
						. " WHERE fl.job_id=? AND cc.fb_id=fl.fb_id AND cc.candidate_id=?";
				if($params['feedbackMode'] == "personal")	$sqlStr .= " AND fl.is_consensual='f' AND fl.entered_by=?";
				else										$sqlStr .= " AND fl.is_consensual='t'";

				$stmtFeedback = $pdo->prepare($sqlStr);
				$stmtFeedback->bindParam(1, $params['jobID']);
				$stmtFeedback->bindParam(2, $candID);
				if($params['feedbackMode'] == "personal")  $stmtFeedback->bindParam(3, $userID);

				$stmtFeedback->execute();

				if($stmtFeedback->rowCount() == 1)
				{
					$checkFlg = 1;
					$evalVal = $stmtFeedback->fetchColumn();
				}
			}

			$totalNum = 0;

			if($params['feedbackMode'] == "consensual")
			{
				$sqlStr  = "SELECT COUNT(DISTINCT entered_by) FROM feedback_list"
		                 . " WHERE job_id=? AND is_consensual='f'";
				$stmtFeedback = $pdo->prepare($sqlStr);
				$stmtFeedback->bindParam(1, $params['jobID']);
				$stmtFeedback->execute();
				$totalNum =  $stmtFeedback->fetchColumn();
			}

			$candHtml[$k] .= '<div class="hide-on-guest js-personal-or-consensual ' . $params['feedbackMode'] . '">';

			for($j=0; $j < count($radioButtonList[$consensualFlg]); $j++)
			{
				$evalStr = "";
				$titleStr = "";
				$enterNum = 0;

				if($params['feedbackMode'] == "consensual")
				{
					$sqlStr = "SELECT fl.entered_by FROM feedback_list fl, candidate_classification cc"
							. " WHERE fl.job_id=? AND cc.fb_id=fl.fb_id AND cc.candidate_id=?"
							. " AND fl.is_consensual='f' AND fl.status=1";

					if($radioButtonList[$consensualFlg][$j][0] == 'TP')  $sqlStr .= " AND (cc.evaluation=1 OR cc.evaluation=2)";
					else                                                 $sqlStr .= " AND cc.evaluation=?";

					$stmtFeedback = $pdo->prepare($sqlStr);
					$stmtFeedback->bindParam(1, $params['jobID']);
					$stmtFeedback->bindParam(2, $candID);
					if($radioButtonList[$consensualFlg][$j][0] != 'TP')  $stmtFeedback->bindParam(3, $radioButtonList[$consensualFlg][$j][1]);

					$stmtFeedback->execute();
					$enterNum = $stmtFeedback->rowCount();

					if($enterNum>0)
					{
						$evalStr = "&nbsp;" . $enterNum;

						for($i=0; $i<$enterNum; $i++)
						{
							if($i > 0) $titleStr .= ", ";
							$titleStr .= $stmtFeedback->fetchColumn();
						}
					}

					if($checkFlg == 0 && $totalNum > 0 && $enterNum == $totalNum)
					{
						$evalVal = $radioButtonList[$consensualFlg][$j][1];
					}
				}

				$candHtml[$k] .= '<span class=""><input type="radio" name="radioCand' . $candID . '"'
				                .  ' value="' . $radioButtonList[$consensualFlg][$j][1] . '"'
				                .  ' label="' . $radioButtonList[$consensualFlg][$j][0] . $evalStr . '"'
								.  ' class="radio-to-button"';

				//if($params['registTime'] == "")  $candHtml[$k] .= ' onclick="ChangeRegistCondition()"';
				if($params['registTime'] == "")
				{
					$candHtml[$k] .= ' onclick="ChangeLesionClassification('.$candID.','
					              .  '\''. str_replace('&nbsp;', '', $radioButtonList[$consensualFlg][$j][0]) .'\')"';
				}

				if($evalVal == $radioButtonList[$consensualFlg][$j][1])
				{
					$candHtml[$k] .= ' checked="checked"';
					$params['lesionCheckCnt']++;
				}

				if(($params['feedbackMode'] != "personal" && $params['feedbackMode'] != "consensual")
				   || ($params['registTime'] != "" && $evalVal != $radioButtonList[$consensualFlg][$j][1]))
				{
					$candHtml[$k] .= ' disabled="disabled"';
				}

				if($params['feedbackMode'] == "consensual" && $titleStr != "")
				{
					$candHtml[$k] .= ' title="' . $titleStr . '"';
				}
				$candHtml[$k] .= ' /></span><!-- -->';
			}

			$candHtml[$k] .= '</div>';
		}

		$candHtml[$k] .= '</div>';
		$k++;
	}

	$candStr = implode("^", $candArr);

	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Retrieve the number of false negatives
	//------------------------------------------------------------------------------------------------------------------
	$params['fnInputStatus'] = 0;
	$params['fnNum'] = 0;
	$params['fnPersonalCnt'] = 0;

	$sqlStr = "SELECT fn.fn_num FROM feedback_list fl, fn_count fn"
			. " WHERE fl.job_id=? AND fn.fb_id=fl.fb_id";

	if($params['feedbackMode']=="personal")         $sqlStr .= " AND fl.entered_by=? AND fl.is_consensual='f'";
	else if($params['feedbackMode']=="consensual")  $sqlStr .= " AND fl.is_consensual='t'";

	$stmt = $pdo->prepare($sqlStr);
	$stmt->bindParam(1, $params['jobID']);
	if($params['feedbackMode']=="personal")  $stmt->bindParam(2, $userID);

	$stmt->execute();

	if($stmt->rowCount() == 1)
	{
		$result = $stmt->fetch(PDO::FETCH_NUM);
		$params['fnNum'] = $result[0];
		$params['fnInputStatus'] = 1;
	}

	if($params['feedbackMode']=="consensual")
	{
		$sqlStr = "SELECT SUM(fn.fn_num) FROM feedback_list fl, fn_count fn"
				. " WHERE fl.job_id=? AND fn.fb_id=fl.fb_id "
				. " AND fl.is_consensual='f' AND fl.status=1";

		$params['fnPersonalCnt'] = DBConnector::query($sqlStr, $params['jobID'], 'SCALAR');
	}

	$params['registStr'] = 'Candidate classification: <span style="color:'
	                     . (($params['candNum']==$params['lesionCheckCnt']) ? 'blue;">complete' : 'red;">incomplete')
						 . '</span><br/>FN input: <span style="color:'
						 . (($params['fnInputStatus']==1) ? 'blue;">complete' : 'red;">incomplete') . '</span>';
	//------------------------------------------------------------------------------------------------------------------


	//------------------------------------------------------------------------------------------------------------------
	// For CAD detail
	//------------------------------------------------------------------------------------------------------------------
	$detailParams = array('orgWidth'     => $params['orgWidth'],
	                      'orgHeight'    => $params['orgHeight'],
						  'dispWidth'    => $params['orgWidth'],
						  'dispHeight'   => $params['orgHeight'],
						  'windowLevel'  => 0,
						  'windowWidth'  => 0,
						  'presetName'   => "",
						  'grayscaleStr' => "",
						  'imgNum'       => 1);

	if($detailParams['dispWidth'] >= $detailParams['dispHeight'] && $detailParams['dispWidth'] > 256)
	{
		$detailParams['dispWidth']  = 256;
		$detailParams['dispHeight'] = (int)((float)$detailParams['orgHeight'] * (float)$detailParams['dispWidth']
											 / (float)$detailParams['orgWidth']);
	}
	else if($detailParams['dispHeight'] > 256)
	{
		$detailParams['dispHeight'] = 256;
		$detailParams['dispWidth']  = (int)((float)$detailParams['orgWidth'] * (float)$detailParams['dispHeight']
                                             / (float)$detailParams['orgHeight']);
	}
	$detailParams['dispWidth']  = (int)($detailParams['dispWidth']  * $RESCALE_RATIO_OF_SERIES_DETAIL);
	$detailParams['dispHeight'] = (int)($detailParams['dispHeight'] * $RESCALE_RATIO_OF_SERIES_DETAIL);

	$detailParams['imgLeftPos'] = (256 * $RESCALE_RATIO_OF_SERIES_DETAIL / 2) - ($detailParams['dispWidth'] / 2);
	$detailParams['imgNumStrLeftPos'] = $detailParams['imgLeftPos'] + 5;

	$stmt = $pdo->prepare("SELECT * FROM grayscale_preset WHERE modality=? ORDER BY priolity ASC");
	$stmt->bindParam(1, $params['modality']);
	$stmt->execute();
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Retrieve preset grayscales
	//------------------------------------------------------------------------------------------------------------------
	$stmt = $pdo->prepare("SELECT * FROM grayscale_preset WHERE modality=? ORDER BY priolity ASC");
	$stmt->bindParam(1, $params['modality']);
	$stmt->execute();

	$grayscaleArray = array();
	$detailParams['presetArr'] = array();

	while($result = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		if($result['priolity'] == 1)
		{
			$detailParams['windowLevel'] = $result['window_level'];
			$detailParams['windowWidth'] = $result['window_width'];
			$detailParams['presetName']  = $result['preset_name'];
		}

		$grayscaleArray[] = $result['preset_name'];
		$grayscaleArray[] = $result['window_level'];
		$grayscaleArray[] = $result['window_width'];

		$detailParams['presetArr'][] = array($result['preset_name'],
											 $result['window_level'],
											 $result['window_width']);
	}

	$detailParams['grayscaleStr'] = implode('^', $grayscaleArray);
	$detailParams['presetNum'] = count($detailParams['presetArr']);
	//------------------------------------------------------------------------------------------------------------------

	$flist = array();
	$flist = GetDicomFileListInPath($params['seriesDir']);
	$detailParams['fNum'] = count($flist);

	$subDir = $params['seriesDir'] . $DIR_SEPARATOR . $SUBDIR_JPEG;

	$tmpFname = $flist[$detailParams['imgNum']-1];

	$srcFname = $params['seriesDir'] . $DIR_SEPARATOR . $tmpFname;

	// For compressed DICOM file
	$tmpFname = str_ireplace("c_", "_", $tmpFname);
	$tmpFname = substr($tmpFname, 0, strlen($tmpFname)-4);

	$dstFname = $subDir . $DIR_SEPARATOR . $tmpFname;
	$dstBase  = $dstFname;
	$detailParams['dstFnameWeb'] = $params['seriesDirWeb'] . $DIR_SEPARATOR_WEB . $SUBDIR_JPEG . $DIR_SEPARATOR_WEB . $tmpFname;

	$dumpFname = $dstFname . ".txt";

	if($detailParams['presetName'] != "" && $detailParams['presetName'] != "Auto")
	{
		$dstFname .= "_" . $detailParams['presetName'];
		$detailParams['dstFnameWeb'] .= "_" . $detailParams['presetName'];
	}
	$dstFname .= '.jpg';
	$detailParams['dstFnameWeb'] .= '.jpg';

	if(!is_file($dstFname))
	{
		DcmExport::createThumbnailJpg($srcFname, $dstBase, $detailParams['presetName'], $JPEG_QUALITY, 1,
		                              $detailParams['windowLevel'], $detailParams['windowWidth']);
	}

	$sqlStr = 'SELECT sid, sub_id, location_x, location_y, location_z, slice_location, volume_size, confidence'
			. ' FROM "' . $params['resultTableName'] . '" WHERE job_id=? ORDER BY ';

	$detailParams['sortStr'] ='Sort by ';

	switch($params['sortKey'])
	{
		case 'confidence':  // confidence
			$sqlStr .= 'confidence';
			$detailParams['sortStr'] .= 'confidence';
			break;

		case 'location_z': // slice number
			$sqlStr .= 'location_z';
			$detailParams['sortStr'] .= 'Image number';
			break;

		case 'volume_size': // volume of candidate
			$sqlStr .= 'volume_size';
			$detailParams['sortStr'] .= 'volume';
			break;
	}

	$sqlStr .= ' '. $params['sortOrder'];

	if($params['sortOrder'] == 'ASC')	$detailParams['sortStr'] .= ' (ascending order)';
	else								$detailParams['sortStr'] .= ' (descending order)';


	$detailData = DBConnector::query($sqlStr, $params['jobID'], 'ALL_NUM');

	for($i = 0; $i < count($detailData); $i++)
	{
		$candClass = "";

		foreach($dispSid as $item)
		{
			if($detailData[$i][0] == $item)
			{
				$candClass = " emphasis";
				break;
			}
		}

		$sqlStr = "SELECT tag FROM tag_list WHERE category=5 AND reference_id=?";
		$detailData[$i][] = implode(', ', DBConnector::query($sqlStr, $detailData[$i][0], 'ALL_COLUMN'));
		$detailData[$i][] = $candClass;
	}
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Write action log table (personal feedback only)
	//------------------------------------------------------------------------------------------------------------------
	if($params['feedbackMode'] == "personal" && $params['registTime'] == "")
	{
		$sqlStr = "INSERT INTO feedback_action_log (job_id, user_id, act_time, action, options)"
				. " VALUES (?,?,?,'open','CAD result')";
		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindParam(1, $params['jobID']);
		$stmt->bindParam(2, $userID);
		$stmt->bindParam(3, date('Y-m-d H:i:s'));
		$stmt->execute();

		$tmp = $stmt->errorInfo();
		echo $tmp[2];
	}
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Make one-time ticket
	//------------------------------------------------------------------------------------------------------------------
	$_SESSION['ticket'] = md5(uniqid().mt_rand());
	$params['ticket'] = $_SESSION['ticket'];
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Settings for Smarty
	//------------------------------------------------------------------------------------------------------------------
	$smarty = new SmartyEx();

	$smarty->assign('params', $params);

	$smarty->assign('consensualFBFlg', $consensualFBFlg);
	$smarty->assign('sliceOffset',     $sliceOffset);

	$smarty->assign('registMsg',     $registMsg);
	$smarty->assign('fnConsCheck',   $fnConsCheck);

	$smarty->assign('candArr',       $candArr);
	$smarty->assign('candHtml',      $candHtml);

	$smarty->assign('candStr',       $candStr);

	// For CAD detail
	$smarty->assign('detailData',   $detailData);
	$smarty->assign('detailParams', $detailParams);

	$smarty->display('cad_results/lesion_cad_display.tpl');
	//------------------------------------------------------------------------------------------------------------------


?>
