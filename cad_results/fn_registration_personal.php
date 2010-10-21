<?php

	$registTime = date('Y-m-d H:i:s');
	$posArr = explode('^', $posStr);
	
	for($j=0; $j<$enteredFnNum; $j++)
	{
		$tmpStr = explode(' ', $posArr[$j * $DEFAULT_COL_NUM + 3]);	
		
		if(strcmp($tmpStr[0],'BT')==0)
		{
			$posArr[$j * $DEFAULT_COL_NUM + 3] = CheckNearestLesionId($posArr[$j * $DEFAULT_COL_NUM],
			                                                          $posArr[$j * $DEFAULT_COL_NUM + 1],
								                                      $posArr[$j * $DEFAULT_COL_NUM + 2],
								                                      $candStr,
								                                      $DIST_THRESHOLD);

			$tmpStr = explode(' ', $posArr[$j * $DEFAULT_COL_NUM + 3]);	
		}

		if(strcmp($tmpStr[0],'-')==0) $tmpStr[0] = 0;
		
		$sqlStr = "SELECT * FROM false_negative_location WHERE exec_id=? AND consensual_flg='f'"
				. " AND location_x=? AND location_y=? AND location_z=? AND entered_by=?";
				
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute(array($params['execID'], $posArr[$j * $DEFAULT_COL_NUM], $posArr[$j * $DEFAULT_COL_NUM+1],
		                     $posArr[$j * $DEFAULT_COL_NUM + 2], $userID));
							 
		$rowNum = $stmt->rowCount();
		$result = $stmt->fetch(PDO::FETCH_NUM);
		
		$sqlStr = "";
		$sqlParam = array();
		
		if($rowNum == 1 && $result['interrupt_flg'] == 't')
		{
			$sqlStr = "UPDATE false_negative_location SET registered_at=?";
			if($interruptFNFlg == 0) $sqlStr .= ", interrupt_flg='f'";
			
			$sqlStr .= " WHERE exec_id=? AND consensual_flg='f' AND entered_by=?"
			        .  " AND location_x=? AND location_y=? AND location_z=?";
			$sqlParam[0] = $registTime;
			$sqlParam[1] = $params['execID'];
			$sqlParam[2] = $userID;
			$sqlParam[3] = $posArr[$j * $DEFAULT_COL_NUM];
			$sqlParam[4] = $posArr[$j * $DEFAULT_COL_NUM + 1];
			$sqlParam[5] = $posArr[$j * $DEFAULT_COL_NUM + 2];
		}		
		else
		{
			$sqlStr = "INSERT INTO false_negative_location (exec_id, entered_by, consensual_flg,"
			        . " location_x, location_y, location_z, nearest_lesion_id, interrupt_flg, registered_at)"
			        . " VALUES (?, ?, 'f', ?, ?, ?, ?, ?, ?)";

			$sqlParam[0] = $params['execID'];
			$sqlParam[1] = $userID;
			$sqlParam[2] = $posArr[$j * $DEFAULT_COL_NUM];
			$sqlParam[3] = $posArr[$j * $DEFAULT_COL_NUM + 1];
			$sqlParam[4] = $posArr[$j * $DEFAULT_COL_NUM + 2];
			$sqlParam[5] = $tmpStr[0];
			$sqlParam[6] = ($interruptFNFlg == 1) ? 't' : 'f';
			$sqlParam[7] = $registTime;
		}
		
		
		if($sqlStr != "")
		{
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParam);
			
			if($stmt->rowCount() != 1)
			{
				$err = $stmt->errorInfo();
				$registMsg = '<span style="color:#f00;">' . $err[2] . '</span>';
				$registTime = "";
				break;
			}
		}
	} // end for : j
	
	//if($registMsg == "")
	//{
	//	$sqlStr = "DELETE FROM false_negative_location WHERE exec_id=?"
	//	        . " AND interrupt_flg='t' AND registered_at!=?"
	//	        . " AND entered_by=?";
	//	
	//	$stmt = $pdo->prepare($sqlStr);
	//	$stmt->execute(array($params['execID'], $registTime, $userID));		
	//	
	//	echo $stmt->rowCount();
	//	
	//	if($stmt->rowCount() != 1)
	//	{
	//		$err = $stmt->errorInfo();
	//		$registMsg = '<span style="color:#f00;">' . $err[2] . '</span>';
	//	}		
	//}

	if($registMsg == "")
	{
		$sqlStr = "SELECT COUNT(*) FROM false_negative_count WHERE exec_id=?"
                . " AND consensual_flg='f' AND entered_by=?";

		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute(array($params['execID'], $userID));			
		
		$status = ($interruptFNFlg == 0) ? 2 : 0;
		
		$sqlStr = "";
		$sqlParam = array();

		if($stmt->fetchColumn() == 1)
		{
			$sqlStr = "UPDATE false_negative_count SET false_negative_num=?, status=?"
			        . " WHERE exec_id=? AND consensual_flg='f' AND entered_by=?";
			$sqlParam[0] = $enteredFnNum;
			$sqlParam[1] = $status;
			$sqlParam[2] = $params['execID'];
			$sqlParam[3] = $userID;
		}
		else
		{
			$sqlStr = "INSERT INTO false_negative_count "
			        . "(exec_id, entered_by, consensual_flg, false_negative_num, status, registered_at)"
			        . " VALUES (?, ?, 'f', ?, ?, ?);";
			$sqlParam[0] = $params['execID'];
			$sqlParam[1] = $userID;
			$sqlParam[2] = $enteredFnNum;
			$sqlParam[3] = $status;
			$sqlParam[4] = $registTime;
		}
	
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($sqlParam);		
		
		if($stmt->rowCount() != 1)
		{
			$err = $stmt->errorInfo();
			$registMsg = '<span style="color:#f00;">' . $err[2] . '</span>';
			$registTime = "";
		}	
	}
	
	if($registMsg == "" && $interruptFNFlg == 0)
	{
		$registMsg = '<span style="color:#00f;">Successfully registered in feedback database.</span>';
		$userStr = $userID . "^0";
		
		// 病変候補分類が完了しているかを判定
		$sqlStr = "SELECT COUNT(*) FROM lesion_feedback WHERE exec_id=? AND consensual_flg='f' AND interrupt_flg='f'";
		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindValue(1, $params['execID']);
		$stmt->execute();		
		
		if($stmt->fetchColumn() <= 0)  $moveCadResultFlg = 1;
		
	}
	
	$registFNFlg = 0;
	$interruptFNFlg = 0;
	
	//echo $registMsg;

?>