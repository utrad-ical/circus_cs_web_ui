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
		"cadName" => array(
			"type" => "cadname",
			"required" => true,
			"errorMes" => "'CAD name' is invalid."),
		"version" => array(
			"type" => "version",
			"required" => true,
			"errorMes" => "'Version' is invalid."),
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
		"fnFoundFlg" => array(
			"type" => "select",
			"required" => true,
			"options" => array("0", "1"),
			"otherwise" => "1"),
		"candStr" => array(
			"type" => "string",
			"regex" => "/^[\d\^]+$/",
			"errorMes" => "[ERROR] 'Candidate string' is invalid."),
		"evalStr" => array(
			"type" => "string",
			"regex" => "/^[\d-\^]+$/",
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

			$stmt = $pdo->prepare("SELECT result_type, score_table FROM cad_master WHERE plugin_name=? AND version=?");
			$stmt->execute(array($params['cadName'], $params['version']));

			$result = $stmt->fetch(PDO::FETCH_NUM);
			$resultType     = $result[0];
			$scoreTableName = $result[1];

			if($resultType == 1)
			{
				$candArr = explode("^", $params['candStr']);
				$evalArr = explode('^', $params['evalStr']);

				$candNum = count($candArr);

				//------------------------------------------------------------------------------------------------
				// Registration to lesion_feedback table
				//------------------------------------------------------------------------------------------------
				$sqlStr = "DELETE FROM lesion_feedback WHERE job_id=? AND is_consensual=?";
				if($params['feedbackMode'] == "personal") $sqlStr .= " AND entered_by=?";

				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindParam(1, $params['jobID']);
				$stmt->bindParam(2, $consensualFlg);
				if($params['feedbackMode'] == "personal")   $stmt->bindParam(3, $userID);
				$stmt->execute();

				$sqlParams = array();

				if($candNum<=1 && strlen($params['candStr'])==0)
				{
					$sqlStr = "INSERT INTO lesion_feedback (job_id, lesion_id, entered_by, is_consensual, "
					        . "evaluation, interrupted, registered_at) VALUES (?, 0, ?, ?, 0, ?, ?);";

					$sqlParams[] = $params['jobID'];
					$sqlParams[] = $userID;
					$sqlParams[] = $consensualFlg;
					$sqlParams[] = ($params['interruptFlg']) ? "t" : "f";
					$sqlParams[] = $registeredAt;

					$stmt = $pdo->prepare($sqlStr);
					$stmt->execute($sqlParams);

					if($stmt->rowCount() != 1)
					{
						$dstData['message'] .= "Fail to register lesion classification.";
					}
				}
				else
				{
					for($i=0; $i<$candNum; $i++)
					{
						$sqlStr = "INSERT INTO lesion_feedback (job_id, lesion_id, entered_by, is_consensual, "
							        . "evaluation, interrupted, registered_at) VALUES (?, ?, ?, ?, ?, ?, ?);";

						$sqlParams[0] = $params['jobID'];
						$sqlParams[1] = $candArr[$i];
						$sqlParams[2] = $userID;
						$sqlParams[3] = $consensualFlg;
						$sqlParams[4] = $evalArr[$i];
						$sqlParams[5] = ($params['interruptFlg']) ? "t" : "f";
						$sqlParams[6] = $registeredAt;

						$stmt = $pdo->prepare($sqlStr);
						$stmt->execute($sqlParams);

						if($stmt->rowCount() != 1)
						{
							$dstData['message'] .= "Fail to register lesion classification;";
							break;
						}
					}
				}
				//----------------------------------------------------------------------------------------------------

				//----------------------------------------------------------------------------------------------------
				// Registration to false_negative_count table
				//----------------------------------------------------------------------------------------------------
				if($dstData['message'] == "")
				{
					$status = ($params['interruptFlg']) ? 1 : 2;

					$sqlStr = "SELECT * FROM false_negative_count WHERE job_id=? AND is_consensual=?";
					if($params['feedbackMode'] == "personal") $sqlStr .= " AND entered_by=?";

					$stmt = $pdo->prepare($sqlStr);
					$stmt->bindValue(1, $params['jobID']);
					$stmt->bindValue(2, $consensualFlg, PDO::PARAM_BOOL);
					if($params['feedbackMode'] == "personal")   $stmt->bindValue(3, $userID);

					$stmt->execute();
					$rowNum = $stmt->rowCount();
					$result = $stmt->fetch(PDO::FETCH_ASSOC);

					$sqlParams = array();

					if($rowNum == 0 && !$params['fnFoundFlg'])
					{
						$sqlStr = "INSERT INTO false_negative_count "
						        . "(job_id, entered_by, is_consensual, false_negative_num, status, registered_at)"
						        . " VALUES (?, ?, ?, 0, ?, ?);";
						$sqlParams[] = $params['jobID'];
						$sqlParams[] = $userID;
						$sqlParams[] = $consensualFlg;
						$sqlParams[] = $status;
						$sqlParams[] = $registeredAt;

						$stmtFN = $pdo->prepare($sqlStr);
						$stmtFN->execute($sqlParams);

						if($stmtFN->rowCount() != 1) $dstData['message'] .= "Fail to save the number of FN.";
					}
					else if($rowNum == 1)
					{
						$savedFnNum = $result['false_negative_num'];
						$savedStatus= $result['status'];

						if($savedFnNum == 0)
						{
							if($savedStatus != $status)
							{
								$sqlStr = "UPDATE false_negative_count SET status=?, registered_at=?";
								$sqlParams[] = $status;
								$sqlParams[] = $registeredAt;

								if($params['feedbackMode'] == "consensual")
								{
									$sqlStr .= ", entered_by=?";
									$sqlParams[] = $userID;
								}

								$sqlStr .= " WHERE job_id=? AND is_consensual=?";
								$sqlParams[] = $params['jobID'];
								$sqlParams[] = $consensualFlg;

								if($params['feedbackMode'] == "personal")
								{
									$sqlStr .= " AND entered_by=?";
									$sqlParams[] = $userID;
								}

								$stmt = $pdo->prepare($sqlStr);
								$stmt->execute($sqlParams);

								if($stmt->rowCount() != 1) $dstData['message'] .= "Fail to update FN table.";
							}
						}
						else
						{
						 	if($savedStatus != $status)
						 	{
								$sqlStr = "UPDATE false_negative_count SET status=?, registered_at=?";
								$sqlParams[] = $status;
								$sqlParams[] = $registeredAt;

								if($params['feedbackMode'] == "consensual")
								{
									$sqlStr .= ", entered_by=?";
									$sqlParams[] = $userID;
								}

								$sqlStr .= " WHERE job_id=? AND is_consensual=?";
								$sqlParams[] = $params['jobID'];
								$sqlParams[] = $consensualFlg;

								if($params['feedbackMode'] == "personal")
								{
									$sqlStr .= " AND entered_by=?";
									$sqlParams[] = $userID;
								}

								$stmt = $pdo->prepare($sqlStr);
								$stmt->execute($sqlParams);

								if($stmt->rowCount() != 1) 	$dstData['message'] .= "Fail to update FN table.";

								if($dstData['message'] == "")
								{
									$sqlParams = array();

									$sqlStr = "UPDATE false_negative_location SET interrupted=?,"
									        . " registered_at=?";
									$sqlParams[] = ($params['interruptFlg']) ? 't' : 'f';
									$sqlParams[] = $registeredAt;

									if($params['feedbackMode'] == "consensual")
									{
										$sqlStr .= ", entered_by=?";
										$sqlParams[] = $userID;
									}

									$sqlStr .= " WHERE job_id=? AND is_consensual=?";
									$sqlParams[] = $params['jobID'];
									$sqlParams[] = $consensualFlg;

									if($params['feedbackMode'] == "personal")
									{
										$sqlStr .= " AND entered_by=?";
										$sqlParams[] = $userID;
									}

									$stmt = $pdo->prepare($sqlStr);
									$stmt->execute($sqlParams);

									if($stmt->rowCount() != $result['false_negative_num'])
									{
										$dstData['message'] .= "Fail to update FN table.";
									}
								}
							}
						}
					}
				}

				//----------------------------------------------------------------------------------------------------------
				// Write action log table (personal feedback only)
				//----------------------------------------------------------------------------------------------------------
				if($params['feedbackMode'] == "personal")
				{
					$sqlStr = "INSERT INTO feedback_action_log (job_id, user_id, act_time, action, options) VALUES ";

					if($params['interruptFlg']==1)	$sqlStr .= "(?,?,?,'save', 'candidate classification')";
					else							$sqlStr .= "(?,?,?,'register','')";

					$stmt = $pdo->prepare($sqlStr);
					$stmt->bindParam(1, $params['jobID']);
					$stmt->bindParam(2, $userID);
					$stmt->bindParam(3, $registeredAt);
					$stmt->execute();

					//$tmp = $stmt->errorInfo();
					//echo $tmp[2];
				}
				//----------------------------------------------------------------------------------------------------------

				if($dstData['message'] == "" && $params['interruptFlg'] == 0)
				{
					$dstData['message'] .= 'Successfully registered in feedback database.';
				}
			}
			else if($resultType == 2)  // under modified (2010.11.5)
			{
				$scoreTableName = ($scoreTableName !== "") ? $scoreTableName : "visual_assessment";

				$sqlStr = "SELECT interrupted FROM \"" . $scoreTableName . "\" WHERE job_id=?"
						. " AND is_consensual=?";

				if($params['feedbackMode'] == "personal") $sqlStr .= " AND entered_by=?";

				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindValue(1, $params['jobID']);
				$stmt->bindValue(2, $consensualFlg, PDO::PARAM_BOOL);
				if($feedbackMode == "personal")  $stmt->bindValue(3, $userID);

				$stmt->execute();
				$rowNum = $stmt->rowCount();

				$sqlStr = "";
				$sqlParams = array();

				if($scoreTableName == "visual_assessment")
				{
					if($rowNum == 0)
					{
						$sqlStr = "INSERT INTO visual_assessment"
						        . " (job_id, entered_by, is_consensual, interrupted, score, registered_at)"
								. " VALUES (?, ?, ?, ?, ?, ?);";
						$sqlParams[] = $params['jobID'];
						$sqlParams[] = $userID;
						$sqlParams[] = $consensualFlg;
						$sqlParams[] = ($params['interruptFlg']) ? "t" : "f";
						$sqlParams[] = $evalStr;
						$sqlParams[] = $registeredAt;
					}
					else if($rowNum == 1 && $stmt->fetchColumn() == 't')
					{
						$sqlStr = "UPDATE visual_assessment SET score=?, registered_at=?";
						$sqlParams[] = $evalStr;
						$sqlParams[] = $registeredAt;

						if($params['interruptFlg'] == 0)
						{
							$sqlStr .= ", interrupted='f'";
						}

						if($params['feedbackMode'] == "consensual")
						{
							$sqlStr .= ", entered_by=?";
							$sqlParams[] = $userID;
						}

						$sqlStr .= " WHERE job_id=? AND is_consensual=?";
						$sqlParams[] = $params['jobID'];
						$sqlParams[] = $consensualFlg;

						if($params['feedbackMode'] == "personal")
						{
							$sqlStr .= " AND entered_by=?";
							$sqlParams[] = $userID;
						}
					}
				}
				else
				{
					$tmpArr = explode("^", $params['evalStr']);

					// Retrieve calumn names
					$sqlStr = "SELECT attname FROM pg_attribute WHERE attnum > 3"
					        . " AND attrelid = (SELECT relfilenode FROM pg_class WHERE relname='".$scoreTableName."')"
							. " AND attname != 'registered_at' AND attname != 'interrupted' ORDER BY attnum";

					$stmtCol = $pdo->prepare();
					$stmtCol->execute();
					$colNum = $stmtCol->rowCount();

					if($rowNum == 0)
					{
						$sqlStr = "INSERT INTO \"" . $scoreTableName . "\""
						        . " (job_id, entered_by, is_consensual, interrupted,";

						while($resultCol = $stmtCol->fetch(PDO::FETCH_NUM))
						{
							$sqlStr .= $colRow[0] . ', ';
						}

						$sqlParams = array();

						$sqlStr .= " registered_at) VALUES (?, ?, ?, ?,";
						$sqlParams[0] = $params['jobID'];
						$sqlParams[1] = $userID;
						$sqlParams[2] = $consensualFlg;
						$sqlParams[3] = ($params['interruptFlg']) ? "t" : "f";

						for($i=0; $i<$colNum; $i++)
						{
							$sqlStr .= "?,";
							$sqlParams[] = $tmpArr[$i];
						}

						$sqlStr .= "'?)";
						$sqlParams[] = $registeredAt;
					}
					//else if($rowNum == 1 && $stmt->fetchColumn() == 't')
					else if($rowNum == 1 && ($stmt->fetchColumn() == 't' || $params['interruptFlg']))
					{
						$sqlStr = "UPDATE \"" . $scoreTableName . "\" SET ";

						for($i=0; $i<$colNum; $i++)
						{
							$resultCol = $stmtCol->fetch(PDO::FETCH_NUM);
							$sqlStr .= $colRow[0] . '=?, ';
							$sqlParams[$i] = $tmpArr[$i];
						}

						$sqlStr .= " registered_at=?";
						$sqlParams[] = $registeredAt;

						if($params['interruptFlg'] == 0)  $sqlStr .= ", interrupted='f'";

						if($params['feedbackMode'] == "consensual")
						{
							$sqlStr .= ", entered_by=?";
							$sqlParams[] = $userID;
						}

						$sqlStr .= " WHERE job_id=? AND is_consensual=?";
						$sqlParam[] = $params['jobID'];
						$sqlParam[] = $consensualFlg;

						if($params['feedbackMode'] == "personal")
						{
							$sqlStr .= " AND entered_by=?";
							$sqlParam[] = $userID;
						}
					}
					//echo $sqlStr;
				}

				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute($sqlParam);

				if($stmt->rowCount() == 1)
				{
					$dstData['message'] = 'Successfully registered in feedback database.';
				}
				else
				{
					$tmp = $stmt->errorInfo();
					$dstData['message'] = $tmp[2];
				}
			}
			//echo json_encode($dstData);
		}
		catch (PDOException $e)
		{
			var_dump($e->getMessage());
		}
		$pdo = null;
	}
	echo json_encode($dstData);
?>
