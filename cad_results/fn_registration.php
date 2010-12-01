<?php

	session_cache_limiter('none');
	session_start();

	$params = array('toTopDir' => "../");

	include_once("../common.php");
	include_once("../auto_logout.php");	
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
			"required" => true,
			"min" => 1,
			"errorMes" => "[ERROR] Input data (CAD ID) is invalid."),
		"feedbackMode" => array(
			"type" => "select",
			"required" => true,
			"options" => array("personal", "consensual"),
			"errorMes" => "[ERROR] Input data (feedbackMode) is invalid."),
		"fnData" => array(
			'type' => 'json',
			'rule' => array(
				'type' => 'array',
				'childrenRule' => array(
					'type' => 'assoc',
					'rule' => array(
						"x" => array('type' => 'int', 'min' => 0, 'required' => true),
						"y" => array('type' => 'int', 'min' => 0, 'required' => true),
						"z" => array('type' => 'int', 'min' => 0, 'required' => true),
						"rank" => array('type' => 'string', 'regrex' => "/^[-\d\s\.\/]+$", 'required' => true),
						"enteredBy" => array('type' => 'string'),
						"idStr" => array('type' => 'string'),
						"colorSet" => array('type' => 'int', 'min' => 0)
					),
				),
				'minLength' => 0
			),
			'required' => true,
			"errorMes" => "[ERROR] Input data (fnData) is invalid."),
		"dstAddress" => array(
			"type" => "string",
			"regex" => "/^[-_.!~*\'()\w;\/?:\@&=+\$,%#]+$/",
			"default" => "undefined",
			"errorMes" => "[ERROR] Input data (dstAddress) is invalid.")
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
			
			foreach($params['fnData'] as $item)
			{
				$tmpStr = explode(' ', $item["rank"]);	
				if(strcmp($tmpStr[0],'-')==0) $tmpStr[0] = 0;

				$stmt->bindValue(4, $item["x"]);
				$stmt->bindValue(5, $item["y"]);
				$stmt->bindValue(6, $item["z"]);
				$stmt->bindValue(7, $tmpStr[0]);
				$stmt->bindValue(8, $registTime);
				$stmt->execute();

				if($stmt->rowCount() != 1)
				{
					//$err = $stmt->errorInfo();
					//$dstData['errorMessage'] = $err[2];
					$dstData['errorMessage'] = 'Fail to savr FN location';

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
									   $item["x"],
									   $item["y"],
									   $item["z"],
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
							//$err = $stmtUpdate->errorInfo();
							//$dstData['errorMessage'] = $err[2];
							$dstData['errorMessage'] = 'Fail to savr FN location';
							break;
						}
					}
			
					if($item["idStr"] != "")
					{
						$idArr = explode(',', $item["idStr"]);
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
								//$err = $stmtUpdate->errorInfo();
								//$dstData['errorMessage'] = $err[2];
								$dstData['errorMessage'] = 'Fail to save FN location';
								break;
							}
						}
						if($dstData['errorMessage'] != "")	break;
					}
				}
				//-------------------------------------------------------------------------------------------------------
			} // end foreach
		
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
				$sqlParams[] = count($params['fnData']);
				$sqlParams[] = $registTime;
	
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($sqlParams);		
		
				if($stmt->rowCount() != 1)
				{
					$err = $stmt->errorInfo();
					$dstData['errorMessage'] = $err[2];
					
					DeleteFnTables($pdo, $params['execID'], $consensualFlg, $userID);
				}
				else if($params['feedbackMode'] == "personal") // Write action log table (personal feedback only)
				{
					$sqlStr = "INSERT INTO feedback_action_log (exec_id, user_id, act_time, action, options)"
							. " VALUES (?,?,?,'save','FN input')";
					$stmt = $pdo->prepare($sqlStr);
					$stmt->bindValue(1, $params['execID']);
					$stmt->bindValue(2, $userID);
					$stmt->bindValue(3, date('Y-m-d H:i:s'));
					$stmt->execute();
				}
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