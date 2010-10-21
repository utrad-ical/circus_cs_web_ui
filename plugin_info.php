<?php
	//session_cache_limiter('none');
	session_start();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>Plug-in information</title>

<link rel="stylesheet" type="text/css" href="css/base_style.css">

<script type="text/javascript" src="js/jquery/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="js/search_condition.js"></script>

<script language="Javascript">;
<!--
jQuery(function(){

	jQuery("#cadMenu").change(function(){
		
		var tmp = jQuery("#cadMenu").val().split('^');
		var address = 'plugin_info.php?cadName=' + tmp[0] + '&version=' + tmp[1];

		location.href = address;
	
	});
});

-->
</script>
</head>

<body bgcolor=#ffffff>
<form id="form1" name="form1">

<div class="listTitle">Plug-in information</div>
<div style="font-size:5px;">&nbsp</div>
<!-- <div style="font-size:16px;">Plug-in name:&nbsp; -->

<?php

	include ('common.php');
	include("auto_logout.php");	

	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//--------------------------------------------------------------------------------------------------------
	$pluginName = (isset($_REQUEST['pluginName'])) ? $_REQUEST['pluginName'] : "";
	$version = (isset($_REQUEST['version'])) ? $_REQUEST['version'] : "";
	//--------------------------------------------------------------------------------------------------------

	$userID = $_SESSION['userID'];
	
	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		//$stmt = $pdo->prepare("SELECT cad_name, version FROM cad_master ORDER BY exec_flg DESC, cad_name ASC, version ASC");
		//$stmt->execute();
	
		//$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		// pull-down menu
		//echo '<select id="cadMenu" name="cadMenu">';

		//echo '<option value="^" selected></option>';

		//foreach($result as $item)
		//{
		//	echo '<option value="' . $item['cad_name'] . '^' . $item['version'] . '"';
		//	if($item['cad_name'] == $cadName && $item['version'] == $version)  echo ' selected';
		//	echo '>';
		//	echo $item['cad_name'] . ' v.' . $item['version'];
		//	echo '</option>';
		//}
		//echo '</select>';

		//echo '</div>';
		//echo '</form>';
		//echo '<div style="font-size:5px;">&nbsp;</div>';
		//echo '<hr>';
		
		$seriesList = array();
		$descriptionNumArr = array();
		
		if($cadName != "" && $version != "")
		{
			$condArr = array($cadName, $version);
		
			// Description, input type
			$stmt = $pdo->prepare("SELECT * FROM cad_master WHERE cad_name=? AND version=?");
			$stmt->execute($condArr);	
	
			$result = $stmt->fetch(PDO::FETCH_ASSOC);

			$inputType   = $result['input_type'];
			$resultType  = $result['result_type'];
			$description = $result['description'];
		
			// Series info
			$sqlStr = "SELECT DISTINCT series_id, modality FROM cad_series"
					. " WHERE cad_name=? AND version=?"
					. " ORDER BY series_id ASC;";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($condArr);

			$cnt = 0;

			while($result = $stmt->fetch(PDO::FETCH_NUM))
			{
				$seriesID = $result[0];
				$modality = $result[1];

				$sqlStr = "SELECT series_description, min_slice, max_slice FROM cad_series"
						. " WHERE cad_name=? AND version=?"
						. " AND series_id=? ORDER BY series_description DESC;";

				$stmtDescription = $pdo->prepare($sqlStr);
				$stmtDescription->execute(array($cadName, $version, $seriesID));
				

				while($resultDescription = $stmtDescription->fetch(PDO::FETCH_NUM))
				{
					if(!($resultDescription[0] == '(default)'
					      && $resultDescription[1] == 0 && $resultDescription[2] == 0))
					{		
						array_push($seriesList, array( 'seriesID'    => $seriesID,
						                               'modality'    => $modality,
				                                       'description' => $resultDescription[0],
													   'minSlice'    => $resultDescription[1],
													   'maxSlice'    => $resultDescription[2]));
						$cnt++;
					}
				}
				array_push($descriptionNumArr, $cnt);
			}
			$seriesNum = count($descriptionNumArr);
			
			// Executed cases
			$stmt = $pdo->prepare("SELECT COUNT(*), MIN(executed_at) FROM executed_plugin_list WHERE plugin_name=? AND version=?");
			$stmt->execute($condArr);
			$result = $stmt->fetch(PDO::FETCH_NUM);
			$caseNum = $result[0];
			$oldestDate = substr($result[1], 0, 10);
	
			// Evaluation
			$evalNumConsensual = $tpNumConsensual = $fnNumConsensual = 0;
			$missedTPNum = $knownTPNum = $fnNumPersonal = 0;
		
			if($caseNum > 0 && $resultType == 1)
			{
				// Consensual based
				$sqlStr = "SELECT COUNT(*)"
					    . " FROM executed_plugin_list el, lesion_feedback lf"
					    . " WHERE el.plugin_name=? AND el.version=?"
					    . " AND lf.exec_id=el.exec_id"
					    . " AND lf.consensual_flg ='t'"
					    . " AND lf.interrupt_flg ='f'";
				
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($condArr);
				
				$evalNumConsensual = $stmt->fetchColumn();
				if($evalNumConsensual == "")  $evalNumConsensual = 0;
			
				$sqlStr = "SELECT COUNT(*)"
				        . " FROM executed_plugin_list el, lesion_feedback lf"
						. " WHERE el.plugin_name=? AND el.version=?"
						. " AND lf.exec_id=el.exec_id"
						. " AND lf.consensual_flg ='t'"
						. " AND lf.evaluation>=1"
						. " AND lf.interrupt_flg ='f'";

				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($condArr);

				$tpNumConsensual = $stmt->fetchColumn();
				if($tpNumConsensual == "")  $tpNumConsensual = 0;
		
				$sqlStr = "SELECT SUM(fn.false_negative_num)"
						. " FROM executed_plugin_list el, false_negative_count fn"
						. " WHERE el.plugin_name=? AND el.version=?"
						. " AND fn.exec_id=el.exec_id"
						. " AND fn.consensual_flg ='t'"
						. " AND fn.status>=1";			
		
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($condArr);

				$fnNumConsensual = $stmt->fetchColumn();
				if($fnNumConsensual == "")  $fnNumConsensual = 0;
		
				array_push($condArr, $userID);
		
				// Personal based
				$sqlStr = "SELECT lf.evaluation, COUNT(*) FROM executed_plugin_list el, lesion_feedback lf"
						. " WHERE el.plugin_name=? AND el.version=?"
						. " AND lf.exec_id=el.exec_id"
						. " AND lf.entered_by=?"
						.  " AND lf.consensual_flg ='f' AND lf.interrupt_flg='f'"
						.  " GROUP BY lf.evaluation;";
					
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($condArr);
				
				while($result = $stmt->fetch(PDO::FETCH_NUM))
				{
					if($result[0] == 1)       $knownTPNum  += $result[1];
					else if($result[0] == 2)  $missedTPNum += $result[1];
				}
		
				$sqlStr = "SELECT SUM(fn.false_negative_num)"
						. " FROM executed_plugin_list el, false_negative_count fn"
						. " WHERE el.plugin_name=? AND el.version=?"
						. " AND fn.exec_id=el.exec_id"
						. " AND fn.entered_by=?"
						. " AND fn.consensual_flg ='f'"
						. " AND fn.status>=1";
						
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($condArr);

				$fnNumPersonal = $stmt->fetchColumn();
				if($fnNumConsensual == "")  $fnNumPersonal = 0;
			}

			//---------------------------------------------------------------------------------------------------
			// Show plug-in information
			//---------------------------------------------------------------------------------------------------	
			echo '<div style="font-size:7px;">&nbsp;</div>';
			echo '<div style="font-size:16px; margin:5px;">';
			echo '<b>Plug-in name: </b>' . $cadName . '<br>';	
			echo '<b>Version: </b>' . $version . '<br>';
			echo '<b>Description: </b>' . $description . '<br>';
		
			echo '<b>No. of executed cases: </b>' . $caseNum;
			if($caseNum > 0)  echo ' (since ' . $oldestDate . ')';
			echo '</div>';

			echo '<div style="font-size:7px;">&nbsp;</div>';
			echo '<div style="font-size:16px; margin-left:5px;"><b>Required DICOM series</b></div>';
			
			echo '<div style="font-size:14px; margin-left:15px;">';
			echo '<table border="1">';
			echo '<tr>';
			echo '<th>Series</th><th>Modality</th><th>Condition</th>';
			echo '</tr>';

			$cnt = 0;
			
			for($j=0; $j<$seriesNum; $j++)
			{
				for($i=$cnt; $i<$cnt + $descriptionNumArr[$j]; $i++)
				{
					echo '<tr>';
					
					if($i==$cnt)
					{
						echo '<td';
						if($descriptionNumArr[$j]>1) echo ' rowspan=' . $descriptionNumArr[$j];
						echo ' align=center>' . ($j+1) . '</td>';
						echo '<td';
						if($descriptionNumArr[$j]>1) echo ' rowspan=' . $descriptionNumArr[$j];
						echo ' align=center>' . $seriesList[$i]['modality'] . '</td>';
					}
			
					if($seriesList[$i]['description'] == '(default)')
					{
						echo '<td>#image: ' .  $seriesList[$i]['minSlice'] . '-' . $seriesList[$i]['maxSlice'] . '</td>';
					}
					else
					{
						echo '<td>series description: ' . $seriesList[$i]['description'] . '</td>';
					}
					echo '</tr>';
				}
				$cnt += $descriptionNumArr[$j];
			}
			
			echo '</table>';
			echo '</div>';
		
			if($caseNum > 0 && $resultType == 1)
			{
				echo '<div style="font-size:7px;">&nbsp;</div>';
			
				if($evalNumConsensual > 0)
				{
					echo '<div style="font-size:16px; margin-left:5px;"><b>Evaluation</b> (Consensual feedback)</div>';
					echo '<div style="font-size:15px; margin-left:15px; margin-bottom:5px;">';
					echo '<b>No. of TP: </b>' . $tpNumConsensual . '<br>';
					echo '<b>No. of FN: </b>' . $fnNumConsensual . '<br>';
					echo '</div>';
				}
			
				if($missedTPNum > 0 || $knownTPNum > 0 || $fnNumPersonal > 0)
				{
					echo '<div style="font-size:16px; margin:5px;"><b>Evaluation</b> (by ' . $userID . ')</div>';
					echo '<div style="font-size:15px; margin-left:15px; margin-bottom:5px;">';
					echo '<b>No. of known TP: </b>' . $knownTPNum . '<br>';
					echo '<b>No. of missed TP: </b>' . $missedTPNum . '<br>';
					echo '<b>No. of FN: </b>' . $fnNumPersonal . '<br>';
					echo '</div>';
				}
			}
			//---------------------------------------------------------------------------------------------------
		}
	}
	catch (PDOException $e)
	{
    	var_dump($e->getMessage());
	}
	$pdo = null;
?>

</body>
</html>