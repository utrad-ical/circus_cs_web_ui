<?php

	session_start();

	include("../common.php");
	
	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables
	//--------------------------------------------------------------------------------------------------------
	$oldPassword     = (isset($_REQUEST['oldPassword']))     ? $_REQUEST['oldPassword']     : "";
	$newPassword     = (isset($_REQUEST['newPassword']))     ? $_REQUEST['newPassword']     : "";
	$reenterPassword = (isset($_REQUEST['reenterPassword'])) ? $_REQUEST['reenterPassword'] : "";
	$userID = $_SESSION['userID'];
	//--------------------------------------------------------------------------------------------------------

	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		$message = "";
		
		$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_id=? AND passcode=?"); 
		$stmt->execute(array($userID, MD5($oldPassword)));

		if($stmt->fetchColumn() != 1)
		{
			$message = "Current password you have entered is incorrect. Please try again.";
		}
		else if($newPassword != $reenterPassword)
		{
			$message = "New password and Re-enter password should be the same.";
		}
		else if($oldPassword == $newPassword || $oldPassword == md5($newPassword))
		{
			$message = "New password is the same as current password.";
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