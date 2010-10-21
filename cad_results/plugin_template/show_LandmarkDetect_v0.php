<?php

	$imgNum = (isset($_REQUEST['imgNum'])) ? $_REQUEST['imgNum'] : 1;

	$stmt = $pdo->prepare("SELECT crop_width, crop_height, crop_depth FROM param_set WHERE exec_id=?");
	$stmt->bindParam(1, $params['execID']);
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_NUM);

	$width  = $result[0];	$dispWidth  = (int)($width/2);
	$height = $result[1];	$dispHeight = (int)($height/2);
	$depth  = $result[2];   $dispDepth  = (int)($depth/2);
	
	$xPos = (int)($width/2);
	$yPos = (int)($height/2);
	$zPos = (int)($depth/2);
	
	$sqlStr = "SELECT sub_id, landmark_name, short_name, location_x as x, location_y as y, location_z as z"
		    . " FROM \"landmark_detection_v0\" WHERE exec_id=? ORDER BY sub_id ASC";	 
	$stmt = $pdo->prepare($sqlStr);
	$stmt->bindParam(1, $params['execID']);
	$stmt->execute();
	$posData = $stmt->fetchAll();

	$dstHtml .= '<input type="hidden" id="orgWidth"   value="' . $width . '">';
	$dstHtml .= '<input type="hidden" id="orgHeight"  value="' . $height . '">';
	$dstHtml .= '<input type="hidden" id="orgDepth"   value="' . $depth . '">';
	$dstHtml .= '<input type="hidden" id="dispWidth"  value="' . $dispWidth . '">';
	$dstHtml .= '<input type="hidden" id="dispHeight" value="' . $dispHeight . '">';
	$dstHtml .= '<input type="hidden" id="dispDepth"  value="' . $dispDepth . '">';
	$dstHtml .= '<input type="hidden" id="xPos"       value="' . $xPos . '">';
	$dstHtml .= '<input type="hidden" id="yPos"       value="' . $yPos . '">';
	$dstHtml .= '<input type="hidden" id="zPos"       value="' . $zPos . '">';
	$dstHtml .= '<input type="hidden" id="webPathOfCADReslut" value="../' . $webPathOfCADReslut . '">';
	
	$dstHtml .= '<div id="resultBody" class="resultBody" style="background-color:#f0f;">';
	
	//----------------------------------------------------------------------------------------------
	// Main table
	//----------------------------------------------------------------------------------------------
	$dstHtml  = '<div class="imgDisp">'
	          . '<table>'
	          . '<tr>';

	// Axial
	$dstHtml .= '<td>';
	$dstHtml .= '<div class="imgArea" style="width:' . $dispWidth . 'px; height:' .  $dispHeight . 'px;">';
	$dstHtml .='<img id="axial" src="../' . $webPathOfCADReslut . '/axialSectionAbdomen_' . sprintf("%04d", $zPos) . '.jpg"';
	$dstHtml .=' width=' . $dispWidth . ' height=' . $dispHeight . '>';
	$dstHtml .= '<img id="axialCross" src="images/magenta_cross.png"';
	$dstHtml .= ' style="position:relative; left:' . ($xPos/2-25) . 'px; top:' . (-$yPos/2-25) . 'px;">';
	$dstHtml .= '</div>';
	$dstHtml .= '</td>';	
	
	$dstHtml .= '<td width=10 rowspan=5></td>';

	$dstHtml .= '<td rowspan=3 align=left>';
	$dstHtml .= '<table>';
	$dstHtml .= '<tr valign=top>';
	$dstHtml .= '<td>';
	$dstHtml .= '<div id="axialEnlargeArea" class="imgArea" style="width:101px; height:101px; position:relative;  top:0px; left:0px;">';	
	$dstHtml .= '<img id="axialEnlargeCross" src="images/magenta_cross_enlarge.png" style="position:absolute; left:0px; top:0px; z-index:2;">';
	$dstHtml .= '<img id="axialEnlarge" src="../' . $webPathOfCADReslut . '/axialSectionAbdomen_' . sprintf("%04d", $zPos) . '.jpg"';
	$dstHtml .= ' width=' . $width . ' height=' . $height;
	$dstHtml .= ' style="position:absolute; left:' . (-$xPos+50) . 'px; top:' . (-$yPos+50) . 'px; z-index:1;">';
	$dstHtml .= '</div>';
	$dstHtml .= '</td>';
	$dstHtml .= '<td rowspan=2; width=3></td>';

	$dstHtml .= '<td>';
	$dstHtml .= '<table>';
	$dstHtml .= '<tr>';
	$dstHtml .= '<td width=40><span id="xPosDisp">X:' . $xPos . '</span></td>';
	$dstHtml .= '<td><img id="xPosPlusButton" src="images/plus.gif">';
	$dstHtml .= '&nbsp;<img id="xPosMinusButton" src="images/minus.gif"></td>';
	$dstHtml .= '</tr>';
	$dstHtml .= '<tr>';
	$dstHtml .= '<td><span id="yPosDisp">Y:' . $yPos . '</span></td>';
	$dstHtml .= '<td><img id="yPosPlusButton" src="images/plus.gif">';
	$dstHtml .= '&nbsp;<img id="yPosMinusButton" src="images/minus.gif"></td>';
	$dstHtml .= '</tr>';
	$dstHtml .= '<tr>';
	$dstHtml .= '<td><span id="zPosDisp">Z:' . $zPos . '</span></td>';
	$dstHtml .= '<td><img id="zPosPlusButton" src="images/plus.gif">';
	$dstHtml .= '&nbsp;<img id="zPosMinusButton" src="images/minus.gif"></td>';
	$dstHtml .= '</tr>';
	$dstHtml .= '<tr>';
	$dstHtml .= '<td colspan=2>Grayscale:&nbsp;';
	$dstHtml .= '<select id="presetMenu">';
	$dstHtml .= '<option value="Abdomen">Abdomen</option>'; 
	$dstHtml .= '<option value="Lung">Lung</option>'; 
	$dstHtml .= '<option value="Bone">Bone</option>';
	$dstHtml .= '</select>'; 
	$dstHtml .= '</td></tr>';
	$dstHtml .= '<tr>';
	$dstHtml .= '<td colspan=2>Scale:&nbsp;';
	$dstHtml .= '<select id="scaleMenu">';
	$dstHtml .= '<option value="1">&times;1</option>'; 
	$dstHtml .= '<option value="2">&times;2</option>'; 
	$dstHtml .= '<option value="3">&times;3</option>';
	$dstHtml .= '<option value="4">&times;4</option>';
	$dstHtml .= '</select>'; 
	$dstHtml .= '</td></tr>';
	$dstHtml .= '</table>';
	$dstHtml .= '</td>';
	$dstHtml .= '</tr>';

	$dstHtml .= '<tr valign=top>';
	$dstHtml .= '<td>';
	$dstHtml .= '<div id="coronalEnlargeArea" class="imgArea" style="width:101px; height:101px; position:relative; top:0px; left:0px;">';	
	$dstHtml .= '<img id="coronalEnlargeCross" src="images/magenta_cross_enlarge.png" style="position:absolute; left:0px; top:0px; z-index:2;">';
	$dstHtml .= '<img id="coronalEnlarge" src="../' . $webPathOfCADReslut . '/coronalSectionAbdomen_' . sprintf("%04d", $yPos) . '.jpg"';
	$dstHtml .= ' width=' . $width . ' height=' . $depth;
	$dstHtml .= ' style="position:absolute; left:' . (-$xPos+50) . 'px; top:' . (-$zPos+50) . 'px; z-index:1;">';	
	$dstHtml .= '</div>';
	$dstHtml .= '</td>';
	
	$dstHtml .= '<td>';
	$dstHtml .= '<div id="sagittalEnlargeArea" class="imgArea" style="width:101px; height:101px; position:relative; top:0px; left:0px;">';	
	$dstHtml .= '<img id="sagittalEnlargeCross" src="images/magenta_cross_enlarge.png" style="position:absolute; left:0px; top:0px; z-index:2;">';		
	$dstHtml .= '<img id="sagittalEnlarge" src="../' . $webPathOfCADReslut . '/sagittalSectionAbdomen_' . sprintf("%04d", $xPos) . '.jpg"';
	$dstHtml .= ' width=' . $height . ' height=' . $depth;
	$dstHtml .= ' style="position:absolute; left:' . (-$yPos+50) . 'px; top:' . (-$zPos+50) . 'px; z-index:1;">';
	$dstHtml .= '</div>';
	$dstHtml .= '</td>';	
	$dstHtml .= '</tr>';
	$dstHtml .= '</table>';	
	$dstHtml .= '</td>';	
	$dstHtml .= '</tr>';
	
	$dstHtml .= '<tr>';
	$dstHtml .= '<td align=center><div id="axialSlider" class="mt5 mb5"></div></td>';	
	$dstHtml .= '</tr>';
	
	$dstHtml .= '<tr><td height=5></td></tr>';
	
	$dstHtml .= '<tr>';
	
	// Coronal
	$dstHtml .= '<td>';
	$dstHtml .= '<div class="imgArea" style="width:' . $dispWidth . 'px; height:' .  $dispDepth . 'px;">';
	$dstHtml .= '<img id="coronal" src="../' . $webPathOfCADReslut . '/coronalSectionAbdomen_' . sprintf("%04d", $yPos) . '.jpg"';
	$dstHtml .= ' width=' . $dispWidth . ' height=' . $dispDepth . '>';
	$dstHtml .= '<img id="coronalCross" src="images/magenta_cross.png"';
	$dstHtml .= ' style="position:relative; left:' . ($xPos/2-25) . 'px; top:' . ($zPos/2-$dispDepth-25) . 'px;">';
	$dstHtml .= '</div>';
	$dstHtml .= '</td>';	
	
	// Saggittal
	$dstHtml .= '<td>';
	$dstHtml .= '<div class="imgArea" style="width:' . $dispHeight . 'px; height:' .  $dispDepth . 'px;">';
	$dstHtml .= '<img id="sagittal" src="../' . $webPathOfCADReslut . '/sagittalSectionAbdomen_' . sprintf("%04d", $xPos) . '.jpg"';
	$dstHtml .= ' width=' . $dispHeight . ' height=' . $dispDepth . '>';
	$dstHtml .= '<img id="sagittalCross" src="images/magenta_cross.png" ';
	$dstHtml .= ' style="position:relative; left:' . ($yPos/2-25) . 'px; top:' . ($zPos/2-$dispDepth-25) . 'px;">';
	$dstHtml .= '</div>';
	$dstHtml .= '</td>';	

	$dstHtml .= '</tr>';
	
	$dstHtml .= '<tr>';
	$dstHtml .= '<td align=center><div id="coronalSlider"  class="mt5 mb5"></div></td>';	
	$dstHtml .= '<td align=center><div id="sagittalSlider" class="mt5 mb5"></div></td>';	
	$dstHtml .= '</tr>';
	$dstHtml .= '</table>';
	$dstHtml .= '</div>';
	//----------------------------------------------------------------------------------------------

	//----------------------------------------------------------------------------------------------
	// Position table
	//----------------------------------------------------------------------------------------------
	$dstHtml .='<div class="rightColumn">';	
	$dstHtml .= '<div class="posTable">';
	$dstHtml .= '<table class="scrollTable">';
	$dstHtml .= '<thead>';
	$dstHtml .= '<tr align=center>';
	$dstHtml .= '<th class="al-c">ID</th><th class="al-c" width=120>Name</th><th class="al-c">Rank</th>';
	$dstHtml .= '<th class="al-c" width=30>x</th><th class="al-c" width=30>y</th><th class="al-c" width=30>z</th>';
	$dstHtml .= '<th class="al-c" width=40>&nbsp;</th>';
	$dstHtml .= '</tr>';
	$dstHtml .= '</thead>';
	$dstHtml .= '<tbody>';
	
	$maxID = 0;
	
	foreach($posData as $item)
	{
		if($maxID < $item['sub_id'])  $maxID = $item['sub_id'];
	
		$dstHtml .= '<tr id="row' . $item['sub_id'] . '" class="landmarkRow">';
		$dstHtml .= '<td class="landmarkID">' .   $item['sub_id'] . '</td>';
		//$dstHtml .= '<td class="landmarkName">' . $item['landmark_name'] . '</td>';
		$dstHtml .= '<td class="landmarkName">' . $item['short_name'] . '</td>';
		$dstHtml .= '<td class="landmarkRank">&nbsp;</td>';
		$dstHtml .= '<td class="landmarkXpos">' . $item['x'] . '</td>';
		$dstHtml .= '<td class="landmarkYpos">' . $item['y'] . '</td>';	
		$dstHtml .= '<td class="landmarkZpos">' . $item['z'] . '</td>';
		$dstHtml .= '<td class="colButtons">';
		$dstHtml .= '<input type="button" id="edit' . $item['sub_id'] . '" class="editPos form-btn" value="E" />';
		$dstHtml .= '<input type="button" id="del' . $item['sub_id'] . '" class="delPos form-btn" value="D" />';
		$dstHtml .= '</td>';
		//$dstHtml .= '<td>&nbsp;&nbsp;&nbsp;</td>';
		$dstHtml .= '</tr>';	
	}
	$dstHtml .= '</tbody>';
	$dstHtml .= '</table>';
	$dstHtml .= '</div>';
	$dstHtml .= '<div class="mt10 ml10">';
	$dstHtml .= '<input type="button" id="addRow" class="form-btn" value="Add row" />&nbsp;';
	$dstHtml .= '<input type="button" id="cancelButton" class="form-btn" value="Cancel" disabled="disbled" />&nbsp;';
	$dstHtml .= '<input type="button" id="saveButton" class="form-btn" value="Save" disabled="disabled" />';
	$dstHtml .= '</div>';

	$dstHtml .= '</div>';
	$dstHtml .= '</div>';
	//----------------------------------------------------------------------------------------------

	//------------------------------------------------------------------------------------------------------------------
	// Settings for Smarty
	//------------------------------------------------------------------------------------------------------------------
	//エラーが発生した場合にエラー表示をする設定
	ini_set( 'display_errors', 1 );

	require_once('../smarty/SmartyEx.class.php');
	$smarty = new SmartyEx();

	$smarty->assign('params', $params);

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

	$smarty->assign('width',              $width);
	$smarty->assign('height',             $height);	
	$smarty->assign('depth',              $depth);
	$smarty->assign('dispWidth',          $dispWidth);
	$smarty->assign('dispHeight',         $dispHeight);
	$smarty->assign('dispDepth',          $dispDepth);
	$smarty->assign('seriesID',           $seriesID);
	$smarty->assign('xPos',               $xPos);
	$smarty->assign('yPos',               $yPos);
	$smarty->assign('zPos',               $zPos);
	$smarty->assign('webPathOfCADReslut', $webPathOfCADReslut);
	$smarty->assign('maxID',              $maxID);

	$smarty->assign('dstHtml', $dstHtml);	

	$smarty->display('cad_results/landmark_detect_v0.tpl');
	//------------------------------------------------------------------------------------------------------------------



?>
