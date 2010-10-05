<?php

	$imgNum = (isset($_REQUEST['imgNum'])) ? $_REQUEST['imgNum'] : 1;

	$img = new Imagick();
	$img->readImage($pathOfCADReslut . $DIR_SEPARATOR . 'result' . sprintf("%03d", $imgNum) . '.png');
	$dispWidth  = $img->getImageWidth();
	$dispHeight = $img->getImageHeight();
	
	$consensualFBFlg = ($_SESSION['groupID'] == 'admin') ? 1 : 0;

	$stmt = $pdo->prepare('SELECT MAX(sub_id) FROM "fat_volumetry_v1.2" WHERE exec_id=?');
	$stmt->bindParam(1, $param['execID']);
	$stmt->execute();
	$maxImgNum = $stmt->fetchColumn();

	$orgImg = $webPathOfCADReslut . '/ct' . sprintf("%03d", $imgNum) . '.png';
	$resImg = $webPathOfCADReslut . '/result' . sprintf("%03d", $imgNum) . '.png';
	
	//------------------------------------------------------------------------------------------------------------------
	// Measuring results
	//------------------------------------------------------------------------------------------------------------------
	$stmt = $pdo->prepare('SELECT * FROM "fat_volumetry_v1.2" WHERE exec_id=? AND sub_id =?');
	$stmt->execute(array($param['execID'], $imgNum));

	$data = $stmt->fetch(PDO::FETCH_ASSOC);
	//------------------------------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Scoring HTML
	//------------------------------------------------------------------------------------------------------------------
	$scoreTitle = array("Heart, diaphragm", "Pelvic floor", "Abdominal cavity", "Other", "Abdominal wall");
	$colName    = array("heart_", "pelvic_", "cavity_", "other_", "wall_");	

	$scoreStr = "";
	$evalComment = "";
	$registTime = "";
	$scoringHTML = "";
	
	$evalVal = array();
	for($j=0; $j<5; $j++)
	for($i=0; $i<3; $i++)
	{
		$evalVal[$j][$i] = 0;
	}	
	
	$sqlStr = 'SELECT * FROM "fat_volumetry_v' . $param['version'] . '_score"'
			.  " WHERE exec_id=? AND consensual_flg='f' AND entered_by=?";

	$stmt = $pdo->prepare($sqlStr);
	$stmt->execute(array($param['execID'], $userID));
		
	if($stmt->rowCount()==1)
	{
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		for($j=0; $j<5; $j++)
		{
			$evalVal[$j][0] = $result[$colName[$j] . 'vat'];
			$evalVal[$j][1] = $result[$colName[$j] . 'sat'];
			$evalVal[$j][2] = $result[$colName[$j] . 'sat'];
		}
			$evalComment = $result['eval_comment'];
			$registTime = $result['registered_at'];

			$scoreStr = $result['heart_vat']."^".$result['heart_sat']."^".$result['heart_bound']."^"
			          . $result['cavity_vat']."^".$result['cavity_sat']."^".$result['cavity_bound']."^"
					  . $result['wall_vat']."^".$result['wall_sat']."^".$result['wall_bound']."^"
					  . $result['pelvic_vat']."^".$result['pelvic_sat']."^".$result['pelvic_bound']."^"
					  . $result['other_vat']."^".$result['other_sat']."^".$result['other_bound'];
	}

	$consensualFlg = ($feedbackMode == "consensualFeedback") ? 1 : 0;
	$modifyFlg = (isset($_REQUEST['modifyFlg'])) ? $_REQUEST['modifyFlg'] : 0;

	$scoringHTML .= '<table class="mt10 ml10">'
	             .  '<tr>'
	             .  '<th>&nbsp;</th><th class="al-c">VAT</th><th class="al-c">SAT</th><th class="al-c">Bound</th>'
				 .  '<th width=15>&nbsp;</th>'
				 .  '<th>&nbsp;</th><th class="al-c">VAT</th><th class="al-c">SAT</th><th class="al-c">Bound</th>'
	             .  '</tr>';
	
	for($j=0; $j<5; $j++)
	{
		$scoringHTML .= ($j%2 == 0) ? '<tr>' : '<td width=15></td>';
		
		$scoringHTML .= '<th class="al-l">' . $scoreTitle[$j] . '&nbsp;</th>';
		
		$scoringHTML .= '<td class="al-c">';
		$scoringHTML .= '<select id="' . $colName[$j] . 'vat"';
		if($registTime != "") $scoringHTML .= ' disabled="disabled"';
		$scoringHTML .= '>';
		
		for($i=-2; $i<=2; $i++)
		{
			$scoringHTML .= '<option value=' . $i;
			if($i == $evalVal[$j][0])  $scoringHTML .= ' selected="selected"';
			$scoringHTML .= '>' . $i . '</option>';
		}
		
		$scoringHTML .= '</select>';
		$scoringHTML .= '&nbsp;</td>';
				
		$scoringHTML .= '<td class="al-c">';
		$scoringHTML .= '<select id="' . $colName[$j] . 'sat"';
		if($registTime != "") $scoringHTML .= ' disabled="disabled"';
		$scoringHTML .= '>';
		
		for($i=-2; $i<=2; $i++)
		{
			$scoringHTML .= '<option value=' . $i;
			if($i == $evalVal[$j][1])  $scoringHTML .= ' selected="selected"';
			$scoringHTML .= '>' . $i . '</option>';
		}
		
		$scoringHTML .= '</select>';
		$scoringHTML .= '&nbsp;</td>';

		$scoringHTML .= '<td class="al-c">';
		$scoringHTML .= '<select id="' . $colName[$j] . 'bound"';
		if($registTime != "") $scoringHTML .= ' disabled="disabled"';
		$scoringHTML .= '>';
		
		for($i=-2; $i<=2; $i++)
		{
			$scoringHTML .= '<option value=' . $i;
			if($i == $evalVal[$j][2])  $scoringHTML .= ' selected="selected"';
			$scoringHTML .= '>' . $i . '</option>';
		}
		
		$scoringHTML .= '</select>';
		$scoringHTML .= '&nbsp;</td>';

		
		if($j%2 == 1)   $scoringHTML .= '</tr>';
		else if($j==4)  $scoringHTML .= '<td></td><td>&nbsp;</td></tr>';
	}
	
	$scoringHTML .= '</table>';

	$scoringHTML .= '<div style="margin:10px; font-size:14px;">'
				 .  '<table>'
				 .  '<tr align=left valign=top>'
				 .  '<th>Comment:</th>'
				 .  '<td><textarea id="evalComment" cols="60" rows="3"';
	if($registTime != "")  $scoringHTML .= ' disabled="disabled"';
	$scoringHTML .= '>' . $evalComment . '</textarea></td>'
	             . '</tr>'
	             . '</table>'
				 . '</div>';
	//------------------------------------------------------------------------------------------------------------------


	//------------------------------------------------------------------------------------------------------------------
	// Settings for Smarty
	//------------------------------------------------------------------------------------------------------------------
	require_once('../smarty/SmartyEx.class.php');
	$smarty = new SmartyEx();

	$smarty->assign('param', $param);
	$smarty->assign('data',  $data);

	$smarty->assign('consensualFBFlg', $consensualFBFlg);

	$smarty->assign('dispWidth',  $dispWidth);
	$smarty->assign('dispHeight', $dispHeight);

	$smarty->assign('imgNum',     $imgNum);
	$smarty->assign('maxImgNum',  $maxImgNum);

	$smarty->assign('sliceOffset',     $sliceOffset);

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
	
	$smarty->assign('orgImg', $orgImg);	
	$smarty->assign('resImg', $resImg);	

	$smarty->assign('scoringHTML', $scoringHTML);	
	$smarty->assign('registTime',  $registTime);
	$smarty->assign('scoreStr',    $scoreStr);
	$smarty->assign('evalComment',  $evalComment);
	
		
	//$smarty->display('cad_results/fat_volumetry_v1.tpl');
	$smarty->display('cad_results/fat_volumetry_v1_with_score.tpl');
	//------------------------------------------------------------------------------------------------------------------		
?>
