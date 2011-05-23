<?php
	include("../common.php");
	Auth::checkSession(false);

	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables
	//--------------------------------------------------------------------------------------------------------
	$oldPassword     = (isset($_POST['oldPassword']))     ? $_POST['oldPassword']     : "";
	$newPassword     = (isset($_POST['newPassword']))     ? $_POST['newPassword']     : "";
	$reenterPassword = (isset($_POST['reenterPassword'])) ? $_POST['reenterPassword'] : "";
	$userID = $_SESSION['userID'];
	//--------------------------------------------------------------------------------------------------------

	try
	{
		// Connect to SQL Server
		$pdo = DBConnector::getConnection();

		$message = "";

		if($oldPassword == "" || $newPassword == "" || $reenterPassword == "")
		{
			$message = "Please fill all text boxes.";
		}
		else
		{
			$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_id=? AND passcode=?");
			$stmt->execute(array($userID, MD5($oldPassword)));

			if($stmt->fetchColumn() != 1)
			{
				$message = "Entered current password is incorrect. Please try again.";
			}
			else if($newPassword != $reenterPassword)
			{
				$message = "New password and Re-enter password should be the same.";
			}
			//else if($oldPassword == $newPassword || $oldPassword == md5($newPassword))
			//{
			//	$message = "New password is the same as current password.";
			//}
		}

		if($message == "")
		{
			$stmt = $pdo->prepare("UPDATE users SET passcode=? WHERE user_id=?");
			$stmt->execute(array(md5($newPassword), $userID));

			if($stmt->rowCount() == 1)
			{
				$message = 'Password was successfully changed.';
			}
			else
			{
				$tmp = $stmt->errorInfo();
				$message = $tmp[2];
			}
		}

		echo $message;

	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;

?>
