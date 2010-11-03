<?php

	$registTime = date('Y-m-d H:i:s');
	$posArr = explode('^', $posStr);
	$consensualFlg = ($params['feedbackMode'] == "consensual") ? 't' : 'f';	

	try
	{
		if($params['errorMessage'] == "")
		{
			// Connect to SQL Server
			$pdo = new PDO($connStrPDO);
			
			$sqlStr = "INSERT INTO false_negative_location (exec_id, entered_by, consensual_flg,"
			        . " location_x, location_y, location_z, nearest_lesion_id, interrupt_flg, registered_at)"
			        . " VALUES (?, ?, ?, ?, ?, ?, ?, 't', ?)";
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindValue(1, $params['execID']);
			$stmt->bindValue(2, $userID);
			$stmt->bindValue(3, ($params['feedbackMode'] == "consensual") ? 't' : 'f');
			
			for($j=0; $j<$enteredFnNum; $j++)
			{
				$tmpStr = explode(' ', $posArr[$j * $DEFAULT_COL_NUM + 3]);	
				if(strcmp($tmpStr[0],'-')==0) $tmpStr[0] = 0;

				$stmt->bindValue(4, $posArr[$j * $DEFAULT_COL_NUM]);
				$stmt->bindValue(5, $posArr[$j * $DEFAULT_COL_NUM + 1]);
				$stmt->bindValue(6, $posArr[$j * $DEFAULT_COL_NUM + 2]);
				$stmt->bindValue(7, $tmpStr[0]);
				$stmt->bindValue(8, $registTime);			

				$stmt->execute();
			
				if($sqlStr != "")
				{
					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute($sqlParam);
			
					if($stmt->rowCount() != 1)
					{
						$err = $stmt->errorInfo();
						$registMsg = '<span style="color:#f00;">' . $err[2] . '</span>';
						$registTime = "";

						$sqlStr = "DELETE FROM false_negative_location WHERE exec_id=?"
								. " AND interrupt_flg='t' AND registered_at!=?"
								. " AND entered_by=?";
						$stmtDel = $pdo->prepare($sqlStr);
						$stmtDel->execute(array($params['execID'], $registTime, $userID));		
						break;
					}
				}
			} // end for : j
		
			if($registMsg == "")
			{
				$sqlStr = "SELECT COUNT(*) FROM false_negative_count WHERE exec_id=?"
		                . " AND consensual_flg=? AND entered_by=?";
						
				$sqlParams = array();

				if(PdoQueryOne($pdo, $sqlStr, array($params['execID'], $consensualFlg, $userID), 'SCALAR') == 1)
				{
					$sqlStr = "UPDATE false_negative_count SET false_negative_num=?, status=1, registered_at=?"
					        . " WHERE exec_id=? AND consensual_flg=? AND entered_by=?";
					$sqlParams[] = $enteredFnNum;
					$sqlParams[] = $registTime;
					$sqlParams[] = $params['execID'];
					$sqlParams[] = $consensualFlg;
					$sqlParams[] = $userID;
				}
				else
				{
					$sqlStr = "INSERT INTO false_negative_count "
					        . "(exec_id, entered_by, consensual_flg, false_negative_num, status, registered_at)"
					        . " VALUES (?, ?, ?, ?, 1, ?);";
					$sqlParams[] = $params['execID'];
					$sqlParams[] = $userID;
					$sqlParams[] = $consensualFlg;
					$sqlParams[] = $enteredFnNum;
					$sqlParams[] = $registTime;
				}
	
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($sqlParams);		
		
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
			}

			
		}
		// Jsono—Í
	
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;	


?>