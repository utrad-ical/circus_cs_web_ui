<?

	//session_cache_limiter('none');
	session_start();

	include("../common.php");

	//------------------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//------------------------------------------------------------------------------------------------------------------
	$execID = (isset($_POST['execID'])) ? $_POST['execID'] : "";
	$pluginType = (isset($_POST['pluginType'])) ? $_POST['pluginType'] : 1;
	$tagID  = (isset($_POST['tagID'])) ? $_POST['tagID'] : 0;
	$tagStr = (isset($_POST['tagStr'])) ? $_POST['tagStr'] : "";
	$mode = (isset($_POST['mode'])) ? $_POST['mode'] : "add";
	
	$userID = $_SESSION['userID'];
	//------------------------------------------------------------------------------------------------------------------	

	$dstData = array('message'        => "",
					 'parentTagHtml'  => "",
					 'popupTableHtml' => "");

	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);
		
		if($mode=="add")
		{

			$stmt = $pdo->prepare("SELECT COUNT(*) FROM executed_plugin_tag WHERE exec_id=? AND tag=?");
			$stmt->execute(array($execID, $tagStr));

			if($stmt->fetchColumn() > 0)
			{
				$dstData['message'] = '[Error] "' . $tagStr . '" was already registered.';
			}
			else
			{
				$stmt = $pdo->prepare("SELECT MAX(tag_id) FROM executed_plugin_tag WHERE exec_id=?");
				$stmt->bindValue(1, $execID);
				$stmt->execute();
				$tagID = $stmt->fetchColumn();

				$sqlStr = "INSERT INTO executed_plugin_tag (exec_id, tag_id, tag, entered_by) VALUES (?, ?, ?, ?)";
				$stmt = $pdo->prepare($sqlStr);
				$stmt->execute(array($execID, $tagID+1, $tagStr, $userID));
			
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
			$stmt = $pdo->prepare("DELETE FROM executed_plugin_tag WHERE exec_id=? AND tag=?");
			$stmt->execute(array($execID, $tagStr));
			$tagID = $stmt->fetchColumn();

			if($stmt->rowCount() != 1)
			{
				$err = $stmt->errorInfo();
				$dstData['message'] .= $err[2];
			}
	
		}
				
		if($dstData['message'] == "")
		{
			//$dstData['message'] .= 'Successfully registered.';
			
			$stmt = $pdo->prepare("SELECT tag_id, tag FROM executed_plugin_tag WHERE exec_id=? ORDER BY tag_id ASC");
			$stmt->bindValue(1, $execID);
			$stmt->execute();
			$rowCnt = $stmt->rowCount();
			
			$dstData['parentTagHtml'] = "Tags:";
			
			while($result = $stmt->fetch(PDO::FETCH_NUM))
			{
				if($pluginType == 1)
				{
					$dstData['parentTagHtml']  .= ' <a href="../cad_log.php?filterTag='.$result[1].'">'.$result[1].'</a> ';
				}
				else if($pluginType == 2)
				{
					$dstData['parentTagHtml']  .= ' <a href="research_list.php?filterTag='.$result[1].'">'.$result[1].'</a> ';
				}
				
				$dstData['popupTableHtml'] .= '<tr><td id="' . $result[0] . '">' . $result[0] . '</td>'
				                           .  '<td id="tagStr' . $result[0] . '" class="al-l" style="width:200px;">' . $result[1] . '</td>'
										   .  '<td class="al-l">'
										   .  '<input type="button" id="del'.$result[0].'" class="s-btn form-btn" value="delete" onclick="DeleteTag('.$result[0].');"/>'
										   .  '</td>'
										   .  '</tr>';
			}

			if($_SESSION['researchFlg']==1)
			{
				$dstData['parentTagHtml'] .= '<a href="#" onclick="EditTag(' . $execID . ');">(Edit)</a>';
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
