<?php

class CadFeedback
{
	public static function GetFeedbackID($pdo, $jobID, $feedbackMode, $userID)
	{
		$pdo = DBConnector::getConnection();
		$ret = 0;

		$sqlStr = "SELECT fb_id FROM feedback_list WHERE job_id=?";

		if($feedbackMode == "personal")  $sqlStr .= " AND entered_by=? AND is_consensual='f'";
		else                             $sqlStr .= " AND is_consensual='t'";

		$stmt = $pdo->prepare($sqlStr);
		$stmt->bindparam(1, $jobID);
		if($feedbackMode == "personal")  $stmt->bindParam(2, $userID);
		$stmt->execute();

		if($stmt->rowCount() == 1)  $ret = $stmt->fetchColumn();

		return $ret;
	}

}

