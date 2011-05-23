<?php
	include_once("../common.php");
	Auth::checkSession(false);

	function DeleteFnTables($pdo, $feedbackID)
	{
		$sqlStr = "DELETE FROM fn_location WHERE fb_id=?";
		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindvalue(1, $feedbackID);
		$stmt->execute($sqlParams);

		$sqlStr = "DELETE FROM fn_count WHERE fb_id=?";
		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindvalue(1, $feedbackID);
		$stmt->execute($sqlParams);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables and validation
	//------------------------------------------------------------------------------------------------------------------
	$params = array();
	$validator = new FormValidator();

	$validator->addRules(array(
		"jobID" => array(
			"type" => "int",
			"required" => true,
			"min" => 1,
			"errorMes" => "[ERROR] Input data (job ID) is invalid."),
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

	if($params['errorMessage'] == "" && $_SESSION['groupID'] != 'demo')
	{
		try
		{
			// Connect to SQL Server
			$pdo = DBConnector::getConnection();

			$userID = $_SESSION['userID'];
			$registeredAt = date('Y-m-d H:i:s');
			$posArr = explode('^', $params['posStr']);
			$consensualFlg = ($params['feedbackMode'] == "consensual") ? 't' : 'f';

			$initialFlg = 0;

			//------------------------------------------------------------------------------------------------
			// Insert (or update) feedback_list
			//------------------------------------------------------------------------------------------------
			$feedbackID = CadFeedback::GetFeedbackID($pdo, $params['jobID'], $params['feedbackMode'], $userID);

			$sqlParams = array();

			if($feedbackID == 0)
			{
				$sqlStr = "INSERT INTO feedback_list (job_id, entered_by, is_consensual, status, registered_at)"
						. "VALUES (?, ?, ?, 0, ?)";

				$sqlParams[] = $params['jobID'];
				$sqlParams[] = $userID;
				$sqlParams[] = $consensualFlg;
				$sqlParams[] = $registeredAt;

				$initialFlg = 1;

				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($sqlParams);

				if($stmt->rowCount() != 1)
				{
					$dstData['errorMessage'] .= "Fail to register feedbacks.";
				}
				else
				{
					$feedbackID = CadFeedback::GetFeedbackID($pdo, $params['jobID'], $params['feedbackMode'], $userID);
				}
			}
			//------------------------------------------------------------------------------------------------
		}
		catch (PDOException $e)
		{
			var_dump($e->getMessage());
		}

		try
		{
			//------------------------------------------------------------------------------------------------
			// Set ID array for updating integrate_fn_id
			//------------------------------------------------------------------------------------------------
			$idArr = array();

			if($params['feedbackMode'] == "consensual")
			{
				foreach($params['fnData'] as $item)
				{
					$sqlStr = "SELECT fn.fn_id FROM feedback_list fl, fn_location fn"
							. " WHERE fl.job_id=? AND fn.fb_id=fl.fb_id"
							. " AND fl.is_consensual='f' AND fl.status=1"
						    . " AND fn.location_x=? AND fn.location_y=? AND fn.location_z=?";
					$sqlParams = array($params['jobID'],
									   $item["x"],
									   $item["y"],
									   $item["z"]);

					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute($sqlParams);

					$idArr[] = array('srcID' => ($stmt->rowCount() == 1) ? 0 :$stmt->fetchColumn(),
									 'idStr' => $item["idStr"]);
				}
			}

			//var_dump($idArr);
			//------------------------------------------------------------------------------------------------

			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// begin transaction
			$pdo->beginTransaction();

			if(!$initialFlg)  DeleteFnTables($pdo, $feedbackID);

			//------------------------------------------------------------------------------------------------
			// Save FN locations
			//------------------------------------------------------------------------------------------------
			$sqlStr = "INSERT INTO fn_location"
					. " (fb_id, location_x, location_y, location_z, nearest_lesion_id)"
			        . " VALUES (?, ?, ?, ?, ?)";
			$sqlParams = array();

			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindValue(1, $feedbackID);

			$cnt = 0;

			foreach($params['fnData'] as $item)
			{
				$tmpStr = explode(' ', $item["rank"]);
				if(strcmp($tmpStr[0],'-')==0) $tmpStr[0] = 0;

				$stmt->bindValue(2, $item["x"]);
				$stmt->bindValue(3, $item["y"]);
				$stmt->bindValue(4, $item["z"]);
				$stmt->bindValue(5, $tmpStr[0]);
				$stmt->execute();

				//--------------------------------------------------------------------------------------------
				// Update integrate_fn_id
				//--------------------------------------------------------------------------------------------
				if($params['feedbackMode'] == "consensual")
				{
					$sqlStr = "SELECT fn.fn_id FROM feedback_list fl, fn_location fn"
							. " WHERE fl.job_id=? AND fn.fb_id=fl.fb_id"
							. " AND fl.is_consensual='t'"
						    . " AND fn.location_x=? AND fn.location_y=? AND fn.location_z=?";
					$sqlParams = array($params['jobID'],
									   $item["x"],
									   $item["y"],
									   $item["z"]);

					$dstID = DBConnector::query($sqlStr, $sqlParams, 'SCALAR');

					//echo $dstID;

					if($idArr[$cnt]['srcID'] != 0)
					{
						$sqlStr = "UPDATE fn_location SET integrate_fn_id=? WHERE fn_id=?";
						$stmtUpdate = $pdo->prepare($sqlStr);
						$stmtUpdate->execute(array($dstID, $idArr[$cnt]['srcID']));
					}

					if($idArr[$cnt]["idStr"] != "")
					{
						$tmpArr = explode(',', $idArr[$cnt]["idStr"]);

						$sqlStr = "UPDATE fn_location SET integrate_fn_id=? WHERE fn_id=?";
						$stmtUpdate = $pdo->prepare($sqlStr);
						$stmtUpdate->bindValue(1, $dstID);

						foreach($tmpArr as $value)
						{
							$stmtUpdate->bindValue(2, $value);
							$stmtUpdate->execute();
						}
					}
				}
				//--------------------------------------------------------------------------------------------

				$cnt++;
			}
			//------------------------------------------------------------------------------------------------

			//------------------------------------------------------------------------------------------------
			// Save the number of FN
			//------------------------------------------------------------------------------------------------
			$sqlParams = array();

			$sqlStr = "INSERT INTO fn_count (fb_id, fn_num) VALUES (?, ?);";
			$sqlParams[] = $feedbackID;
			$sqlParams[] = count($params['fnData']);

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);

			if($params['feedbackMode'] == "personal") // Write action log table (personal feedback only)
			{
				$sqlStr = "INSERT INTO feedback_action_log (job_id, user_id, act_time, action, options)"
						. " VALUES (?,?,?,'save','FN input')";
				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindValue(1, $params['jobID']);
				$stmt->bindValue(2, $userID);
				$stmt->bindValue(3, date('Y-m-d H:i:s'));
				$stmt->execute();
			}
			//------------------------------------------------------------------------------------------------

			$pdo->commit();	 // commit
		}
		catch (PDOException $e)
		{
			$pdo->rollBack();
			//$dstData['errorMessage']  = $e->getMessage();
			$dstData['errorMessage'] .= "Fail to save FN location.";
		}
		echo json_encode($dstData);

		$pdo = null;
	}
?>
