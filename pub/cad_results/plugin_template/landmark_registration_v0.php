<?php

	session_start();

	include("../../common.php");

	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variable
	//--------------------------------------------------------------------------------------------------------
	$mode = (isset($_POST['mode'])) ? $_POST['mode'] : "";
	$execID = (isset($_POST['execID'])) ? $_POST['execID'] : 0;
	$subID = (isset($_POST['subID'])) ? $_POST['subID'] : 0;
	$landmarkName = (isset($_POST['landmarkName'])) ? $_POST['landmarkName'] : "";
	$shortName = (isset($_POST['shortName'])) ? $_POST['shortName'] : "";
	$xPos = (isset($_POST['xPos'])) ? $_POST['xPos'] : 0;
	$yPos = (isset($_POST['yPos'])) ? $_POST['yPos'] : 0;
	$zPos = (isset($_POST['zPos'])) ? $_POST['zPos'] : 0;

	$userID = $_SESSION['userID'];

	$tableName = 'landmark_detection_v0';
	//--------------------------------------------------------------------------------------------------------

	try
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		if($mode == 'delete')
		{
			$sqlStr = 'DELETE FROM "' . $tableName . '" WHERE exec_id=? AND sub_id=?';
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindParam(1, $execID);
			$stmt->bindParam(2, $subID);
			$stmt->execute();

			if($stmt->rowCount() == 1)	echo "Success to detele!!";
			else						echo "Fail to delete!!";
		}
		else if($mode === 'insert')
		{
			$stmt = $pdo->prepare('SELECT count(*) FROM "' . $tableName . '" WHERE exec_id=? AND landmark_name=?');
			$stmt->bindParam(1, $execID);
			$stmt->bindParam(2, $landmarkName);
			$stmt->execute();

			if($stmt->fetchColumn() == 0)
			{
				$sqlStr = 'INSERT INTO "' . $tableName . '" (exec_id, sub_id, landmark_name, short_name, location_x, location_y, location_z)'
						. ' VALUES (?, ?, ?, ?, ?, ?, ?)';
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute(array($execID, $subID, $landmarkName, $shortName, $xPos, $yPos, $zPos));

				if($stmt->rowCount() == 1)	echo "Success to add row!!";
				else						echo "Fail to add row!!";
			}
			else  echo 'Error: Landmark "' . $landmarkName . '" was already defined!!';
		}
		else if($mode === 'update')
		{
			$stmt = $pdo->prepare('SELECT count(*) FROM "' . $tableName . '" WHERE exec_id=? AND landmark_name=? AND sub_id!=?');
			$stmt->bindParam(1, $execID);
			$stmt->bindParam(2, $landmarkName);
			$stmt->bindParam(3, $subID);
			$stmt->execute();

			if($stmt->fetchColumn() == 0)
			{
				$sqlStr = 'UPDATE "' . $tableName . '" SET landmark_name=?, short_name=?, location_x=?, location_y=?, location_z=?'
						. ' WHERE exec_id=? AND sub_id=?';
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute(array($landmarkName, $shortName, $xPos, $yPos, $zPos, $execID, $subID));

				if($stmt->rowCount() == 1)	echo "Success to update row!!";
				else						echo "Fail to update row!!";
			}
			else  echo 'Error: Landmark "' . $landmarkName . '" was already defined!!';
		}
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;

?>
