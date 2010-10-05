<?php
	session_start();

	include("../common.php");
	
	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//--------------------------------------------------------------------------------------------------------
	$userID       = $_SESSION['userID'];
	$mode         = (isset($_POST['mode'])) ? $_POST['mode'] : "";
	$cadName      = (isset($_POST['cadName'])) ? $_POST['cadName'] : "";
	$version      = (isset($_POST['version'])) ? $_POST['version'] : "";
	$sortKey      = (isset($_POST['sortKey'])) ? $_POST['sortKey'] : "";
	$sortOrder    = (isset($_POST['sortOrder'])) ? $_POST['sortOrder'] : "";
	$maxDispNum   = (isset($_POST['maxDispNum'])) ? $_POST['maxDispNum'] : "";
	$confidenceTh = (isset($_POST['confidenceTh'])) ? $_POST['confidenceTh'] : "";
	
	if(preg_match('/^all/i', $maxDispNum)==1)
	{
		$maxDispNum = 0;
	}
	else if($maxDispNum <= 0)
	{
		$maxDispNum = 1;
	}

	$dstData = array('preferenceFlg' => (isset($_REQUEST['preferenceFlg']) && $_REQUEST['preferenceFlg'] == 1) ? 1 : 0,
			         'message'       => "",
					 'newMaxDispNum' => $maxDispNum);

	//--------------------------------------------------------------------------------------------------------
	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		//----------------------------------------------------------------------------------------------------
		// regist or delete prefence
		//----------------------------------------------------------------------------------------------------
		$sqlParam = array(':userID'  => $userID,
		                  ':cadName' => $cadName,
						  ':version' => $version);
		$sqlStr = "";
		
		if($mode == 'update')
		{
			$sqlParam[':sortKey']      = $sortKey;
			$sqlParam[':sortOrder']    = $sortOrder;
			$sqlParam[':maxDispNum']   = $maxDispNum;
			$sqlParam[':confidenceTh'] = $confidenceTh;		
		
			if($dstData['preferenceFlg'] == 0)
			{
				$sqlStr  = "INSERT INTO cad_preference(user_id, cad_name, version, default_sort_key, "
				         . "default_sort_order, max_disp_num, confidence_threshold) VALUES "
						 . "(:userID, :cadName, :version, :sortKey, :sortOrder, :maxDispNum, :confidenceTh)";
			}
			else
			{
				$sqlStr = 'UPDATE cad_preference SET'
				        . " default_sort_key=:sortKey, default_sort_order=:sortOrder,"
						. " max_disp_num=:maxDispNum, confidence_threshold=:confidenceTh"
						. " WHERE user_id=:userID AND cad_name=:cadName AND version=:version";
			}
		}
		else if($mode == 'delete')	// restore default settings
		{
			$sqlStr = "DELETE FROM cad_preference WHERE user_id=:userID AND cad_name=:cadName AND version=:version";
		}
	
		if($sqlStr != "")
		{
			$stmt = $pdo->prepare($sqlStr);
			$stmt->execute($sqlParam);

			if($stmt->rowCount() == 1)
			{
				$dstData['message'] = 'Successed!';
				$dstData['preferenceFlg'] = ($mode == 'delete') ? 0 : 1;
			}
			else
			{
				$tmp = $stmt->errorInfo();
				$dstData['message'] = $tmp[2];
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


