<?php

	session_cache_limiter('none');
	session_start();

	include("../common.php");
	
	//------------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//------------------------------------------------------------------------------------------------------------------
	$execID       = (isset($_REQUEST['execID'])) ? $_REQUEST['execID'] : "";
	$cadName      = (isset($_REQUEST['cadName']))  ? $_REQUEST['cadName']  : "";
	$version      = (isset($_REQUEST['version'])) ? $_REQUEST['version'] : "";
	$interruptFlg = (isset($_REQUEST['interruptFlg'])) ? $_REQUEST['interruptFlg'] : 0;
	$feedbackMode = (isset($_REQUEST['feedbackMode'])) ? $_REQUEST['feedbackMode'] : "";
	$candStr      = (isset($_REQUEST['candStr'])) ? $_REQUEST['candStr'] : "";
	$evalStr      = (isset($_REQUEST['evalStr'])) ? stripslashes($_REQUEST['evalStr']) : "";
	$fnNum        = (isset($_REQUEST['fnNum'])) ? stripslashes($_REQUEST['fnNum']) : "";
				   
	$userID = $_SESSION['userID'];
	//------------------------------------------------------------------------------------------------------------------

	$dstData = array('message' => "");

	try
	{
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);	
	
		$registeredAt = date('Y-m-d H:i:s');
		$consensualFlg = ($feedbackMode == "consensual") ? 't' : 'f';
	
		$stmt = $pdo->prepare("SELECT result_type, score_table FROM cad_master WHERE cad_name=? AND version=?");
		$stmt->execute(array($cadName, $version));

		$result = $stmt->fetch(PDO::FETCH_NUM);
		$resultType     = $result[0];
		$scoreTableName = $result[1];
		
		if($resultType == 1)
		{
			//------------------------------------------------------------------------------------------------
			// Personal mode ‚Å’N‚©‚ªFN“ü—Í‚µ‚Ä‚¢‚éê‡AConsensual mode‚ÌFN input“ü—Í‚ªÏ‚Þ‚Ü‚Å‚Í
			// ˆêŽž“o˜^‚É‚·‚é
			//------------------------------------------------------------------------------------------------
			if($feedbackMode == "consensual")
			{
				$sqlStr = "SELECT COUNT(*) FROM false_negative_location"
						. " WHERE exec_id=? AND consensual_flg='f' AND interrupt_flg='f'";
				$stmtFn = $pdo->prepare($sqlStr);
				$stmtFn->bindValue(1, $execID);
				$stmtFn->execute();

				$fnPersonalCnt = $stmtFn->fetchColumn();
						
				$sqlStr = "SELECT COUNT(*) FROM false_negative_count"
						. " WHERE exec_id=? AND consensual_flg='t' AND status=2";
				$stmtFn = $pdo->prepare($sqlStr);
				$stmtFn->bindValue(1, $execID);
				$stmtFn->execute();

				$fnConsCnt = $stmtFn->fetchColumn();
				
				//echo $fnPersonalCnt . ' ' . $fnConsCnt;
			
				if($fnPersonalCnt > 0 && $fnConsCnt != 1)  $interruptFlg = 1;
			}
			//------------------------------------------------------------------------------------------------

			$candArr = explode("^", $candStr);
			$evalArr   = explode('^', $evalStr);
			
			$candNum = count($candArr);
			
			//------------------------------------------------------------------------------------------------
			// Registration to lesion_feedback table
			//------------------------------------------------------------------------------------------------
			for($i=0; $i<($candNum-1); $i++)
			{
				$sqlStr = "SELECT interrupt_flg FROM lesion_feedback"
						. " WHERE exec_id=? AND lesion_id=? AND consensual_flg=?";
					
				if($feedbackMode == "personal") $sqlStr .= " AND entered_by=?";
			
				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindParam(1, $execID);
				$stmt->bindParam(2, $candArr[$i]);
				$stmt->bindParam(3, $consensualFlg);
				if($feedbackMode == "personal")   $stmt->bindParam(4, $userID);
				$stmt->execute();
			
				$rowNum = $stmt->rowCount();
				
				$sqlStr = "";
				$sqlParam = array();
			
				if($rowNum == 0)
				{
					$sqlStr = "INSERT INTO lesion_feedback (exec_id, lesion_id, entered_by, consensual_flg, "
					        . "evaluation, interrupt_flg, registered_at) VALUES (?, ?, ?, ?, ?, ?, ?);";
						
					$sqlParam[0] = $execID;        $sqlParam[1] = $candArr[$i];  $sqlParam[2] = $userID;
					$sqlParam[3] = $consensualFlg; $sqlParam[4] = $evalArr[$i];
					$sqlParam[5] = ($interruptFlg == 1) ? "t" : "f";
					$sqlParam[6] = $registeredAt;						
				}
				else if($rowNum == 1 && $stmt->fetchColumn())
				{
					$sqlStr = "UPDATE lesion_feedback SET evaluation=?, registered_at=?";
							
					$sqlParam[0] = $evalArr[$i]; $sqlParam[1] = $registeredAt;
						
					if($interruptFlg == 0)  $sqlStr .= ", interrupt_flg='f'";
					if($feedbackMode == "consensual")
					{
						$sqlStr .= ", entered_by=?";
						array_push($sqlParam, $userID);
					}
				
					$sqlStr .= " WHERE exec_id=? AND lesion_id=? AND consensual_flg=?";
					array_push($sqlParam, $execID);
					array_push($sqlParam, $candArr[$i]);
					array_push($sqlParam, $consensualFlg);
				
					if($feedbackMode == "personal")
					{
						$sqlStr .= " AND entered_by=?";
						array_push($sqlParam, $userID);
					}
				}
			
				//echo $sqlStr;
			
				if($sqlStr != "")
				{			
					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute($sqlParam);
				
					if($stmt->rowCount() != 1)
					{
						$err = $stmt->errorInfo();
						$dstData['message'] .= $err[2];
						break;
					}
				}
			}
			//----------------------------------------------------------------------------------------------------
	
			//----------------------------------------------------------------------------------------------------
			// Registration to false_negative_count table
			//----------------------------------------------------------------------------------------------------
			if($registMsg == "")
			{
				$sqlStr = "SELECT * FROM false_negative_count WHERE exec_id=? AND consensual_flg=?";
					
				if($feedbackMode == "personal") $sqlStr .= " AND entered_by=?";
		
				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindValue(1, $execID);
				$stmt->bindValue(2, $consensualFlg, PDO::PARAM_BOOL);
				if($feedbackMode == "personal")   $stmt->bindValue(3, $userID);		
		
				$stmt->execute();
				$rowNum = $stmt->rowCount();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
						
				$fnCntStatus = ($interruptFlg == 1) ? 0 : 1;
				
				$sqlStr = "";
				$sqlParam = array();
				
				if($rowNum == 0)
				{		
					$sqlStr = "INSERT INTO false_negative_count "
					        . "(exec_id, entered_by, consensual_flg, false_negative_num, status, registered_at)"
					        . " VALUES (?, ?, ?, ?, ?, ?);";
					$sqlParam[0] = $execID;  $sqlParam[1] = $userID;       $sqlParam[2] = $consensualFlg;
					$sqlParam[3] = $fnNum;   $sqlParam[4] = $fnCntStatus;  $sqlParam[5] = $registeredAt;
				}
				else if($rowNum == 1 && $result['status'] == 0)
				{
					$sqlStr = "UPDATE false_negative_count SET false_negative_num=?, status=?";
					$sqlParam[0] = $fnNum;  $sqlParam[1] = $fnCntStatus;		
					
					if($feedbackMode == "consensual")
					{
						$sqlStr .= ", entered_by=?";
						array_push($sqlParam, $userID);
					}
				
					$sqlStr .= " WHERE exec_id=? AND consensual_flg=?";
					array_push($sqlParam, $execID);
					array_push($sqlParam, $consensualFlg);
				
					if($feedbackMode == "personal")
					{
						$sqlStr .= " AND entered_by=?";
						array_push($sqlParam, $userID);
					}
				}

				if($sqlStr != "")
				{			
					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute($sqlParam);

					if($stmt->rowCount() != 1)
					{
						$tmp = $stmt->errorInfo();
						$dstData['message'] .= $tmp[2];
					}
				}
			}
			//----------------------------------------------------------------------------------------------------------

			if($dstData['message'] == "" && $interruptFlg == 0)
			{
				$dstData['message'] .= 'Successfully registered in feedback database.';
				$consensualFeedBackFlg = 1;
			}
		}
		else if($resultType == 2)
		{
			$scoreTableName = ($scoreTableName !== "") ? $scoreTableName : "visual_assessment";
		
			$sqlStr = "SELECT interrupt_flg FROM \"" . $scoreTableName . "\" WHERE exec_id=?"
					. " AND consensual_flg=?";
						
			if($feedbackMode == "personal") $sqlStr .= " AND entered_by=?";
			
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindValue(1, $execID);
			$stmt->bindValue(2, $consensualFlg, PDO::PARAM_BOOL);
			if($feedbackMode == "personal")  $stmt->bindValue(3, $userID);
			
			$stmt->execute();
			$rowNum = $stmt->rowCount();
			
			$sqlStr = "";
			$sqlParam = array();		
			
			if($scoreTableName == "visual_assessment")
			{
				if($rowNum == 0)
				{
					$sqlStr = "INSERT INTO visual_assessment"
					        . " (exec_id, entered_by, consensual_flg, interrupt_flg, score, registered_at)"
							. " VALUES (?, ?, ?, ?, ?, ?);";
					$sqlParam[0] = $execID;  $sqlParam[1] = $userID;  $sqlParam[2] = $consensualFlg;
					$sqlParam[3] = ($interruptFlg == 1) ? "t" : "f";
					$sqlParam[4] = $evalStr;
					$sqlParam[5] = $registeredAt;						
				}
				else if($rowNum == 1 && $stmt->fetchColumn() == 't')
				{
					$sqlStr = "UPDATE visual_assessment SET score=?, registered_at=?";
					$sqlParam[0] = $evalStr;  $sqlParam[1] = $registeredAt;
	
					if($interruptFlg == 0)
					{
						$sqlStr .= ", interrupt_flg='f'";
					}
					if($feedbackMode == "consensual")
					{
						$sqlStr .= ", entered_by=?";
						array_push($sqlParam, $userID);
					}
	
					$sqlStr .= " WHERE exec_id=? AND consensual_flg=?";
					array_push($sqlParam, $execID);
					array_push($sqlParam, $consensualFlg);				
					
					if($feedbackMode == "personal")
					{
						$sqlStr .= " AND entered_by=?";
						array_push($sqlParam, $userID);					
					}	
				}
			}
			else
			{
				$tmpArr = explode("^", $evalStr);
	
				// ƒJƒ‰ƒ€–¼‚ÌŽæ“¾
				$sqlStr = "SELECT attname FROM pg_attribute WHERE attnum > 3"
				        . " AND attrelid = (SELECT relfilenode FROM pg_class WHERE relname='".$scoreTableName."')"
						. " AND attname != 'registered_at' AND attname != 'interrupt_flg' ORDER BY attnum";
						
				$stmtCol = $pdo->prepare();
				$stmtCol->execute();
				$colNum = $stmtCol->rowCount();
				
				if($rowNum == 0)
				{
					$sqlStr = "INSERT INTO \"" . $scoreTableName . "\""
					        . " (exec_id, entered_by, consensual_flg, interrupt_flg,";	
				
					while($resultCol = $stmtCol->fetch(PDO::FETCH_NUM)) 
					{
						$sqlStr .= $colRow[0] . ', ';
					}
			
					$sqlStr .= " registered_at) VALUES (?, ?, ?, ?,";
					$sqlParam[0] = $execID;
					$sqlParam[1] = $userID;
					$sqlParam[2] = $consensualFlg;
					$sqlParam[3] = ($interruptFlg == 1) ? "t" : "f";
							
					for($i=0; $i<$colNum; $i++)
					{
						$sqlStr .= "?,";
						array_push($sqlParam, $tmpArr[$i]);
					}
						
					$sqlStr .= "'?)";
					array_push($sqlParam, $registeredAt);
				}
				//else if($rowNum == 1 && $stmt->fetchColumn() == 't')
				else if($rowNum == 1 && ($stmt->fetchColumn() == 't' || $interruptFlg == 1))
				{
					$sqlStr = "UPDATE \"" . $scoreTableName . "\" SET ";
					
					for($i=0; $i<$colNum; $i++)
					{
						$resultCol = $stmtCol->fetch(PDO::FETCH_NUM);
						$sqlStr .= $colRow[0] . '=?, ';
						$sqlParam[$i] = $tmpArr[$i];
					}				
					
					$sqlStr .= " registered_at=?";
					array_push($sqlParam, $registeredAt);
					
					if($interruptFlg == 0)  $sqlStr .= ", interrupt_flg='f'";
					
					if($feedbackMode == "consensual")
					{
						$sqlStr .= ", entered_by=?";
						array_push($sqlParam, $userID);	
					}
					
					$sqlStr .= " WHERE exec_id=? AND consensual_flg=?";
					array_push($sqlParam, $execID);
					array_push($sqlParam, $consensualFlg);		
	
					if($feedbackMode == "personal")
					{
						$sqlStr .= " AND entered_by=?";
						array_push($sqlParam, $userID);
					}		
				}
				
				//echo $sqlStr;
			}
			
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParam);
		
			if($stmt->rowCount() == 1)
			{
				$dstData['message'] = 'Successfully registered in feedback database.';
				$consensualFBFlg = 1;
			}
			else
			{
				$tmp = $stmt->errorInfo();
				$dstData['message'] = $tmp[2];
				//break;
			}
		}
		echo json_encode($dstData);
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;

?>