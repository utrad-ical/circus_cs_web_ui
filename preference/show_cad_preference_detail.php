<?php
	session_start();

	include("../common.php");
	
	//--------------------------------------------------------------------------------------------------------
	// Import $_REQUEST variables 
	//--------------------------------------------------------------------------------------------------------
	$params = array('cadName'             => (isset($_REQUEST['cadName'])) ? $_REQUEST['cadName'] : "",
				    'version'             => (isset($_REQUEST['version'])) ? $_REQUEST['version'] : "",
				    'userID'              =>  $_SESSION['userID'],
				    'preferenceFlg'       => 0,
			        'sortKey'             => "",
				    'sortOrder'           => "",
				    'maxDispNum'          => "",
				    'confidenceTh'        => "",
				    'defaultSortKey'      => "",
				    'defaultSortOrder'    => "",
				    'defaultMaxDispNum'   => "",
				    'defaultConfidenceTh' => "",
				    'message'             => "&nbsp;");
	//--------------------------------------------------------------------------------------------------------

	try
	{	
		// Connect to SQL Server
		$pdo = new PDO($connStrPDO);

		$stmt = $pdo->prepare("SELECT * FROM cad_master WHERE cad_name=? AND version=?");	
		$stmt->execute(array($params['cadName'], $params['version']));
		
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		
		$params['defaultSortKey']      = $result['default_sort_key'];
		$params['defaultSortOrder']    = ($result['default_sort_order']==true) ? "t" : "f";
		$params['defaultMaxDispNum']   = $result['max_disp_num'];
		$params['defaultConfidenceTh'] = $result['confidence_threshold'];
		
		$stmt = $pdo->prepare("SELECT * FROM cad_preference WHERE cad_name=? AND version=? AND user_id=?");	
		$stmt->execute(array($params['cadName'], $params['version'], $params['userID']));
	
		if($stmt->rowCount() == 1)
		{
			$params['preferenceFlg'] = 1;

			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$params['sortKey']      = $result['default_sort_key'];
			$params['sortOrder']    = ($result['default_sort_order']==true) ? "t" : "f";
			$params['maxDispNum']   = ($result['max_disp_num']==0) ? "all" : $result['max_disp_num'];
			$params['confidenceTh'] = $result['confidence_threshold'];
		}
		else
		{
			$params['preferenceFlg'] = 0;
			$params['message']      = 'Default settings.';
			$params['sortKey']      = $params['defaultSortKey'];
			$params['sortOrder']    = $params['defaultSortOrder'];
			$params['maxDispNum']   = $params['defaultMaxDispNum'];
			$params['confidenceTh'] = $params['defaultConfidenceTh'];
		}
		
		echo json_encode($params);
	}
	catch (PDOException $e)
	{
		var_dump($e->getMessage());
	}
	$pdo = null;	

?>


