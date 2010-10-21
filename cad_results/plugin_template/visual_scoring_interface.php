<?

	$consensualFlg = ($params['feedbackMode'] == "consensual") ? 1 : 0;
		
	if($params['feedbackMode'] == "personal" || $params['feedbackMode'] == "consensual")
	{
		$sqlParam = array();
		
		$sqlStr = "SELECT score FROM visual_assessment WHERE exec_id=?";
		$sqlParam[0] = $params['execID'];
		
		
		if($params['feedbackMode'] == "personal")
		{
			$sqlStr .= " AND consensual_flg='f' AND entered_by=?";
			$sqlParam[1] = $userID;
		}
		else
		{
			$sqlStr .= " AND consensual_flg='t'";
		}
			
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($sqlParam);

		if($stmt->rowCount() == 1)
		{
			$evalVal = $stmt->fetchColumn();
		}
	}
			
	$totalNum = 0;
	$checkFlg = 0;
			
	if($params['feedbackMode'] == "consensual")
	{
		$sqlStr  = "SELECT COUNT(DISTINCT entered_by) FROM visual_assessment"
                 . " WHERE exec_id=? AND consensual_flg='f'";
				 
		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindParam(1, $params['execID']);
		$stmt->execute();		 
		$totalNum = $stmt->fetchColumn();
	}

	$scoreStr = array("Bad", "Unsatisfactory", "Fair", "Good", "Excellent");
	$enterNumArr = array_fill(0,5,0);
	$maxScoreCnt = 0;
	$maxScoreVal = 3;

	if($params['feedbackMode'] == "consensual")
	{
		$sqlStr = "SELECT score, count(*) FROM visual_assessment WHERE exec_id=?"
	            . " AND consensual_flg='f' AND interrupt_flg='f' GROUP BY score ORDER BY score ASC;";

		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindParam(1, $params['execID']);
		$stmt->execute();		 
		
		$numRows = $stmt->rowCount();

		for($i=0; $i<$numRows; $i++)
		{
			$result = $stmt->fetch(PDO::FETCH_NUM);
			$tmp = $result[0];
			$enterNumArr[$tmp-1] = $result[1];

			if($enterNumArr[$tmp-1] >= $maxScoreCnt)
			{
				$maxScoreCnt = $enterNumArr[$tmp-1];
				$maxScoreVal = $tmp;
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
			
			$sqlStr = "SELECT entered_by FROM visual_assessment WHERE exec_id=?"
					. " AND consensual_flg='f' AND interrupt_flg='f' AND score=?";

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($params['execID'], $j));
			
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

		if($registTime != "")
		{
			if($evalVal == $j)	$scoringHtml .= ' checked="checked"';
			else				$scoringHtml .= ' disabled="disabled"';
		}
		else if(($params['feedbackMode'] == "personal" && $j == 3 && $checkFlg == 0)
			   || ($params['feedbackMode'] == "consensual" && $j == $maxScoreVal))
		{
			$scoringHtml .= ' checked="checked"';
		}
		
		if($params['feedbackMode'] == "consensual" && $titleStr != "")	$scoringHtml .= ' title="' . $titleStr . '" />';
		else															$scoringHtml .= ' />';
	}
?>