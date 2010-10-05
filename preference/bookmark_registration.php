<?php

	session_start();

	include("../common.php");

	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variable
	//--------------------------------------------------------------------------------------------------------
	$label = (isset($_REQUEST['label'])) ? $_REQUEST['label'] : "(no label)";
	$title = (isset($_REQUEST['title'])) ? $_REQUEST['title'] : "(Untitled)";
	$address = (isset($_REQUEST['address'])) ? $_REQUEST['address'] : "";
	$comment = (isset($_REQUEST['comment'])) ? $_REQUEST['comment'] : "";

	if($title == "")   $title = "(Untitled)";
	if($label == "")   $label = "(no label)";
	if($address == "")  die("Error: address is not entered!!");

	$userID = $_SESSION['userID'];
	//--------------------------------------------------------------------------------------------------------
	
	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
	
		$stmt = $pdo->prepare("SELECT count(*), MAX(sub_id) FROM personal_bookmark WHERE user_id=?");
		$stmt->bindParam(1, $userID);
		$stmt->execute()
	
		$rowCnt = $stmt->fetchColumn();
		$sub_id = ($rowCnt == 0) ? 1 : ($stmt->fetchColumn()+1);
		
		$sqlStr = 'INSERT INTO personal_bookmark (user_id, sub_id, label, title, address, "comment") VALUES '
				. "(?, ?, ?, ?, ?, ?)";
		$stmt = $pdo->prepare($sqlStr);
		$stmt->execute(array($userID, $sub_id, $label, $title, $address, $comment));
						
		if($stmt->rowCount() == 1)	echo "Success!!";
		else						echo "Faied!!";
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}

	$pdo = null;	

?>
