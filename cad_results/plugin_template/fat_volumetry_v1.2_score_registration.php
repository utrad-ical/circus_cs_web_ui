<?php

	session_start();

	include("../../common.php");

	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variable
	//--------------------------------------------------------------------------------------------------------
	$execID = (isset($_REQUEST['execID'])) ? $_REQUEST['execID'] : 0;
	$modifyFlg = (isset($_REQUEST['modifyFlg'])) ? $_REQUEST['modifyFlg'] : 0;
	$scoreStr = (isset($_REQUEST['scoreStr'])) ? $_REQUEST['scoreStr'] : "";
	$comment = (isset($_REQUEST['comment'])) ? $_REQUEST['comment'] : "";

	$scoreArr = explode("^", $scoreStr);
	
	$userID = $_SESSION['userID'];
	$registeredAt = date('Y-m-d H:i:s');
	//--------------------------------------------------------------------------------------------------------

	$dstData = array('message' => "");
	
	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		$sqlStr = 'SELECT * FROM "fat_volumetry_v1.2_score"'
				. "WHERE exec_id=? AND consensual_flg='f' AND entered_by=?";

		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute(array($execID, $userID));
		
		if($stmt->rowCount()==1)
		{
			$sqlStr = "UPDATE \"fat_volumetry_v1.2_score\""
					. " SET heart_vat=?, heart_sat=?, heart_bound=?,"
					. " cavity_vat=?, cavity_sat=?, cavity_bound=?,"
					. " wall_vat=?, wall_sat=?, wall_bound=?,"
					. " pelvic_vat=?, pelvic_sat=?, pelvic_bound=?,"
					. " other_vat=?, other_sat=?, other_bound=?,"
					. " eval_comment=?, registered_at=?"
					. " WHERE exec_id=? AND consensual_flg='f' AND entered_by=?";
			
			$stmt = $pdo->prepare($sqlStr);
				
			for($i=0; $i<15; $i++)
			{
				$stmt->bindValue($i+1, $scoreArr[$i]);
			}
			$stmt->bindValue(16, $comment);
			$stmt->bindValue(17, $registeredAt);
			$stmt->bindValue(18, $execID);
			$stmt->bindValue(19, $userID);
			
			$stmt->execute();
		
			if($stmt->rowCount() != 1)
			{
				$err = $stmt->errorInfo();
				$dstData['message'] .= $err[2];
			}
			else
			{
				$dstData['message'] = "Successfully modified in feedback database.";
			}
		}
		else
		{
			$sqlStr = "INSERT INTO \"fat_volumetry_v1.2_score\" VALUES (?, ?, 'f', 'f', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindValue(1, $execID);
			$stmt->bindValue(2, $userID);
			
			for($i=0; $i<15; $i++)
			{
				$stmt->bindValue($i+3, $scoreArr[$i]);
			}
			$stmt->bindValue(18, $comment);
			$stmt->bindValue(19, $registeredAt);
			
			$stmt->execute();
			
			if($stmt->rowCount() != 1)
			{
				$err = $stmt->errorInfo();
				$dstData['message'] .= $err[2];
			}
			else
			{
				$dstData['message'] = "Successfully registered in feedback database.";
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
