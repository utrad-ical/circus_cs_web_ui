<?php

	$candNum = 0;
	$candArr = array();
	
	$stmt = $pdo->prepare('SELECT COUNT(*) FROM "' . $resultTableName . '" WHERE exec_id=?');
	$stmt->bindValue(1, $param['execID']);
	$stmt->execute();
	
	$param['totalCandNum'] = $stmt->fetchColumn();
	
	if($param['maxDispNum'] <= 0)  $param['maxDispNum'] = $param['totalCandNum'];
		
	if($param['feedbackMode'] == "consensual")
	{
		$sqlStr = 'SELECT * FROM "' . $resultTableName . '" WHERE exec_id= :execID'
				. " AND sub_id IN (SELECT DISTINCT(lesion_id) FROM lesion_feedback"
				. " WHERE exec_id=:execID AND consensual_flg='f' AND interrupt_flg='f')"
				. ' ORDER BY ';
				
		switch($param['sortKey'])
		{
			case 0: $sqlStr .= 'confidence';   break;  // confidence
			case 1: $sqlStr .= 'location_z';   break;  // slice number
			case 2: $sqlStr .= 'volume_size';  break;  // volume of candidate
		}	
		$sqlStr .= ($param['sortOrder'] == 'f') ? ' ASC' : ' DESC';

		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindValue(':execID', $param['execID']); 
		$stmt->execute();
	}	
	else // for personal
	{
		$sqlStr = 'SELECT * FROM (SELECT * FROM "' . $resultTableName . '"'
				. " WHERE exec_id=? AND confidence >= ? ORDER BY confidence DESC"
				. " LIMIT ?) set1 ORDER BY set1.";
				  
		switch($param['sortKey'])
		{
			case 0: $sqlStr .= 'confidence';   break;  // confidence
			case 1: $sqlStr .= 'location_z';   break;  // slice number
			case 2: $sqlStr .= 'volume_size';  break;  // volume of candidate
		}	
		$sqlStr .= ($param['sortOrder'] == 'f') ? ' ASC' : ' DESC';
	
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute(array($param['execID'], $param['confidenceTh'], $param['maxDispNum']));
		//var_dump($stmt);
	}	
	
	$candNum = $stmt->rowCount();
	
	$param['resultColNum'] = 3;  // 3 candidates per row
	
	if($candNum < 5 && $mainModality == 'CT')   // for HIMEDIC
	//if($candNum < 5)					      // for other case...
	{
		$param['resultColNum'] = 2;
		$dispWidth = 384;	
	}

	$dispHeight = (int)($cropHeight * ($dispWidth / $cropWidth) + 0.5);	

	//--------------------------------------------------------------------------------------------------------
	// Make one-time ticket
	//--------------------------------------------------------------------------------------------------------
	$_SESSION['ticket'] = md5(uniqid().mt_rand());
	//--------------------------------------------------------------------------------------------------------

	//--------------------------------------------------------------------------------------------------------
	// image and feedback buttons
	//--------------------------------------------------------------------------------------------------------
	$fnConsCheck = 0;
	
	if($_SESSION['personalFBFlg'] || $_SESSION['consensualFBFlg'] || $_SESSION['groupID'] == 'demo')
	{
		if($registTime != "")
		{
			$registMsg = 'registered at ' . $registTime;
			if($param['feedbackMode'] == "consensual")
			{
				$registMsg .= ' (by ' . $enteredBy . ')';
			}
		}
		
		//------------------------------------------------------------------------------------------------
		// Personal mode で誰かがFN入力している場合、Consensual modeのFN input入力が済むまでは
		// 正式登録にせず、FN入力画面へ遷移する
		//------------------------------------------------------------------------------------------------		
		if($param['feedbackMode'] == "consensual" && $param['interruptFlg'] == 1)
		{		
			$sqlStr = "SELECT COUNT(*) FROM false_negative_location"
					. " WHERE exec_id=? AND consensual_flg='f' AND interrupt_flg='f'";
			$stmtFn = $pdo->prepare($sqlStr);
			$stmtFn->bindValue(1, $param['execID']);
			$stmtFn->execute();

			$fnPersonalCnt = $stmtFn->fetchColumn();
			
			$sqlStr = "SELECT COUNT(*) FROM false_negative_count"
					. " WHERE exec_id=? AND consensual_flg='t' AND status=2";
			$stmtFn = $pdo->prepare($sqlStr);
			$stmtFn->bindValue(1, $param['execID']);
			$stmtFn->execute();

			$fnConsCnt = $stmtFn->fetchColumn();
			
			if($fnPersonalCnt > 0 && $fnConsCnt != 1)  $fnConsCheck = 1;
		}
		//--------------------------------------------------------------------------------------------
	}
	
	$candHtml = array();
	
	$k = 0;
	
	$scale = $dispWidth/$cropWidth;

	while($result = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		$candID     = $result['sub_id'];
		$confidence = $result['confidence'];
		$posX       = $result['location_x'];
		$posY       = $result['location_y'];
		$posZ       = $result['location_z'];
		
		$candStr .= $candID . "^";
		array_push($candArr,  $candID);
	
		$srcFname = sprintf("%s%sresult%03d.png", $pathOfCADReslut, $DIR_SEPARATOR, $candID);
		$srcFnameWeb = sprintf("../%s%sresult%03d.png", $webPathOfCADReslut, $DIR_SEPARATOR_WEB, $candID);

		if(!is_file($srcFname))  dcm2png($cmdForProcess, $cmdDcmToPng, $DIR_SEPARATOR, $srcFname, $posZ, $candID, $windowLevel, $windowWidth);

		$img = new Imagick();
		$img->readImage($srcFname);			
		$width  = $img->getImageWidth();
		$height = $img->getImageHeight();
		$img->destroy();
		
		$candHtml[$k]  = '<div id="lesionBlock' . $candID . '" class="result-record-' . $param['resultColNum'] . 'cols al-c"';
		if($candID == $param['remarkCand'])  $candHtml[$k] .= ' style="border: 1px solid #F00;"';
		$candHtml[$k] .= '>';
		$candHtml[$k] .= '<div class="al-l">';
		$candHtml[$k] .= sprintf("<b>&nbsp;Image No.: </b>%d<br>", $posZ);
		$candHtml[$k] .= sprintf("<b>&nbsp;Slice location: </b>%.2f [mm]<br>", $result['slice_location']);
		$candHtml[$k] .= sprintf("<b>&nbsp;Volume: </b>%.2f [mm3]<br>", $result['volume_size']);
		//$candHtml[$k] .= sprintf("<b>&nbsp;Confidence: </b>%.3f<br>", $confidence);
		$candHtml[$k] .= '</div>';

		$candHtml[$k] .= '<div style="width:' . $dispWidth . 'px; height:' .  $dispHeight . 'px;'
		              .  ' overflow:hidden; position:relative; margin-bottom:7px;" class="block-al-c" ondblclick="ShowCADDetail(' . $posZ . ')">';
					  
		if($confidence >= $doubleCircleTh)
		{
		 	$candHtml[$k] .= '<img class="transparent" src="images/double_circle.png"'
		                  .  ' style="position:absolute; left:' . (($posX-$orgX)*$scale-15)
						  .  'px; top:' . (($posY-$orgY)*$scale-15) . 'px; z-index:2;">';		
		}
		else if($confidence < $yellowCircleTh)
		{
		 	$candHtml[$k] .= '<img class="transparent" src="images/yellow_circle.png"'
		                  .  ' style="position:absolute; left:' . (($posX-$orgX)*$scale-12)
						  .  'px; top:' . (($posY-$orgY)*$scale-12) . 'px; z-index:2;">';
		}
		else
		{
		 	$candHtml[$k] .= '<img class="transparent" src="images/magenta_circle.png"'
		                  .  ' style="position:absolute; left:' . (($posX-$orgX)*$scale-12)
						  .  'px; top:' . (($posY-$orgY)*$scale-12) . 'px; z-index:2;">';
		}
		
		$candHtml[$k] .= '<img src="' . $srcFnameWeb . '" width=' . $width*$scale . ' height=' . $height*$scale
		              .  ' style="position:absolute; left:' . (-$orgX*$scale) . 'px; top:' . (-$orgY*$scale) . 'px; z-index:1;">'
					  .  '</div>';
		
		// MRA with axial MIP display
		//if($param['cadName'] == "MRA-CAD")
		//{
		//	$srcMIPFnameWeb = sprintf("../%s%sMIP_axial.png", $webPathOfCADReslut, $DIR_SEPARATOR_WEB);
		//
		//	$candHtml[$k] .= '<div style="width:' . $dispWidth . 'px; height:' .  $dispHeight . 'px;'
		//	              .  ' overflow:hidden; position:relative; margin-bottom:7px;" class="block-al-c">'
		//	              .  '<img class="transparent" src="images/magenta_circle.png"'
		//	              .  ' style="position:absolute; left:' . (($posX-$orgX)*$scale-12) . 'px; top:' . (($posY-$orgY)*$scale-12) . 'px; z-index:2;">'
		//	              .  '<img src="' . $srcMIPFnameWeb . '" width=' . $width*$scale . ' height=' . $height*$scale
		//	              .  ' style="position:absolute; left:' . (-$orgX*$scale) . 'px; top:' . (-$orgY*$scale) . 'px; z-index:1;">'
		//	              .  '</div>';
		//}
		
		if($resultType == 1 && ($_SESSION['personalFBFlg'] || $_SESSION['consensualFBFlg']))
		{
			$consensualFlg = ($param['feedbackMode'] == "consensual") ? 1 :0;
		
			//$evalVal = ($registTime == "") ? -1 : -2;
			$evalVal = -2;
			$checkFlg = 0;
			
			if($param['feedbackMode'] == "personal" || $param['feedbackMode'] == "consensual")
			{			
				$sqlStr = "SELECT evaluation FROM lesion_feedback WHERE exec_id=? AND lesion_id=?";
				if($param['feedbackMode'] == "personal")	$sqlStr .= " AND consensual_flg='f' AND entered_by=?";		
				else										$sqlStr .= " AND consensual_flg='t'";
				
				$stmtFeedback = $pdo->prepare($sqlStr);
				$stmtFeedback->bindParam(1, $param['execID']);
				$stmtFeedback->bindParam(2, $candID);
				if($param['feedbackMode'] == "personal")  $stmtFeedback->bindParam(3, $userID);
		
				$stmtFeedback->execute();

				if($stmtFeedback->rowCount() == 1)
				{
					$checkFlg = 1;
					$evalVal = $stmtFeedback->fetchColumn();
				}
			}
			
			$totalNum = 0;
			
			if($param['feedbackMode'] == "consensual")
			{
				$sqlStr  = "SELECT COUNT(DISTINCT entered_by) FROM lesion_feedback"
		                 . " WHERE exec_id=? AND consensual_flg='f'";
				$stmtFeedback = $pdo->prepare($sqlStr);
				$stmtFeedback->bindParam(1, $param['execID']);		
				$stmtFeedback->execute();						 
				$totalNum =  $stmtFeedback->fetchColumn();
			}			
			
			$candHtml[$k] .= '<div class="hide-on-guest js-personal-or-consensual ' . $param['feedbackMode'] . '">';
			
			//$maxNum = 0;
	
			for($j=0; $j<count($radioButtonList[$consensualFlg]); $j++)
			{
				$evalStr = "";
				$titleStr = "";
				$enterNum = 0;
			
				if($param['feedbackMode'] == "consensual")
				{
					$sqlStr = "SELECT entered_by FROM lesion_feedback WHERE exec_id=?"
				            . " AND lesion_id=? AND consensual_flg='f' AND interrupt_flg='f'";
							
					if($radioButtonList[$consensualFlg][$j][0] == 'TP')  $sqlStr .= " AND evaluation>0";
					else                                                 $sqlStr .= " AND evaluation=?";
									
					$stmtFeedback = $pdo->prepare($sqlStr);
					$stmtFeedback->bindParam(1, $param['execID']);
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
					
					//if($checkFlg == 0 && $totalNum > 0 && $enterNum > $maxNum)
					if($checkFlg == 0 && $totalNum > 0 && $enterNum == $totalNum)
					{
						$evalVal = $radioButtonList[$consensualFlg][$j][1];
						//$maxNum = $enterNum;
					}
				}
				
				$candHtml[$k] .= '<span class=""><input type="radio" name="radio' . $candID . '"'
				                .  ' value="' . $radioButtonList[$consensualFlg][$j][1] . '"'
				                .  ' label="' . $radioButtonList[$consensualFlg][$j][0] . $evalStr . '"'
								.  ' class="radio-to-button"';

				if($registTime == "")  $candHtml[$k] .= ' onclick="DispRegistCaution()"';
			
				if($evalVal == $radioButtonList[$consensualFlg][$j][1])		$candHtml[$k] .= ' checked="checked"';
				
				if(($param['feedbackMode'] != "personal" && $param['feedbackMode'] != "consensual")
				   || ($registTime != "" && $evalVal != $radioButtonList[$consensualFlg][$j][1]))	 $candHtml[$k] .= ' disabled="disabled"';
				
				if($param['feedbackMode'] == "consensual" && $titleStr != "")	$candHtml[$k] .= ' title="' . $titleStr . '" /></span><!-- -->';
				else													        $candHtml[$k] .= ' /></span><!-- -->';
			}

			$candHtml[$k] .= '</div>';
			
		//	$sqlStr = "SELECT tag FROM lesion_candidate_tag WHERE exec_id=? AND candidate_id=?";
		//	if($param['feedbackMode'] == "consensual")
		//	{
		//		$sqlStr .= " AND consensual_flg='t'";
		//	}
		//	else
		//	{
		//		$sqlStr .= " AND consensual_flg='f' AND entered_by=?";
		//	}
		//	$sqlStr .= " ORDER BY tag_id ASC";
		//
		//	$stmtTag = $pdo->prepare($sqlStr);
		//	$stmtTag->bindParam(1, $param['execID']);
		//	$stmtTag->bindParam(2, $candID);
		//	if($param['feedbackMode'] == "personal")  $stmtTag->bindParam(3, $userID);
		//		
		//	$stmtTag->execute();
		//	$tagNum = $stmtTag->rowCount();
		//	
		//	$candHtml[$k] .= '<p id="candidateTagArea' . $candID . '" class="fs-xs" style="margin-top:3px;"> Tags:';
		//	
		//	while($resCandTag = $stmtTag->fetch(PDO::FETCH_NUM))
		//	{
		//		$candHtml[$k] .= " " . $resCandTag[0];
		//	}
		//	
		//	if($_SESSION['researchFlg']==1)
		//	{			
		//		$candHtml[$k] .= ' <a href="#" onclick="EditCandidateTag(' . $param['execID'] . ',' . $candID
		//					  .  ',\'' . $param['feedbackMode'] . '\',\'' . $userID . '\');">(Edit)</a></p>';
		//	}
		}
		
		$candHtml[$k] .= '</div>';
		$k++;
	}

	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Input FN number
	//------------------------------------------------------------------------------------------------------------------
	if($_SESSION['personalFBFlg'] || $_SESSION['consensualFBFlg'] || $_SESSION['groupID'] == 'demo')
	{
		$sqlStr = "SELECT * FROM false_negative_count WHERE exec_id=?";
			
		if($param['feedbackMode']=="personal")         $sqlStr .= " AND entered_by=? AND consensual_flg='f'";
		else if($param['feedbackMode']=="consensual")  $sqlStr .= " AND consensual_flg='t'";
		
		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindParam(1, $param['execID']);
		if($param['feedbackMode']=="personal")  $stmt->bindParam(2, $userID);
		
		$stmt->execute();
		
		$fnNum = 0;
		$fnCountStatus = 0;
	
		if($stmt->rowCount() == 1)
		{
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$fnNum = $result['false_negative_num'];
			$fnCountStatus = $result['status'];
		}
	}
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// For CAD detail
	//------------------------------------------------------------------------------------------------------------------
	$detailData = array( 'orgWidth'     => $orgWidth,
	                     'orgHeight'    => $orgHeight,
						 'dispWidth'    => $orgWidth,
						 'dispHeight'   => $orgHeight,
						 'windowLevel'  => 0,
						 'windowWidth'  => 0,
						 'presetName'   => "",
						 'grayscaleStr' => "",
						 'imgNum'       => 1);
			
	if($detailData['dispWidth'] >= $detailData['dispHeight'] && $detailData['dispWidth'] > 256)
	{
		$detailData['dispWidth']  = 256;
		$detailData['dispHeight'] = (int)((float)$detailData['orgHeight'] * (float)$detailData['dispWidth']/(float)$detailData['orgWidth']);
	}
	else if($detailData['dispHeight'] > 256)
	{
		$detailData['dispHeight'] = 256;
		$detailData['dispWidth']  = (int)((float)$detailData['orgWidth'] * (float)$detailData['dispHeight']/(float)$detailData['orgHeight']);
	}	
	$detailData['dispWidth']  = (int)($detailData['dispWidth']  * $RESCALE_RATIO_OF_SERIES_DETAIL);
	$detailData['dispHeight'] = (int)($detailData['dispHeight'] * $RESCALE_RATIO_OF_SERIES_DETAIL);

	$detailData['imgLeftPos'] = (256 * $RESCALE_RATIO_OF_SERIES_DETAIL / 2) - ($detailData['dispWidth'] / 2);
	$detailData['imgNumStrLeftPos'] = $detailData['imgLeftPos'] + 5;
		
	$stmt = $pdo->prepare("SELECT * FROM grayscale_preset WHERE modality=? ORDER BY priolity ASC");
	$stmt->bindParam(1, $modality);
	$stmt->execute();
			
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
	foreach($result as $key => $item)
	{
		if($item['priolity'] == 1)
		{
			$detailData['windowLevel'] = $item['window_level'];
			$detailData['windowWidth'] = $item['window_width'];
			$detailData['presetName']  = $item['preset_name'];
		}
		if($key > 0)  $detailData['grayscaleStr'] .= '^';
		
		$detailData['grayscaleStr'] .= $item['preset_name'].'^'.$item['window_level'].'^'.$item['window_width'];
	}
	
	$flist = array();
	$flist = GetDicomFileListInPath($seriesDir);
	$detailData['fNum'] = count($flist);
		
	$subDir = $seriesDir . $DIR_SEPARATOR . $SUBDIR_JPEG;
	
	$tmpFname = $flist[$detailData['imgNum']-1];

	$srcFname = $seriesDir . $DIR_SEPARATOR . $tmpFname;

	// For compresed DICOM file
	$tmpFname = str_ireplace("c_", "_", $tmpFname);
	$tmpFname = substr($tmpFname, 0, strlen($tmpFname)-4);

	$dstFname = $subDir . $DIR_SEPARATOR . $tmpFname;
	$detailData['dstFnameWeb'] = $seriesDirWeb . $DIR_SEPARATOR_WEB . $SUBDIR_JPEG . $DIR_SEPARATOR_WEB . $tmpFname;	
	
	$dumpFname = $dstFname . ".txt";
	
	if($detailData['presetName'] != "" && $detailData['presetName'] != "Auto") 
	{
		$dstFname .= "_" . $detailData['presetName'];
		$detailData['dstFnameWeb'] .= "_" . $detailData['presetName'];
	}
	$dstFname .= '.jpg';
	$detailData['dstFnameWeb'] .= '.jpg';		
	
	if(!is_file($dstFname))
	{
		CreateThumbnail($cmdForProcess, $cmdCreateThumbnail, $srcFname, $dstFname, $JPEG_QUALITY, 1, $data['windowLevel'], $data['windowWidth']);
	}
				
	$fp = fopen($dumpFname, "r");
	
	$detailData['sliceNumber'] = 0;
	$detailData['sliceLocation'] = 0;
		
	if($fp != NULL)
	{
		while($str = fgets($fp))
		{
			$dumpTitle   = strtok($str,":");
			$dumpContent = strtok("\r\n");
	
			switch($dumpTitle)
			{
				case 'Img. No.':
				case 'Image No.':
					$detailData['sliceNumber'] = $dumpContent;
					break;

				case 'Slice location':
					$detailData['sliceLocation'] = sprintf("%.2f [mm]", $dumpContent);
					break;
			}
		}
		fclose($fp);
	}
		
	if($detailData['grayscaleStr'] != "")
	{
		$detailData['presetArr'] = explode("^", $detailData['grayscaleStr']);
	}
	$detailData['presetNum'] = count($detailData['presetArr'])/3;
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Settings for Smarty
	//------------------------------------------------------------------------------------------------------------------
	//エラーが発生した場合にエラー表示をする設定
	ini_set( 'display_errors', 1 );

	require_once('../smarty/SmartyEx.class.php');
	$smarty = new SmartyEx();

	$smarty->assign('param', $param);

	$smarty->assign('consensualFBFlg', $consensualFBFlg);
	$smarty->assign('sliceOffset',     $sliceOffset);

	$smarty->assign('ticket', htmlspecialchars($_SESSION['ticket'], ENT_QUOTES));

	$smarty->assign('patientID',         $patientID);
	$smarty->assign('patientName',       $patientName);	
	$smarty->assign('sex',               $sex);
	$smarty->assign('age',               $age);
	$smarty->assign('studyID',           $studyID);
	$smarty->assign('studyDate',         $studyDate);
	$smarty->assign('seriesID',          $seriesID);
	$smarty->assign('modality',          $modality);
	$smarty->assign('seriesDescription', $seriesDescription);
	$smarty->assign('seriesDate',        $seriesDate);
	$smarty->assign('seriesTime',        $seriesTime);
	$smarty->assign('bodyPart',          $bodyPart);
	
	$smarty->assign('registTime',    $registTime);	
	$smarty->assign('registMsg',     $registMsg);

	$smarty->assign('fnConsCheck',   $fnConsCheck);
	
	$smarty->assign('candNum',       $candNum);	

	$smarty->assign('candArr',       $candArr);	
	$smarty->assign('candHtml',      $candHtml);	
	
	$smarty->assign('fnNum',         $fnNum);
	$smarty->assign('fnCountStatus', $fnCountStatus);
	$smarty->assign('candStr',       $candStr);

	// For CAD detail
	$smarty->assign('detailData', $detailData);

	$smarty->display('cad_results/lesion_cad_display.tpl');
	//------------------------------------------------------------------------------------------------------------------		

	
?>
