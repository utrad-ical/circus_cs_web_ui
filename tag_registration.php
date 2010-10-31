<?
	session_start();

	include("common.php");
	require_once('class/validator.class.php');

	//------------------------------------------------------------------------------------------------------------------
	// Import $_POST variables 
	//------------------------------------------------------------------------------------------------------------------
	$mode = (isset($_POST['mode']) && ($_POST['mode']==="add" || $_POST['mode']==="delete")) ? $_POST['mode'] : "";
	
	$request = array('sid'         => (isset($_POST['sid'])) ? $_POST['sid'] : "",
	                 'category'    => (isset($_POST['category'])) ? $_POST['category'] : "",
					 'referenceID' => (isset($_POST['referenceID'])) ? $_POST['referenceID'] : "",
					 'tagStr'      => (isset($_POST['tagStr'])) ? $_POST['tagStr'] : "");
	
	$userID = $_SESSION['userID'];
	$params = array();
	$message == "";
	//------------------------------------------------------------------------------------------------------------------	

	//-----------------------------------------------------------------------------------------------------------------
	// Validation
	//-----------------------------------------------------------------------------------------------------------------
	$validator = new FormValidator();
	
	$validator->addRules(array(
		"category" => array(
			"type" => "int",
			"min" => 1,
			"max" => 5,
			"required" => 1,
			"errorMes" => "Cagegory is invalid."),
			"referenceID" => array(
			"type" => "int",
			"min" => 1,
			"required" => 1,
			"errorMes" => "Reference ID is invalid."),
		));	

	if($mode==='add')
	{
		$validator->addRules(array(
			"tagStr" => array(
				"type" => "string",
				"required" => 1,
				"errorMes" => "Entered tag is invalid.")
			));
	}	
	else if($mode==='delete')
	{
		$validator->addRules(array(
			"sid" => array(
			"type" => "int",
			"min" => 1,
			"required" => 1,
			"errorMes" => "[ERROR] SID is invalid."),
		));	
	}
	else
	{
		$message = "'mode' is invalid. ";
	}
	
	if($validator->validate($request))
	{
		$params = $validator->output;
		$params['errorMessage'] = "";
	}
	else
	{
		$params = $request;
		$params['errorMessage'] = sprintf("%s%s", $message, implode('<br/>', $validator->errors));
	}
	$params['mode'] = $mode;
	//-----------------------------------------------------------------------------------------------------------------

	$dstData = array('message'        => $params['errorMessage'],
					 'parentTagHtml'  => "",
					 'popupTableHtml' => "");
					 
	try
	{	
	
		if($dstData['message'] == "")
		{
			// Connect to SQL Server
			$pdo = new PDO($connStrPDO);
			
			if($params['mode']=="add")
			{
				$sqlStr= "SELECT COUNT(*) FROM tag_list WHERE category=? AND reference_id=? AND tag=?";
	
				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindValue(1, $params['category']);
				$stmt->bindValue(2, $params['referenceID']);
				$stmt->bindValue(3, $params['tagStr']);
				$stmt->execute();
	
				if($stmt->fetchColumn() > 0)
				{
					$dstData['message'] = '[ERROR] "' . $params['tagStr'] . '" was already registered.';
				}
				else
				{
					$sqlStr = "INSERT INTO tag_list(category,reference_id,tag,entered_by)VALUES(?,?,?,?)";
					$stmt = $pdo->prepare($sqlStr);
					$stmt->bindValue(1, $params['category']);
					$stmt->bindValue(2, $params['referenceID']);
					$stmt->bindValue(3, $params['tagStr']);
					$stmt->bindValue(4, $userID);
					$stmt->execute();
				
					if($stmt->rowCount() != 1)
					{
						//$err = $stmt->errorInfo();
						$dstData['message'] .= "[ERROR] Fail to add new tag.";
					}
				}
			}
			else if($params['mode']=="delete")
			{
				$stmt = $pdo->prepare("DELETE FROM tag_list WHERE sid=?");
				$stmt->bindValue(1, $params['sid']);
				$stmt->execute();
				$tagID = $stmt->fetchColumn();
	
				if($stmt->rowCount() != 1)
				{
					//$err = $stmt->errorInfo();
					$dstData['message'] .= "[ERROR] Fail to delete the tag (" . $params['tagStr'] . ")"; 
				}
		
			}
					
			if($dstData['message'] == "")
			{
				//$dstData['message'] .= 'Successfully registered.';
				
				$sqlStr = "SELECT sid, tag, entered_by FROM tag_list"
						. " WHERE category=? AND reference_id=? ORDER BY sid ASC";
				
				$stmt = $pdo->prepare($sqlStr);
				$stmt->bindValue(1, $params['category']);
				$stmt->bindValue(2, $params['referenceID']);
				$stmt->execute();
				$rowCnt = $stmt->rowCount();
				
				if($params['category'] >=3)
				{
					$dstData['parentTagHtml'] = "Tags:";
				}
				
				$cnt = 1;
				
				while($result = $stmt->fetch(PDO::FETCH_NUM))
				{
					if($params['category'] ==3)
					{				
						$dstData['parentTagHtml']  .= ' <a href="series_list.php?filterTag=' . $result[1] . '">'
												   .  $result[1] . '</a> ';
					}
					else if($params['category'] ==3)
					{				
						$dstData['parentTagHtml']  .= ' <a href="cad_log.php?filterTag=' . $result[1] . '">'
												   .  $result[1] . '</a> ';
					}
					$dstData['popupTableHtml'] .= '<tr><td>' . $cnt . '</td>'
					                           .  '<td id="tagStr' . $result[0] . '" class="al-l">' . $result[1] . '</td>'
					                           .  '<td class="al-l">' . $result[2] . '</td>'
											   .  '<td class="al-l">'
											   .  '<input type="button" id="del'.$cnt.'" class="s-btn form-btn" value="delete"'
											   .  ' onclick="EditTag(\'delete\',' . $result[0] . ');"/>'
											   .  '</td>'
											   .  '</tr>';
					$cnt++;
				}
				
				if($params['category'] >=3)
				{	
					$dstData['parentTagHtml'] .= '<a href="#" onclick="EditTag(' . $params['sid'] . ');">(Edit)</a>';
				}
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
