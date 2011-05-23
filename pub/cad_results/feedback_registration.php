<?php

	session_cache_limiter('none');
	session_start();

	include("../common.php");

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
			"errorMes" => "[ERROR] CAD ID is invalid."),
		"pluginID" => array(
			"type" => "int",
			"required" => true,
			"min" => 1,
			"errorMes" => "[ERROR] Plug-in ID is invalid."),
		"feedbackMode" => array(
			"type" => "select",
			"required" => true,
			"options" => array("personal", "consensual"),
			"errorMes" => "[ERROR] 'Feedback mode' is invalid."),
		"interruptFlg" => array(
			"type" => "select",
			"required" => true,
			"options" => array("0", "1"),
			"errorMes" => "[ERROR] 'interruptFlg' is invalid."),
		//"fnFoundFlg" => array(
		//	"type" => "select",
		//	"required" => true,
		//	"options" => array("0", "1"),
		//	"otherwise" => "1"),
		"candStr" => array(
			"type" => "string",
			"regex" => "/^[\d\^]+$/",
			"errorMes" => "[ERROR] 'Candidate string' is invalid."),
		"evalStr" => array(
			"type" => "string",
		//	"regex" => "/^[\d-\^]+$/",
			"errorMes" => "[ERROR] 'Evaluation string' is invalid.")
		));

	if($validator->validate($_POST))
	{
		$params = $validator->output;
		$params['errorMessage'] = "";
	}
	else
	{
		$params = $validator->output;
		$params['errorMessage'] = implode('<br/>', $validator->errors);
	}

	$params['toTopDir'] = '../';
	$userID = $_SESSION['userID'];
	//------------------------------------------------------------------------------------------------------------------

	$dstData = array('message'      => $params['errorMessage'],
					 'interruptFlg' => $params['interruptFlg']);

	if($_SESSION['groupID'] != 'demo')
	{
		try
		{
			// Connect to SQL Server
			$pdo = DBConnector::getConnection();

			$registeredAt = date('Y-m-d H:i:s');
			$consensualFlg = ($params['feedbackMode'] == "consensual") ? 't' : 'f';

			$sqlStr = "SELECT result_type, score_table FROM plugin_cad_master WHERE plugin_id=?";
			$result = DBConnector::query($sqlStr, $params['pluginID'], 'ARRAY_NUM');
			
			$resultType     = $result[0];
			$scoreTableName = $result[1];

			$initialFlg = 0;

			//------------------------------------------------------------------------------------------------
			// Insert (or update) feedback_list
			//------------------------------------------------------------------------------------------------
			$feedbackID = CadFeedback::GetFeedbackID($pdo, $params['jobID'], $params['feedbackMode'], $userID);
			
			$sqlParams = array();
			
			if($feedbackID == 0)
			{
				$sqlStr = "INSERT INTO feedback_list(job_id, entered_by, is_consensual, status, registered_at)"
						. "VALUES (?, ?, ?, ?, ?)";

				$sqlParams[] = $params['jobID'];
				$sqlParams[] = $userID;
				$sqlParams[] = $consensualFlg;
				$sqlParams[] = ($params['interruptFlg']) ? 0 : 1;
				$sqlParams[] = $registeredAt;

				$initialFlg = 1;
			}
			else
			{
				$sqlStr = "UPDATE feedback_list SET status=?, registered_at=? WHERE fb_id=?";
				$sqlParams[] = ($params['interruptFlg']) ? 0 : 1;
				$sqlParams[] = $registeredAt;
				$sqlParams[] = $feedbackID;
			}

			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParams);

			if($stmt->rowCount() != 1)
			{
				$dstData['message'] .= "Fail to register feedbacks.";
			}
			else
			{
				$feedbackID = CadFeedback::GetFeedbackID($pdo, $params['jobID'], $params['feedbackMode'], $userID);
			}
			//------------------------------------------------------------------------------------------------
		}
		catch (PDOException $e)
		{
			var_dump($e->getMessage());
		}

		if($dstData['message'] == "")
		{

			try
			{
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

				if($resultType == 1)	// for lesion detection
				{
					//----------------------------------------------------------------------------------------
					// Registration to lesion_classification table
					//----------------------------------------------------------------------------------------
					$candArr = explode("^", $params['candStr']);
					$evalArr = explode('^', $params['evalStr']);
					$candNum = count($candArr);
					$sqlParams = array();

					$sqlStr = "SELECT COUNT(*) FROM candidate_classification WHERE fb_id=?";
					$registeredCandNum = DBConnector::query($sqlStr, $feedbackID, 'SCALAR');

					// begin transaction
					$pdo->beginTransaction();

					for($i=0; $i<$candNum; $i++)
					{
						if($initialFlg == 1 || $registeredCandNum == 0)
						{
							$sqlStr = "INSERT INTO candidate_classification"
									. " (fb_id, candidate_id, evaluation) VALUES (?, ?, ?);";
							$sqlParams[0] = $feedbackID;
							$sqlParams[1] = $candArr[$i];
							$sqlParams[2] = $evalArr[$i];
						}
						else
						{
							$sqlStr = "UPDATE candidate_classification SET evaluation=?"
									. " WHERE fb_id=? AND candidate_id=?";
							$sqlParams[0] = $evalArr[$i];
							$sqlParams[1] = $feedbackID;
							$sqlParams[2] = $candArr[$i];
						}

						$stmt = $pdo->prepare($sqlStr);
						$stmt->execute($sqlParams);
					}
					//----------------------------------------------------------------------------------------

					//----------------------------------------------------------------------------------------
					// Registration to fn_count table
					//----------------------------------------------------------------------------------------
					if($initialFlg == 1)
					{
						$sqlStr = "INSERT INTO fn_count(fb_id, fn_num) VALUES (?, 0)";
						$stmt = $pdo->prepare($sqlStr);
						$stmt->execute(array($feedbackID, $status));
					}
					//----------------------------------------------------------------------------------------

					//----------------------------------------------------------------------------------------
					// Write action log table (personal feedback only)
					//----------------------------------------------------------------------------------------
					if($params['feedbackMode'] == "personal")
					{
						$sqlStr = "INSERT INTO feedback_action_log"
								. " (job_id, user_id, act_time, action, options) VALUES ";

						if($params['interruptFlg']) $sqlStr .= "(?,?,?,'save', 'candidate classification')";
						else						$sqlStr .= "(?,?,?,'register','')";

						$stmt = $pdo->prepare($sqlStr);
						$stmt->bindParam(1, $params['jobID']);
						$stmt->bindParam(2, $userID);
						$stmt->bindParam(3, $registeredAt);
						$stmt->execute();

						//$tmp = $stmt->errorInfo();
						//echo $tmp[2];
					}
					//----------------------------------------------------------------------------------------

					$pdo->commit();

					if($dstData['message'] == "" && $params['interruptFlg'] == 0)
					{
						$dstData['message'] .= 'Successfully registered in feedback database.';
					}
				}
				else if($resultType == 2)  // under modified (2010.11.5)
				{
					// begin transaction
					$pdo->beginTransaction();

					$sqlStr = "DELETE FROM feedback_attributes WHERE fb_id=?";
					$stmt = $pdo->prepare($sqlStr);
					$stmt->bindvalue(1, $feedbackID);
					$stmt->execute();

					$evalArr = explode("^", $params['evalStr']);

					$sqlStr = "INSERT INTO feedback_attributes"
							    . " (fb_id, key, value) VALUES (?, ?, ?);";
					$stmt = $pdo->prepare($sqlStr);
					$stmt->bindvalue(1, $feedbackID);

					for($i = 0; $i < count($evalArr)/2; $i++)
					{
						$stmt->bindvalue(2, $evalArr[$i*2]);
						$stmt->bindvalue(3, $evalArr[$i*2+1]);
						$stmt->execute();
					}
					$pdo->commit();

					if($dstData['message'] == "")
					{
						$dstData['message'] = 'Successfully registered in feedback database.';
					}
				}
				//$pdo->commit();
			}
			catch (PDOException $e)
			{
				$pdo->rollBack();
				$dstData['message']  = $e->getMessage();
				//$dstData['message'] .= "Fail to register feedbacks.";

				if($initialFlg == 1)
				{
					$sqlStr = "DELETE FROM feedback_list WHERE fb_id=?";
					$stmt = $pdo->prepare($sqlStr);
					$stmt->bindValue(1, $feedbackID);
					$stmt->execute();
				}
				else if(!$params['interruptFlg'])
				{
					$sqlStr = "UPDATE feedback_list SET status=0 WHERE fb_id=?";
					$stmt = $pdo->prepare($sqlStr);
					$stmt->bindValue(1, $feedbackID);
					$stmt->execute();
				}
			}
		}
		$pdo = null;
	}
	echo json_encode($dstData);
?>
