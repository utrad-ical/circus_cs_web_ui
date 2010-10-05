<?

	//session_cache_limiter('none');
	session_start();

	include("../common.php");

	//------------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//------------------------------------------------------------------------------------------------------------------
	$execID = (isset($_POST['execID'])) ? $_POST['execID'] : "";
	$candID = (isset($_POST['candID'])) ? $_POST['candID'] : "";
	$tagID  = (isset($_POST['tagID'])) ? $_POST['tagID'] : 0;
	$tagStr = (isset($_POST['tagStr'])) ? $_POST['tagStr'] : "";
	$feedbackMode = (isset($_POST['feedbackMode'])) ? $_POST['feedbackMode'] : "personal";
	$mode = (isset($_POST['mode'])) ? $_POST['mode'] : "add";
	
	$userID = (isset($_POST['userID'])) ? $_POST['userID'] : "";
	//------------------------------------------------------------------------------------------------------------------	

	$dstData = array('message'        => "",
					 'parentTagHtml'  => "",
					 'popupTableHtml' => "",
					 'candID'         => $candID);

	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
		
		if($mode=="add")
		{
			$sqlStr .= "SELECT COUNT(*) FROM lesion_candidate_tag WHERE exec_id=? AND candidate_id=? AND tag=?";

			if($feedbackMode == "consensual")  $sqlStr .= " AND consensual_flg='t'";
			else							   $sqlStr .= " AND consensual_flg='f' AND entered_by=?";			

			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindValue(1, $execID);
			$stmt->bindValue(2, $candID);
			$stmt->bindValue(3, $tagStr);
			if($feedbackMode == "personal")  $stmt->bindValue(4, $userID);
			
			$stmt->execute();

			if($stmt->fetchColumn() > 0)
			{
				$dstData['message'] = '[Error] "' . $tagStr . '" was already registered.';
			}
			else
			{
				$sqlStr = "SELECT MAX(tag_id) FROM lesion_candidate_tag WHERE exec_id=? AND candidate_id=?";
				
				if($feedbackMode == "consensual")  $sqlStr .= " AND consensual_flg='t'";
				else							   $sqlStr .= " AND consensual_flg='f' AND entered_by=?";

				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindValue(1, $execID);
				$stmt->bindValue(2, $candID);
				if($feedbackMode == "personal")  $stmt->bindValue(3, $userID);
				
				$stmt->execute();
				$tagID = $stmt->fetchColumn();

				$sqlStr = "INSERT INTO lesion_candidate_tag (exec_id, candidate_id, tag_id, tag, entered_by, consensual_flg)"
						. " VALUES (?, ?, ?, ?, ?,";

				if($feedbackMode == "consensual")  $sqlStr .= "'t')";
				else							   $sqlStr .= "'f')";
				
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute(array($execID, $candID, $tagID+1, $tagStr, $userID));
			
				if($stmt->rowCount() != 1)
				{
					$err = $stmt->errorInfo();
					$dstData['message'] .= $err[2];
				}
			}
		}
		else if($mode=="update")
		{
		
		
		
		}
		else if($mode=="delete")
		{
			$sqlStr = "DELETE FROM lesion_candidate_tag WHERE exec_id=? AND candidate_id=? AND tag=?";
			
			if($feedbackMode == "consensual")  $sqlStr .= " AND consensual_flg='t'";
			else							   $sqlStr .= " AND consensual_flg='f' AND entered_by=?";
		
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindValue(1, $execID);
			$stmt->bindValue(2, $candID);
			$stmt->bindValue(3, $tagStr);
			if($feedbackMode == "personal")  $stmt->bindValue(4, $userID);		
		
			$stmt->execute();

			if($stmt->rowCount() != 1)
			{
				$err = $stmt->errorInfo();
				$dstData['message'] .= $err[2];
			}
	
		}
				
		if($dstData['message'] == "")
		{
			//$dstData['message'] .= 'Successfully registered.';
			
			$sqlStr = "SELECT tag_id, tag FROM lesion_candidate_tag WHERE exec_id=? AND candidate_id=?";
			
			if($feedbackMode == "consensual")  $sqlStr .= " AND consensual_flg='t'";
			else							   $sqlStr .= " AND consensual_flg='f' AND entered_by=?";

			$sqlStr .= " ORDER BY tag_id ASC";
			
			$stmt = $pdo->prepare($sqlStr);
			$stmt->bindValue(1, $execID);
			$stmt->bindValue(2, $candID);
			if($feedbackMode == "personal")  $stmt->bindParam(3, $userID);

			$stmt->execute();
			
			$dstData['parentTagHtml'] = "Tags:";
			
			while($result = $stmt->fetch(PDO::FETCH_NUM))
			{
				$dstData['parentTagHtml'] .= " " . $result[1];
				
				$dstData['popupTableHtml'] .= '<tr><td id="' . $result[0] . '">' . $result[0] . '</td>'
				                           .  '<td id="tagStr' . $result[0] . '" class="al-l" style="width:200px;">' . $result[1] . '</td>'
										   .  '<td class="al-l">'
										   .  '<input type="button" id="del'.$result[0].'" class="s-btn form-btn" value="delete" onclick="DeleteTag('.$result[0].');"/>'
										   .  '</td>'
										   .  '</tr>';
			}

			if($_SESSION['researchFlg']==1)
			{
				$dstData['parentTagHtml'] .= ' <a href="#" onclick="EditCandidateTag(' . $execID . ',' . $candID
										  .  ',\'' . $feedbackMode . '\',\'' . $userID . '\');">(Edit)</a></p>';
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
