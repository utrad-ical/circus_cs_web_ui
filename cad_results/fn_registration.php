<?php

	session_cache_limiter('none');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include_once("../auto_logout.php");	
	include_once("fn_input_private.php");
	require_once('../class/validator.class.php');
	
	function DeleteFnTables($pdo, $execID, $consensualFlg, $userID)
	{
		$sqlParams = array($execID, $consensualFlg);
		if($consensualFlg == 'f') $sqlParams[] = $userID;

		$sqlStr = "DELETE FROM false_negative_location WHERE exec_id=?"
				. " AND consensual_flg=?";
		if($consensualFlg == 'f') $sqlStr .= " AND entered_by=?";
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($sqlParams);
			
		$sqlStr = "DELETE FROM false_negative_count"
				. " WHERE exec_id=? AND consensual_flg=?";
		if($consensualFlg == 'f') $sqlStr .= " AND entered_by=?";
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute($sqlParams);
	}
	
	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables and validation
	//------------------------------------------------------------------------------------------------------------------
	$params = array();
	$validator = new FormValidator();
	
	$validator->addRules(array(
		"execID" => array(
			"type" => "int",
			"required" => 1,
			"min" => 1,
			"errorMes" => "[ERROR] CAD ID is invalid."),
		"feedbackMode" => array(
			"type" => "select",
			"required" => 1,
			"options" => array("personal", "consensual"),
			"errorMes" => "[ERROR] 'Feedback mode' is invalid."),
		"rowNum" => array(
			"type" => "int",
			"required" => 1,
			"min" => 1),
		"posStr" => array(
			"type" => "string",
			"regex" => "/^[\w\s-\/\,\.\^]+$/",
			"errorMes" => "[ERROR] 'Postion string' is invalid."),
		"dstAddress" => array(
			"type" => "string",
			"regex" => "/^[-_.!~*\'()\w;\/?:\@&=+\$,%#]+$/",
			"default" => "undefined",
			"errorMes" => "[ERROR] 'dstAddress' is invalid.")
		));	
		
	if($validator->validate($_POST))
	{
		$params = $validator->output;
		$params['errorMessage'] = "";
	}
	else
	{
		$params = $validator->output;
		$params['errorMessage'] = implode(' ', $validator->errors);
	}

	if($params['dstAddress'] == "undefined" || !isset($params['dstAddress'])) $params['dstAddress'] = "";
	//------------------------------------------------------------------------------------------------------------------

	$dstData = array('errorMessage' => $params['errorMessage'],
	                 'dstAddress'   => $params['dstAddress']);

	try
	{
		if($params['errorMessage'] == "")
		{
			$userID = $_SESSION['userID'];

			$registTime = date('Y-m-d H:i:s');
			$posArr = explode('^', $params['posStr']);
			$consensualFlg = ($params['feedbackMode'] == "consensual") ? 't' : 'f';	

			// Connect to SQL Server
			$pdo = new PDO($connStrPDO);
			
			DeleteFnTables($pdo, $params['execID'], $consensualFlg, $userID);
			
			$sqlStr = "INSERT INTO false_negative_location (exec_id, entered_by, consensual_flg,"
			        . " location_x, location_y, location_z, nearest_lesion_id, interrupt_flg, registered_at)"
			        . " VALUES (?, ?, ?, ?, ?, ?, ?, 't', ?)";

			$stmt = $pdo->prepare($sqlStr);

			$sqlParams = array();
			$stmt->bindValue(1, $params['execID']);
			$stmt->bindValue(2, $userID);
			$stmt->bindValue(3, ($params['feedbackMode'] == "consensual") ? 't' : 'f');
			
			for($j=0; $j<$params['rowNum']; $j++)
			{
				$tmpStr = explode(' ', $posArr[$j * $DEFAULT_COL_NUM + 3]);	
				if(strcmp($tmpStr[0],'-')==0) $tmpStr[0] = 0;

				$stmt->bindValue(4, $posArr[$j * $DEFAULT_COL_NUM]);
				$stmt->bindValue(5, $posArr[$j * $DEFAULT_COL_NUM + 1]);
				$stmt->bindValue(6, $posArr[$j * $DEFAULT_COL_NUM + 2]);
				$stmt->bindValue(7, $tmpStr[0]);
				$stmt->bindValue(8, $registTime);
				$stmt->execute();

				if($stmt->rowCount() != 1)
				{
					$err = $stmt->errorInfo();
					$dstData['errorMessage'] = $err[2];

					DeleteFnTables($pdo, $params['execID'], $consensualFlg, $userID);
					break;
				}
				
				//-------------------------------------------------------------------------------------------------------
				// Update integrate_location_id
				//-------------------------------------------------------------------------------------------------------
				if($params['feedbackMode'] == "consensual")
				{
					$sqlStr = "SELECT location_id FROM false_negative_location WHERE exec_id=? AND consensual_flg='t'"
						    . " AND location_x=? AND location_y=? AND location_z=? AND registered_at=?";
					$sqlParams = array($params['execID'],
									   $posArr[$j * $DEFAULT_COL_NUM],
									   $posArr[$j * $DEFAULT_COL_NUM + 1],
									   $posArr[$j * $DEFAULT_COL_NUM + 2],
									   $registTime);

					$dstID = PdoQueryOne($pdo, $sqlStr, $sqlParams, 'SCALAR');
					$srcID = 0;
					
					$sqlStr = "SELECT location_id FROM false_negative_location WHERE exec_id=?"
							. " AND consensual_flg='f' AND interrupt_flg='f'"
						    . " AND location_x=? AND location_y=? AND location_z=?";
							
					array_pop($sqlParams);
					$stmtUpdate = $pdo->prepare($sqlStr);
					$stmtUpdate->execute($sqlParams);
			
					if($stmtUpdate->rowCount() == 1)
					{
						$srcID = $stmtUpdate->fetchColumn();
						
						$sqlStr = "UPDATE false_negative_location SET integrate_location_id=? WHERE location_id=?";
						$stmtUpdate = $pdo->prepare($sqlStr);
						$stmtUpdate->execute(array($dstID, $srcID));
						
						if($stmtUpdate->rowCount() != 1)
						{
							$err = $stmtUpdate->errorInfo();
							$dstData['errorMessage'] = $err[2];
							break;
						}
					}
			
					if($posArr[$j * $DEFAULT_COL_NUM + 5] != "")
					{
						$idArr = explode(',', $posArr[$j * $DEFAULT_COL_NUM + 5]);
						$idNum = count($idArr);
		
						$sqlStr = "UPDATE false_negative_location SET integrate_location_id=? WHERE location_id=?";
						$stmtUpdate = $pdo->prepare($sqlStr);
						$stmtUpdate->bindParam(1, $dstID);
			
						foreach($idArr as $value)
						{
							$stmtUpdate->bindParam(2, $value);
							$stmtUpdate->execute();
			
							if($stmtUpdate->rowCount() != 1)
							{
								$err = $stmtUpdate->errorInfo();
								$dstData['errorMessage'] = $err[2];
								break;
							}
						}
						if($dstData['errorMessage'] != "")	break;
					}
				}
				//-------------------------------------------------------------------------------------------------------
			} // end for : j
		
			if($dstData['errorMessage'] == "")
			{
				$sqlStr = "SELECT COUNT(*) FROM false_negative_count WHERE exec_id=?"
		                . " AND consensual_flg=? AND entered_by=?";
						
				$sqlParams = array();

				$sqlStr = "INSERT INTO false_negative_count "
				        . "(exec_id, entered_by, consensual_flg, false_negative_num, status, registered_at)"
				        . " VALUES (?, ?, ?, ?, 1, ?);";
				$sqlParams[] = $params['execID'];
				$sqlParams[] = $userID;
				$sqlParams[] = $consensualFlg;
				$sqlParams[] = $params['rowNum'];
				$sqlParams[] = $registTime;
	
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($sqlParams);		
		
				if($stmt->rowCount() != 1)
				{
					$err = $stmt->errorInfo();
					$dstData['errorMessage'] = $err[2];
					
					DeleteFnTables($pdo, $params['execID'], $consensualFlg, $userID);
				}
			}
			
			//if($dstData['errorMessage'] == "")
			//{
			//	$dstData['errorMessage'] = 'Successfully saved in feedback database.';
			//}
		
		}
		echo json_encode($dstData);
	
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;	


?>