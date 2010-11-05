<?php

	$params['candNum'] = 0;
	$candArr = array();
	
	$stmt = $pdo->prepare('SELECT COUNT(*) FROM "' . $params['resultTableName'] . '" WHERE exec_id=?');
	$stmt->bindValue(1, $params['execID']);
	$stmt->execute();
	
	$params['totalCandNum'] = $stmt->fetchColumn();
	
	if($params['maxDispNum'] <= 0)  $params['maxDispNum'] = $params['totalCandNum'];
		
	if($params['feedbackMode'] == "consensual")
	{
		$sqlStr = 'SELECT * FROM "' . $params['resultTableName'] . '"'
				. " WHERE exec_id= :execID"
				. " AND sub_id IN (SELECT DISTINCT(lesion_id) FROM lesion_feedback"
				. " WHERE exec_id=:execID AND consensual_flg='f' AND interrupt_flg='f')"
				. ' ORDER BY ';
				
		switch($params['sortKey'])
		{
			case 0: $sqlStr .= 'confidence';   break;  // confidence
			case 1: $sqlStr .= 'location_z';   break;  // slice number
			case 2: $sqlStr .= 'volume_size';  break;  // volume of candidate
		}	
		$sqlStr .= ($params['sortOrder'] == 'f') ? ' ASC' : ' DESC';

		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindValue(':execID', $params['execID']); 
		$stmt->execute();
	}	
	else // for personal
	{
		$sqlStr = 'SELECT * FROM (SELECT * FROM "' . $params['resultTableName'] . '"'
				. " WHERE exec_id=? AND confidence >= ? ORDER BY confidence DESC"
				. " LIMIT ?) set1 ORDER BY set1.";
				  
		switch($params['sortKey'])
		{
			case 0: $sqlStr .= 'confidence';   break;  // confidence
			case 1: $sqlStr .= 'location_z';   break;  // slice number
			case 2: $sqlStr .= 'volume_size';  break;  // volume of candidate
		}	
		$sqlStr .= ($params['sortOrder'] == 'f') ? ' ASC' : ' DESC';
	
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute(array($params['execID'], $params['confidenceTh'], $params['maxDispNum']));
		//var_dump($stmt);
	}	
	
	$params['candNum'] = $stmt->rowCount();
	
	$params['resultColNum'] = 3;  // 3 candidates per row
	
	if($params['candNum'] < 5 && $params['mainModality'] == 'CT')   // for HIMEDIC
	//if($params['candNum'] < 5)					      // for other case...
	{
		$params['resultColNum'] = 2;
		$params['dispWidth'] = 384;	
	}

	$params['dispHeight'] = (int)($params['cropHeight'] * ($params['dispWidth'] / $params['cropWidth']) + 0.5);	

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
		if($params['registTime'] != "")
		{
			$registMsg = 'registered at ' . $params['registTime'];
			if($params['feedbackMode'] == "consensual")
			{
				$registMsg .= ' (by ' . $enteredBy . ')';
			}
		}
	}
	
	$candHtml = array();
	
	$k = 0;
	
	$params['scale'] = $params['dispWidth']/$params['cropWidth'];
	$params['lesionCheckCnt'] = 0;	

	while($result = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		$candID     = $result['sub_id'];
		$confidence = $result['confidence'];
		$posX       = $result['location_x'];
		$posY       = $result['location_y'];
		$posZ       = $result['location_z'];
		
		$candStr .= $candID . "^";
		array_push($candArr,  $candID);
	
		$srcFname = sprintf("%s%sresult%03d.png", $params['pathOfCADReslut'], $DIR_SEPARATOR, $candID);
		$srcFnameWeb = sprintf("../%s%sresult%03d.png", $params['webPathOfCADReslut'], $DIR_SEPARATOR_WEB, $candID);

		if(!is_file($srcFname)) DcmExport::dcm2png($srcFname, $posZ, $params['windowLevel'], $params['windowWidth']);

		$img = new Imagick();
		$img->readImage($srcFname);			
		$width  = $img->getImageWidth();
		$height = $img->getImageHeight();
		$img->destroy();
		
		$candHtml[$k]  = '<div id="lesionBlock' . $candID . '" class="result-record-' . $params['resultColNum'] . 'cols al-c"';
		if($candID == $params['remarkCand'])  $candHtml[$k] .= ' style="border: 1px solid #F00;"';
		$candHtml[$k] .= '>';
		$candHtml[$k] .= '<div class="al-l">';
		$candHtml[$k] .= sprintf("<b>&nbsp;Image No.: </b>%d<br>", $posZ);
		$candHtml[$k] .= sprintf("<b>&nbsp;Slice location: </b>%.2f [mm]<br>", $result['slice_location']);
		$candHtml[$k] .= sprintf("<b>&nbsp;Volume: </b>%.2f [mm3]<br>", $result['volume_size']);
		//$candHtml[$k] .= sprintf("<b>&nbsp;Confidence: </b>%.3f<br>", $confidence);
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
		
			//$evalVal = ($registTime == "") ? -1 : -2;
			$evalVal = -2;
			$checkFlg = 0;
			
			if($params['feedbackMode'] == "personal" || $params['feedbackMode'] == "consensual")
			{			
				$sqlStr = "SELECT evaluation FROM lesion_feedback WHERE exec_id=? AND lesion_id=?";
				if($params['feedbackMode'] == "personal")	$sqlStr .= " AND consensual_flg='f' AND entered_by=?";		
				else										$sqlStr .= " AND consensual_flg='t'";
				
				$stmtFeedback = $pdo->prepare($sqlStr);
				$stmtFeedback->bindParam(1, $params['execID']);
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
				$sqlStr  = "SELECT COUNT(DISTINCT entered_by) FROM lesion_feedback"
		                 . " WHERE exec_id=? AND consensual_flg='f'";
				$stmtFeedback = $pdo->prepare($sqlStr);
				$stmtFeedback->bindParam(1, $params['execID']);		
				$stmtFeedback->execute();						 
				$totalNum =  $stmtFeedback->fetchColumn();
			}			
			
			$candHtml[$k] .= '<div class="hide-on-guest js-personal-or-consensual ' . $params['feedbackMode'] . '">';
			
			//$maxNum = 0;
			
			for($j=0; $j<count($radioButtonList[$consensualFlg]); $j++)
			{
				$evalStr = "";
				$titleStr = "";
				$enterNum = 0;
			
				if($params['feedbackMode'] == "consensual")
				{
					$sqlStr = "SELECT entered_by FROM lesion_feedback WHERE exec_id=?"
				            . " AND lesion_id=? AND consensual_flg='f' AND interrupt_flg='f'";
							
					if($radioButtonList[$consensualFlg][$j][0] == 'TP')  $sqlStr .= " AND evaluation>0";
					else                                                 $sqlStr .= " AND evaluation=?";
									
					$stmtFeedback = $pdo->prepare($sqlStr);
					$stmtFeedback->bindParam(1, $params['execID']);
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
				
				$candHtml[$k] .= '<span class=""><input type="radio" name="radioCand' . $candID . '"'
				                .  ' value="' . $radioButtonList[$consensualFlg][$j][1] . '"'
				                .  ' label="' . $radioButtonList[$consensualFlg][$j][0] . $evalStr . '"'
								.  ' class="radio-to-button"';

				if($params['registTime'] == "")  $candHtml[$k] .= ' onclick="DispRegistCaution()"';
			
				if($evalVal == $radioButtonList[$consensualFlg][$j][1])
				{
					$candHtml[$k] .= ' checked="checked"';
					$params['lesionCheckCnt']++;
				}				
				
				if(($params['feedbackMode'] != "personal" && $params['feedbackMode'] != "consensual")
				   || ($params['registTime'] != "" && $evalVal != $radioButtonList[$consensualFlg][$j][1]))	 $candHtml[$k] .= ' disabled="disabled"';
				
				if($params['feedbackMode'] == "consensual" && $titleStr != "")	$candHtml[$k] .= ' title="' . $titleStr . '" /></span><!-- -->';
				else													        $candHtml[$k] .= ' /></span><!-- -->';
			}

			$candHtml[$k] .= '</div>';
			
		//----------------------------------------------------------------------------------------------------
		// Tag list
		//----------------------------------------------------------------------------------------------------

		}
		
		$candHtml[$k] .= '</div>';
		$k++;
	}

	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Retrieve the number of FNs
	//------------------------------------------------------------------------------------------------------------------
	$params['fnInputFlg'] = 0;
	$params['fnNum'] = 0;
	$params['fnPersonalCnt'] = 0;	
	
	$sqlStr = "SELECT false_negative_num FROM false_negative_count WHERE exec_id=? AND status>=1";
		
	if($params['feedbackMode']=="personal")         $sqlStr .= " AND entered_by=? AND consensual_flg='f'";
	else if($params['feedbackMode']=="consensual")  $sqlStr .= " AND consensual_flg='t'";
		
	$stmt = $pdo->prepare($sqlStr);
	$stmt->bindParam(1, $params['execID']);
	if($params['feedbackMode']=="personal")  $stmt->bindParam(2, $userID);

	$stmt->execute();
		
	if($stmt->rowCount() == 1)
	{
		$params['fnNum'] = $stmt->fetchColumn();
		$params['fnInputFlg'] = 1;
	}
		
	if($params['feedbackMode']=="consensual")
	{
		$sqlStr = "SELECT SUM(false_naegative_num) FROM false_negative_count"
				. " WHERE exec_id=? AND consensual_flg='f' AND status=2";
			
		$params['fnPersonalCnt'] = PdoQueryOne($pdo, $sqlStr, $params['execID'], 'SCALAR');
	}
	//------------------------------------------------------------------------------------------------------------------

	$params['registStr'] = 'Lesion classification: '
	                     . (($params['candNum']==$params['lesionCheckCnt']) ? 'complete' : 'incomplete')
						 . '<br/>FN input: '
						 . (($params['fnInputFlg']) ? 'complete' : 'incomplete');

	//------------------------------------------------------------------------------------------------------------------
	// For CAD detail
	//------------------------------------------------------------------------------------------------------------------
	$detailData = array( 'orgWidth'     => $params['orgWidth'],
	                     'orgHeight'    => $params['orgHeight'],
						 'dispWidth'    => $params['orgWidth'],
						 'dispHeight'   => $params['orgHeight'],
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
	$stmt->bindParam(1, $params['modality']);
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
	$flist = GetDicomFileListInPath($params['seriesDir']);
	$detailData['fNum'] = count($flist);
		
	$subDir = $params['seriesDir'] . $DIR_SEPARATOR . $SUBDIR_JPEG;
	
	$tmpFname = $flist[$detailData['imgNum']-1];

	$srcFname = $params['seriesDir'] . $DIR_SEPARATOR . $tmpFname;

	// For compresed DICOM file
	$tmpFname = str_ireplace("c_", "_", $tmpFname);
	$tmpFname = substr($tmpFname, 0, strlen($tmpFname)-4);

	$dstFname = $subDir . $DIR_SEPARATOR . $tmpFname;
	$dstBase  = $dstFname;
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
		DcmExport::createThumbnailJpg($srcFname, $dstBase, $detailData['presetName'], $JPEG_QUALITY, 1,
		                              $detailData['windowLevel'], $detailData['windowWidth']);
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
	require_once('../smarty/SmartyEx.class.php');
	$smarty = new SmartyEx();

	$smarty->assign('params', $params);

	$smarty->assign('consensualFBFlg', $consensualFBFlg);
	$smarty->assign('sliceOffset',     $sliceOffset);

	$smarty->assign('ticket', htmlspecialchars($_SESSION['ticket'], ENT_QUOTES));
	
	$smarty->assign('registTime',    $registTime);	
	$smarty->assign('registMsg',     $registMsg);

	$smarty->assign('fnConsCheck',   $fnConsCheck);

	$smarty->assign('candArr',       $candArr);	
	$smarty->assign('candHtml',      $candHtml);	
	
	$smarty->assign('candStr',       $candStr);

	// For CAD detail
	$smarty->assign('detailData', $detailData);

	$smarty->display('cad_results/lesion_cad_display.tpl');
	//------------------------------------------------------------------------------------------------------------------		

	
?>
