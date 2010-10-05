<?php

	$registeredAt = date('Y-m-d H:i:s');
	$consensualFlg = ($param['feedbackMode'] == "consensual") ? 't' : 'f';

	$posArr = explode('^', $posStr);

	if($interruptFNFlg == 1)
	{
		for($j=0; $j<$rowNum; $j++)
		{
			$tmpStr = explode(' ', $posArr[$j * $DEFAULT_COL_NUM + 3]);	
		
			if(strcmp($tmpStr[0],'BT')==0)
			{
				$posArr[$j * $DEFAULT_COL_NUM + 3] = CheckNearestLesionId($posArr[$j * $DEFAULT_COL_NUM],
				                                                          $posArr[$j * $DEFAULT_COL_NUM + 1],
									                                      $posArr[$j * $DEFAULT_COL_NUM + 2],
									                                      $candStr,
									                                      $DIST_THRESHOLD);
			}
		}	
	}
	else if($registFNFlg == 1)
	{	
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
			
			$sqlStr = "INSERT INTO false_negative_location (exec_id, entered_by, consensual_flg,"
					. " location_x, location_y, location_z, nearest_lesion_id, interrupt_flg, registered_at)"
					. " VALUES (?, ?, 't', ?, ?, ?, ?, 'f', ?)";
	
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute(array($param['execID'], $userID, $posArr[$j * $DEFAULT_COL_NUM], $posArr[$j * $DEFAULT_COL_NUM + 1],
			                     $posArr[$j * $DEFAULT_COL_NUM + 2], $tmpStr[0], $registeredAt));
	
			if($stmt->rowCount() != 1)
			{
				$err = $stmt->errorInfo();
				$registMsg = '<span style="color:#f00;">' . $err[2] . '</span>';
				break;
			}
			
			//---------------------------------------------------------------------------------------------------
			// Update integrate_location_id
			//---------------------------------------------------------------------------------------------------
			$sqlStr = "SELECT location_id FROM false_negative_location WHERE exec_id=? AND consensual_flg='t'"
				    . " AND location_x=? AND location_y=? AND location_z=? AND registered_at=?";

			$sqlParam = array($param['execID'], $posArr[$j * $DEFAULT_COL_NUM], $posArr[$j * $DEFAULT_COL_NUM + 1],
			                  $posArr[$j * $DEFAULT_COL_NUM + 2], $registeredAt);

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParam);
			$dstID = $stmt->fetchColumn();
			
			$srcID = 0;
			
			$sqlStr = "SELECT location_id FROM false_negative_location WHERE exec_id=?"
					. " AND consensual_flg='f' AND interrupt_flg='f'"
				    . " AND location_x=? AND location_y=? AND location_z=?";
					
			array_pop($sqlParam);
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParam);
	
			if($stmt->rowCount() == 1)
			{
				$srcID = $stmt->fetchColumn();
				
				$stmt = $pdo->prepare("UPDATE false_negative_location SET integrate_location_id=? WHERE location_id=?");
				$stmt->execute(array($dstID, $srcID));
				
				if($stmt->rowCount() != 1)
				{
					$err = $stmt->errorInfo();
					$registMsg = '<span style="color:#f00;">' . $err[2] . '</span>';
					break;
				}
			}
	
			if($posArr[$j * $DEFAULT_COL_NUM + 5] != "")
			{
				$idArr = explode(',', $posArr[$j * $DEFAULT_COL_NUM + 5]);
				$idNum = count($idArr);

				$stmt = $pdo->prepare("UPDATE false_negative_location SET integrate_location_id=? WHERE location_id=?");
				$stmt->bindParam(1, $dstID);
	
				foreach($idArr as $value)
				{
					$stmt->bindParam(2, $value);
					$stmt->execute();
	
					if($stmt->rowCount() != 1)
					{
						$err = $stmt->errorInfo();
						$registMsg = '<span style="color:#f00;">' . $err[2] . '</span>';
						break;
					}
				}
				if($registMsg != "")	break;
			}
			//---------------------------------------------------------------------------------------------------
			
		} // end for : j
			
		if($registMsg == "")
		{
			$stmt = $pdo->prepare("SELECT COUNT(*) FROM false_negative_count WHERE exec_id=? AND consensual_flg='t'");
			$stmt->bindParam(1, $param['execID']);
			$stmt->execute();
			
			$sqlStr = "";
			$sqlParam = array( 'execID'       => $param['execID'],
			                   'userID'       => $userID,
							   'rowNum'       => $enteredFnNum,
							   'registeredAt' => $registeredAt);
		
			if($stmt->fetchColumn() == 1)
			{
				$sqlStr = "UPDATE false_negative_count SET false_negative_num=:rowNum, status=2,"
				        . " entered_by=:userID, registered_at=:registeredAt"
				        . " WHERE exec_id=:execID AND consensual_flg='t'";
			}
			else
			{
				$sqlStr = "INSERT INTO false_negative_count"
				   	    . " (exec_id, entered_by, consensual_flg, false_negative_num, status, registered_at)"
						. " VALUES (:execID, :userID, 't', :rowNum, 2, :registeredAt)";
			}
			
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParam);
	
			if($stmt->rowCount() != 1)
			{
				$err = $stmt->errorInfo();
				$registMsg = '<span style="color:#f00;">' . $err[2] . '</span>';
			}	
				
			if($registMsg == "")
			{
				$registMsg = '<span style="color:#00f;">Successfully registered in CAD feedback database.</span>';
				$registTime = $registeredAt;
				$consRegistSucessFlg = 1;
				$userStr = $userID . "^0";
			}
		}		
	}
	
	$registFNFlg = 0;
	$registeredAt = "";
	$interruptFNFlg = 0;

?>