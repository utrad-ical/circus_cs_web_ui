<?php

	$consensualFlg = ($params['feedbackMode'] == "consensual") ? 1 : 0;
	
	if($params['feedbackMode'] == "personal" || $params['feedbackMode'] == "consensual")
	{
		$sqlParams = array();
		
		$sqlStr = "SELECT fa.value FROM feedback_list fl, feedback_attributes fa"
				. " WHERE fl.job_id=? AND fa.fb_id=fl.fb_id"
				. " AND fa.key='total'";
		$sqlParams[] = $params['jobID'];
		
		
		if($params['feedbackMode'] == "personal")
		{
			$sqlStr .= " AND fl.is_consensual='f' AND fl.entered_by=?";
			$sqlParams[] = $userID;
		}
		else
		{
			$sqlStr .= " AND fl.is_consensual='t'";
		}
			
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($sqlParams);

		if($stmt->rowCount() == 1)
		{
			$evalVal = $stmt->fetchColumn();
		}
	}
			
	$totalNum = 0;
	$checkFlg = 0;
			
	if($params['feedbackMode'] == "consensual")
	{
		$sqlStr  = "SELECT COUNT(DISTINCT entered_by) FROM feedback_list"
                 . " WHERE job_id=? AND is_consensual='f'";
				 
		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindParam(1, $params['jobID']);
		$stmt->execute();		 
		$totalNum = $stmt->fetchColumn();
	}

	$scoreStr = array("Bad", "Unsatisfactory", "Fair", "Good", "Excellent");
	$enterNumArr = array_fill(0,5,0);
	$maxScoreCnt = 0;
	$maxScoreVal = 3;

	if($params['feedbackMode'] == "consensual")
	{
		$sqlStr = "SELECT fa.value FROM feedback_list fl, feedback_attributes fa"
				. " WHERE fl.job_id=? AND fa.fb_id=fl.fb_id"
	            . " AND fl.is_consensual='f' AND fl.status=1 AND fa.key='total'";
		$result = DBConnector::query($sqlStr, $params['jobID'], 'ARRAY_NUM');

		foreach($result as $item)  $enterNumArr[$item[0]-1]++;

		for($i=0; $i<5; $i++)
		{
			if($enterNumArr[$i] >= $maxScoreCnt)
			{
				$maxScoreCnt = $enterNumArr[$i];
				$maxScoreVal = $i+1;
			}
		}
	}

	$scoringHtml = '';

	for($j=5; $j>=1; $j--)
	{
		$evalStr = "";
		$titleStr = "";
		$enterNum = 0;
		
		if($params['feedbackMode'] == "consensual" && $enterNumArr[$j-1] > 0)
		{
			$evalStr = " " . $enterNumArr[$j-1];
			
			$sqlStr = "SELECT fl.entered_by FROM feedback_list fl, feedback_attributes fa"
					. " WHERE fl.job_id=? AND fa.fb_id=fl.fb_id"
					. " AND fl.is_consensual='f' AND fl.status=1"
					. " AND fa.key='total' AND fa.value=?";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($params['jobID'], strval($j)));
			
			$enterNum = $stmt->rowCount();
			
			for($i=0; $i<$enterNum; $i++)
			{
				$titleStr .= $stmt->fetchColumn();
				if($i < $enterNum-1) $titleStr .= ", ";
			}
		}

		$scoringHtml .= '<input type="radio" name="visualScore" value="' . $j . '"'
		             .  ' label="' . $scoreStr[$j-1] . $evalStr . '"'
		             .  ' class="radio-to-button"  onclick="DispRegistCaution();"';

		if($params['registTime'] != "")
		{
			if($evalVal == $j)	$scoringHtml .= ' checked="checked"';
			else				$scoringHtml .= ' disabled="disabled"';
		}
		else if(($params['feedbackMode'] == "personal" && $j == 3 && $checkFlg == 0)
			   || ($params['feedbackMode'] == "consensual" && $j == $maxScoreVal))
		{
			$scoringHtml .= ' checked="checked"';
		}
		
		if($params['feedbackMode'] == "consensual" && $titleStr != "")
		{
			$scoringHtml .= ' title="' . $titleStr . '"';
		}
		$scoringHtml .= ' />';
	}
?>
